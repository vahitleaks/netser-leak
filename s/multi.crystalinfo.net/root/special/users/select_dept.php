<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
      define("SPIMAGE_ROOT", "/special/images/");
   $cUtility = new Utility();
   $cdb = new db_layer();
   session_cache_limiter('nocache');
   require_valid_login();
   $conn = $cdb->getConnection();
   //Site Admin veya Admin Hakký yoksa bu tanýmý yapamamalý
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Burayý Görme Hakkýnýz Yok");
    exit;
   }

   page_track();
   cc_page_meta(0);
   $img_dir_open = SPIMAGE_ROOT."eksi.gif";
   $img_dir_close = SPIMAGE_ROOT ."arti.gif";
   $img_item = SPIMAGE_ROOT."eksi.gif";
   $someString="";
   if($USER_ID=="")
     die;
   if ($SITE_ID=="" || $SITE_ID=="-1")
       $SITE_ID = $SESSION['site_id'];

   $sql_str = "SELECT NAME, SURNAME, USERNAME FROM USERS WHERE USER_ID = $USER_ID";
   if (!($cdb->execute_sql($sql_str,$resusr,$error_msg))){
          print_error($error_msg);
          exit;
  }
  if (mysql_numrows($resusr)>0){
    $rwUsr = mysql_fetch_object($resusr);
    $NAME = $rwUsr->NAME;
    $SURNAME = $rwUsr->SURNAME;
    $USERNAME = $rwUsr->USERNAME;
  }
?>   
<style>
  .button {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight : bold;
	color : #FFFFFF;
	background-color: #7C99B6;
	border: 1px solid #415a75;
    cursor:hand;
}
</style>
   <script language='javascript'>
   var arrSayac=0;
   var jdivArr=new Array();
   var ct_image_dir = new Image();ct_image_dir.src="<?=$img_dir_open?>";
   var ct_image_diropen = new Image();ct_image_dir.src="<?=$img_dir_close?>";
   var ct_image_item = new Image();ct_image_dir.src="<?=$img_item?>";
   function setExpandedIco(id){var i=document.getElementById(id+'_codethat_image');i.src='<?=$img_dir_open?>';}
        function setCollapsedIco(id){var i=document.getElementById(id+'_codethat_image');i.src='<?=$img_dir_close?>';}
        function toggleNode(id){if(toggleLayer(id+'_codethat_subitems'))setExpandedIco(id);else setCollapsedIco(id);}
        function toggleLayer(id){var l=document.getElementById(id);var s=l.style||l;if(s.visibility=='hidden'){s.visibility='visible';s.display='block';return true;}else{s.visibility='hidden';s.display='none';return false;}}

    function colloapseAll(){var l, s;for(i=0;i<arrSayac;i++){l=document.getElementById(jdivArr[i]+'_codethat_subitems');if(l!=null){s=l.style||l;s.visibility='hidden';s.display='none';setCollapsedIco(jdivArr[i]);}}}
    function expandAll(){var l, s;for(i=0;i<arrSayac;i++){l=document.getElementById(jdivArr[i]+'_codethat_subitems');if(l!=null){s=l.style||l;s.visibility='visible';s.display='block';setExpandedIco(jdivArr[i]);}}}
    
    function checkControl(chkOb, id){
      var divOb = document.getElementById(id+'_codethat_subitems');
      if(divOb==null) return;
      chkObArr = divOb.getElementsByTagName('INPUT');
      for(i=0;i<chkObArr.length;i++){
        if(chkObArr[i].type.toUpperCase()=='CHECKBOX'){
          chkObArr[i].checked=chkOb.checked;
        }
      }
    }
    
    function showChecked(){
      var divOb = document.getElementById('divTreeMain');
      if(divOb==null) return;
      chkObArr = divOb.getElementsByTagName('INPUT');
      for(i=0;i<chkObArr.length;i++){
        if(chkObArr[i].type.toUpperCase()=='CHECKBOX'){
          if(chkObArr[i].checked){
            var tmpOb = chkObArr[i].parentNode;
            while(tmpOb!=null && tmpOb.id!='divTreeMain'){
              if(tmpOb.tagName.toLowerCase()=='div'){
                var tmpId=tmpOb.id;
                if(tmpId.indexOf('_codethat_subitems')>0){
                  tmpId=tmpId.replace('_codethat_subitems','');
                  if(document.getElementById(tmpId+'_codethat_image')!=null){
                    document.getElementById(tmpId+'_codethat_image').src='<?=$img_dir_open?>';
                  }
                }
                tmpOb.style.visibility='visible';
                tmpOb.style.display='block';
              }
              tmpOb = tmpOb.parentNode;
            }
          }
        }
      }
    }
    
    
   </script>


<br><center>
<?
  table_header("Raporunu Alabileceði Departmanlar","800");
  $chkBoxes = 0;
?>
  <form name="site" action="select_dept.php" method="post">
  <input type="hidden" name="USER_ID" value="<?=$USER_ID?>">
  <table width="100%" cellpadding="1" cellspacing="1" border="0">
    <tr>
      <td>
      <strong>Kullancý :</strong><?=$NAME." ".$SURNAME," (".$USERNAME.")"?>
      
    <table width="100%">
    <tr>
      <td>Site</td>
      <td>
         <select name="SITE_ID" class="select1" style="width:200" onchange="site.submit();">
            <?
                $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES";
                echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $SITE_ID);
            ?>
         </select>
<?
    $sqlStr = "SELECT * FROM DEPT_REP_RIGHTS where SITE_ID=$SITE_ID and USER_ID=$USER_ID and DEPT_ID=0";
    if (!($cdb->execute_sql($sqlStr, $resSite,$error_msg))){
      print_error($error_msg);
      exit;
    }
    if (mysql_numrows($resSite)>0){
?>
      </td>
    </tr>
    <tr>
      <td align="left" colspan="2">Kullanýcý bu sitenin tüm raporlarýný alabilmektedir.
         &nbsp;
         <input type="button" class="button" value="Tüm Site Hakkýný Çýkar" onclick="saveSiteRight('del');">
      </td>
    </tr>
    
<?
    }else{
?>
         &nbsp;
         <input type="button" class="button" value="Tüm Siteye Hak Ver" onclick="saveSiteRight('add');">
      </td>
    </tr>
    <tr>
      <td align="right" colspan="2">
      <input type="button" value="Collapse all" onclick="colloapseAll();" class="button">&nbsp;&nbsp;
      <input type="button" value="Expand all" onclick="expandAll();" class="button">
      </td>
    </tr>
    <tr>
      <td colspan="2">
      <div id="divTreeMain">
      <?=ReadDefLevel(0, 0,$someString)?>
      </div>
      <input type="hidden" name="chkBoxes" value="<?=$chkBoxes?>">
      </td>
    </tr>
<?
    }
?>
  </table>
    </td>
  </tr>
</table>
</form>
<?
  table_footer();
?>

<div id="saveRightsBtn" style="position:absolute;top:0;background-color:#9eBBD8;width:150;height:50;border:1px solid black;z-index:10000;<?if($chkBoxes==0){echo "display:none;";}?>">
<table width="100%" height="100%">
  <tr>
    <td align="center" valign="middle"><input type="button" class="button" value="Haklarý Kaydet" onclick="saveDeptRights();"></td>
  </tr>
</table>
</div>
</center>
<script language="JavaScript">
  function saveDeptRights(){
    site.action='select_dept_db.php';
    site.submit();
  }
  
  function saveSiteRight(sType){
    site.action='select_dept_db.php?act=site'+sType;
    site.submit();
  }
  
  function positionSaveButton(){
      var objDiv = document.getElementById('saveRightsBtn');
      var yPos='ie5'? document.body.scrollTop+((document.body.clientHeight)/2) : window.pageYOffset+(window.innerHeight)/2;    
      var xPos='ie5'? document.body.scrollLeft+((document.body.clientWidth)-155) : window.pageYOffset+(window.innerHeight)/2;    
      objDiv.style.top=document.body.scrollTop;
      objDiv.style.left=xPos;
  }
  positionSaveButton();
  showChecked();
  setInterval("positionSaveButton();", 100);
</script>
<?
  function  ReadDefLevel($id, $level, &$xLastOne){
     global $cdb;
     global $SITE_ID;global $USER_ID;global $chkBoxes;
     $strng = "";
     if($level=="") {$level=0;}
	 //' Prepare and execute the SQL query statement 

	 $query = " SELECT InDeptId AS node_id, InMainDeptId AS node_parent, StDeptName AS node_name, StMailAddress, REP_ID 
      FROM TbDepartments a 
        left join DEPT_REP_RIGHTS b on b.DEPT_ID = a.InDeptId and b.USER_ID=".$USER_ID." and b.SITE_ID=".$SITE_ID." 
      WHERE InMainDeptId = ".$id." and InSiteId='".$SITE_ID."'";
      //echo $query ;
      if (!($cdb->execute_sql($query,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
       
	 //' Loop on results 
	 $class_counter = 0;
     $recCnt=mysql_num_rows($result);
     if($recCnt<=0 && $id==0)
      return "Bu sitede Hiç departman tanýmlanmamýþ!";
	 while($row = mysql_fetch_object($result)){
        $clss        = "link"   ;
        $node_id     = $row->node_id;
        $node_parent = $row->node_parent;
        $node_name   = $row->node_id."-".$row->node_name." (".$row->StMailAddress.")";

        $lastOne=0;
        if($recCnt==$class_counter+1) {$lastOne=1;}
       // setLevelStat($xLastOne, $level, $lastOne);
       $xLastOne[$level] = $lastOne;
       if($level==0){$visible=true;}

        $subitems= ReadDefLevel($node_id, $level+1,$xLastOne);
        $url_link = " value='".$node_id."' onclick=\"checkControl(this, '".$node_id."');\" " ;
        $visible=false;
        if($row->REP_ID!=null && $row->REP_ID!=""){
          $url_link .= " checked " ;
        }
        if ($subitems == "")
          $clss="link1";
        $strng=$strng.CreateItem($node_id, $node_name, $url_link, $clss, $subitems,$node_parent, $level, $visible, $lastOne, $xLastOne);
        $class_counter = $class_counter+1;
      }
	return $strng;
  }

 function CreateItem($id,$text,$url,$css,$subitems,$sub_id,$level,$visible, $lastOne, &$xLastOne){
   global $chkBoxes;
   $img_item = SPIMAGE_ROOT."eksi.gif";
   $img_dir_close = SPIMAGE_ROOT."arti.gif";
   if($subitems==""){
     $image = "\"".$img_item . "\"";
   }else{
     $image = "\"" . $img_dir_close . "\"";
   }
   if($visible){
     $image = "\"".$img_item."\"";
   }
   $imgtag = "<img border=0 id=\"" .$id . "_codethat_image\" src=" . $image . ">";
   $td="";
  // $myLastOne = split(",",$xLastOne);
   for ($i=0;$i<=$level;$i++){
      if ($i==$level ){
        if ($lastOne==1)
          $td.="<td width=11px align=\"left\"><img src='".SPIMAGE_ROOT."br_line.gif'></td>";
        else
          $td.="<td width=11px align=\"left\"><img src='".SPIMAGE_ROOT."hr_line.gif'></td>";
      }else{
        if($xLastOne[$i])
          $td.="<td width=11px align=\"left\"></td>";
        else
          $td.="<td width=11px align=\"left\"><img src='".SPIMAGE_ROOT."vr_line.gif'></td>";
      }
   }
   $atag = "href=\"".$url."\"";
   $updLink="<td width='13px' nowrap>&nbsp;</td>\n<td width='13px' nowrap>&nbsp;</td>\n<td width='13px' nowrap>&nbsp;</td>\n";
   if($subitems==""){
     $chkBoxes++;
     $html="<table cellpadding=1 cellspacing=0 border=0 width=\"100%\"><tr onmouseover=\"this.style.backgroundColor='#FEF6DC';\" onmouseout=\"this.style.backgroundColor='';\">".$updLink.$td."<td width='11px'>".$imgtag."</td><td align=left><p><INPUT type=\"CHECKBOX\" name='chkbox_".$chkBoxes."' id=\"chkbox_".$chkBoxes."\" $url>".$text."</p></td></tr></table></a>\n";
   }else{
     $chkBoxes++;
     $html="<table cellpadding=1 cellspacing=0 border=0 width=\"100%\"><tr onmouseover=\"this.style.backgroundColor='#FEF6DC';\" onmouseout=\"this.style.backgroundColor='';\">".$updLink.$td."<td width='11px'><a  class=link href=\"javascript:toggleNode('".$id."');\">".$imgtag."</a></td><td align=left><p><INPUT type=\"CHECKBOX\" name='chkbox_".$chkBoxes."' id=\"chkbox_".$chkBoxes."\" $url>".$text."</p></td></tr></table></a>" ;
   }
   $html = $html."<script language=\"javascript\">jdivArr[arrSayac]='".$id."';arrSayac++;</script>\n" ;
   // We create item as one main layer
   // Id, // Relative, // Top, // Left, // Width, // Height, // Css class, // Background color,
   // URL of background image, // Is it visible?, // Z index, // HTML text, // Events)
   $src=CreateLayer($id, true, "", "", "", "", "link", "", "", true, 1,  $html, "");
   if($subitems!=""){
      $src.=CreateLayer($id."_codethat_subitems",true,"","","","","","","",$visible,1,$subitems,"");
   }
   return $src;
 }

  function CreateLayer($id,$relative,$xtop,$xleft,$xwidth,$xheight,$css,$bgColor,$bgImage,$visible,$zIndex,$html,$events){
   $src = "";
   $src .= "<div ";
   $src .= myParam("id",$id,"=","\""," ");
   $src .= myParam("class",$css,"=","\""," ");
   $style="";
   if($relative )
     $style .= myParam("position","relative",":","",";");
   else
     $style .= myParam("position","absolute",":","",";");
   $style .= myParam("overflow","hidden",":","",";");
   if($visible ){
     $style .= myParam("visibility","visible",":","",";");
     $style .= myParam("display","block",":","",";");
   }else{
     $style .= myParam("visibility","hidden",":","",";");
     $style .= myParam("display","none",":","",";");
   }
   $style .= myParam("top","0",":","",";");
   $style .= myParam("left","0",":","",";");
   $style .= myParam("width",$xwidth,":","",";");
   $style .= myParam("height",$xheight,":","",";");
   $style .= myParam("z-index",$zIndex,":","",";");
   $style .= myParam("background-color",$bgColor,":","",";");
   $style .= myParam("background-image",$bgImage,":","",";");
   $src .= myParam("style",$style,"=","\"","");
   $src .=  ">";
   $src .=  $html;
   $src .= "</div>\n";
   return $src;
 }

 function myParam($name, $value, $equal, $brackets, $post){
   $strng="";
   if ($value!="") {
      $strng = $name.$equal.$brackets.$value.$brackets.$post;
   }
   return $strng;
 }

 function setLevelStat(&$lvlStr, $lvl, $stat){
   $myArray = split(",",$lvlStr);
   if ($lvl>count($myArray)+1 ){
     if ($lvlStr!=""){ $lvlStr=$lvlStr.",";}
     $lvlStr=$lvlStr.$stat ;
     return;
   }else{
     $myArray[$lvl]=$stat;
   }
   $retStr="";
   for($i=0;$i<count($myArray);$i++){
     if ($i>0){ $retStr=$retStr.",";}
     $retStr=$retStr&$myArray[$i];
   }
   $lvlStr=$retStr;
 }
 
?>
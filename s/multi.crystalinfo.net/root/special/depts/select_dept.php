<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
      define("SPIMAGE_ROOT", "/special/images/");
   $cUtility = new Utility();
   $cdb = new db_layer();
   session_cache_limiter('nocache');
   require_valid_login();
   $conn = $cdb->getConnection();
   page_track();
   cc_page_meta(0);
   $img_dir_open = SPIMAGE_ROOT."eksi.gif";
   $img_dir_close = SPIMAGE_ROOT ."arti.gif";
   $img_item = SPIMAGE_ROOT."eksi.gif";
   $someString="";
   if ($SITE_ID=="" || $SITE_ID=="-1")
       $SITE_ID = $SESSION['site_id'];
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
   </script>


<br><center>
<?
  table_header("Departmanlar","600");
?>
  <form name="site" action="select_dept.php" method="post">

  <table width="100%" cellpadding="1" cellspacing="1" border="0">
    <tr>
      <td>
    <table width="100%">
    <tr>
      <td align="right">
      <input type="button" value="Collapse all" onclick="colloapseAll();" class="button">&nbsp;&nbsp;
      <input type="button" value="Expand all" onclick="expandAll();" class="button"></td></tr></table>
      <?=ReadDefLevel(0, 0,$someString)?>
      </td>
    </tr>
  </table>
</form>
<?
  table_footer();
?>
    </td>
  </tr>
</table>
</center>
<script language="JavaScript">
  function openRecEdit(defId, subId, stDef, stVal, level){
    var selBox = window.opener.document.getElementById('selDeptId');
    if(selBox!=null){
      for(i=0;i<selBox.options.length;i++){
        if(selBox.options[i].value==defId){
          selBox.selectedIndex=i;
          self.close();
          return true;
        }
      }
    }
  }
 </script>
<?
  function  ReadDefLevel($id, $level, &$xLastOne){
     global $cdb;
     global $SITE_ID;
     $strng = "";
     if($level=="") {$level=0;}
	 //' Prepare and execute the SQL query statement 
	 $query = " SELECT InDeptId AS node_id, InMainDeptId AS node_parent, StDeptName AS node_name, StMailAddress 
      FROM TbDepartments WHERE InMainDeptId = ".$id." and InSiteId='".$SITE_ID."'";
      //echo $query ;
      if (!($cdb->execute_sql($query,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
	 //' Loop on results 
	 $class_counter = 0;
     $recCnt=mysql_num_rows($result);
	 while($row = mysql_fetch_object($result)){
        $clss        = "link"   ;
        $node_id     = $row->node_id;
        $node_parent = $row->node_parent;
        $node_name   = $row->node_id."-".$row->node_name." (".$row->StMailAddress.")";

        $lastOne=0;
        if($recCnt==$class_counter+1) {$lastOne=1;}
        setLevelStat($xLastOne, $level, $lastOne);

        $subitems= ReadDefLevel($node_id, $level+1,$xLastOne);
        $url_link = "javascript:openRecEdit('".$node_id."','', '".$row->node_name."','".$row->StMailAddress."', '".$level."')";
        if ($subitems == "")
          $clss="link1";
        $strng=$strng.CreateItem($node_id, $node_name, $url_link, $clss, $subitems,$node_parent, $level, true, $lastOne, $xLastOne);
        $class_counter = $class_counter+1;
      }
	return $strng;
  }

 function CreateItem($id,$text,$url,$css,$subitems,$sub_id,$level,$visible, $lastOne, &$xLastOne){
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
   for ($i=1;$i<=$level;$i++){
      if ($i==$level ){
        if ($lastOne==1)
          $td.="<td width=11px align=\"left\"><img src='".SPIMAGE_ROOT."br_line.gif'></td>";
        else
          $td.="<td width=11px align=\"left\"><img src='".SPIMAGE_ROOT."hr_line.gif'></td>";
      }else{
        if($myLastOne[i])
          $td.="<td width=11px align=\"left\"></td>";
        else
          $td.="<td width=11px align=\"left\"><img src='".SPIMAGE_ROOT."vr_line.gif'></td>";
      }
   }
   $atag = "href=\"".$url."\"";
   $updLink="<td width='13px' nowrap>&nbsp;</td>\n<td width='13px' nowrap>&nbsp;</td>\n<td width='13px' nowrap>&nbsp;</td>\n";
   if($subitems==""){
     $html="<table cellpadding=1 cellspacing=1 border=0 width=\"100%\"><tr onmouseover=\"this.style.backgroundColor='#FEF6DC';\" onmouseout=\"this.style.backgroundColor='';\">".$updLink.$td."<td width='11px'><a class=link ".$atag.">".$imgtag."</a></td><td align=left><p><a  class=link ".$atag.">".$text."</a></p></td></tr></table></a>\n";
   }else{
     $html="<table cellpadding=1 cellspacing=1 border=0 width=\"100%\"><tr onmouseover=\"this.style.backgroundColor='#FEF6DC';\" onmouseout=\"this.style.backgroundColor='';\">".$updLink.$td."<td width='11px'><a  class=link href=\"javascript:toggleNode('".$id."');\">".$imgtag."</a></td><td align=left><p><a  class=link1 ".$atag." onClick=\"toggleNode('".$id."');\">".$text."</a></p></td></tr></table></a>" ;
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
     $src .= myParam("position","relative",":","",";");
   Else
     $src .= myParam("position","absolute",":","",";");
   $src .= myParam("overflow","hidden",":","",";");
   if($visible ){
     $src .= myParam("visibility","visible",":","",";");
     $src .= myParam("display","block",":","",";");
   }else{
     $src .= myParam("visibility","hidden",":","",";");
     $src .= myParam("display","block",":","",";");
   }
   $src .= myParam("top","0",":","",";");
   $src .= myParam("left","0",":","",";");
   $src .= myParam("width",$xwidth,":","",";");
   $src .= myParam("height",$xheight,":","",";");
   $src .= myParam("z-index",$zIndex,":","",";");
   $src .= myParam("background-color",$bgColor,":","",";");
   $src .= myParam("background-image",$bgImage,":","",";");
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
   }Else{
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
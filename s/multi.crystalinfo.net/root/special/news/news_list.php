<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
//     require_valid_login();
     $conn = $cdb->getConnection();

     $NEWS_LIST_CNT = 20;

     //Determine howmany news items will be listed
     if (!isset($msg_cnt))
    $msg_cnt = $NEWS_LIST_CNT;
     else if ($msg_cnt>200)
       $msg_cnt = $NEWS_LIST_CNT;

   $sql_str = "SELECT NEWS.SIRA_NO, NEWS.USER_ID, NEWS.BASLIK, NEWS.DETAY, ".
               "NEWS.INSERT_DATE,NEWS.INSERT_TIME,NEWS.LEVEL,USERS.NAME,USERS.SURNAME ".  
                "FROM NEWS INNER JOIN USERS ON NEWS.USER_ID = USERS.USER_ID ".
              " ORDER BY INSERT_DATE DESC, INSERT_TIME DESC LIMIT ".$msg_cnt.";";
            
 if (isset($frm_search)){
   $sql_str="SELECT * FROM  NEWS ".
            "WHERE (DETAY LIKE '%".$frm_search ."%') OR (BASLIK LIKE '%".$frm_search ."%' ) ".
            "ORDER BY INSERT_DATE DESC, INSERT_TIME DESC;";
 }

 if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
    print_error($error_msg);
    exit;
 }

?>
<?
   cc_page_meta();
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr> 
    <td valign="top" height="74"> 
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td valign="bottom" height="96" rowspan="2" width="58%"> 
            <table width="775" border="0" cellspacing="0" cellpadding="0">
              <tr> 
                <td height="70" valign="bottom" width="199"><a href="http://www.crystalinfo.net/" target = "_blank"><img src="<?=IMAGE_ROOT?>logo1.gif" border="0"></a></td>
                <td height="70" align="center" width="387" valign="bottom"><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="390" height="70">
                    <param name=movie value="<?=IMAGE_ROOT?>ci_homebanner1.swf">
                    <param name=quality value=high>
                    <embed src="<?=IMAGE_ROOT?>ci_homebanner1.swf" quality=high pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="390" height="70">
                    </embed> 
                  </object></td>
                <td height="70" valign="bottom" rowspan="2" width="189"> 
                  <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr> 
                      <td align="center" valign="bottom"><img src="<?=IMAGE_ROOT?>hp_left.gif" width="14" height="27"><a href="/special/main.php"><img src="<?=IMAGE_ROOT?>hp_home.gif" width="27" height="27" border="0"></a><img src="<?=IMAGE_ROOT?>hp_logout.gif" width="27" height="27" border="0"><img src="<?=IMAGE_ROOT?>hp_login.gif" width="27" height="27" border="0"><img src="<?=IMAGE_ROOT?>hp_passchange.gif" width="27" height="27" border="0"><img src="<?=IMAGE_ROOT?>hp_kisisel.gif" width="27" height="27" border="0"><img src="<?=IMAGE_ROOT?>hp_admin.gif" width="27" height="27" border="0"><img src="<?=IMAGE_ROOT?>hp_right.gif" width="13" height="27"></td>
                    </tr>
                    <tr> 
                      <td background="<?=IMAGE_ROOT?>hp_bg.gif" height="20"></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr> 
                <td background="<?=IMAGE_ROOT?>menu8.gif" colspan="2"><img src="<?=IMAGE_ROOT?>menu1.gif" width="53" height="26"><a href="/redirect.php"><img src="<?=IMAGE_ROOT?>menu3.gif" width="64" height="26" border="0"></a><img src="<?=IMAGE_ROOT?>menu2.gif" width="25" height="26" border="0"><a HREF="/special/news/news_list.php"><img src="<?=IMAGE_ROOT?>menu4.gif" width="76" height="26" border="0"></a><img src="<?=IMAGE_ROOT?>menu2.gif" width="25" height="26" border="0"><img src="<?=IMAGE_ROOT?>menu5.gif" width="59" height="26" border="0"><img src="<?=IMAGE_ROOT?>menu2.gif" width="25" height="26" border="0"><a HREF="/special/fihrist/rehber.php"><img src="<?=IMAGE_ROOT?>menu6.gif" width="49" height="26" border="0"></a><img src="<?=IMAGE_ROOT?>menu2.gif" width="25" height="26" border="0"><a href="#"><img src="<?=IMAGE_ROOT?>menu9.gif" width="60" height="26" border="0" ></a><img src="<?=IMAGE_ROOT?>menu7.gif" width="35" height="26" border="0"></td>
              </tr>
            </table>
          </td>
          <td width="65%" height="70">&nbsp;</td>
        </tr>
        <tr> 
          <td background="<?=IMAGE_ROOT?>menu8.gif" height="26" width="80%">&nbsp;</td>
        </tr>
        <tr> 
          <td height="21" colspan="2" background="<?=IMAGE_ROOT?>bg_menu_alt.gif">
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td valign="top" align="center" height="95%" background="<?=IMAGE_ROOT?>hp_bg1.gif"> 
      <table width="777" border="0" cellspacing="0" cellpadding="0" height="100%" align="left">
        <tr>
          <td valign="top" background="<?=IMAGE_ROOT?>hp_bg2.gif" width="4"></td>
          <td valign="top" bgcolor="#D8E4F1" width="769" align="center"> 
            <center> 

<script language="JavaScript">
function popup ( name, url_which, width_which, height_which, resizable_which, scrollbars_which, titlebar_which ){
  var yenipencere = null;
  yenipencere=window.open('', name ,'width=' + width_which + ',height=' + height_which + ',status=no,toolbar=no,menubar=no,directories=no,location=no,resizable=' + resizable_which + ',scrollbars=' +  scrollbars_which + ',titlebar=' + titlebar_which + ',alwaysRaised=yes,screenX=0,screenY=0,left=250,top=150');
  if (yenipencere != null) {
        if (yenipencere.opener == null){
           yenipencere.opener = self;
        }  
      yenipencere.location.href=url_which;
      yenipencere.focus();
  }
}
function go (url,prm1){
    if (prm1==""){
      location.href = url+"0";
    }else{
      location.href = url+prm1;
    }  
}
</script>
<script language="javascript">
function haber_ara() {
  if (document.haber_search.frm_search.value == "")
    alert ("Arama kriteri girmediniz");
  else
    document.haber_search.submit();
}

</script>

<? 
table_header("Haberler Listesi","90%"); 
?>
<img border="0" src="<?=IMAGE_ROOT ?>positive.gif" width="14" height="7">&nbsp;Olumlu Haberler 
<img border="0" src="<?=IMAGE_ROOT ?>negative.gif" width="14" height="7">&nbsp;Olumsuz Haberler 
<img border="0" src="<?=IMAGE_ROOT ?>neutral.gif" width="14" height="7">&nbsp;Nötr Haberler 
<br><br>
                  
              <table width="100%" border="0" cellspacing="1" cellpadding="1" align="center">
                <tr class="header1" > 
                  <td>No</td>
                  <td>Level</td>
                  <td>Baþlýk</td>
                  <td>Kaydeden</td>
                  <td>Tarih-Saat</td>
                </tr>
                <?
                    $i=1;
                    while ($row=mysql_fetch_object($result)){
                  // Now find out if the news is good or bad
                   if ($row->LEVEL == 0)
                     $level_img = "neutral.gif";
                   else if ($row->LEVEL > 0)
                     $level_img = "positive.gif";
                   else 
                     $level_img = "negative.gif";
                           
                   $level_height = 10 * (abs($row->LEVEL)==0 ? 5 : abs($row->LEVEL));   
                        $tt= $row->DEPT_ID."#".$row->LEVEL."#".$row->BASLIK; 

                           ?>
                                <tr class="<?=bgc($i)?>">
                                        <td><?=$i?></td>
                                        <td><img src="/images/<?=$level_img?>" width="<?=$level_height?>" height="7"></td>
                                        <td><a  href="javascript:popup('yenipencere','news_win.php?frm_sira_no=<?=$row->SIRA_NO?>',550,450,'yes','yes','yes');"><font size=1 ><?=strlen($row->BASLIK)>50?substr($row->BASLIK,0,50)."...":$row->BASLIK;?></font></a></td>
                                        <td nowrap><?=$row->NAME." ".$row->SURNAME?></td>
                                        <td nowrap><?=DB2NORMAL($row->INSERT_DATE),$row->INSERT_TIME?></td>
                                </tr>
                           <?
                            $i++;
                    }//}
?>
  </table>
  
<br>
<div align="center">
Son <input class="input1" type="text" name="list_mess_cnt" size="3">
<a href="javascript:go('news_list.php?msg_cnt=',list_mess_cnt.value);">adet haberi göster</a>
<form method="POST" name="haber_search" action="news_list.php?type=list">
   Ýçerik ile arama
   <input class="input1" type="text" name="frm_search" size="40">
   <img border="0" src="/images/ara.gif" onclick="haber_ara()">
</form>
</div>
<?
   table_footer();
   page_footer(0);
?>

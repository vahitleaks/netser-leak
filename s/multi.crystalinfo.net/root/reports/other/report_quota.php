<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
     require_valid_login();
   
    //Hak Kontrolü
    if (right_get("SITE_ADMIN")){
        //Site admin hakký varsa herþeyi görebilir.  
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
    // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
      $SITE_ID = $SESSION['site_id'];
    }else{
      print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 
    //Hak kontrolü sonu  
    $start = $cUtility->myMicrotime();

    cc_page_meta();
    echo "<center>";
    page_header();
?>

<form name="quota_search" method="post" action="report_quota_prn.php?act=src">
<table cellpadding="0" cellspacing="0" align="center" border="0" width="98%">
  <tr height="8"><td colspan="3"></td></tr>
  <tr>
    <td colspan="3">
      <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
      <tr>  
         <td width="35%" align="right" class="td1_koyu">Site Adý</td>
           <td width="5%"></td>
             <td>
                 <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?>>
                     <?
                    if(right_get("ADMIN") || right_get("ALL_REPORT")){
						$strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES WHERE SITE_ID=$SITE_ID ORDER BY SITE_NAME";
						echo $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $SITE_ID);
					}if (right_get("SITE_ADMIN")){
						$strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ORDER BY SITE_NAME";
						echo $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $SITE_ID);
					}
                     ?>
                 </select>
             </td>
      </tr>
     </table>
     </td>
     </tr>
  <tr height="8"><td colspan="3"></td></tr>   
  <tr>
      <input type="hidden" name="myrep_type" value="">
    <td nowrap width="50%" valign="top">
    <?table_header("Kota Raporlarý","100%");?>
    <center>
    <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
      <tr height="22"> 
        <td width="10%"><input type="radio" class="input1" name="report_quota" value="quota_incity" onclick="javascript:set_me('quota_incity')"></td>      
        <td width="90%" class="td1_acik" id="quota_incity"><b>Þehiriçi</b> Konuþma Kotasýný Aþanlar</td>
      </tr>
      <tr height="22"> 
        <td width="10%"><input type="radio" class="input1" name="report_quota" value="quota_intercity" onclick="javascript:set_me('quota_intercity')"></td>      
        <td width="90%" class="td1_acik" id="quota_intercity"><b>Þehirlerarasý</b> Konuþma Kotasýný Aþanlar</td>
      </tr>
      <tr height="22">
        <td width="10%"><input type="radio" class="input1" name="report_quota" value="quota_gsm" onclick="javascript:set_me('quota_gsm')"></td>      
        <td width="90%" class="td1_acik" id="quota_gsm"><b>GSM</b> Kotasýný Konuþma Aþanlar</td>
      </tr>    
      <tr height="22"> 
        <td width="10%"><input type="radio" class="input1" name="report_quota" value="quota_intern" onclick="javascript:set_me('quota_intern')"></td>      
        <td width="90%" class="td1_acik" id="quota_intern"><b>Uluslararasý</b> Konuþma Kotasýný Aþanlar</td>
             </tr>
      <tr height="22"> 
        <td width="10%"><input type="radio" class="input1" name="report_quota" value="quota_price" onclick="javascript:set_me('quota_price')"></td>      
        <td width="90%" class="td1_acik" id="quota_price"><b>Ücret</b> Kotasýný Aþanlar</td>
             </tr>
        </table>
    <?table_footer();?>
  </td>
  <td width="2%"></td>
  <td nowrap width="48%" valign="top">
    <?table_header("Kriterler","100%");?>
    <center>
    <table cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td colspan="1" class="td1_koyu">Kota Sahibi</td>
        <td>
          <select name="type" style="width:100;" class="select1">
            <option  value="ext"   <?if ($type=='ext')  echo "selected";?>>Dahili</option>            
            <option  value="dept"   <?if ($type=='dept') echo "selected";?>>Departman</option>
            <option  value="auth"   <?if ($type=='auth') echo "selected";?>>Auth.Kodu</option>
                </select>
        </td>
      </tr>
      <tr align="left">
        <td colspan="1" align="left" class="td1_koyu">Tarih:&nbsp&nbsp
              <td>
          <select name="MY_DATE" style="width:100;" class="select1">
            <option  value="c" selected>Bu Ay</option>
            <option  value="g">Geçen Ay</option>
                </select>
             </td>
      </tr>
    </table>
    <?table_footer();?>
  </td>
  </tr>
  <tr>
      <td colspan=4 align=center><br>
           <a href="javascript:submit_form('quota_search');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>raporal.gif"></a>
         </td>
     </tr>          
</table>    
   </form>
   <a href="report_quota.php"><img border="0" src="<?=IMAGE_ROOT?>kriter_temizle.gif"></a>
<?page_footer(0);?>
<script LANGUAGE="javascript" src="/scripts/popup.js"></script>
<script language="javascript" src="/scripts/form_validate.js"></script>
<script language="JavaScript" type="text/javascript">

//Time functions starts here
  function set_me(mytype){
   var myoldtype; 
   myoldtype = document.all('myrep_type').value;
   if (myoldtype == mytype){return;}
   document.all('myrep_type').value = mytype;
   document.all(mytype).className = "header_beyaz3";
   if(myoldtype != ''){
     document.all(myoldtype).className = "td1_acik";
   }
  }
  
  function submit_form(){
    var mytype = document.all('myrep_type').value;
        popup('','report_screen',800,600)
    document.all('SITE_ID').disabled=false;    
    document.all('quota_search').action = 'report_quota_prn.php?act=src&type=' + mytype;
        document.all('quota_search').target= 'report_screen';
        document.all('quota_search').submit();
    }
//-->
</script>
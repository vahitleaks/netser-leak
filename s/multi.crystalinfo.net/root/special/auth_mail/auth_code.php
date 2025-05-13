<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   session_cache_limiter('nocache');
   require_valid_login();
   $conn = $cdb->getConnection();

 //Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Bu sayfaya eriþim hakkýnýz yok!");
    exit;
   }

   if ($act=="upd" && $id!="" && is_numeric($id)){
    //Admin Hakký versa ve Site Admin hakký yoksa sadece kendi sitesine ait bilgiyi görebilmeli
       if (right_get("ADMIN") && !right_get("SITE_ADMIN")){
           $site_cr = " AND SITE_ID = ".$SESSION['site_id'];
       }
       $sql_str = "SELECT * FROM AUTH_CODES WHERE AUTH_CODE_ID = $id".$site_cr;
       if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
    if (mysql_numrows($result)>0){
         $row = mysql_fetch_object($result);
    }else{
        print_error("Belirtilen Kayýt Bulunamadý");
          exit;
    }
   }
   
//Action upd ise SITE_ID db den gelen deðilse Session'dan gelen olmalý.
  if($act=='upd')
     $SITE_ID = $row->SITE_ID;
  else
     $SITE_ID = $SESSION['site_id'];

  cc_page_meta();
     echo "<center>";
     page_header();
     echo "<center><br>";
     table_header("Authorizasyon Kodu","75%");
?>
<script>function submit_form() {
    if(check_form(auth_code_frm)){
      document.all("SITE_ID").disabled=false;
        document.auth_code_frm.submit();
    }
}
</script>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
      <td>
      <center>
            <table class="formbg">
            <form name="auth_code_frm" method="post" onsubmit="return check_form(this);" action="auth_code_db.php?act=<? echo  $HTTP_GET_VARS['act'] ?>">
            <input type="hidden" name="id" value="<?=$id?>">
           <tr class="form">
                <td class="td1_koyu">Site Adý</td>
                <td>
                    <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?>>>
                    <?
                        $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES";
                        echo $cUtility->FillComboValuesWSQL($conn, $strSQL, true,  $SITE_ID);
                    ?>
                    </select>
                    </td>
                </tr>
                <tr class="form">
                  <td class="td1_koyu">Auth. Kodu</td>
                   <td><input type="text" class="input1" size="10" name="AUTH_CODE" VALUE="<?echo $row->AUTH_CODE?>" Maxlength="15"></td> 
               </tr>
                <tr class="form">
                  <td class="td1_koyu">E-Mail</td>
                   <td><input type="text" class="input1" size="30" name="EMAIL" VALUE="<?echo $row->EMAIL?>"></td> 
               </tr>
               <tr class="form">
                  <td class="td1_koyu">Açýklama</td>
                     <td><TEXTAREA class="textarea1" NAME="AUTH_CODE_DESC" COLS=35 ROWS=3><?=$row->AUTH_CODE_DESC?></TEXTAREA></td>
              </tr>
        <tr>
            <td></td>
          <td><img border="0" src="<?=IMAGE_ROOT?>kaydet.gif" style="cursor:hand;" onclick="javascript:submit_form()"></td>
               </tr>
          </form>
            </table>
        </td>
  </tr>
  </table><br>
  <table align="right" width="100%" border="0">
    <tr>
      <td colspan="4" width="70%"></td>
      <td align="right"><a href="auth_code_db.php?act=del&id=<?=$id?>&SITE_ID=<?=$SITE_ID?>"><img border="0" src="<?=IMAGE_ROOT?>kayit_sil.gif" style="cursor:hand;"></a></td>
      <td align="right"><a href="auth_code_src.php"><img border="0" src="<?=IMAGE_ROOT?>arama_yap.gif" style="cursor:hand;"></a></td>
      <td align="right"><a href="auth_code.php?act=new"><img border="0" src="<?=IMAGE_ROOT?>yeni_kayit.gif" style="cursor:hand;"></a></td>
    </tr>
  </table>

    <script language="javascript" src="/scripts/form_validate.js"></script>
    <script language="javascript">
            form_fields[0] = Array ("SITE_ID",   "Authorizasyon Kodunun Baðlý Olduðu Siteyi Seçiniz.", TYP_DROPDOWN);            
            form_fields[1] = Array ("AUTH_CODE", "Authorizasyon Kodunu Rakam Olarak Giriniz.", TYP_NOT_NULL+TYP_DIGIT);
    </script>
<?table_footer();
page_footer(0);?>
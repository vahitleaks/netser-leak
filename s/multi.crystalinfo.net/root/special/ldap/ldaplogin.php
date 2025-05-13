<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    require_once(dirname($DOCUMENT_ROOT)."/root/special/ldap/ldapfnc.php");
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

       $sql_str = "SELECT * FROM TbLdapUser Limit 1";
       if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
       if (mysql_numrows($result)>0){
         $row = mysql_fetch_object($result);
         
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
     table_header("LDAP Login parametreleri","50%");
?>
<script>function submit_form() {
    if(check_form(ldap_frm)){
        document.ldap_frm.submit();
    }
}
</script>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
      <td>
      <center>
            <table class="formbg">
            <form name="ldap_frm" method="post" onsubmit="return check_form(this);" action="ldaplogin_db.php?act=<? echo  $HTTP_GET_VARS['act'] ?>">
            <input type="hidden" name="InLdapUserId" value="<?=$row->InLdapUserId?>">
           <tr class="form">
                </tr>
                <tr class="form">
                  <td class="td1_koyu">Ldap Server</td>
                   <td><input type="text" class="input1" name="StLdapServer" VALUE="<?echo $row->StLdapServer?>"></td> 
               </tr>

                <tr class="form">
                  <td class="td1_koyu">Ldap Port No</td>
                   <td><input type="text" class="input1" name="StPortNo" VALUE="<?echo $row->StPortNo?>"></td> 
               </tr>
                <tr class="form">
                  <td class="td1_koyu">Kullanýcý Adý</td>
                   <td><input type="text" class="input1" size="70" name="StLdapUserName" VALUE="<?echo myDecryption($row->StLdapUserName)?>"></td> 
               </tr>
                <tr class="form">
                  <td class="td1_koyu">Þifre</td>
                   <td><input type="password" class="input1" name="StLdapPassword" VALUE=""></td> 
               </tr>
                <tr class="form">
                  <td class="td1_koyu">Base Dn</td>
                   <td><input type="text" class="input1" size="70" name="StLdapBaseDn" VALUE="<?echo myDecryption($row->StLdapBaseDn)?>"></td> 
               </tr>
 
                <tr class="form">
                  <td class="td1_koyu">Filtre</td>
                   <td><input type="text" class="input1" name="StFilter" VALUE="<?echo $row->StFilter?>"></td> 
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

    <script language="javascript" src="/scripts/form_validate.js"></script>
    <script language="javascript">
      form_fields[0] = Array ("StLdapServer",   "LDAP Sunucusunun IP Adresini giriniz.", TYP_NOT_NULL);            
      form_fields[1] = Array ("StLdapUserName", "LDAP Kullanýcý adýný giriniz.", TYP_NOT_NULL);
      form_fields[2] = Array ("StLdapBaseDn", "LDAP base dn giriniz.", TYP_NOT_NULL);
      <?if($row->InLdapUserId =="" || !is_numeric($row->InLdapUserId)){?>
      form_fields[3] = Array ("StLdapPassword", "LDAP þifresini giriniz.", TYP_NOT_NULL);
      <?}?>
	</script>
<?table_footer();
page_footer(0);?>
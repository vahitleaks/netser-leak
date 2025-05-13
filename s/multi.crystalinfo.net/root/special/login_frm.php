<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   if (!defined("IMAGE_ROOT")){ // Note that it should be quoted
      define("IMAGE_ROOT", "/images/");
   }
   echo $SESSION['user_name'];
 ?>
<HTML>
<HEAD>
<TITLE>CrystalInfo Login</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-9">
<link rel="stylesheet" href="crystal.css" type="text/css">
</HEAD>
<BODY BGCOLOR=#FFFFFF leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" background="<?=IMAGE_ROOT?>hp_bg1.gif">
<?cc_page_meta(0);?>
<body bgcolor="#FFFFFF" text="#000000" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form name="giris" method="post" action="login.php<? echo '?REDIR='.$REDIR ?>" >
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr>
    <td align="center" valign="middle"> 
      <table width="478" border="0" cellspacing="0" cellpadding="0">
        <tr> 
          <td align="right" valign="bottom"><img src="<?=IMAGE_ROOT?>login_ust.gif" width="222" height="14"></td>
        </tr>
        <tr> 
          <td colspan="2"> 
            <table width="478" border="0" cellspacing="0" cellpadding="0" height="222">
              <tr> 
                <td width="1" valign="top" background="<?=IMAGE_ROOT?>bg_border.gif"><img src="<?=IMAGE_ROOT?>bg_border.gif" width="1" height="1"></td>
                <td width="199" background="<?=IMAGE_ROOT?>bg_login.gif" align="center" valign="middle"> 
                  <table width="190" border="0" cellspacing="6" cellpadding="0">
              <tr><td colspan="3"><img src="<?=IMAGE_ROOT?>logo1.gif"></td></tr>
                    <?if ($act == "retry") {?>
          <tr><td colspan="3" align="center"><font color=red>Geçersiz Kullanıcı/Şifre!</font></td></tr>
          <?}?>
                    <tr> 
                      <td class="header" nowrap><img SRC="<?=IMAGE_ROOT?>ok.gif"> Kullanıcı Adı</td><td>:</td>
                      <td width="100"><input type="text" name="user_name" size="10"></td>
                    </tr>
                    <tr> 
                      <td class="header" nowrap><img SRC="<?=IMAGE_ROOT?>ok.gif"> Şifre</td><td>:</td>
                      <td width="100"><input type="password" name="user_pass" size="10"></td>
                    </tr>
                    <tr> 
                      <td colspan="2">&nbsp;</td>
                      <td width="91"><input tabindex="4" type="image" onclick="javascript:return send_form()" name="Image6" border="0" src="<?=IMAGE_ROOT?>login_button.gif"></td>
                    </tr>
                  </table>
                </td>
                <td width="279" valign="top" align="left"><img src="<?=IMAGE_ROOT?>login_img01.gif" width="135" height="222"><img src="<?=IMAGE_ROOT?>login_img02.gif" width="144" height="222"></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

<script language="JavaScript" type="text/javascript">
<!--
   function send_form()
   {
      if (giris.user_name.value ==""){
        alert("Kullanıcı kodunuzu giriniz");
        giris.user_name.focus();
        return false;
      }
      if (giris.user_pass.value ==""){
        alert("Kullanıcı şifrenizi giriniz");
        giris.user_pass.focus();
        return false ;
      }
      document.giris.submit();
   }
//-->
</script>


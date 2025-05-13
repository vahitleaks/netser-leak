<?  //INCLUDES
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   
   require_valid_login();
    
   $cUtility = new Utility();
   $cdb = new db_layer();

   $conn = $cdb->getConnection();
   check_right("ADMIN");
   cc_page_meta();
   echo "<center>";

   $link["Ana Sayfa"]= "../main.php";
   $link["Admin Ana Sayfa"]= "main.php";
   page_header($link);
   
   //Sistem Genel Bilgileri
   $sql_str  = "SELECT COUNT(USER_ID) AS USER_AMOUNT FROM USERS WHERE DISABLED = 'N'";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $user_amount = $row->USER_AMOUNT;

  $sql_str  = "SELECT COUNT(DEPT_ID) AS DEPT_AMOUNT FROM DEPTS";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $dept_amount = $row->DEPT_AMOUNT;
   
  $sql_str  = "SELECT COUNT(TRUNK_ID) AS TRUNK_AMOUNT FROM TRUNKS";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $trunk_amount = $row->TRUNK_AMOUNT;

  $sql_str  = "SELECT COUNT(ACCESS_CODE_ID) AS ACOD_AMOUNT FROM ACCESS_CODES";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $acod_amount = $row->ACOD_AMOUNT;

  $sql_str  = "SELECT COUNT(AUTH_CODE_ID) AS AUTH_AMOUNT FROM AUTH_CODES";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $auth_amount = $row->AUTH_AMOUNT;

  $sql_str  = "SELECT COUNT(CONTACT_ID) AS CONTACT_AMOUNT FROM CONTACTS";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $contact_amount = $row->CONTACT_AMOUNT;   
   
  $sql_str  = "SELECT COUNT(EXT_ID) AS EXT_AMOUNT FROM EXTENTIONS";
         
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
         }

    $row = mysql_fetch_object($result);  
    $ext_amount = $row->EXT_AMOUNT;   
    

?>
<script language=javascript>
    var info_text = new Array;
    
    info_text[0]="Kritik durumlarda veya belirli periyotlarda ilgili ki�ilere e-posta g�nderilmesi i�in gerekli tan�mlamalar�n yap�ld��� b�l�md�r.";
    info_text[1]="�a�r�lar�n de�i�ik a��lardan analizlerinin yap�ld��� b�l�md�r.";
    info_text[2]="Dahili numaralar�n CrystalInfo sistemine tan�t�lmas� ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[3]="CrystalInfo sistemini kullanacak ki�ilerin tan�mlanmas� ve yetkilendirilmesini sa�layan b�l�md�r.";
    info_text[4]="Departman bilgilerinin CrystalInfo sistemine tan�t�lmas� ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[5]="�irket ve ki�isel kontaklar�n ve bunlar�n telefon bilgilerinin girilmesini ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[6]="�a�r�lar �zerinde analiz yaparak, sistemde g�z�ken anormal durumlar�n bildirilmesini sa�layan b�l�md�r.";
    info_text[7]="Hatlar�n CrystalInfo sistemine tan�t�lmas� ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[8]="Baz� santrallerde hatlara eri�mek i�in kullan�lan �zel numaralar�n tan�mlanmas�n� ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[9]="Santralden son gelen verilerin ger�ek zamanl� g�r�nt�lenmesini sa�layan b�l�md�r.";
    info_text[10]="CrystalInfo sisteminin kurulu oldu�u bilgisayar�n �zerinde inceleme yaparak durum raporu sunan b�l�md�r.";
    info_text[11]="Veritaban�ndaki bilgilerin ar�ivlenerek gerekti�inde buradan rapor al�nmas�n� sa�layan b�l�md�r.";
    info_text[12]="Sistem ile ilgili parametrelerin d�zenlenmesini sa�layan b�l�md�r.";
    info_text[13]="Veritaban�na ba�lant�da sorun olmas� durumunda, dosyalara yaz�lm�� olan verilerin tekrar veritaban�na at�lmas�n� sa�layan b�l�md�r.";
    info_text[14]="Site bilgilerinin CrystalInfo sistemine tan�t�lmas� ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[15]="Dahililere, departmanlara ve Otorizasyon kodlar�na kota atamalar�n�n yap�larak kotas�n� a�anlar�n raporlanmas�n� sa�layan b�l�md�r.";
    info_text[16]="GSM operat�rleri aras�ndaki �cret tarifelerini g�r�nt�ler g�ncellemenizi sa�lar...";
    info_text[17]="Santralden gelmi� olan datalar�n i�lenmemi� haline eri�ilmesini sa�layan b�l�md�r.";
    info_text[18]="Uzak Mesafe Telefon Hizmetleri al�m�nda karar verilebilmesi amac� ile haz�rlanan �zel analiz b�l�m�d�r.";
    info_text[19]="Baz� santrallerde ki�ilerin yetkilendirilmesi i�in kullan�lan Otorizasyon kodlar�n�n CrystalInfo sistemine tan�t�lmas� ve g�ncellenmesini sa�layan b�l�md�r.";
    info_text[20]="Yede�i al�nm�� olan verilerin indirilerek kullan�c�n�n bilgisayar�na kaydedilmesini sa�layan b�l�md�r.";
    function show_hide(i){
        if(document.all('info').style.visibility=='visible'){
            document.all('info').style.visibility='hidden';
            
        }else{
            if(info_text[i]!=''){
                document.all('info').style.visibility='visible';
                document.all('info_text').innerText = info_text[i];
            }
        }
    }
</script>
<br>
<table align="left" border="0"  width="90%">
  <tr>
    <td width="5%"></td>
    <td width="30%" valign="top" height="500">
      <? table_header("Sistem �zeti","100%"); ?>
      <table cellspacing="1" cellpadding="3" align="left" border="0"  width="100%">
        <tr height="20" class="bgc1">
          <td>Dahili Adedi</td>  
          <td><b><?echo $ext_amount;?></b></td>
        </tr>
        <tr height="20" class="bgc2">
          <td>Kullan�c� Adedi</td>  
          <td><b><?echo $user_amount;?></b></td>
        </tr>
        <tr height="20" class="bgc1">
          <td>Departman Adedi</td>  
          <td><b><?echo $dept_amount;?></b></td>
        </tr>
        <tr height="20" class="bgc2">
          <td>Fihrist Adedi</td>  
          <td><b><?echo $contact_amount;?></b></td>
        </tr>
        <tr height="20" class="bgc2">
          <td>Hat Adedi</td>  
          <td><b><?echo $trunk_amount;?></b></td>
        </tr>
        <tr height="20" class="bgc1">
          <td>Eri�im Kodu Adedi</td>  
          <td><b><?echo $acod_amount;?></b></td>
        </tr>      
        <tr height="20" class="bgc2">
          <td>Auth. Kodu Adedi</td>  
          <td><b><?echo $auth_amount;?></b></td>
        </tr>
        <tr height="15"></tr>
        <tr>
          <td colspan="2" align="center" valign="top">
              <a href="/special/index.php">
                <img border="0" src="<?=IMAGE_ROOT?>admin/strateji_center.gif" alt="�irketinize �zel olarak haz�rlanm�� mod�llere eri�menizi sa�lar!"><br>
                �zel Mod�ller
            </a>            
          </td>
        </tr>      
      </table>
      <? table_footer();?>    
<br>
    <div id="info" style="visibility:hidden;">
        <table width="100%" border="1" cellspacing="0" cellpadding="6" bordercolor="#929292" bgcolor="#F0F8FF">
          <tr>
            <td bgcolor="F0F8FF" bordercolor="#F0F8FF" width="1%" align="right">
            <img src="/images/info.gif" width="24" height="25"></td>
            <td bgcolor="#F0F8FF" bordercolor="#F0F8FF" width="99%" class="text">
              <div id="info_text">

              </div>
            
            </td>
          </tr>
        </table>
</div>      
  </td>
  <td width="5%"></td>
  <td width="65%" VALIGN="top">
  <table border="0" width="100%" style="border: 0 dotted #000080">
    <tr> 
      <td valign="middle" colspan="5" height="20" bgcolor="99B1C3" class="cigate_header">&nbsp;<img src="<?=IMAGE_ROOT?>ok5.gif" width="13" height="8">Analizler </td>
    </tr>
    <tr>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/reports/report_main.php" onmouseover="javascript:show_hide(1)" onmouseout="javascript:show_hide(1)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/raporlar.gif" alt=""><br>Raporlar</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/umth/umth_forw.php" onmouseover="javascript:show_hide(18)" onmouseout="javascript:show_hide(18)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/umth.gif" alt=""><br>UMTH Analizi</a>
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/alerts/alert_main.php" onmouseover="javascript:show_hide(0)" onmouseout="javascript:show_hide(0)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/alert.gif" alt=""><br>Uyar�lar</a>
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/audit/audit_forw.php" onmouseover="javascript:show_hide(6)" onmouseout="javascript:show_hide(6)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/anormallikler.gif" alt=""><br>Anormallikler</a>            
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/admin/logview.php" onmouseover="javascript:show_hide(17)" onmouseout="javascript:show_hide(17)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/sistem_loglari1.gif" alt=""><br>Santral Loglar�</a>            
      </td>
	</tr>
    <tr> 
      <td valign="middle" colspan="5" height="20" bgcolor="99B1C3" class="cigate_header">&nbsp;<img src="<?=IMAGE_ROOT?>ok5.gif" width="13" height="8">Tan�mlamalar </td>
    </tr>
    <tr>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/sites/site_src.php" onmouseover="javascript:show_hide(14)" onmouseout="javascript:show_hide(14)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/sites.gif" alt=""><br>Siteler</a>            
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/trunks/trunk_src.php" onmouseover="javascript:show_hide(7)" onmouseout="javascript:show_hide(7)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/trunk.gif" alt=""><br>Hatlar</a>      
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/depts/dept_src.php" onmouseover="javascript:show_hide(4)" onmouseout="javascript:show_hide(4)">
        <img border="0" src="<?=IMAGE_ROOT?>departman.gif" alt=""><br>Departmanlar</a>
      </td>    
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/extentions/extentions_src.php" onmouseover="javascript:show_hide(2)" onmouseout="javascript:show_hide(2)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/dahili.gif" alt=""><br>Dahililer</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/users/user_src.php" onmouseover="javascript:show_hide(3)" onmouseout="javascript:show_hide(3)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/kullanici.gif" alt=""><br>Kullan�c�lar</a>            
      </td>
    <tr> 
      <td valign="middle" colspan="5" height="1"></td>
    </tr>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/contacts/contact_src.php" onmouseover="javascript:show_hide(5)" onmouseout="javascript:show_hide(5)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/fihrist.gif" alt=""><br>Fihrist</a>
      </td>    
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/access_codes/access_code_src.php" onmouseover="javascript:show_hide(8)" onmouseout="javascript:show_hide(8)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/cikis_kodlari.gif" alt=""><br>Eri�im Kodlar�</a>
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/admin/konsol.php" onmouseover="javascript:show_hide(9)" onmouseout="javascript:show_hide(9)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/konsol.gif" alt=""><br>Konsol</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/auth_codes/auth_code_src.php" onmouseover="javascript:show_hide(19)" onmouseout="javascript:show_hide(19)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/auth_code.gif" alt=""><br>Auth. Kodlar�</a>
      </td>    
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/quotas/quota_src.php" onmouseover="javascript:show_hide(15)" onmouseout="javascript:show_hide(15)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/quota_atama.gif" alt=""><br>Kota Atamalar�</a>            
      </td>
	</tr>
    <tr> 
      <td valign="middle" colspan="5" height="20" bgcolor="99B1C3" class="cigate_header">&nbsp;<img src="<?=IMAGE_ROOT?>ok5.gif" width="13" height="8">Sistem </td>
    </tr>
    <tr>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/admin/sanity.php" onmouseover="javascript:show_hide(10)" onmouseout="javascript:show_hide(10)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/sanity.gif" alt=""><br>Sanity</a>            
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/admin/archieve.php" onmouseover="javascript:show_hide(11)" onmouseout="javascript:show_hide(11)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/archive.gif" alt=""><br>Ar�ivleme</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="systemprm_src.php" onmouseover="javascript:show_hide(12)" onmouseout="javascript:show_hide(12)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/sistem_prm.gif" alt=""><br>Parametreler</a>            
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="text_to_db.php" onmouseover="javascript:show_hide(13)" onmouseout="javascript:show_hide(13)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/veri_yukle.gif" alt=""><br><br>Veri Y�kleme</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/admin/download.php" onmouseover="javascript:show_hide(20)" onmouseout="javascript:show_hide(20)">
        <img border="0" src="<?=IMAGE_ROOT?>backup.gif" alt=""><br>Yedekleme</a>            
      </td>
    </tr>
  </table>
  </td>
  </tr>
</table>
    
<?
   page_footer(0);
?>

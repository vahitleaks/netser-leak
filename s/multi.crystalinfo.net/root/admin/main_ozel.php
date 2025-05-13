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
    
    info_text[0]="Kritik durumlarda veya belirli periyotlarda ilgili kiþilere e-posta gönderilmesi için gerekli tanýmlamalarýn yapýldýðý bölümdür.";
    info_text[1]="Çaðrýlarýn deðiþik açýlardan analizlerinin yapýldýðý bölümdür.";
    info_text[2]="Dahili numaralarýn CrystalInfo sistemine tanýtýlmasý ve güncellenmesini saðlayan bölümdür.";
    info_text[3]="CrystalInfo sistemini kullanacak kiþilerin tanýmlanmasý ve yetkilendirilmesini saðlayan bölümdür.";
    info_text[4]="Departman bilgilerinin CrystalInfo sistemine tanýtýlmasý ve güncellenmesini saðlayan bölümdür.";
    info_text[5]="Þirket ve kiþisel kontaklarýn ve bunlarýn telefon bilgilerinin girilmesini ve güncellenmesini saðlayan bölümdür.";
    info_text[6]="Çaðrýlar üzerinde analiz yaparak, sistemde gözüken anormal durumlarýn bildirilmesini saðlayan bölümdür.";
    info_text[7]="Hatlarýn CrystalInfo sistemine tanýtýlmasý ve güncellenmesini saðlayan bölümdür.";
    info_text[8]="Bazý santrallerde hatlara eriþmek için kullanýlan özel numaralarýn tanýmlanmasýný ve güncellenmesini saðlayan bölümdür.";
    info_text[9]="Santralden son gelen verilerin gerçek zamanlý görüntülenmesini saðlayan bölümdür.";
    info_text[10]="CrystalInfo sisteminin kurulu olduðu bilgisayarýn üzerinde inceleme yaparak durum raporu sunan bölümdür.";
    info_text[11]="Veritabanýndaki bilgilerin arþivlenerek gerektiðinde buradan rapor alýnmasýný saðlayan bölümdür.";
    info_text[12]="Sistem ile ilgili parametrelerin düzenlenmesini saðlayan bölümdür.";
    info_text[13]="Veritabanýna baðlantýda sorun olmasý durumunda, dosyalara yazýlmýþ olan verilerin tekrar veritabanýna atýlmasýný saðlayan bölümdür.";
    info_text[14]="Site bilgilerinin CrystalInfo sistemine tanýtýlmasý ve güncellenmesini saðlayan bölümdür.";
    info_text[15]="Dahililere, departmanlara ve Otorizasyon kodlarýna kota atamalarýnýn yapýlarak kotasýný aþanlarýn raporlanmasýný saðlayan bölümdür.";
    info_text[16]="GSM operatörleri arasýndaki ücret tarifelerini görüntüler güncellemenizi saðlar...";
    info_text[17]="Santralden gelmiþ olan datalarýn iþlenmemiþ haline eriþilmesini saðlayan bölümdür.";
    info_text[18]="Uzak Mesafe Telefon Hizmetleri alýmýnda karar verilebilmesi amacý ile hazýrlanan özel analiz bölümüdür.";
    info_text[19]="Bazý santrallerde kiþilerin yetkilendirilmesi için kullanýlan Otorizasyon kodlarýnýn CrystalInfo sistemine tanýtýlmasý ve güncellenmesini saðlayan bölümdür.";
    info_text[20]="Yedeði alýnmýþ olan verilerin indirilerek kullanýcýnýn bilgisayarýna kaydedilmesini saðlayan bölümdür.";
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
      <? table_header("Sistem Özeti","100%"); ?>
      <table cellspacing="1" cellpadding="3" align="left" border="0"  width="100%">
        <tr height="20" class="bgc1">
          <td>Dahili Adedi</td>  
          <td><b><?echo $ext_amount;?></b></td>
        </tr>
        <tr height="20" class="bgc2">
          <td>Kullanýcý Adedi</td>  
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
          <td>Eriþim Kodu Adedi</td>  
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
                <img border="0" src="<?=IMAGE_ROOT?>admin/strateji_center.gif" alt="Þirketinize özel olarak hazýrlanmýþ modüllere eriþmenizi saðlar!"><br>
                Özel Modüller
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
        <img border="0" src="<?=IMAGE_ROOT?>admin/alert.gif" alt=""><br>Uyarýlar</a>
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/audit/audit_forw.php" onmouseover="javascript:show_hide(6)" onmouseout="javascript:show_hide(6)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/anormallikler.gif" alt=""><br>Anormallikler</a>            
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/admin/logview.php" onmouseover="javascript:show_hide(17)" onmouseout="javascript:show_hide(17)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/sistem_loglari1.gif" alt=""><br>Santral Loglarý</a>            
      </td>
	</tr>
    <tr> 
      <td valign="middle" colspan="5" height="20" bgcolor="99B1C3" class="cigate_header">&nbsp;<img src="<?=IMAGE_ROOT?>ok5.gif" width="13" height="8">Tanýmlamalar </td>
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
        <img border="0" src="<?=IMAGE_ROOT?>admin/kullanici.gif" alt=""><br>Kullanýcýlar</a>            
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
        <img border="0" src="<?=IMAGE_ROOT?>admin/cikis_kodlari.gif" alt=""><br>Eriþim Kodlarý</a>
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/admin/konsol.php" onmouseover="javascript:show_hide(9)" onmouseout="javascript:show_hide(9)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/konsol.gif" alt=""><br>Konsol</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="/auth_codes/auth_code_src.php" onmouseover="javascript:show_hide(19)" onmouseout="javascript:show_hide(19)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/auth_code.gif" alt=""><br>Auth. Kodlarý</a>
      </td>    
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="/quotas/quota_src.php" onmouseover="javascript:show_hide(15)" onmouseout="javascript:show_hide(15)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/quota_atama.gif" alt=""><br>Kota Atamalarý</a>            
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
        <img border="0" src="<?=IMAGE_ROOT?>admin/archive.gif" alt=""><br>Arþivleme</a>            
      </td>
      <td align="center" valign="top" bgcolor="#CEDBE3">
        <a href="systemprm_src.php" onmouseover="javascript:show_hide(12)" onmouseout="javascript:show_hide(12)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/sistem_prm.gif" alt=""><br>Parametreler</a>            
      </td>
      <td align="center" valign="top" bgcolor="#B6C7D3">
        <a href="text_to_db.php" onmouseover="javascript:show_hide(13)" onmouseout="javascript:show_hide(13)">
        <img border="0" src="<?=IMAGE_ROOT?>admin/veri_yukle.gif" alt=""><br><br>Veri Yükleme</a>            
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

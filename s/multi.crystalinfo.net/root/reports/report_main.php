<?  //INCLUDES
   //require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   //require_once $_SERVER['DOCUMENT_ROOT'].'/cgi-bin/functions.php';
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   
   require_valid_login();
    
   $cUtility = new Utility();
   $cdb = new db_layer();
//echo SITE ; echo $SESSION['site_id'];
   $conn = $cdb->getConnection();
 
   cc_page_meta();
   echo "<center>";
   page_header();
   echo "<br>"
   

?>
<table border="0" width="85%" align="center">
  <tr>
    <td width="100%">
        <table border="0" width="650" bgcolor="#FFFFFF" cellspacing="1" cellpadding="4">
            <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">�zet Raporlar</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Giden �a�r� Raporlar�</td>
      </tr>
      <tr>  
        <td class="rep2_cells1" align="center" width="15%">
          <a href="/reports/general/report_general.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_ozet.gif" alt=""><br>
          </a>
        </td>
        <td class="rep2_cells2" width="35%">�a�r� T�rlerine Da��l�m� <br>Dahililere Da��l�m�<br>
            Auh. Kodlar�na Da��l�m�<br>Departmanlara Da��l�m�<br>
            �llere Da��l�m�
          
        </td>
        <td class="rep2_cells1" width="15%" align="center" valign="top">
            <a href="/reports/outbound/report_outb.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_outb.gif" alt=""><br>
           </a>            
        </td>
        <td class="rep2_cells2" width="35%">Bir Dahilinin Aramalar�<br>Bir Departman�n Aramalar�<br>Bir �ehre Yap�lan Aramalar<br>Bir �lkeye Yap�lan Aramalar
        </td>
      </tr>
            <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Top Raporlar</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">GSM Raporlar�</td>
      </tr>
      <tr>
        <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/top/report_top.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_top.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">En �ok Aranan Numaralar<br> En �ok Arama Yapan Dahililer<br> En Uzun S�re Konu�an Dahililer<br> 
          En Fazla Aranan �ller
        </td>
        <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/gsm/report_gsm.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_gsm.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Operat�rlere g�re yap�lan aramalar<br> Operat�r Matrixi<br> GSM kodlar�na g�re yap�lan aramalar<br> 
         </td>
      </tr>
      <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Dahili Arama Raporlar�</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Hat Raporlar�</td>
      </tr>
      <tr>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/local/report_local.php">
              <img border="0" src="<?=IMAGE_ROOT?>alert_phone.gif" alt=""><br>
          </a>
        </td>
        <td class="rep2_cells2" width="35%">Dahiliden Dahiliye<br>  Yap�lan Aramalar� Raporlar. 
        </td>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/trunk/report_trunk.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_trunk.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">�a�r�lar�n Hatlara Da��l�m�<br> 
          Giden �a�r�lar�n �ebekelere <br> Gelen �a�r�lar�n �ebekelere Da��l�m� <br>
        </td>
      </tr>
      <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Sistem Raporlar�</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Gelen �a�r� Raporlar�</td>
      </tr>
      <tr>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/other/report_system.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_system.gif" alt=""><br>
          </a>
        </td>
        <td class="rep2_cells2" width="35%">Dahili Listesi<br>Departman Listesi<br>
        �l Kodlar�<br> �lke Kodlar�
        </td>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/inbound/report_inb.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_inb.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">�a�r�lar�n Dahililere Da��l�m�<br>�a�r�lar�n Departmanlara Da��l�m�<br>

        </td>
      </tr>
      <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Y�netim Raporu</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Multisite Raporlar�</td>
      </tr>
      <tr>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/man/report_management.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_ozet.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Y�neticiler i�in haz�rlanm�� rapor<br>Toplam Telefon Giderleri
                <br>En fazla g�r��en 5 departman<br>En fazla aranan 5 il</td>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/multisite/report_multiside.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_top.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">B�t�n siteleri i�eren �a�r�lar<br>Toplam Telefon Gideri<br>Sitelere G�re Giderler</td>
            </tr>
      </table>
    </td>
  </tr>
</table>
    
<?
   page_footer(0);
?>

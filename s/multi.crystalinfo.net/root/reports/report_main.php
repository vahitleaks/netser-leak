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
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Özet Raporlar</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Giden Çaðrý Raporlarý</td>
      </tr>
      <tr>  
        <td class="rep2_cells1" align="center" width="15%">
          <a href="/reports/general/report_general.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_ozet.gif" alt=""><br>
          </a>
        </td>
        <td class="rep2_cells2" width="35%">Çaðrý Türlerine Daðýlýmý <br>Dahililere Daðýlýmý<br>
            Auh. Kodlarýna Daðýlýmý<br>Departmanlara Daðýlýmý<br>
            Ýllere Daðýlýmý
          
        </td>
        <td class="rep2_cells1" width="15%" align="center" valign="top">
            <a href="/reports/outbound/report_outb.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_outb.gif" alt=""><br>
           </a>            
        </td>
        <td class="rep2_cells2" width="35%">Bir Dahilinin Aramalarý<br>Bir Departmanýn Aramalarý<br>Bir Þehre Yapýlan Aramalar<br>Bir Ülkeye Yapýlan Aramalar
        </td>
      </tr>
            <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Top Raporlar</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">GSM Raporlarý</td>
      </tr>
      <tr>
        <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/top/report_top.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_top.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">En Çok Aranan Numaralar<br> En Çok Arama Yapan Dahililer<br> En Uzun Süre Konuþan Dahililer<br> 
          En Fazla Aranan Ýller
        </td>
        <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/gsm/report_gsm.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_gsm.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Operatörlere göre yapýlan aramalar<br> Operatör Matrixi<br> GSM kodlarýna göre yapýlan aramalar<br> 
         </td>
      </tr>
      <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Dahili Arama Raporlarý</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Hat Raporlarý</td>
      </tr>
      <tr>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/local/report_local.php">
              <img border="0" src="<?=IMAGE_ROOT?>alert_phone.gif" alt=""><br>
          </a>
        </td>
        <td class="rep2_cells2" width="35%">Dahiliden Dahiliye<br>  Yapýlan Aramalarý Raporlar. 
        </td>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/trunk/report_trunk.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_trunk.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Çaðrýlarýn Hatlara Daðýlýmý<br> 
          Giden Çaðrýlarýn Þebekelere <br> Gelen Çaðrýlarýn Þebekelere Daðýlýmý <br>
        </td>
      </tr>
      <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Sistem Raporlarý</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Gelen Çaðrý Raporlarý</td>
      </tr>
      <tr>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/other/report_system.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_system.gif" alt=""><br>
          </a>
        </td>
        <td class="rep2_cells2" width="35%">Dahili Listesi<br>Departman Listesi<br>
        Ýl Kodlarý<br> Ülke Kodlarý
        </td>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/inbound/report_inb.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_inb.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Çaðrýlarýn Dahililere Daðýlýmý<br>Çaðrýlarýn Departmanlara Daðýlýmý<br>

        </td>
      </tr>
      <tr class="rep2_tr">
              <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Yönetim Raporu</td>
        <td nowrap class="rep2_header" colspan="2" width="50%"><img src="<?=IMAGE_ROOT?>ok.gif">Multisite Raporlarý</td>
      </tr>
      <tr>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/man/report_management.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_ozet.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Yöneticiler için hazýrlanmýþ rapor<br>Toplam Telefon Giderleri
                <br>En fazla görüþen 5 departman<br>En fazla aranan 5 il</td>
          <td class="rep2_cells1" width="15%" align="center" valign="top">
          <a href="/reports/multisite/report_multiside.php">
              <img border="0" src="<?=IMAGE_ROOT?>rep_top.gif" alt=""><br>
          </a>            
        </td>
        <td class="rep2_cells2" width="35%">Bütün siteleri içeren çaðrýlar<br>Toplam Telefon Gideri<br>Sitelere Göre Giderler</td>
            </tr>
      </table>
    </td>
  </tr>
</table>
    
<?
   page_footer(0);
?>

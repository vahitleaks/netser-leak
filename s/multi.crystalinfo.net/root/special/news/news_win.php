<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   $conn = $cdb->getConnection();
   cc_page_meta();
?>
<br>
<? if (isset($frm_sira_no)) {
     $lookup = "SELECT * FROM NEWS ".
              "INNER JOIN USERS ON NEWS.USER_ID = USERS.USER_ID ".
              "WHERE NEWS.SIRA_NO=".$frm_sira_no.";";
    
    //// If any records found
     if (!($cdb->execute_sql($lookup,$result,$error_msg))){
       echo "Belirtilen kaydýn detaylarý bulunamadý...Hata:".$error_msg;
       exit;
     }else{
       $row = mysql_fetch_object($result);
     }
   }else{
     $lookup = "SELECT * FROM NEWS ".
              "INNER JOIN USERS ON NEWS.USER_ID = USERS.USER_ID ".
              "ORDER BY NEWS.SIRA_NO DESC LIMIT 1 ;";
     if (!($cdb->execute_sql($lookup,$result,$error_msg))){
       echo "Belirtilen kaydýn detaylarý bulunamadý...Hata:".$error_msg;
       exit;
     }else{
       $row = mysql_fetch_object($result);
     }
   
   }
   table_header("Haber Detaylarý","100%");   
?>
<br>  
  <table width="100%" border="0" cellspacing="1" cellpadding="1" align="center">
      <tr class="header1">
           <td>Yayýnlayan</td>
           <td>Departman</td>
           <td>Tarih-Saat</td>
    </tr>   
        <tr> 
            <td><? echo $row->NAME." ".$row->SURNAME;?></td>
            <td><? echo $row->DEPT_NAME;?></td>
            <td><? echo db2normal($row->INSERT_DATE)." - ".$row->INSERT_TIME;?></td>
        </tr>
        <tr class="header1"> 
            <td colspan="3">BAÞLIK:<? echo $row->BASLIK?></td>
        </tr>
        <tr> 
           <td colspan="3"><? echo NL2BR($row->DETAY);?></td>
        </tr>
   </table>
  <p align="center"><a href="javascript:close();"><img src="<?=IMAGE_ROOT?>kapat.gif" border="0"></a></p>
<?  
 table_footer();
?>
</body>
</html>

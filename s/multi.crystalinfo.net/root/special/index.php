<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   if (!defined("IMAGE_ROOT")){ // Note that it should be quoted
      define("IMAGE_ROOT", "/images/");
   }  
   $cUtility = new Utility();
   $cdb = new db_layer();
   $conn = $cdb->getConnection();
   if(!$conn) exit;
   $t0 = day_of_last_month("first");
   $t1 = day_of_last_month("last");
/*   $sql_ctl = "SELECT DCOST FROM TGSMCALLS WHERE MYDATE>='$t0' AND MYDATE<='$t1'";
   if (!($cdb->execute_sql($sql_ctl, $rsctl, $error_msg))){
     print_error($error_msg);
     exit;
   }
   $gsm_loaded=false;
   if(mysql_num_rows($rsctl)>0){
     $gsm_loaded=true;
   }*/
?>
<?cc_page_meta(0);
page_header();
echo "<br>";
table_header("M��teri �zel B�l�m�","80%");?>
<center>
<table awidth="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
  <tr>
    <td align="center" valign="middle"><a href="extentions/extentions_src.php">Dahili Arama</a></td>
  </tr>
  <tr>
    <td align="center" valign="middle"><a href="ykb/report_general.php">�zet Raporlar</a></td>
  </tr>
  <tr>
    <td align="center" valign="middle"><a target="_blank" href="ykb/report_system_prn.php?type=ext">Dahili Listesi</a></td>
  </tr>
  <tr>
    <td align="center" valign="middle"><a target="_blank" href="ykb/report_system_prn.php?type=dept">Departman Listesi</a></td>
  </tr>
  <tr>
    <td align="center" valign="middle"><a href="upload_csv.php">CSV'den Y�kle</a></td>
  </tr>
  <tr>
    <td align="center" style="color:red" valign="middle"><b>Not: Ayl�k Mail Raporlar� Sadece Rapor G�n�nde �al��t�r�lmal�! Bunun d���nda kullan�lmamal�d�r.</b></td>
  </tr>
  <tr>
    <td align="center" valign="middle"><a href="#" onclick="ext_send_mails();">Ayl�k Dahili Detay Raporunu G�nder </a></td>
  </tr>
  <tr>
    <td align="center" valign="middle"><a href="#" onclick="dept_send_mails();">Ayl�k Y�netici Departman Raporunu G�nder   </a></td>
  </tr>
  <TR>
    <td align="center" valign="middle">
    <input type="checkbox" value="1" name="unsentmails" id="unsentmails">&nbsp;Daha �nceki g�nderimde mail alamayanlara g�nder.
    </td>
  </TR>
  <tr>
    <td align="center" valign="middle">�zel Not :
      <TEXTAREA name="stnotes" cols="50" rows="3"></TEXTAREA>
      <hr>
    </td>
  </tr>
  
  <tr>
    <td align="center" valign="middle">
    <strong>Bir Kullan�c�n�n Sicil  Detay Raporunu G�nder veya G�r�nt�le :<br></strong>
    Kullan�c�n�n  Sicil Numaras� 
    <input type="text" class="input1" name="sicilnocrt" value="">
    <input type="button" value="G�r�nt�le" class="button" style="cursor:hand" onclick="send_one_mail('sicil', '1');">&nbsp;
    <input type="button" value="G�nder" class="button" style="cursor:hand" onclick="send_one_mail('sicil', '');">&nbsp;
    </td>
  </tr>
  <tr>
    <td align="center" valign="middle">
    <strong>Bir Kullan�c�n�n Dahili Detay Raporunu G�nder veya G�r�nt�le :<br></strong>
    Kullan�c�n�n Dahili Numaras�
    <input type="text" class="input1" name="extnocrt" value="">
    <input type="button" value="G�r�nt�le" class="button" style="cursor:hand" onclick="send_one_mail('ext', '1');">&nbsp;
    <input type="button" value="G�nder" class="button" style="cursor:hand" onclick="send_one_mail('ext', '');">&nbsp;
    </td>
  </tr>
  <tr>
    <td align="center" valign="middle"><strong>Y�netici Departman Raporunu G�nder veya G�r�nt�le<br></strong>
    Departman
                         <select name="DEPT_ID" class="select1" style="width:235">
                              <?
                               $strSQL = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS ";
                               echo    $cUtility->FillComboValuesWSQL($conn, $strSQL, true,  "");
                             ?>
                        </select>

    <input type="button" value="G�r�nt�le" class="button" style="cursor:hand" onclick="send_one_mail('dept', '1');">&nbsp;
    <input type="button" value="G�nder" class="button" style="cursor:hand" onclick="send_one_mail('dept', '');">&nbsp;
    </td>
  </tr>
</table>
<script>
  function ext_send_mails(){
    var snotes = document.all('stnotes').innerText;
    var unsnt = '';
    if(document.all('unsentmails').checked)
    {unsnt = '&unsentmails=1';}
    if(window.confirm('Bu Komut T�m Kullan�c�lara toplu mail g�nderme prosed�r�n� �al��t�racak ve biraz zaman alacakt�r! Emin misiniz?')){location.href='/crons/ykb_ext_detail.php?force=1&stnotes='+snotes+unsnt;}
  }
  function dept_send_mails(){
    var snotes = document.all('stnotes').innerText;
    var unsnt = '';
    if(document.all('unsentmails').checked)
    {unsnt = '&unsentmails=1';}
    if(window.confirm('Bu Komut T�m departman y�neticilerine toplu mail g�nderme prosed�r�n� �al��t�racak ve biraz zaman alacakt�r! Emin misiniz?')){location.href='/crons/ykb_dept_sum.php?force=1&stnotes='+snotes+unsnt;}
  }
  
  function send_one_mail(sType, dbg){
    var debug='';
    var snotes = document.all('stnotes').innerText;
    if(dbg=='1'){
      debug='&debug=1';
    }
    if(sType=='sicil'){
      if(document.all('sicilnocrt').value==''){
        alert('Sicil numaras�n� girmelisiniz!');
        return 0;
      }
      location.href='/crons/ykb_sicil_detail.php?force=1&sicilnocrt='+document.all('sicilnocrt').value+debug;
    }else{
      
       if(sType=='ext'){
      if(document.all('extnocrt').value==''){
        alert('Dahili numaras�n� girmelisiniz!');
        return 0;
      }
      location.href='/crons/ykb_ext_detail.php?force=1&extnocrt='+document.all('extnocrt').value+debug;
    }else{
      if(document.all('DEPT_ID').value=='' || document.all('DEPT_ID').value=='-1'){
        alert('Departman Se�melisiniz!');
        return 0;
      }

      location.href='/crons/ykb_dept_sum.php?force=1&deptid='+document.all('DEPT_ID').value+debug;
    }
  }
  }
</script>
</center>
<?table_footer();
page_footer(0);?>

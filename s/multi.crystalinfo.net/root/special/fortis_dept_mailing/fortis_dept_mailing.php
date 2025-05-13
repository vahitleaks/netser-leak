<?    
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
     $cache_status = call_cache("reports/cache/ozet");
     $cUtility = new Utility();
     $cdb = new db_layer(); 
     $conn = $cdb->getConnection();
     $show_chart=false;
     require_valid_login();
      $SITE_ID=1;
     $usr_crt = "";
     if (right_get("SITE_ADMIN")){
       //Site admin hakký varsa herþeyi görebilir.  
       //Site id gelmemiþse kiþinin bulunduðu site raporu alýnýr.
       if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
     }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
       // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
       $SITE_ID = $SESSION['site_id'];
     }elseif(got_dept_right($SESSION["user_id"])==1){
       //Bir departmanýn raporunu görebiliyorsa kendi sitesindekileri girebilir.
       $SITE_ID = $SESSION['site_id'];
       //echo $dept_crt = get_depts_crt($SESSION["user_id"]);
       $usr_crt = get_users_crt($SESSION["user_id"], 1, $SITE_ID);
       $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini içerir.";
    }else{
       print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
       exit;
    } 

     $my_time0 = $t0;
     $my_time1 = $t1;


     cc_page_meta();
     echo "<center>";
    
    $sql_str1="SELECT SITE_NAME, MAX_ACCE_DURATION FROM SITES WHERE SITE_ID = ".$SITE_ID; 
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if (mysql_num_rows($result1)>0){
    $row1 = mysql_fetch_object($result1);
        $company = $row1->SITE_NAME;
        $max_acc_dur =  ($row1->MAX_ACCE_DURATION)*60;
    }else{
    print_error("Site paramatreleri bulunamadý.");
    exit;
  }
  
   $local_country_code = get_country_code($SITE_ID);//Ýlgili sitenin ülke kodu.

    function write_me($MyVal,$calc_type){
     if ($calc_type == 1){
       $MyRetVal = write_price($MyVal);
     }elseif($calc_type==2){
       $MyRetVal = calculate_all_time($MyVal);
     }else{
       print_error("Hatalý Durum Oluþtu. Lütfen Tekrar Deneyiniz.");
      exit;
     }
     return $MyRetVal;
  }


    //Zaman kriterleri ve tablo ismi seçimi baþlangýç
    add_time_crt();//Zaman kriteri
	$link  ="";

     if($forceMainTable)
       $CDR_MAIN_DATA = "CDR_MAIN_DATA";
     else
       $CDR_MAIN_DATA = getTableName($t0,$t1);

     if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  

    //Zaman kriterleri ve tablo ismi seçimi bitiþ
	
    $header="Çaðrýlarýn Dahililere Göre Daðýlýmý";

    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments Where InDeptId=".$DEPT_ID;
//    echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
?>
<center>
<div id="divPleaseWait">
<img src="/special/images/25-1.gif"><br>
Rapor Hazýrlanýyor. Lütfen Bekleyiniz!
</div>
</center>
<table width="100%" cellpadding="0">
  <tr>
    <td>
<?
      while($rw = mysql_fetch_object($result)){  
        
        $mainDeptid = $rw->InDeptId;
        if(displayDeptReport(deptIdsStr($rw->InDeptId, $SITE_ID), $rw->StDeptName, $SITE_ID)){
          displayReport($rw->InDeptId, $rw->StDeptName, $SITE_ID);
          nestedDepts($rw->InDeptId, $SITE_ID);
        }
        echo "<br><br>";
      }
?>
    </td>
  </tr>
</table>
<script>
  document.getElementById('divPleaseWait').style.display='none';
</script>
<?
function nestedDepts($deptId, $SITE_ID){
  global $cdb;
    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments where InMainDeptId=".$deptId;
//    echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
     if(mysql_num_rows($result)>0){
       while($rw1=mysql_fetch_object($result)){
         displayReport($rw1->InDeptId, $rw1->StDeptName, $SITE_ID);
         nestedDepts($rw1->InDeptId, $SITE_ID);
       }
     }
}

function deptIdsStr($deptId, $SITE_ID){
  global $cdb;
    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments where InMainDeptId=".$deptId;
    $deptsStr=$deptId;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
     if(mysql_num_rows($result)>0){
       while($rw1=mysql_fetch_object($result)){
         $deptsStr.=",".deptIdsStr($rw1->InDeptId, $SITE_ID);
       }
     }
    return $deptsStr;
}

function getDeptName($deptId){
  global $cdb;
    $sql_str=" SELECT StDeptName From TbDepartments where InDeptId=".$deptId;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
     $deptName="";
     if(mysql_num_rows($result)>0){
       $rw1=mysql_fetch_object($result);
       $deptName=$rw1->StDeptName;
     }
    return $deptName;
}

      ?>
<br>
<br>






<?
  function displayReport($dId, $deptName, $SITE_ID){
    global $cdb;global $CDR_MAIN_DATA;
    global $company;global $max_acc_dur;
    global $header;global $mainDeptid;
    global $t0;global $t1;
    $calc_type=1;
    $sql_str  = "call spExtSumm('$SITE_ID', '$CDR_MAIN_DATA', '$max_acc_dur', '$t0', '$t1', '$dId'); ";
    $mysqli = new mysqli(DB_IP, DB_USER, DB_PWD, DB_NAME );
    $ivalue=1;
    $result = $mysqli->query( $sql_str  );
    if ($result->num_rows>0) {
?>

<br><br>
<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2">
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
          <tr bgcolor="FFFFFF">
            <td colspan=6><a name="dept_<?=$dId?>"></a>
              Departman:<?=$deptName?>
            </td>
            <td>
              <a href="#dept_<?=$mainDeptid?>_main">Baþa Dön</a>
            </td>
          </tr>
          
          <tr>
                <td class="rep_table_header" width="28%">Dahili</td>
                <td class="rep_table_header" width="12%">Þehir Ýçi</td>
                <td class="rep_table_header" width="14%">Þehirler Arasý</td>
                <td class="rep_table_header" width="12%">GSM</td>
                <td class="rep_table_header" width="12%">Uluslar Arasý</td>
                <td class="rep_table_header" width="10%">Diðer </td>
                <td class="rep_table_header" width="12%">Toplam</td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
        $i = 0;;
        $my_pr=0;
        $m = 0;
        $j = 0;
        unset($dept_totals);
        $i = 0;

      while( $row = $result->fetch_row() ) {
            $j++;
            $i++;$j++;
            $bg_color = "E4E4E4";   
            if($i%2) $bg_color ="FFFFFF";
            echo " <tr  BGCOLOR=$bg_color>\n";
            $k_x = ($row[0]=="0"?"Dahili Yok":$row[0]);
            echo  " <td class=\"rep_td\">&nbsp;<b>".$k_x."</b> - ".$row[1]."</td>\n";
            $total = 0;
            for($k=2;$k<=7;$k++){
              echo " <td class=\"rep_td\" align=\"right\">".write_me($row[$k],$calc_type)."</td>\n";
              $total += $row[$k];
              $dept_totals[$k-1] += $row[$k];
            }
            echo "</tr>\n";
            $my_pr = $my_pr + $total;
            $m++;
      }
      $j++;
      echo  " <tr bgcolor=\"E4E4E4\"><td class=\"rep_td\">&nbsp;<b>Alt Toplamlar</b></td>\n";
      for($k=1;$k<=6;$k++){
        echo " <td class=\"rep_td\" align=\"right\"><b>".write_me($dept_totals[$k],$calc_type)."</b></td>\n";
      }
            echo "</tr>";   

            echo  " <tr bgcolor=\"000000\"><td colspan=7 height=\"1\" align=center valign=center></td></tr>\n";   
      //}
          //}  
      ?>
      </table>
    </td>
  </tr>
</table>  

<?
  }
$mysqli->close();

}
?>







<?
  function displayDeptReport($dId, $deptName, $SITE_ID){
    global $cdb;global $CDR_MAIN_DATA;
    global $company;global $max_acc_dur;
    global $header;global $mainDeptid;
    global $t0;global $t1;
    $calc_type=1;
    $sql_str  = "call spDeptSumm('$SITE_ID', '$CDR_MAIN_DATA', '$max_acc_dur', '$t0', '$t1', '$dId'); ";
    $mysqli = new mysqli(DB_IP, DB_USER, DB_PWD, DB_NAME );
    $ivalue=1;
    $result = $mysqli->query( $sql_str  );
    if($result->num_rows >0){
?>

<br><br>
<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center"><a name="dept_<?=$mainDeptid?>_main"></a>
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><img border="0" SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>logo2.gif" ></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>Departman:<?=$deptName?><br>Departman Özeti</TD>
              <TD width="25%" align=right><img SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>
      </td>
  </tr>
  <tr>
    <td colspan="2">
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
          <tr>
                <td class="rep_table_header" width="28%">Departman</td>
                <td class="rep_table_header" width="12%">Þehir Ýçi</td>
                <td class="rep_table_header" width="14%">Þehirler Arasý</td>
                <td class="rep_table_header" width="12%">GSM</td>
                <td class="rep_table_header" width="12%">Uluslar Arasý</td>
                <td class="rep_table_header" width="10%">Diðer </td>
                <td class="rep_table_header" width="12%">Toplam</td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
        $i = 0;;
        $my_pr=0;
        $m = 0;
        $j = 0;
        unset($dept_totals);
        $i = 0;

      while( $row = $result->fetch_row() ) {
            $j++;
            $i++;$j++;
            $bg_color = "E4E4E4";   
            if($i%2) $bg_color ="FFFFFF";
            echo " <tr  BGCOLOR=$bg_color>\n";
            $k_x = ($row[0]=="0"?"Dahili Yok":$row[0]);
            echo  " <td class=\"rep_td\">&nbsp;<a href=\"#dept_".$row[0]."\"><b>".$k_x."</b> - ".getDeptName($row[0])."</a></td>\n";
            $total = 0;
            for($k=1;$k<=6;$k++){
              echo " <td class=\"rep_td\" align=\"right\">".write_me($row[$k],$calc_type)."</td>\n";
              $total += $row[$k];
              $dept_totals[$k] += $row[$k];
            }
            echo "</tr>\n";
            $my_pr = $my_pr + $total;
            $m++;
      }
      $j++;
      echo  " <tr bgcolor=\"E4E4E4\"><td class=\"rep_td\">&nbsp;<b>Alt Toplamlar</b></td>\n";
      for($k=1;$k<=6;$k++){
        echo " <td class=\"rep_td\" align=\"right\"><b>".write_me($dept_totals[$k],$calc_type)."</b></td>\n";
      }
            echo "</tr>";   

            echo  " <tr bgcolor=\"000000\"><td colspan=7 height=\"1\" align=center valign=center></td></tr>\n";   
      //}
          //}  
      ?>
      </table>
    </td>
  </tr>
</table>  

<?
  $retVal=true;
}else{
  $retVal=false;
}
$mysqli->close();
return $retVal;
}

?>
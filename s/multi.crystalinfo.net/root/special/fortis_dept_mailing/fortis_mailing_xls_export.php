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
       $MyRetVal = number_format($MyVal,2, '.', ',');
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

    
  $xlsStyles=" <Styles>
  <Style ss:ID='Default' ss:Name='Normal'>
   <Alignment ss:Vertical='Bottom'/>
   <Borders/>
   <Font x:CharSet='162'/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID='s22'>
   <Font x:CharSet='162' x:Family='Swiss' ss:Bold='1'/>
  </Style>
  <Style ss:ID='s29'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='1'/>
   </Borders>
   <Font x:CharSet='162' x:Family='Swiss' ss:Color='#FFFFFF' ss:Bold='1'/>
   <Interior ss:Color='#808080' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s30'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='1'/>
   </Borders>
   <Font x:CharSet='162' x:Family='Swiss' ss:Bold='1'/>
   <Interior ss:Color='#C0C0C0' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s31'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='1'/>
   </Borders>
   <Interior ss:Color='#C0C0C0' ss:Pattern='Solid'/>
  </Style>
  <Style ss:ID='s32'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='1'/>
   </Borders>
   <Font x:CharSet='162' x:Family='Swiss' ss:Bold='1'/>
  </Style>
  <Style ss:ID='s33'>
   <Borders>
    <Border ss:Position='Bottom' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Left' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Right' ss:LineStyle='Continuous' ss:Weight='1'/>
    <Border ss:Position='Top' ss:LineStyle='Continuous' ss:Weight='1'/>
   </Borders>
  </Style>
  <Style ss:ID='s34'>
   <Font x:CharSet='162' x:Family='Swiss' ss:Bold='1'/>
   <NumberFormat ss:Format='Short Date'/>
  </Style>
 </Styles>";
    
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
    <td colspan="2" align="center">
<?            
      while($rw = mysql_fetch_object($result)){  
        $mainDeptid = $rw->InDeptId;
        $maxLevelCnt =0;
        $minLevelCnt =1000;
        $deptIdStrCrt = deptIdsStr($rw->InDeptId, $SITE_ID);
        $maxLevelCnt -= $minLevelCnt;
        $htmlstr=displayDeptReport($rw->InDeptId, $rw->StDeptName, $SITE_ID);
        if($htmlstr!=false){


            $data = "<?xml version='1.0'?>
            <?mso-application progid='Excel.Sheet'?>
            <Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet'
             xmlns:o='urn:schemas-microsoft-com:office:office'
             xmlns:x='urn:schemas-microsoft-com:office:excel'
             xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet'
             xmlns:html='http://www.w3.org/TR/REC-html40'>".$xlsStyles;
          $htmlstr = $data .$htmlstr;

          $htmlstr.=displayAllListReport($deptIdStrCrt, $rw->StDeptName, $SITE_ID);
          $htmlstr.=displayReport($rw->InDeptId, $rw->StDeptName, $SITE_ID);
          $htmlstr.=nestedDepts($rw->InDeptId, $SITE_ID);
         
          $htmlstr .= "</Workbook>\n";
          //echo $htmlstr;
          $fd = fopen($DOCUMENT_ROOT."/temp/pdfs/excel_export_$mainDeptid.xls", w);
          fwrite($fd,turkish2utf($htmlstr));
          fclose($fd);
        }
	echo  "<center><a href=\"/temp/pdfs/excel_export_$mainDeptid.xls\" target=_blank>Download excel_export_$mainDeptid.xls</a><br><br></center>";

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
         $htmlStr .= displayReport($rw1->InDeptId, $rw1->StDeptName, $SITE_ID);
         $htmlStr .= nestedDepts($rw1->InDeptId, $SITE_ID);
       }
     }
   return $htmlStr;
}
function calcCurrentLevel($deptId, &$tmpLevel){
  global $mainDeptid ;global $cdb;
  $sql_str=" SELECT d2.InDeptId From TbDepartments d1 
             inner join TbDepartments d2 on d2.InDeptId = d1.InMainDeptId where d1.InDeptId=".$deptId;
  if (!($cdb->execute_sql($sql_str,$resp,$error_msg))){
    print_error($error_msg);
    exit;
  }
  if(mysql_num_rows($resp)>0){
    $tmpLevel++;
    if($mainDeptid!=$rw1->InDeptId){
      $rw1=mysql_fetch_object($resp);
      calcCurrentLevel($rw1->InDeptId, $tmpLevel);
    }
  }
}

function deptIdsStr($deptId, $SITE_ID){
  global $cdb;global $maxLevelCnt;global $minLevelCnt;
    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments where InMainDeptId=".$deptId;
    $deptsStr=$deptId;
    $tmpLevel=0;
    calcCurrentLevel($deptId, $tmpLevel);
    if($tmpLevel>$maxLevelCnt){$maxLevelCnt=$tmpLevel;}
    if($tmpLevel<=$minLevelCnt){$minLevelCnt=$tmpLevel;}
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

 




  function displayAllListReport($dId, $deptName, $SITE_ID){
    global $cdb;global $CDR_MAIN_DATA;
    global $company;global $max_acc_dur;
    global $header;global $mainDeptid;
    global $t0;global $t1;
    $calc_type=1;
    $sql_str  = "call spAllExtSumm('$SITE_ID', '$CDR_MAIN_DATA', '$max_acc_dur', '$t0', '$t1', '$dId'); ";
    $mysqli = new mysqli(DB_IP, DB_USER, DB_PWD, DB_NAME );
    $ivalue=1;
    $result = $mysqli->query( $sql_str  );
    $htmlStr="";
    if ($result->num_rows>0) {

    $htmlStr="<Worksheet ss:Name='TUM LISTE'>
                <Table>
                    <Row>
                        <Cell ss:MergeAcross='7' ss:StyleID='s22'><Data ss:Type='String'>Departman:".$deptName." Tüm Dahililer</Data></Cell>
          </Row>
          <Row>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Dahili</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Departman</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Þehir Ýçi</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Þehirler Arasý</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>GSM</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Uluslar Arasý</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Diðer </Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Toplam</Data></Cell>
          </Row>
          ";
        $i = 0;;
        $my_pr=0;
        unset($dept_totals);
        $i = 0;

      while( $row = $result->fetch_row() ) {
            $i++;
            $modus=$i%2;
            $modus=($modus*2)+30;
            $htmlStr.= " <Row>
            ";
            $k_x = ($row[0]=="0"?"Dahili Yok":$row[0]);
            $htmlStr.= " <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'> <b>".$k_x."</b> - ".get_ext_name2($row[0], $SITE_ID)."</Data></Cell>
            ";
            $htmlStr.= " <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'>".$row[1]."</Data></Cell>
            ";
            $total = 0;
            for($k=2;$k<=7;$k++){
              $htmlStr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($row[$k],$calc_type)."</Data></Cell>
              ";
              $total += $row[$k];
              $dept_index = $k-1;
              $dept_totals[$dept_index] += $row[$k];
            }
            $htmlStr.= "</Row>\n
            ";
            $my_pr = $my_pr + $total;

      }
      $htmlStr.=  " <Row><Cell ss:MergeAcross='1' ss:StyleID='s29'><Data ss:Type='String'>Alt Toplamlar</Data></Cell>
      ";
      for($k=1;$k<=6;$k++){
        $htmlStr.= " <Cell ss:StyleID='s29'><Data ss:Type='Number'>".write_me($dept_totals[$k],$calc_type)."</Data></Cell>
        ";
      }
      $htmlStr.= "</Row>
      ";   
      $htmlStr.="      </Table> 
      </Worksheet>
      ";

  }
$mysqli->close();
return $htmlStr;
}





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
    $htmlStr="";
    if ($result->num_rows>0) {

    $htmlStr="<Worksheet ss:Name='".getSheetName($deptName, $dId)."'>
                <Table>
                    <Row>
                        <Cell ss:MergeAcross='6' ss:StyleID='s22'><Data ss:Type='String'>Departman:".$deptName."</Data></Cell>
          </Row>
          <Row>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Dahili</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Þehir Ýçi</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Þehirler Arasý</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>GSM</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Uluslar Arasý</Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Diðer </Data></Cell>
                <Cell ss:StyleID='s29'><Data ss:Type='String'>Toplam</Data></Cell>
          </Row>";
        $i = 0;;
        $my_pr=0;
        unset($dept_totals);
        $i = 0;

      while( $row = $result->fetch_row() ) {
            $i++;
            $modus=$i%2;
            $modus=($modus*2)+30;
            $htmlStr.= " <Row>";
            $k_x = ($row[0]=="0"?"Dahili Yok":$row[0]);
            $htmlStr.= " <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'> <b>".$k_x."</b> - ".$row[1]."</Data></Cell>";
            $total = 0;
            for($k=2;$k<=7;$k++){
              $htmlStr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($row[$k],$calc_type)."</Data></Cell>";
              $total += $row[$k];
              $dept_totals[$k-1] += $row[$k];
            }
            $htmlStr.= "</Row>";
            $my_pr = $my_pr + $total;
      }
      $htmlStr.=  " <Row><Cell ss:StyleID='s29'><Data ss:Type='String'>Alt Toplamlar</Data></Cell>";
      for($k=1;$k<=6;$k++){
        $htmlStr.= " <Cell ss:StyleID='s29'><Data ss:Type='Number'>".write_me($dept_totals[$k],$calc_type)."</Data></Cell>";
      }
      $htmlStr.= "</Row>";   
      $htmlStr.="      </Table> </Worksheet>";

  }
$mysqli->close();
return $htmlStr;
}







  function displaySubDeptsReportRows($dId, $SITE_ID, &$i, &$my_pr, &$dept_totals, &$htmlstr, $up_dept_row_totals = ""){
    global $cdb;global $CDR_MAIN_DATA;
    global $company;global $max_acc_dur;
    global $t0;global $t1;global $maxLevelCnt;global $minLevelCnt;
    $calc_type=1;

    //$sql_str  = "call spHDeptSumm('$SITE_ID', '$CDR_MAIN_DATA', '$max_acc_dur', '$t0', '$t1', '$dId'); ";

    $sql_str  = "select A.DEPT_ID, sum((case A.TYPE when 0 Then TOTAL Else 0 end)) as `Þehir içi`,
    sum((case A.TYPE when 1 Then TOTAL Else 0 end)) as BB,sum((case A.TYPE when 2 Then TOTAL Else 0 end)) as CC,
    sum((case A.TYPE when 3 Then TOTAL Else 0 end)) as DD,sum((case A.TYPE when 0 Then 0 when 1 then 0 when 2 then 0 when 3 then 0 Else TOTAL end)) as EE,
    sum(TOTAL) as TT from (SELECT CDR_MAIN_DATA.LocationTypeid AS TYPE, EXTENTIONS.DEPT_ID AS DEPT_ID, SUM(CDR_MAIN_DATA.PRICE) AS TOTAL FROM 
    ".$CDR_MAIN_DATA." CDR_MAIN_DATA inner join EXTENTIONS on EXTENTIONS.EXT_NO=CDR_MAIN_DATA.ORIG_DN 
      where CDR_MAIN_DATA.SITE_ID='".$SITE_ID."' and ERR_CODE=0 and CALL_TYPE=1 and DURATION<".$max_acc_dur." 
     and EXTENTIONS.DEPT_ID in (select dp.InDeptId from TbDepartments dp where dp.InMainDeptId=".$dId.") 
      and MY_DATE>='".$t0."' and MY_DATE<='".$t1."'  GROUP BY DEPT_ID, TYPE) A group by A.DEPT_ID";
        if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
    unset($sub_dept_totals);
    unset($sub_dept_totals_from_fnc);
    if(mysql_num_rows($result1) >0){
      $tmpLevel=0;
      calcCurrentLevel($dId, $tmpLevel); 
      $tmpLevel -= $minLevelCnt;
      while( $rowSub = mysql_fetch_row($result1) ) {
            $i++;
            $modus=$i%2;
            $modus=($modus*2)+30;
            $htmlstr.= " <Row>";
            for($dongit=0;$dongit<=$tmpLevel;$dongit++){
              $htmlstr.=    "   <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'></Data></Cell>
              ";
            }
            $htmlstr.=  " <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'>".getDeptName($rowSub[0])."</Data></Cell>
            ";
            for($dongit=1;$dongit<$maxLevelCnt-$tmpLevel;$dongit++){
              $htmlstr.=    "   <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'></Data></Cell>
              ";
            }
            unset($sub_dept_row_totals);

            $total = 0;
            for($k=1;$k<=6;$k++){
              $total += $rowSub[$k];
              $dept_totals[$k] += $rowSub[$k];
              $sub_dept_totals[$k] += $rowSub[$k];
              $sub_dept_row_totals[$k] = $rowSub[$k];
            }
            $my_pr = $my_pr + $total;
            $htmstr = "";
            $sub_dept_totals_from_fnc = displaySubDeptsReportRows($rowSub[0], $SITE_ID, $i, $my_pr, $dept_totals, $htmstr, $sub_dept_row_totals);
            if(is_array($sub_dept_totals_from_fnc)){
              for($k=1;$k<=6;$k++){
                $sub_dept_totals[$k] += $sub_dept_totals_from_fnc[$k];
              }
            }

          for($k=1;$k<=6;$k++){
            if(is_array($sub_dept_totals_from_fnc)){
              $htmlstr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($rowSub[$k]+$sub_dept_totals_from_fnc[$k],$calc_type)."</Data></Cell>
              ";
            }else{
              $htmlstr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($rowSub[$k],$calc_type)."</Data></Cell>
              ";
            }
          }
            
            $htmlstr.= "</Row>
            ";
            $htmlstr.= $htmstr;
      }

    }
    return $sub_dept_totals;
}



  function displayDeptReport($dId, $deptName, $SITE_ID){
    global $cdb;global $CDR_MAIN_DATA;
    global $company;global $max_acc_dur;
    global $header;global $mainDeptid;
    global $t0;global $t1;global $maxLevelCnt;
    $calc_type=1;
    $sql_str  = "call spDeptSumm('$SITE_ID', '$CDR_MAIN_DATA', '$max_acc_dur', '$t0', '$t1', '$dId'); ";
    $mysqli = new mysqli(DB_IP, DB_USER, DB_PWD, DB_NAME );
    $ivalue=1;
    $result = $mysqli->query( $sql_str  );
    if($result->num_rows >0){
     $krtrstartDate="";$krtrendDate="";
     if($t0!=""){$krtrstartDate=$t0."T00:00:00.000";}
     if($t1!=""){$krtrendDate=$t1."T00:00:00.000";}
     $htmlstr="<Worksheet ss:Name='Departman Özeti'>
                <Table>\n
                    <Row>
                        <Cell ss:StyleID='s22'><Data ss:Type='String'>Rapor Aralýðý :</Data></Cell>
                        <Cell ss:StyleID='s34'><Data ss:Type='DateTime'>$t0</Data></Cell>
                        <Cell ss:StyleID='s34'><Data ss:Type='DateTime'>$t1</Data></Cell>
                    </Row>
                    <Row>
                        <Cell ss:MergeAcross='6' ss:StyleID='s22'><Data ss:Type='String'>".$company." Departman Özeti</Data></Cell>
                    </Row>
                    <Row>
                      <Cell ss:MergeAcross='6' ss:StyleID='s22'><Data ss:Type='String'>Departman:".$deptName."</Data></Cell>
                    </Row>
                    <Row>
                    ";
                    for($i=0;$i<=$maxLevelCnt;$i++){
      $htmlstr.=    "   <Cell ss:StyleID='s29'><Data ss:Type='String'></Data></Cell>
      ";
                    }
                    //<Cell ss:StyleID='s29'><Data ss:Type='String'>Departman</Data></Cell>
      $htmlstr.=    "   <Cell ss:StyleID='s29'><Data ss:Type='String'>Þehir Ýçi</Data></Cell>
                        <Cell ss:StyleID='s29'><Data ss:Type='String'>Þehirler Arasý</Data></Cell>
                        <Cell ss:StyleID='s29'><Data ss:Type='String'>GSM</Data></Cell>
                        <Cell ss:StyleID='s29'><Data ss:Type='String'>Uluslar Data</Data></Cell>
                        <Cell ss:StyleID='s29'><Data ss:Type='String'>Diðer</Data> </Cell>
                        <Cell ss:StyleID='s29'><Data ss:Type='String'>Toplam</Data></Cell>
                  </Row>";
        $i = 0;;
        $my_pr=0;
        unset($dept_totals);
        $i = 0;
      while( $row = $result->fetch_row() ) {
            $i++;
            $modus=$i%2;
            $modus=($modus*2)+30;
            $htmlstr.= " <Row>";
            $htmlstr.=  " <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'>".getDeptName($row[0])."</Data></Cell>";
                    for($dongit=0;$dongit<$maxLevelCnt;$dongit++){
      $htmlstr.=    "   <Cell ss:StyleID='s".$modus."'><Data ss:Type='String'></Data></Cell>
      ";
                    }
            $total = 0;
            for($k=1;$k<=6;$k++){
              //$htmlstr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($row[$k],$calc_type)."</Data></Cell>";
              $total += $row[$k];
              $dept_totals[$k] += $row[$k];
            }
            $my_pr = $my_pr + $total;
            unset($sub_dept_totals_from_fnc);
            $htmstr = "";
            $sub_dept_totals_from_fnc = displaySubDeptsReportRows($row[0], $SITE_ID, $i, $my_pr, $dept_totals, $htmstr);
              for($k=1;$k<=6;$k++){
                if(is_array($sub_dept_totals_from_fnc)){
                  $htmlstr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($row[$k]+$sub_dept_totals_from_fnc[$k],$calc_type)."</Data></Cell>
                  ";
                }else{
                  $htmlstr.= " <Cell ss:StyleID='s".($modus+1)."'><Data ss:Type='Number'>".write_me($row[$k],$calc_type)."</Data></Cell>
                  ";
                }
              }
            $htmlstr.= "</Row>
            ";
            $htmlstr.= $htmstr;
      }
      /*$htmlstr.=  " <Row><Cell ss:StyleID='s29'><Data ss:Type='String'>Alt Toplamlar</Data></Cell>";
      for($i=0;$i<$maxLevelCnt;$i++){
        $htmlstr.=    "   <Cell ss:StyleID='s29'><Data ss:Type='String'></Data></Cell>
        ";
      }
      for($k=1;$k<=6;$k++){
        $htmlstr.= " <Cell ss:StyleID='s29'><Data ss:Type='Number'>".write_me($dept_totals[$k],$calc_type)."</Data></Cell>";
      }
            $htmlstr.= "</Row>
            ";   
*/
     $htmlstr.="</Table></Worksheet>";


  $retVal=$htmlstr;
}else{
  $retVal=false;
}
$mysqli->close();
return $retVal;
}


function getSheetName($deptName, $deptId){
  $deptNameArr = Split(" ", $deptName);
  $retval="";
  for($x=0;$x<=sizeof($deptNameArr);$x++){
    if(trim($deptNameArr[$x])!=""){
      $retval .= substr($deptNameArr[$x], 0, 1).".";
    } 
  }
  return strtoupper($retval)." ".$deptId;
}

?>


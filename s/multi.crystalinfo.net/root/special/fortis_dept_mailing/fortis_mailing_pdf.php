<?    
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
     require_once(dirname($DOCUMENT_ROOT)."/root/tcpdf/config/lang/tur.php");
     require_once(dirname($DOCUMENT_ROOT)."/root/tcpdf/tcpdf.php");
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
     $MY_DATE="g";


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

    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments";
//    echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
      while($rw = mysql_fetch_object($result)){  
        $mainDeptid = $rw->InDeptId;
        $htmlstr=displayDeptReport(deptIdsStr($rw->InDeptId, $SITE_ID), $rw->StDeptName, $SITE_ID);
        if($htmlstr!=false){
          $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true); 
          $pdf->SetCreator(PDF_CREATOR);
          $pdf->SetAuthor("Crystalinfo");
          $pdf->SetTitle("Departman Raporu");
          $pdf->SetSubject("Departman Raporu");
          $pdf->SetKeywords("TCPDF, PDF, crystalinfo, rapor, departman");
          $pdf->SetHeaderData("logo2.gif", 30, turkish2utf($rw->StDeptName), turkish2utf("Aylýk Çaðrý Profili"));
          $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
          $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
          $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
          $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
          $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
          $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
          $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
          $pdf->setLanguageArray($l); 
          $pdf->AliasNbPages();
          $pdf->AddPage();
          $pdf->SetFont("freeserif", "", 10);
          $pdf->Bookmark(turkish2utf('Departman Özeti'), 0, 0);
          $pdf->writeHTML(turkish2utf($htmlstr));
          $htmlstr.=displayReport($rw->InDeptId, $rw->StDeptName, $SITE_ID,$pdf);
          //$pdf->writeHTML(turkish2utf($htmlstr));
          $htmlstr.=nestedDepts($rw->InDeptId, $SITE_ID, $pdf);
          echo $htmlstr;
          $pdf->Ln(2);
          $pdf->Output(dirname($DOCUMENT_ROOT)."/root/temp/pdfs/mailingreport_$mainDeptid.pdf", "F");
        }
        echo "Bir departmanýn sonuuuu<br>";
      }

function nestedDepts($deptId, $SITE_ID, $pdf){
  global $cdb;
    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments where InMainDeptId=".$deptId;
//    echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
     if(mysql_num_rows($result)>0){
       while($rw1=mysql_fetch_object($result)){
         $htmlStr .= displayReport($rw1->InDeptId, $rw1->StDeptName, $SITE_ID, $pdf);
         $htmlStr .= nestedDepts($rw1->InDeptId, $SITE_ID, $pdf);
       }
     }
   return $htmlStr;
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

 







  function displayReport($dId, $deptName, $SITE_ID, $pdf){
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
    $pdf->AddPage();
    $pdf->Bookmark(turkish2utf($deptName), 0, 0);
    $htmlStr="<br/>
    <table border=1>
          <tr>
            <td colspan=7>Departman:".$deptName."</td>
          </tr>
          <tr>
                <td>Dahili</td>
                <td>Þehir Ýçi</td>
                <td>Þehirler Arasý</td>
                <td>GSM</td>
                <td>Uluslar Arasý</td>
                <td>Diðer </td>
                <td>Toplam</td>
          </tr>";
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
            $htmlStr.= " <tr>";
            $k_x = ($row[0]=="0"?"Dahili Yok":$row[0]);
            $htmlStr.= " <td> <b>".$k_x."</b> - ".get_ext_name2($row[0], $SITE_ID)."</td>";
            $total = 0;
            for($k=1;$k<=6;$k++){
              $htmlStr.= " <td>".write_me($row[$k],$calc_type)."</td>";
              $total += $row[$k];
              $dept_totals[$k] += $row[$k];
            }
            $htmlStr.= "</tr>";
            $my_pr = $my_pr + $total;
            $m++;
      }
      $j++;
      $htmlStr.=  " <tr><td> <b>Alt Toplamlar</b></td>";
      for($k=1;$k<=6;$k++){
        $htmlStr.= " <td><b>".write_me($dept_totals[$k],$calc_type)."</b></td>";
      }
      $htmlStr.= "</tr>";   
      $htmlStr.="      </table> <br/>";

      $pdf->writeHTML(turkish2utf($htmlStr));
  }
$mysqli->close();
return $htmlStr;
}









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

     $htmlstr="<table border=1>
                    <tr>
                      <td colspan=7><strong>".$company." Departman Özeti</strong></td>
                    </tr>
                    <tr>
                      <td colspan=7>Departman:".$deptName."</td>
                    </tr>
                  <tr>
                        <td><strong>Departman</strong></td>
                        <td><strong>Þehir Ýçi</strong></td>
                        <td><strong>Þehirler Arasý</strong></td>
                        <td><strong>GSM</strong></td>
                        <td><strong>Uluslar Arasý</strong></td>
                        <td><strong>Diðer</strong> </td>
                        <td><strong>Toplam</strong></td>
                  </tr>";
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
            $htmlstr.= " <tr>";
            $k_x = ($row[0]=="0"?"Dahili Yok":$row[0]);
            $htmlstr.=  " <td><b>".$k_x."</b> - ".getDeptName($row[0])."</td>";
            $total = 0;
            for($k=1;$k<=6;$k++){
              $htmlstr.= " <td>".write_me($row[$k],$calc_type)."</td>";
              $total += $row[$k];
              $dept_totals[$k] += $row[$k];
            }
            $htmlstr.= "</tr>\n";
            $my_pr = $my_pr + $total;
            $m++;
      }
      $j++;
      $htmlstr.=  " <tr><td> <b>Alt Toplamlar</b></td>";
      for($k=1;$k<=6;$k++){
        $htmlstr.= " <td><b>".write_me($dept_totals[$k],$calc_type)."</b></td>";
      }
            $htmlstr.= "</tr>";   

     $htmlstr.="</table> <br/>";


  $retVal=$htmlstr;
}else{
  $retVal=false;
}
$mysqli->close();
return $retVal;
}

?>
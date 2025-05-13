<?    
     require_once("doc_root.cnf");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");


      session_cache_limiter('nocache');
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      clearstatcache ();

     $cache_status = call_cache("reports/cache/ozet");
     $cUtility = new Utility();
     $cdb = new db_layer(); 
     $conn = $cdb->getConnection();
     $show_chart=false;
      $SITE_ID=1;
     $usr_crt = "";
     $MY_DATE="g";


    
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

    
// $emailCrt="ali.kirval@fortis.com.tr";
    if($emailCrt!=""){$emailCrt=" Where StMailAddress='".$emailCrt."'";}
    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments ".$emailCrt." ORDER By InDeptId";
//    echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
      while($rw = mysql_fetch_object($result)){  
        $mainDeptid = $rw->InDeptId;
        $maxLevelCnt =0;
        $minLevelCnt =1000;
        echo "Main dept ID :".$mainDeptid."\n";
        $deptIdStrCrt = deptIdsStr($rw->InDeptId, $SITE_ID);
        $maxLevelCnt -= $minLevelCnt;
    
        
        
       // echo "Bir departmanýn sonuuuu<br>";
      }

function calcCurrentLevel($deptId, &$tmpLevel){
  global $mainDeptid ;global $cdb;
  $sql_str=" SELECT d2.InDeptId From TbDepartments d1 
             inner join TbDepartments d2 on d2.InDeptId = d1.InMainDeptId where d1.InDeptId=".$deptId;
  if (!($cdb->execute_sql($sql_str,$resp,$error_msg))){
    print_error($error_msg);
    exit;
  }
  if($deptId==21235)
    echo "number of rows :".mysql_num_rows($resp)."\n";
  if(mysql_num_rows($resp)>0){
    $tmpLevel++;
  if($deptId==21235)
    echo "Dept Id :".$rw1->InDeptId."\n";
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
    echo $deptId.": calculating Level \n";
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
?>
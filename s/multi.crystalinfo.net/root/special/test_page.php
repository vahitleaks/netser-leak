<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   if (!defined("IMAGE_ROOT")){ // Note that it should be quoted
      define("IMAGE_ROOT", "/images/");
   }  
   session_cache_limiter("nocache");


//   require_valid_login();

   $cUtility = new Utility();
   $cdb = new db_layer();
   $conn = $cdb->getConnection();
   page_track();
   cc_page_meta(0);
   $SITE_ID = 1;

$mainDeptid = "19221";
$maxLevelCnt = 0;
 
echo deptIdsStr($mainDeptid, $SITE_ID);
echo "<br><br><br> ";
echo $maxLevelCnt;

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
  global $cdb;global $maxLevelCnt;
    $sql_str=" SELECT InDeptId, StDeptName, StMailAddress, InMainDeptId From TbDepartments where InMainDeptId=".$deptId;
    $tmpLevel=0;
    calcCurrentLevel($deptId, $tmpLevel);
    $deptsStr=$deptId;
    if($tmpLevel>$maxLevelCnt){$maxLevelCnt=$tmpLevel;}
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

<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   if (!defined("IMAGE_ROOT")){ // Note that it should be quoted
      define("IMAGE_ROOT", "/images/");
   }  
   $cUtility = new Utility();
   $cDB = new db_layer();
   session_cache_limiter('nocache');
   require_valid_login();
   $conn = $cDB->getConnection();

/*lstType   = Request("lstType")
InDefId   = Request("InDefId")
InDefTpId = Request("InDefTpId")
StDef     = Request("StDef")
StVal     = Request("StVal")
InSubId   = Request("InSubId")
itemLevel = Request("level")*/
$StDef=str_replace("~and~","&",$StDef);
$StVal=str_replace("~and~","&",$StVal);

if ($lstType=="remove"){
  $stSonuc = checkRelatedRecs($InDefId);
  $inSonuc = "fbdn";
  if ($stSonuc==""){
    $sql_str = "Delete From TbDepartments Where InDeptId = ".$InDefId;
    if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $sql_str = "Delete From DEPTS Where DEPT_ID = ".$InDefId;
    if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $inSonuc = "1";
    $stSonuc="1";
  }
  echo "<?xml version=\"1.0\" encoding=\"ISO-8859-9\" ?>\n";
   echo "<DATABASE>\n";
   echo "<SUB>\n";
   echo "<idCol>".$inSonuc."</idCol>\n";
   echo "<nameCol>".$stSonuc."</nameCol>\n";
   echo "</SUB>\n";
   echo "</DATABASE>";
  die();
}else if($lstType=="add"){
  if($InSubId==""){ $InSubId="0";}
  if($itemLevel==""){ $itemLevel="0";}
 /* $sqlChk = "Select isNull(max(InId),0) as newId From TbDefinitions Where InDefinitionTypeId=" & InDefTpId & " and InLevel=" & itemLevel
  set rsMax = runSqlReturnRS(session("conn"), sqlChk, "") 
  newId= rsMax(0)
  newId=newId+1*/
  $strSQL = "insert into TbDepartments(InMainDeptId,StDeptName,StMailAddress,InLevel, DtInsertDateTime, StInsertedBy, InSiteId) 
  values(".$InSubId.", '".$StDef."', '".$StVal."', ".$itemLevel.", now(), '" . $SESSION["username"] . "', '".$SESSION["site_id"]."')";
  if (!($cDB->execute_sql($strSQL,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
  $DEPT_ID=mysql_insert_id();
  $strSQL = "insert into DEPTS(DEPT_ID,DEPT_NAME,DEPT_RSP_EMAIL,SITE_ID) 
  values(".$DEPT_ID.", '".$StDef."', '".$StVal."', '".$SESSION["site_id"]."')";
  if (!($cDB->execute_sql($strSQL,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
  echo "<?xml version=\"1.0\" encoding=\"ISO-8859-9\" ?>\n" ;
  echo "<DATABASE>\n" ;
  echo "<SUB>\n";
   echo  "<idCol>1</idCol>\n" ;
   echo "<nameCol>1</nameCol>\n" ;
   echo "</SUB>\n";
   echo "</DATABASE>";
  die();
}else if($lstType=="upd"){
  $strSQL = "update  TbDepartments set StDeptName='".$StDef."',StMailAddress='".$StVal."', StUpdatedBy='" . $SESSION["username"] . "', 
  DtUpdateDateTime=now()   Where InDeptId = ".$InDefId ;
  if (!($cDB->execute_sql($strSQL,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
  $strSQL = "update  DEPTS set DEPT_NAME='".$StDef."',DEPT_RSP_EMAIL='".$StVal."' Where DEPT_ID = ".$InDefId ;
  if (!($cDB->execute_sql($strSQL,$result1,$error_msg))){
      print_error($error_msg);
      exit;
    }
  echo "<?xml version=\"1.0\" encoding=\"ISO-8859-9\" ?>\n" ;
  echo "<DATABASE>\n" ;
   echo "<SUB>\n" ;
   echo  "<idCol>1</idCol>\n" ;
   echo "<nameCol>1</nameCol>\n" ;
   echo "</SUB>\n" ;
   echo "</DATABASE>";
  die();
}

function checkRelatedRecs($InDefId){
  global $cDB;
  $retStr="";
  $sqlStr="SELECT EXT_ID FROM EXTENTIONS WHERE DEPT_ID=".$InDefId;
  if (!($cDB->execute_sql($sqlStr,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $i=0;
  while($row = mysql_fetch_object($result)){
    $i++;
  }
  if($i>0){
    $retStr="Bu departmana $i adet dahili atanmýþtýr.\n Dahili atanmýþ bir departmaný silemezsiniz!";
  }
  return $retStr;
}
?>

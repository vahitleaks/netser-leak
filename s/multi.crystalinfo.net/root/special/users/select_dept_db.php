<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   require_valid_login();
   $conn = $cdb->getConnection();
   
   //Site Admin veya Admin Hakk� yoksa bu tan�m� yapamamal�
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Buray� G�rme Hakk�n�z Yok");
    exit;
   }
  if($SITE_ID=="" || $SITE_ID=="-1" || $USER_ID=="" || $USER_ID=="-1"){
        print_error("Yanl�� giri� yap�lm��!");
        exit;
  }
  $sql_str = "DELETE FROM DEPT_REP_RIGHTS WHERE USER_ID = '$USER_ID' AND SITE_ID = '$SITE_ID'";
  if (!($cdb->execute_sql($sql_str, $result, $error_msg))){
    print_error($error_msg);
    exit;
  }
  if($act=="siteadd"){
    $sqlInsert = "INSERT INTO DEPT_REP_RIGHTS(SITE_ID, USER_ID, DEPT_ID) values('$SITE_ID', '$USER_ID', 0)";
    if (!($cdb->execute_sql($sqlInsert,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    header("Location:select_dept.php?USER_ID=".$USER_ID."&SITE_ID=".$SITE_ID);
    exit;
  }else if($act=="sitedel"){
    header("Location:select_dept.php?USER_ID=".$USER_ID."&SITE_ID=".$SITE_ID);
    exit;
  }
  if($chkBoxes=="" || $chkBoxes=="0"){
        print_error("Yanl�� giri� yap�lm��!");
        exit;
  }
  for($i=1;$i<=$chkBoxes;$i++){
    $tmpVar = "chkbox_".$i;
    $chkVal =$$tmpVar;
    if(strlen($chkVal))
      insertIntoRepDept($SITE_ID, $USER_ID, $chkVal);
  }
  header("Location:select_dept.php?USER_ID=".$USER_ID."&SITE_ID=".$SITE_ID);
  
  function insertIntoRepDept($SITE_ID, $USER_ID, $DEPT_ID){
    global $cdb;
    $sqlInsert = "INSERT INTO DEPT_REP_RIGHTS(SITE_ID, USER_ID, DEPT_ID) values('$SITE_ID', '$USER_ID', '$DEPT_ID')";
    if (!($cdb->execute_sql($sqlInsert,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
  }
  
?>
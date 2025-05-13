<?
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  $conn = $cdb->getConnection();

  if(!$conn) exit;

  function heavy_sqls(){
    global $cdb ;
    $sql_str = "SELECT ID, `SQL` FROM HEAVY_SQLS" ;
	if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
      exit;
    }
    while($row1 = mysql_fetch_object($result1)){
      $sql =  $row1->SQL ;
    if(!$sql) return;
      if (!($cdb->execute_sql($sql,$result,$error_msg))){
        //print_error($error_msg);
        exit;
      }
      $str = "";
      while($row = mysql_fetch_row($result)){
        for($k=0;$k<mysql_num_fields($result);$k++){
          $field = mysql_fetch_field($result,$k);
          $str .= $field->name."||";
          $str .= $row[$k].";;";
        }
        $str .= "##";
      }
    $str = str_replace("'","\'",$str);
      $sql_str = "UPDATE HEAVY SET VALUE = '$str' WHERE SQL_ID = '$row1->ID'" ;
      if (!($cdb->execute_sql($sql_str,$result3,$error_msg))){
        print_error($error_msg);
        exit;
      }
    }
  }
   //run heavy_sql
   heavy_sqls();
?>
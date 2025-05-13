<?
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php"); 
  $cdb = new db_layer();

  //GET THE REQUIRED SYSTEM PARAMETERS MAXIMUM RECORD COUNT AND MAX REPORTABLE DAYS
  $MAX_REC_COUNT= get_system_prm('MAX_RECORD_COUNT');
  $MAX_RECORD_DAYS= get_system_prm('MAX_RECORD_DAYS');

  // DELETE THE ARCHIEVED ITEMS THAT ARE MORE THAN MAX_RECORD_COUNT
  $qry = "SELECT COUNT(ID) AS RAW_ADET FROM RAW_ARCHIEVE";
  if (!($cdb->execute_sql($qry, $result, $error_msg))){
    echo $error_msg;
    exit;
  }
  $row = mysql_fetch_object($result);
  if ($row->RAW_ADET > $MAX_REC_COUNT){
    $qry = "SELECT ID FROM RAW_ARCHIEVE ORDER BY ID DESC LIMIT $MAX_REC_COUNT, 1";
    $DEL_ID = 0;
    if (!($cdb->execute_sql($qry, $resulta, $error_msg))){
      echo $error_msg;
      exit;
    }
    $row = mysql_fetch_object($resulta);
    $DEL_ID = $row->ID;
    $qry = "DELETE FROM RAW_ARCHIEVE WHERE ID <= '$DEL_ID' AND DONE <> '0'";
    if (!($cdb->execute_sql($qry, $resultb, $error_msg))){
      echo $error_msg;
      exit;
    }
  }

  //ARCHIEVE THE SEMI_FINISHED TABLE AND DELETE THE ARCHIEVED ITEMS

  $qry = "SELECT COUNT(ID) AS SEMI_ADET FROM SEMI_ARCHIEVE";
  if (!($cdb->execute_sql($qry, $result, $error_msg))){
    echo $error_msg;
    exit;
  }
  $row = mysql_fetch_object($result);
  if ($row->SEMI_ADET > $MAX_REC_COUNT){
    $DEL_ID = 0;
    $qry = "SELECT ID FROM SEMI_ARCHIEVE ORDER BY ID DESC LIMIT $MAX_REC_COUNT, 1";
    if (!($cdb->execute_sql($qry, $resultd, $error_msg))){
      echo $error_msg;
      exit;
    }
    $row = mysql_fetch_object($resultd);
    $DEL_ID = $row->ID;

    $qry = "DELETE FROM SEMI_ARCHIEVE WHERE ID <= '$DEL_ID' AND DONE <> '0'";
    if (!($cdb->execute_sql($qry, $resulte, $error_msg))){
      echo $error_msg;
      exit;
    }
  }

  //ARCHIEVE THE CDR_MAIN_DATA TABLE AND DELETE THE ARCHIEVED ITEMS
  $DEL_ID = 0;
  $qry = "SELECT CDR_ID FROM CDR_MAIN_DATA 
          WHERE MY_DATE = DATE_SUB(NOW(),INTERVAL $MAX_RECORD_DAYS DAY) 
          ORDER BY CDR_ID ASC LIMIT 0,1;";
  if (!($cdb->execute_sql($qry, $resultf, $error_msg))){
    echo $error_msg;
    exit;
  }
  if (mysql_num_rows($resultf) > 0){
    $row = mysql_fetch_object($resultf);
    $DEL_ID = $row->CDR_ID;
  }else{
    $DEL_ID = "";
  }
  if ($DEL_ID){
    $qry = "INSERT INTO CDR_ARCHIEVE SELECT * FROM CDR_MAIN_DATA WHERE CDR_ID <= '$DEL_ID'";
    if (!($cdb->execute_sql($qry, $resultg3, $error_msg))){
      echo $error_msg;
      exit;
    }
    $qry = "DELETE FROM CDR_MAIN_DATA WHERE CDR_ID <= '$DEL_ID'";
    if (!($cdb->execute_sql($qry, $resulth, $error_msg))){
      echo $error_msg;
      exit;
    }
  }

  //ARCHIEVE THE CDR_MAIN_INB TABLE AND DELETE THE ARCHIEVED ITEMS
  $DEL_ID = 0;
  $qry = "SELECT CDR_ID FROM CDR_MAIN_INB 
          WHERE MY_DATE=DATE_SUB(NOW(),INTERVAL $MAX_RECORD_DAYS DAY) 
          ORDER BY CDR_ID LIMIT 0,1;";
  if (!($cdb->execute_sql($qry, $resultf, $error_msg))){
    echo $error_msg;
    exit;
  }
  if (mysql_num_rows($resultf) > 0){
    $row = mysql_fetch_object($resultf);
    $DEL_ID = $row->CDR_ID;
  }else{
    $DEL_ID = "";
  }
  if ($DEL_ID){
    $qry = "INSERT INTO CDR_MAIN_INB_ARCH SELECT * FROM CDR_MAIN_INB WHERE CDR_ID <= '$DEL_ID'";
    if (!($cdb->execute_sql($qry, $resultg3, $error_msg))){
      echo $error_msg;
      exit;
    }
    $qry = "DELETE FROM CDR_MAIN_INB WHERE CDR_ID <= '$DEL_ID'";
    if (!($cdb->execute_sql($qry, $resulth, $error_msg))){
      echo $error_msg;
      exit;
    }
  }
?>


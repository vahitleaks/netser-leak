<?
require_once("doc_root.cnf");
  //$DOCUMENT_ROOT = "/usr/local/wwwroot/multi.crystalinfo.net/root";
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/site.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");

  $cUtility = new Utility();
  $cdb = new db_layer();
  $conn = $cdb->getConnection();
  include("mail_send.php");
  //$Mail = new phpmailer();

  //STEPS -- 
  /*
  1- Get Continuaous alerts with definations
  2- format an sql with the values
  3- Get the data to sent to the e-Mails
  4- if there is data to be  sent get e-mail     
  5- Send e-Mails and update ALERT_DEFS to LAST_PROC_ID 
  */      
      
  $sql_str = "SELECT * FROM ALERTS
              INNER JOIN ALERT_DEFS ON ALERTS.ALERT_ID = ALERT_DEFS.ALERT_ID
              WHERE ALERTS.ALERT_ID=16
             " ; 
  if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
    print_error($error_msg);
    exit;
  }
  while($row = mysql_fetch_object($result)){
    $ALERT_DEF_ID = $row->ALERT_DEF_ID;
    $LAST_PROC_ID = $row->LAST_PROC_ID;
    ////////////////////CANCAT THE CRITERS/////////////////////

    $sql_A = "SELECT * FROM ALERT_CRT 
              WHERE ALERT_CRT.ALERT_DEF_ID = '$row->ALERT_DEF_ID'
           " ; 
    if (!($cdb->execute_sql($sql_A, $rs_A, $error_msg))){
      print_error($error_msg);
      exit;
    }
    $kriter = "";
    while($row_A = mysql_fetch_object($rs_A)){
      $kriter .=" AND ". $row_A->FIELD_NAME .$row_A->OPERATOR ."'". $row_A->VALUE ."'";
    }            
    //////////////////////////////////////////////////////////////
    $sql1 = "SELECT CDR_MAIN_DATA.SITE_ID, CDR_ID, EXTENTIONS.EMAIL, TIME_STAMP, CHG_INFO, CLID, TER_DN, EXTENTIONS.DESCRIPTION AS DES, E.DESCRIPTION as EDES FROM CDR_MAIN_INB AS CDR_MAIN_DATA 
             INNER JOIN EXTENTIONS ON EXTENTIONS.EXT_NO = CDR_MAIN_DATA.TER_DN
             LEFT JOIN EXTENTIONS as E ON E.EXT_NO = CDR_MAIN_DATA.CLID
             AND EXTENTIONS.SITE_ID = CDR_MAIN_DATA.SITE_ID            
             WHERE ERR_CODE=0 AND CALL_TYPE=2 AND CLID<>'' AND CDR_ID > '$row->LAST_PROC_ID' $kriter
             LIMIT 20" ; 
    if (!($cdb->execute_sql($sql1, $rs1, $error_msg))){
      print_error($error_msg);
      exit;
    }

    ////////////package the data to be sent
    while($row1 = mysql_fetch_object($rs1)){
      $DATA ="";
      $row1->CLID = str_replace("X","",$row1->CLID);
      $DATA = "<b> $row1->CLID - $row1->EDES</b> numaralý telefon <b> $row1->TIME_STAMP </b>  tarihinde sizi( Dahili :<b> $row1->TER_DN   $row1->DES</b>) aradý, ulaþamadý. Çalma süresi - $row1->CHG_INFO <BR><BR><BR>";
      $DATA .= "You( Extention :<b> $row1->TER_DN  $row1->DES</b>) called by <b>$row1->CLID </b> at <b> $row1->TIME_STAMP </b> but the number couldn't reach. Ring time - $row1->CHG_INFO <BR> ";
      $sbjct = "Cyrstallinfo- $row1->CLID - $row1->EDES numaralý telefon $row1->TIME_STAMP tarihinde sizi( Dahili : $row1->TER_DN $row1->DES) aradý, ulaþamadý";
      $LAST_PROC_ID = $row1->CDR_ID;

      if($row1->CLID && $row1->EMAIL && $DATA){
        mail_send($row1->EMAIL,$sbjct,$DATA);
      }                
    }
  }
  $sql1 = " UPDATE ALERT_DEFS SET LAST_PROC_ID = '$LAST_PROC_ID' WHERE ALERT_DEF_ID = '$ALERT_DEF_ID'" ; 
  if (!($cdb->execute_sql($sql1, $rs1, $error_msg))){
    print_error($error_msg);
    exit;
  }
?>
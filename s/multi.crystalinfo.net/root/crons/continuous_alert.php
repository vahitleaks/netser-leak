<?
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  $conn = $cdb->getConnection();
  include("mail_send.php");

//////STEPS--
/*
  1- Get Continuoous alerts with definitions
  2- format an sql with the values
  3- Get the data to sent to the e-Mails
  4- if there is data to be  sent get e-mail     
  5- Send e-Mails and update ALERT_DEFS to LAST_PROC_ID 
*/

  $sql_str = "SELECT * FROM ALERTS
              INNER JOIN ALERT_DEFS ON ALERTS.ALERT_ID = ALERT_DEFS.ALERT_ID
              WHERE IS_CONTINUOUS > 0 ORDER BY IS_CONTINUOUS
             ";
  if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
    print_error($error_msg);
    exit;
  }
  while($row = mysql_fetch_object($result)){
    $ALERT_DEF_ID = $row->ALERT_DEF_ID;
    $ALERT_NAME = $row->ALERT_NAME;
    $ALERT_DEF_NAME = $row->ALERT_DEF_NAME;
    $LAST_PROC_ID = $row->LAST_PROC_ID;
    $SITE_ID = $row->SITE_ID;
    $is_continuous = $row->IS_CONTINUOUS;
    ////////////////////CONCAT THE CRITERS/////////////////////
    $sql2 = "SELECT ALERT_TO_EMAIL.* FROM ALERT_DEFS
             INNER JOIN ALERT_TO_EMAIL ON ALERT_DEFS.ALERT_DEF_ID = ALERT_TO_EMAIL.ALERT_DEF_ID
             WHERE ALERT_TO_EMAIL.ALERT_DEF_ID = '$ALERT_DEF_ID '
            " ; 
    if (!($cdb->execute_sql($sql2, $rs2, $error_msg))){
      print_error($error_msg);
      exit;
    }

    if ($is_continuous == 1){
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
      /////////////////////////////////////////////////////////////////
      $sql1 = "SELECT CDR_ID, ORIG_DN, LocationTypeid, Locationid, CountryCode,LocalCode,PURE_NUMBER,COUNTER, DURATION, PRICE, DATE_FORMAT(TIME_STAMP,'%d.%m.%Y %H:%i:%s') AS MY_TIME
	           FROM CDR_MAIN_DATA 
               WHERE CDR_ID > '$row->LAST_PROC_ID' AND ERR_CODE='0' $kriter
              " ;
      if (!($cdb->execute_sql($sql1, $rs1, $error_msg))){
        print_error($error_msg);
        exit;
      }

      if(get_site_prm('MAIL_SENT_TO_EXT', $SITE_ID)==1){
        $send_ext = 1;
      }
      //////////////package the data to be sent
      if (mysql_num_rows($rs1)>'0'){
        while($row1 = mysql_fetch_object($rs1)){
          $DATA="<table border=\"0\" width=\"500\">
                   <tr>
                     <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"cid:my-crystal\" ></a></td>
                     <td width=\"50%\" align=center CLASS=\"header\">&nbsp;</td>
                     <td width=\"25%\" align=right><img SRC=\"cid:my-attach\"></td>
                   </tr>
                 </table>";
          $DATA .= "<table width=\"500\"><tr><td colspan=2 bgcolor=\"#88ACD5\"><b>
                      Uyarýlarda belirtilen kriterlere uyan aþaðýdaki çaðrý yapýlmýþtýr.</b></td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\" width=\"75\">Uyarý Türü</td><td bgcolor=\"#E6EEF7\">".$ALERT_NAME."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Uyarý Adý</td><td bgcolor=\"#E6EEF7\">".$ALERT_DEF_NAME."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Dahili</td><td bgcolor=\"#E6EEF7\">".$row1->ORIG_DN." - ".get_ext_name2($row1->ORIG_DN,$SITE_ID)."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Çaðrý Türü</td><td bgcolor=\"#E6EEF7\">".get_call_type($row1->LocationTypeid)."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Aranan Numara</td><td bgcolor=\"#E6EEF7\">".$row1->CountryCode." ".$row1->LocalCode." ".$row1->PURE_NUMBER."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Aranan Lokasyon</td><td bgcolor=\"#E6EEF7\">".get_tel_place($row1->Locationid)."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Aranan Saat</td><td bgcolor=\"#E6EEF7\">".$row1->MY_TIME."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Kontör Miktarý</td><td bgcolor = \"#E6EEF7\">".$row1->COUNTER."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Süre</td><td bgcolor = \"#E6EEF7\">".calculate_all_time($row1->DURATION)."</td></tr>";
          $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Tutar</td><td bgcolor = \"#E6EEF7\">".write_price($row1->PRICE)."</td></tr>";
          $DATA .= "</table>";
          $LAST_PROC_ID = $row1->CDR_ID;
          $extra_mail = "";
          if ($send_ext==1){
          if (get_ext_mail($row1->ORIG_DN,$SITE_ID))
            $extra_mail = get_ext_mail($row1->ORIG_DN,$SITE_ID);
          }
          $str = $DATA;
          if ($str!=""){
            if(mysql_num_rows($rs2)>'0'){
              while($row2 = mysql_fetch_object($rs2)){
                mail_send($row2->MAIL,"Uyarýlarda belirtilen kriterlere uyan aþaðýdaki çaðrý yapýlmýþtýr.",$DATA);
              }
              mysql_data_seek($rs2,0);//MAÝL LOOP
            }
            if($extra_mail){//Kendisine de gitsin
              mail_send($extra_mail,"Sistem yöneticinizin belirttiði aþaðýdaki kriterlere uyan bir çaðrý yaptýnýz.",$DATA);
            }//Ekstra mail
          }//str dolu
        }//Uygun kayýtlar dolanýyor
      }//Uygun kayýr var mý?
    }elseif($is_continuous==3){
      $ALERT_DEF_ID = $row->ALERT_DEF_ID;
      $ALERT_NAME = $row->ALERT_NAME;
      $ALERT_DEF_NAME = $row->ALERT_DEF_NAME;
      $sql_A = "SELECT * FROM ALERT_CRT
                WHERE ALERT_CRT.ALERT_DEF_ID = '$row->ALERT_DEF_ID'
                ORDER BY ALERT_CRT_ID ASC
                 ";
      if (!($cdb->execute_sql($sql_A, $rs_A, $error_msg))){
        print_error($error_msg);
        exit;
      }
      $kriter = " SITE_ID = ".$SITE_ID;
      while($row_A = mysql_fetch_object($rs_A)){
        if ($row_A->FIELD_NAME=='DESCRIPTION'){
          $switch_desc = $row_A->VALUE;
        }elseif ($row_A->FIELD_NAME=='SWITCH_CODE'){
          $kriter .=" AND DATA LIKE '%".$row_A->VALUE ."%'";
        }
      }
      //echo $kriter;exit;
      /////////////////////////////////////////////////////////////////
      $sql1 = "SELECT * FROM RAW_ARCHIEVE 
               WHERE ".$kriter." AND ID > '$row->LAST_PROC_ID'
              " ;
      //echo $sql1;exit;
      if (!($cdb->execute_sql($sql1, $rs1, $error_msg))){
        print_error($error_msg);
        exit;
      }
      if(mysql_num_rows($rs1)>'0'){
        while($row1 = mysql_fetch_object($rs1)){
          $DATA="";
          $DATA = "Santral Uyarýlarýnda belirtilen kriterlere uyan aþaðýdaki kayýt gelmiþtir.<br>";
          $DATA .= "Uyarý Türü = ".$ALERT_NAME."<br>";
          $DATA .= "Uyarý Adý = ".$ALERT_DEF_NAME."<br>";
          $DATA .= "Açýklama = ".$switch_desc."<br>";
          $LAST_PROC_ID = $row1->CDR_ID;
          //////////////package the data to be sent
          if(mysql_num_rows($rs2)>'0'){
            while($row2 = mysql_fetch_object($rs2)){
              mail_send($row2->MAIL,"Santralden belirtilen kriterlere uygun aþaðýdaki kayýt gelmiþtir.",$DATA);
            }
            mysql_data_seek($rs2,0);//MAÝL LOOP
          }
        }
      }
    }
    $sql1 = " UPDATE ALERT_DEFS SET LAST_PROC_ID = '$LAST_PROC_ID' WHERE ALERT_DEF_ID = '$ALERT_DEF_ID'" ; 
    if (!($cdb->execute_sql($sql1, $rs1, $error_msg))){
      print_error($error_msg);
      exit;
    }
  }
?>
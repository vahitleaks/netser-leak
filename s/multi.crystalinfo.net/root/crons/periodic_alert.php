<?
      require_once("doc_root.cnf");
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
      $cUtility = new Utility();
      $cdb = new db_layer();
      $conn = $cdb->getConnection();
      include("periodic_sum.php");
      include("mail_send.php");
      if(!$conn) exit;

//////STEPS -- 
/*
          1- Get daily alerts with definitions
          2- format an sql with the values
          3- Get the data to sent to the e-Mails
          4- if there is data to be  sent get e-mail     
          5- Send e-Mails and update ALERT_DEFS to LAST_PROC_ID 
*/      
      
      $sql_str = "SELECT * FROM ALERT_DEFS
                  INNER JOIN ALERTS ON ALERTS.ALERT_ID = ALERT_DEFS.ALERT_ID
                  WHERE ALERTS.ALERT_ID = 7 OR ALERTS.ALERT_ID = 8 OR ALERTS.ALERT_ID = 13" ;
                   
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
            print_error($error_msg);
            exit;
      }
      while($row = mysql_fetch_object($result)){
            $ALERT_ID = $row->ALERT_ID;
            $ALERT_DEF_ID = $row->ALERT_DEF_ID;
            $SITE_ID = $row->SITE_ID;
            $LAST_PROC_ID = $row->LAST_PROC_ID;
            $DATA = "";
            //////////////Get the mails to be sent to 
            $sql2 = "SELECT ALERT_TO_EMAIL.* FROM ALERT_DEFS
                     INNER JOIN ALERT_TO_EMAIL ON ALERT_DEFS.ALERT_DEF_ID = ALERT_TO_EMAIL.ALERT_DEF_ID
                     WHERE ALERT_TO_EMAIL.ALERT_DEF_ID = '$ALERT_DEF_ID '
                        " ;
            if (!($cdb->execute_sql($sql2, $rs2, $error_msg))){
                  print_error($error_msg);
                  exit;
            }

            //echo $ALERT_ID;exit;
            if ($ALERT_ID == 8){
                if(date("d")==1 || $force==1){//Ayýn ilk günü ise çalýþsýn. Çünkü aylýk mail. Force ise hangi gün olursa olsun üret.
                    $DATA = periodic_summary($SITE_ID,'month');
                }
            }elseif($ALERT_ID == 13){
                if(strftime("%u")==1  || $force==1){//Haftanýn ilk günü ise çalýþsýn. Çünkü haftalýk mail.Force ise hangi gün olursa olsun üret.
                    $DATA = periodic_summary($SITE_ID,'week');
                }
            }elseif($ALERT_ID == 7){//Günün özeti.
                $DATA = periodic_summary($SITE_ID,'day');
            }
            $k=0;
            if(mysql_num_rows($rs2) >= 1){
            while($row2 = mysql_fetch_object($rs2)){
//            echo "$row2->MAIL \n";
                  mail_send($row2->MAIL,$row->ALERT_NAME,$DATA);
                   $k++;
            } //MAÝL LOOP*/
       }
   }
?>

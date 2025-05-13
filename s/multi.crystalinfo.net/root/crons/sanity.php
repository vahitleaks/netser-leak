<?php
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  $conn = $cdb->getConnection();
  include("mail_send.php");
  if(!$conn) exit;

  //////STEPS --
  /*
   1- Get Sanity report 
   2- format an sql with the values
   3- Get the data to sent to the e-Mails
   4- if there is data to be  sent get e-mail
   5- Send e-Mails and update ALERT_DEFS to LAST_PROC_ID 
  */
  function get_err_def($ERR_CODE){
    global $cdb;
    $sql_str2 = "SELECT * FROM ERR_CODES WHERE ERR_CODE = ".$ERR_CODE;
    if (!($cdb->execute_sql($sql_str2,$result2,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if (mysql_num_rows($result2)>0){
      $row = mysql_fetch_object($result2);
      return $row2->HEADER;
    }else{
      return "";
    }
  }

  $sql_str = "SELECT * FROM ALERT_DEFS WHERE ALERT_ID = 10 ";
  if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
    print_error($error_msg);
    exit;
  }

  if (mysql_num_rows($result)>0){
    $row=mysql_fetch_object($result);
    $ALERT_DEF_ID = $row->ALERT_DEF_ID;
    $ALERT_DEF_NAME = $row->ALERT_DEF_NAME;
    $frequency = $row->FREQUENCY;
    $ALERT_DEF_NAME = $row->ALERT_DEF_NAME;

    //////////////Get the mails to be sent to 
    $sql2 = "SELECT ALERT_TO_EMAIL.* FROM ALERT_DEFS
             INNER JOIN ALERT_TO_EMAIL ON ALERT_DEFS.ALERT_DEF_ID = ALERT_TO_EMAIL.ALERT_DEF_ID
             WHERE ALERT_TO_EMAIL.ALERT_DEF_ID = '$ALERT_DEF_ID '
             " ;
    if (!($cdb->execute_sql($sql2, $rs2, $error_msg))){
      print_error($error_msg);
      exit;
    }

    if ($frequency==3 && date("d")==1){//Ayl�k isteniyor ve ay�n ba��
      //Ok sorun yok. Rapor �retilecek.Bu durumda nceki ay al�nmal�.
	  $date_crt = " DATE_FORMAT(MY_DATE,'%m') = MONTH(NOW())-1";
	  $Name = "�nceki ay";
    }else if($frequency==2 && date("w")==1){//Haftal�k rapor isteniyor ve haftan�n ilk g�n�
      //Ok sorun yok. Rapor �retilecek.��inde bulunulan ay yap�labilir.
 	  $date_crt = " DATE_FORMAT(MY_DATE,'%m') = MONTH(NOW())";
	  $Name = "Bu ay";
    }else if($frequency==1){//G�nl�k rapor isteniyorsa ba�ka kontrole gerek yok.
      //Ok sorun yok. Rapor �retilecek.��inde bulunulan ay yap�labilir.
	  $date_crt = " DATE_FORMAT(MY_DATE,'%m') = MONTH(NOW())";
	  $Name = "Bu ay";
    }else if($force==1){//Herhangi bir anda zorla rapor isteniyor.
      //Ok sorun yok. Rapor �retilecek.
    }else{
      exit;
    }
    //Ok ise devam edip raporu haz�rlayal�m.
    $DATA="<table border=\"0\" width=\"600\">
             <tr>
               <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"cid:my-crystal\" ></a></td>
               <td width=\"50%\" align=center CLASS=\"header\">CrystalInfo Sistem Bilgi Raporu</td>
               <td width=\"25%\" align=right><img SRC=\"cid:my-attach\"></td>
             </tr>
           </table>";
    $DATA .= "<table width=\"500\"><tr><td colspan=2 bgcolor=\"#88ACD5\"><b>Sistem �zet Bilgileri</b></td></tr>";
    //////////////////////DISK STATUS//////////////////////////////
    exec((" df /| awk '{ print $5 }'"), $aa) ;
    $dolu = str_replace("%", "", $aa[1]);
    $bos = 100 -$dolu;
    //////////////////////END OF DISK STATUS//////////////////////////////
    $DATA .= "<tr><td bgcolor=\"#B3CAE3\" width=\"200\">Disk Durumu</td><td bgcolor=\"#E6EEF7\"><b>%".$dolu."</b> Dolu - <b>%".$bos."</b> Bo�</td></tr>";
    //////////////////////DB SIZE//////////////////////////////      
    unset($aa);
    exec(("du -h /usr/local/mysql/var/MCRYSTALINFONE/ | awk '{ print $1 }'"), $aa) ;
    exec(("du -h /usr/local/mysql/data/MCRYSTALINFONE/ | awk '{ print $1 }'"), $bb) ;
    //////////////////////END OF DB SIZE//////////////////////////////           
    $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Database Boyutu</td><td bgcolor=\"#E6EEF7\">".$aa[0].$bb[0]."</td></tr>";
    //////////////////////LAST RECORD//////////////////////////////
    $sql_str = "SELECT * FROM SEMI_ARCHIEVE ORDER BY ID DESC LIMIT 1" ; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $row = mysql_fetch_object($result);
    $DATA .= "<tr><td bgcolor=\"#B3CAE3\">Son ��lenen Kay�t</td><td bgcolor=\"#E6EEF7\">".$row->LINE1."</td></tr>";
    $DATA .= "<tr><td colspan=2 bgcolor=\"#88ACD5\"><b>".$Name." Giden �a�r�lar�n Kay�t Durumlar�</b></td></tr>";
    //////////////////////THIS MONTHS RECORD STATUS//////////////////////////////
    $sql_str1 = " SELECT COUNT(*) AS CNT, ERR_CODE, ERR_CODES.HEADER FROM CDR_MAIN_DATA
                  INNER JOIN ERR_CODES ON ERR_CODES.ID = CDR_MAIN_DATA.ERR_CODE
                  WHERE ".$date_crt." GROUP BY ERR_CODE
                  " ;
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
      print_error($error_msg);
      exit;
    }
    while($row1 = mysql_fetch_object($result1)){
      $DATA .= "<tr><td bgcolor=\"#B3CAE3\">".$row1->HEADER."</td><td bgcolor=\"#E6EEF7\">".$row1->CNT."</td></tr>";
    }
    $DATA .= "<tr><td colspan=2 bgcolor=\"#88ACD5\"><b>".$Name." Gelen �a�r�lar�n Kay�t Durumlar�</b></td></tr>";
    //////////////////////THIS MONTHS RECORD STATUS//////////////////////////////
    $sql_str1 = "SELECT COUNT(*) AS CNT, ERR_CODE, ERR_CODES.HEADER FROM CDR_MAIN_INB
                 INNER JOIN ERR_CODES ON ERR_CODES.ID = CDR_MAIN_INB.ERR_CODE
                 WHERE ".$date_crt." GROUP BY ERR_CODE
                 " ;
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
      print_error($error_msg);
      exit;
    }
    while($row1 = mysql_fetch_object($result1)){
      $DATA .= "<tr><td bgcolor=\"#B3CAE3\">".$row1->HEADER."</td><td bgcolor=\"#E6EEF7\">".$row1->CNT."</td></tr>";
    }
    //////////////////////END OF THIS MONTHS RECORD STATUS//////////////////////////////           
    $DATA .= "</table>";
    //Mailler g�nderiliyor
    if(mysql_num_rows($rs2)>'0'){
      while($row2 = mysql_fetch_object($rs2)){
        mail_send($row2->MAIL,"CrystalInfo Sistemi �zet Raporu.",$DATA);
      }
    }
  }
?>
 

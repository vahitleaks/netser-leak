<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
    $cUtility = new Utility();
   $cdb = new db_layer(); 
   $conn = $cdb->getConnection();
   require_once(dirname($DOCUMENT_ROOT)."/root/crons/mail_send.php");
//   include("mail_send.php");
   $mymonth = strftime("%m", mktime(0, 0, 0, date("m")-1 ,1, date("Y")));
   $myyear  = strftime("%Y", mktime(0, 0, 0, date("m")-1 ,1, date("Y")));;
   
  setlocale(LC_TIME, 'tr_TR');    

    // maximum acceptable duration aliniyor...
    $max_acc_dur = get_site_prm('MAX_ACCE_DURATION',$SITE_ID) * 60;

    function get_dept_extnos($DPT_ID, $ST_ID){
      global $cdb;
      global $conn;
      $dept_qry = "SELECT EXT_NO FROM EXTENTIONS WHERE DEPT_ID=".$DPT_ID." AND SITE_ID=".$ST_ID;
      if (!($cdb->execute_sql($dept_qry,$rsltdpt,$error_msg))){
         print_error($error_msg);
         exit;
      }
      if(mysql_num_rows($rsltdpt)>0){
        while($rowDept = mysql_fetch_object($rsltdpt)){
          if($retVal == ""){
            $retVal = $rowDept->EXT_NO;
          }else{
            $retVal = $retVal.", ".$rowDept->EXT_NO;
          }
        }
        return "(".$retVal.")";
      }else{
        return "('-1')";
      }
    }
    
    function get_work_days($tmon, $tyear){
        $lastday = strftime ("%d", mktime(0,0,0,$tmon+1,0,$tyear));
        for($dd=1;$dd<=$lastday;$dd++){
          $week_day = strftime("%u", mktime(0,0,0,$tmon,$dd,$tyear));
           if($week_day>=1 && $week_day<=5){
             $job_day = $job_day+1;
           }
        }
        return $job_day;
    }
    if($mymonth<10){
      $CDR_MAIN_DATA = "CDR_MAIN_0".$mymonth."_".$myyear;
    }else{
      $CDR_MAIN_DATA = "CDR_MAIN_".$mymonth."_".$myyear;
     }
     if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";
/*       Rapor gönderilecek mail adreslerini çek       */    

     $sql_mail = "SELECT TO_EMAIL FROM CALL_PROF_MAILING GROUP BY TO_EMAIL";
     if (!($cdb->execute_sql($sql_mail, $res_mail, $error_msg))){
       print_error($error_msg);
       exit;
    }
    while($rw_mail = mysql_fetch_object($res_mail)){
      $email = $rw_mail->TO_EMAIL;
      $sql_rep = "SELECT * FROM CALL_PROF_MAILING WHERE TO_EMAIL = '".$rw_mail->TO_EMAIL."' ORDER BY REP_TYPE ASC";
      if (!($cdb->execute_sql($sql_rep, $res_rep, $error_msg))){
         print_error($error_msg);
         exit;
      }

        $report_name = "AYLIK ÇAÐRI PROFÝLÝ";
        $DATA_HEADER ="
        <html>
        <head>
        <title>Crystal Info</title>
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1254\">
        <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-9\">
        <style>
          body {font-family:Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
          .homebox {font-family: Verdana, Arial, Helvetica, sans-serif;         font-size: 7pt;         font-weight: bold;         font-variant: normal;         text-transform: none;         color: FF6600;         text-decoration: none}
          .header {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #2C5783;     text-decoration: none}
          .header_beyaz2 {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: FFFFFF;        background-color:  508AC5;    text-decoration: none}
          .header_sm {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 7pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #2C5783;     text-decoration: none}
          a.a1 {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #1B4E81;     text-decoration: none}
          a.a1:hover {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
          a {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #FF6600;     text-decoration: none}
          .text {font-family: Verdana, Arial, Helvetica, sans-serif;  font-size: 8pt;  font-weight: normal;  font-variant: normal;  text-transform: none;  color: #1b4e81 ;  text-decoration: none}
          a:hover {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #FF9000;     text-decoration: none}
          .copyright {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 7pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #0099CC;     text-decoration: none}
          .table_header {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #ECF9FF;     text-decoration: none}
          td.td1 {font-size: 8pt;    border-style: solid ;     border-width: 0};
          td.header1 {background-color: #0099CC;     font-size: 8pt;     font-weight: Bold;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
          td.td1_koyu {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: Bold;     font-variant: normal;     text-transform: none;     color: #1B4E81;     border:0;    height:22px;    text-decoration: none}
          tr.header1 {background-color: #0099CC;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
          tr.bgc1 {background-color: #B1CBE4}
          tr.bgc2 {background-color: #C6D9EC}
          table{font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000; text-decoration: none}
          .header_beyaz{font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 9pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: F0F8FF;     text-decoration: none;background-color:#6699CC}
          .font_beyaz {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 9pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: F0F8FF;     height:22px;    text-decoration: none;}
          .header_mavi {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #1B4E81;     text-decoration: none}
          .rep_td {font-size: 9pt;     font-family: Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     color: #000000;     height:22px;}  
          .rep_header {font-size: 8pt;     font-family: Verdana,Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     font-weight:bold;    color: #000000;     height:20px;    background-color:#FFFFFF}
          .rep_table_header {font-size: 9pt;     font-family: Verdana,Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     font-weight:bold;    color: #ffffff;     height:25px;    background-color:#959595      }
        </style>
        </head>
        <body bgcolor=\"#FFFFFF\" text=\"#000000\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">
        <center>  
        <br><br>
        <table width=\"1000\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
          <tr>
            <td colspan=\"2\" width=\"100%\" align=\"center\" class=\"rep_header\" align=\"center\">
            <TABLE BORDER=\"0\" WIDTH=\"100%\">
              <TR>
                <TD><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"cid:my-crystal\" ></a></TD>
                <TD width=\"50%\" align=center CLASS=\"header\"><BR>$report_name<br><STRONG>Dönem</STRONG> : ".strftime("%B", mktime(0, 0, 0, $mymonth-1 ,32, $myyear))." ".$myyear."<!-- DONEM --></TD>
                <TD width=\"25%\" align=right><img SRC=\"cid:my-attach\"></TD>
              </TR>
            </TABLE>
            </td>
          </tr>
          <tr>
            <td colspan=\"2\" width=\"100%\" align=\"Left\" class=\"rep_header\" align=\"center\">
            </td>
          </tr>
          <tr>
            <td colspan=\"2\">
            <table border=\"0\"  width=100% bgcolor=\"#C7C7C7\" cellspacing=\"1\" cellpadding=\"0\">
               <tr>
                  <td class=\"rep_table_header\" rowspan=2 align=center width=\"200\">Açýklama</td>
                  <td class=\"rep_table_header\" colspan=3 align=center>Giden</td>
                  <td class=\"rep_table_header\" colspan=2 align=center>Gelen</td>
                  <td class=\"rep_table_header\" colspan=2 align=center>Dahili Gelen</td>
                  <td class=\"rep_table_header\" colspan=3 align=center>Genel Toplam</td>
                  <td class=\"rep_table_header\" colspan=3 align=center>Ortalama/Gün</td>
              </tr>
              <tr>
                  <td class=\"rep_table_header\" width=\"50\">Adet</td>
                  <td class=\"rep_table_header\" width=\"70\">Süre</td>
                  <td class=\"rep_table_header\" width=\"65\">Tutar</td>
                  <td class=\"rep_table_header\" width=\"50\">Adet</td>
                  <td class=\"rep_table_header\" width=\"70\">Süre</td>
                  <td class=\"rep_table_header\" width=\"50\">Adet</td>
                  <td class=\"rep_table_header\" width=\"70\">Süre</td>
                  <td class=\"rep_table_header\" width=\"50\">Adet</td>
                  <td class=\"rep_table_header\" width=\"70\">Süre</td>
                  <td class=\"rep_table_header\" width=\"65\">Tutar</td>
                  <td class=\"rep_table_header\" width=\"50\">Adet</td>
                  <td class=\"rep_table_header\" width=\"70\">Süre</td>
                  <td class=\"rep_table_header\" width=\"65\">Tutar</td>
              </tr>
                  <tr>
                    <td colspan=\"14\" bgcolor=\"#000000\" height=\"1\"></td>
                  </tr>
        ";
        $DATA_FOOTER = "
            </table>
            </td>
          </tr>
        </table>";

      
      
      while($rw_rep = mysql_fetch_object($res_rep)){
        $SITE_ID = $rw_rep->SITE_ID;
        $DEPT_ID = $rw_rep->DEPT_ID;
        $EXT_NO = $rw_rep->EXT_NO;

       $kriter = "";
       $kriter .= $cdb->field_query($kriter,"SITE_ID",               "=",      "$SITE_ID"); 

        if($rw_rep->REP_TYPE == 1){//Site Raporu
           $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'general'"); 
        }elseif($rw_rep->REP_TYPE == 2){//Departman raporu
           $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'department'"); 
           $kriter .= $cdb->field_query($kriter,"DEPT_ID",            "=",      "$DEPT_ID"); 
        }elseif($rw_rep->REP_TYPE == 3){//Dahili raporu
           $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'dahili'"); 
           $kriter .= $cdb->field_query($kriter,"ORIG_DN",            "=",      "'$EXT_NO'"); 
        }

        $header_text = "DEPARTMAN : ".get_dept_name($DEPT_ID, $SITE_ID);

       //Bu mutlaka olmali ilgili siteyi belirliyor.
       $kriter .= $cdb->field_query($kriter,"TIME_STAMP_MONTH",      "=",      "$mymonth");
       $kriter .= $cdb->field_query($kriter,"TIME_STAMP_YEAR",       "=",      "$myyear");

       //Tarih kriterleri (Voip çaðrýlarý için)


       $sql_str  = "SELECT * 
                      FROM MONTHLY_ANALYSE 
                     ";
       $sql_str .= " WHERE ".$kriter;
       
      $job_days = get_work_days($mymonth, $myyear);

      //echo $sql_str."<br>";
      //echo $sql_voip;exit;
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      if(mysql_num_rows($result)==0){
        echo "Kayýt Bulunamadý";
      }else{
        $row = mysql_fetch_object($result);
          $OUT_TOTAL_AMOUNT = $row->LOC_AMOUNT+$row->NAT_AMOUNT+$row->GSM_AMOUNT+$row->INT_AMOUNT+$row->OTH_AMOUNT;
          $OUT_TOTAL_DUR = $row->LOC_DUR+$row->NAT_DUR+$row->GSM_DUR+$row->INT_DUR+$row->OTH_DUR;
          $OUT_TOTAL_PRICE = $row->LOC_PRICE+$row->NAT_PRICE+$row->GSM_PRICE+$row->INT_PRICE+$row->OTH_PRICE;
          $INB_TOTAL_AMOUNT = $row->INB_AMOUNT;
          $INB_TOTAL_DUR = $row->INB_DUR;
          $INTERNAL_TOTAL_AMOUNT = $row->INTERNAL_AMOUNT;
          $INTERNAL_TOTAL_DUR = $row->INTERNAL_DUR;
          $EXT_TOTAL_AMOUNT = $OUT_TOTAL_AMOUNT+$INB_TOTAL_AMOUNT+$INTERNAL_TOTAL_AMOUNT;
          $EXT_TOTAL_DUR = $OUT_TOTAL_DUR+$INB_TOTAL_DUR+$INTERNAL_TOTAL_DUR;
          $EXT_TOTAL_PRICE = $OUT_TOTAL_PRICE;
          $EXT_AVG_AMOUNT = round($EXT_TOTAL_AMOUNT/$job_days, 2);
          $EXT_AVG_DUR = round($EXT_TOTAL_DUR/$job_days, 2);
          $EXT_AVG_PRICE = round($EXT_TOTAL_PRICE/$job_days, 2);

          if($rw_rep->REP_TYPE == 1){//Site Raporu
             $rep_table_hdr = "<tr bgcolor=\"#FFFFFF\">\n<td colspan=14 height=20><font color=FF0000><b>Site Analiz Raporu</b></font></td>\n";
             $rows_header = "<td class=\"rep_td\">".get_site_name($SITE_ID)."</td>\n";
          }elseif($rw_rep->REP_TYPE == 2){//Departman raporu
             $rows_header = "<td class=\"rep_td\">".get_dept_name($DEPT_ID, $SITE_ID)." (".get_site_name($SITE_ID).")</td>\n";
             $rep_table_hdr = "<tr bgcolor=\"#FFFFFF\">\n<td colspan=14 height=20><font color=FF0000><b>Deparman Analiz Raporu</b></font></td>\n";
          }elseif($rw_rep->REP_TYPE == 3){//Dahili raporu
             $rows_header = "<td class=\"rep_td\">$row->ORIG_DN - ".get_ext_name2($row->ORIG_DN, $SITE_ID)." (".get_site_name($SITE_ID).")</td>\n";
             $rep_table_hdr = "<tr bgcolor=\"#FFFFFF\">\n<td colspan=14 height=20><font color=FF0000><b>Dahili Analiz Raporu</b></font></td>\n";
          }

          if($myType<>$rw_rep->REP_TYPE){
              $DATA .= $rep_table_hdr;
           }  
           $myType = $rw_rep->REP_TYPE;     
          $DATA .= "<tr bgcolor=\"#FFFFFF\">\n";

          $DATA .= $rows_header;
          $DATA .= " <td class=\"rep_td\" align=right>".number_format($OUT_TOTAL_AMOUNT,0,'','.')."</td>\n
          <td class=\"rep_td\" align=right>".calculate_all_time($OUT_TOTAL_DUR)."</td>\n
          <td class=\"rep_td\" align=right>".write_price($OUT_TOTAL_PRICE)."</td>\n
          <td class=\"rep_td\" align=right>".number_format($INB_TOTAL_AMOUNT,0,'','.')."</td>\n
          <td class=\"rep_td\" align=right>".calculate_all_time($INB_TOTAL_DUR)."</td>\n
          <td class=\"rep_td\" align=right>".number_format($INTERNAL_TOTAL_AMOUNT,0,'','.')."</td>\n
          <td class=\"rep_td\" align=right>".calculate_all_time($INTERNAL_TOTAL_DUR)."</td>\n
          <td class=\"rep_td\" align=right>".number_format($EXT_TOTAL_AMOUNT,0,'','.')."</td>\n
          <td class=\"rep_td\" align=right>".calculate_all_time($EXT_TOTAL_DUR)."</td>\n
          <td class=\"rep_td\" align=right>".write_price($EXT_TOTAL_PRICE)."</td>\n
          <td class=\"rep_td\" align=right>".number_format($EXT_AVG_AMOUNT,0,'','.')."</td>\n
          <td class=\"rep_td\" align=right>".calculate_all_time($EXT_AVG_DUR)."</td>\n
          <td class=\"rep_td\" align=right>".write_price($EXT_AVG_PRICE)."</td>\n
          </tr>\n";
        
      }
      
      
      
      
      
      
    }
    if($DATA !=""){
      echo $DATA = $DATA_HEADER.$DATA.$DATA_FOOTER;
      //mail_send($email,"AYLIK ÇAÐRI PROFÝLÝ",$DATA);
    
    }
    $DATA = "";
    $myType = 0;     
}    


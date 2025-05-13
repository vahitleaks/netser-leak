<?    
   require_once("doc_root.cnf");
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
   $cUtility = new Utility();
   $cdb = new db_layer(); 
   $conn = $cdb->getConnection();
   include("mail_send.php");
   set_time_limit(36000);

   function add_mailing_log($stype,$mailadd, $stat){
     global $cdb;
     $sql_str = "Insert into TMAILLOGS(SREP_TYPE, SMAIL, ISTAT, DMAILDATE, SID) 
     VALUES('".$stype."', '".$mailadd."', ".$stat.", now(), '".$SID."');";
     if (!($cdb->execute_sql($sql_str, $rs, $error_msg))){
       print_error($error_msg);
       exit;
     }
   }
   
   if(!$conn) exit;
   $SID=0;
   $t0 = day_of_last_month("first");
   $t1 = day_of_last_month("last");
   if($unsentmails=="1"){
     $sql_log = "select max(SID) as SID FROM TMAILSESSION WHERE SREP_TYPE='DEPT';";
     if (!($cdb->execute_sql($sql_log, $rslog, $error_msg))){
       print_error($error_msg);
       exit;
     }
     while($urw=mysql_fetch_object($rslog)){
       $SID=$urw->SID;
     }
   }else{
     $sql_log = "INSERT INTO TMAILSESSION(SREP_TYPE,DMAILDATE) VALUES('DEPT', now());";
     if (!($cdb->execute_sql($sql_log, $rslog, $error_msg))){
       print_error($error_msg);
       exit;
     }
     $SID=mysql_insert_id();
   }
   $sql_str = "SELECT * FROM SITES";
   if (!($cdb->execute_sql($sql_str, $rs, $error_msg))){
     print_error($error_msg);
     exit;
   }
   $acc_code_arr["GSM"] = "GSM Görüþmeleri";
   while($row = mysql_fetch_object($rs)){//Bütün siteler taranmalý ///Site taramasý start
      $SITE_ID = $row->SITE_ID;
      $SITE_NAME = $row->SITE_NAME;
      $max_acc_duration = $row->MAX_ACCE_DURATION*60;
      $PRICE_FACTOR = $row->PRICE_FACTOR;

      $sql_acc = "SELECT * FROM ACCESS_CODES WHERE SITE_ID=".$SITE_ID;
      if (!($cdb->execute_sql($sql_acc, $rs_acc, $error_msg))){
        print_error($error_msg);
        exit;
      }
      while($rw_acc = mysql_fetch_object($rs_acc)){
        $acc_code_arr[$rw_acc->ACCESS_CODE] = $rw_acc->ACCESS_CODE_DESC;
      }
      //echo $row->SITE_ID."<br>";
      if($row->MONTHLY_MAILING_DEPT_DAY > '0'){//0'dan büyükse ilgili günde mailing yapýlacakýr.  ///Mail atýmonayý  start
        if($row->MONTHLY_MAILING_DEPT_DAY == date("d") || $force==1){ ///Mail gün kontrolü start
          $kriter = "";
          //Temel kriterler. Verinin hýzlý gelmesi için baþa konuldu.
          $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
          $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
          $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_duration"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
          $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
          $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
        
          $report_name = "Aylýk Departman Görüþme Özet Raporu";
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
          //Önceki ayýn datasýndan alsýn.
	      $CDR_MAIN_DATA = getTableName($t0,$t1);
          if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  
          $local_country_code = get_country_code($SITE_ID);
          $SQLAVG= "SELECT ROUND(AVG(DCOST), 3) AS AVERG FROM TGSMCALLS WHERE MYDATE>='$t0' AND MYDATE<='$t1'";
          if (!($cdb->execute_sql($SQLAVG, $rsltG, $error_msg))){
            print_error($error_msg);
            exit;
          }
          $rwAV = mysql_fetch_object($rsltG);
          $avg_gsm=$rwAV->AVERG;
          $DATA_HEAD ="
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
            table {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000; text-decoration: none}
            .header_beyaz{font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 9pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: F0F8FF;     text-decoration: none;background-color:#6699CC}
            .font_beyaz {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 9pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: F0F8FF;     height:22px;    text-decoration: none;}
            .header_mavi {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #1B4E81;     text-decoration: none}
            .rep_td {font-size: 9pt;     font-family: Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     color: #000000;     height:22px;}  
            .rep_header {font-size: 8pt;     font-family: Verdana,Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     font-weight:bold;    color: #000000;     height:20px;    background-color:#FFFFFF}
            .rep_table_header {font-size: 9pt;     font-family: Verdana,Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     font-weight:bold;    color: #ffffff;     height:25px;    background-color:#959595      }
          </style>
          </head>
          <body bgcolor=\"#FFFFFF\" text=\"#000000\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">
          Geçen aya ait yönetiminiz çalýþanlarýnýn telefon görüþme tutarlarý ektedir. <br>
          Bilgi için baþvuru telefonu: 1212<br>
        $stnotes
          <table width=\"85%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
            <tr>
              <td colspan=\"2\" width=\"100%\" align=\"center\" class=\"rep_header\" align=\"center\">
              <TABLE BORDER=\"0\" WIDTH=\"100%\">
                <TR>
                  <TD width=\"100%\" align=center CLASS=\"header\">".$SITE_NAME. "<BR>".$report_name."<br><!--DEPT_NAME--></TD>
                </TR>
              </TABLE>
              </td>
            </tr>
            <tr>
              <td width=\"100%\" class=\"rep_header\" align=\"right\"></td>
			</tr>
            <tr>
              <td colspan=\"2\">
              <table width=\"100%\" border=\"0\" bgcolor=\"#C7C7C7\" cellspacing=\"1\" cellpadding=\"0\">
                <tr>
                  <td class=\"rep_table_header\" width=\"28%\">Dahili</td>
                  <td class=\"rep_table_header\" width=\"12%\">Þehiriçi</td>
                  <td class=\"rep_table_header\" width=\"14%\">Þehirlerarasý</td>
                  <td class=\"rep_table_header\" width=\"12%\">GSM</td>
                  <td class=\"rep_table_header\" width=\"12%\">Uluslararasý</td>
                  <td class=\"rep_table_header\" width=\"10%\">Diðer</td>
                  <td class=\"rep_table_header\" width=\"12%\">Toplam</td>
                  <!--<td class=\"rep_table_header\" width=\"12%\" NOWRAP> Fark % si</td>-->
                </tr>
                <tr>
                  <td colspan=\"8\" bgcolor=\"#000000\" height=\"1\"></td>
                </tr>
          ";
          if(strlen($mailcrt)>0){
            $mailcrt=" AND DEPT_RSP_EMAIL='".$mailcrt."'  ";
          }
          
          if($unsentmails=="1"){
            $QRY = "Select DEPT_ID, DEPT_RSP_EMAIL from DEPTS 
			Left join TMAILLOGS ON TMAILLOGS.SMAIL=DEPTS.DEPT_RSP_EMAIL AND TMAILLOGS.SREP_TYPE='DEPT' AND SID='".$SID."' 
			WHERE (DEPT_RSP_EMAIL<>'' AND INSTR(DEPT_RSP_EMAIL,'@')) AND TMAILLOGS.ID IS NULL  ".$mailcrt."
			Group by DEPT_ID, DEPT_RSP_EMAIL ORDER BY DEPT_RSP_EMAIL";
          }else{
            $QRY = "Select DEPT_ID, DEPT_RSP_EMAIL from DEPTS WHERE (DEPT_RSP_EMAIL<>'' AND INSTR(DEPT_RSP_EMAIL,'@')) ".$mailcrt." Group by DEPT_RSP_EMAIL ORDER BY DEPT_RSP_EMAIL";
          }

          if (!($cdb->execute_sql($QRY, $rslt, $error_msg))){
            print_error($error_msg);
            exit;
          }
          while($rwx = mysql_fetch_object($rslt)){ ///Departman e-mail adresleri dönüþü start
            if($rwx->DEPT_RSP_EMAIL != ""){  ///Departman e-mail kontrolü start
              $sql_scl="Select SICIL_NO From EXTENTIONS WHERE DEPT_ID='".$rwx->DEPT_ID."'";
              if (!($cdb->execute_sql($sql_scl,$resscl,$error_msg))){
                print_error($error_msg);
                exit;
              }
              $sicil_nos = "";
              while($rwscl = mysql_fetch_object($resscl)){
                if($sicil_nos != ""){$sicil_nos = $sicil_nos.", ";}
                $sicil_nos = $sicil_nos."'".$rwscl->SICIL_NO."'";
              }
              $DEPT_ID = $rwx->DEPT_ID;
              $DATA = "";
              $sql_str="SELECT CDR_MAIN_DATA.LocationTypeid AS TYPE, CDR_MAIN_DATA.ORIG_DN, ACCESS_CODE, 
                          SUBSTRING(EXTENTIONS.DESCRIPTION,1,50) AS DESCRIPTION,
                          SUM(CDR_MAIN_DATA.PRICE) AS PRICE, MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) as MON, YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) as CYEAR
                        FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                        LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                        WHERE $kriter AND EXTENTIONS.DEPT_ID = '".$DEPT_ID."'
                        GROUP BY ORIG_DN, ACCESS_CODE, LocationTypeid";
              if($sicil_nos!=""){
                $sql_str=$sql_str." UNION 
                        SELECT 2, GSM_NO, 'GSM', SNAME, SUM(DCOST) AS PRICE , MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) as MON, YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH)) as CYEAR
                        FROM TGSMCALLS WHERE MYDATE>='$t0' AND MYDATE<='$t1'  AND SICIL_NO IN (".$sicil_nos.")
                        GROUP BY GSM_NO, SNAME;";
              }
              //ECHO $sql_str;
              if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                print_error($error_msg);
                exit;
              }
              UNSET($datas);
              unset($totalsbyacc);
              $genel_toplam = 0;$ortalama = 0;
              $cnt = 0;
              while($row = mysql_fetch_object($result)){ /// Data toplama start
                $CURR_MON  = $row->MON;
                $CURR_YEAR = $row->CYEAR;
                $genel_toplam += $row->PRICE * $PRICE_FACTOR;
                $acc_code=$row->ACCESS_CODE;
                if($acc_code==""){$acc_code="-";}
                $ORIG_DESCRIPTIONS[$row->ORIG_DN]=$row->DESCRIPTION;
                if($row->ORIG_DN != ""){  //Dahili kontrolü ve toplama start
                  $datas[$row->ORIG_DN][5][$acc_code]=1;
                  if($row->TYPE == 0){
                    $datas[$row->ORIG_DN][0][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else if($row->TYPE == 1){
                    $datas[$row->ORIG_DN][1][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else if($row->TYPE == 2){
                    $datas[$row->ORIG_DN][2][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else if($row->TYPE == 3){
                    $datas[$row->ORIG_DN][3][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else{
                    $datas[$row->ORIG_DN][4][$acc_code] += $row->PRICE * $PRICE_FACTOR;
                  }
                  $datas[$row->ORIG_DN][6][$acc_code] += $row->PRICE * $PRICE_FACTOR;
                }else{
                  if($row->TYPE == 0){
                    $datas[0][0][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else if($row->TYPE == 1){
                    $datas[0][1][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else if($row->TYPE == 2){
                    $datas[0][2][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else if($row->TYPE == 3){
                    $datas[0][3][$acc_code] = $row->PRICE * $PRICE_FACTOR;
                  }else{
                    $datas[0][4][$acc_code] += $row->PRICE * $PRICE_FACTOR;
                  }
                  $datas[0][6][$acc_code] += $row->PRICE;
                }///Dahili kontrolü ve toplama end
                $totalsbyacc[$acc_code]+=$row->PRICE;
              }  ///Data toplama end
              $cnt = sizeof($datas);
              if($cnt > 0) $ortalama = $genel_toplam/$cnt;
              $i = 0;
              $my_pr=0;
              $DATA_HEADER =  $DATA_HEAD;
              $DATA_HEADER = str_replace("<!--DEPT_NAME-->", "<STRONG>Departman</STRONG> : ".$rwx->DEPT_RSP_EMAIL."<BR> <STRONG>Dönem</STRONG> : ".($MONTH_LIST[$CURR_MON-1])."  ".$CURR_YEAR, $DATA_HEADER);
              if(is_array($datas)){   ///Dahili ekran düzenleme start
                foreach($totalsbyacc as $keyacc=>$valueacc){ ///Acc dönme start
                      $m = 0;
                      $si  = 0;
                      $sa  = 0;
                      $gsm = 0;
                      $ua  = 0;
                      $oth = 0;
                      $GNL=0;
                    $DATA .= "
                    <tr>
                      <td  colspan=\"8\" ALIGN=\"left\"><strong> ".$acc_code_arr[$keyacc]." ($keyacc) Görüþmeleri</strong></td>
                    </tr>\n
                    ";
                    foreach($datas as $key=>$value){ ///Data dönme start
                      $i++;
                      $bg_color = "E4E4E4";   
                      if($i%2) $bg_color ="FFFFFF";
                      $k_x = ($key=="0"?"Dahili Yok":$key);
                      $k_y = ($key=="0"?"-2":$key);
                      $DATAROW="";
                      $total = 0;
                      for($k=0;$k<=4;$k++){ /// Data ekleme start
                        $DATAROW .= " <td class=\"rep_td\" align=\"right\">&nbsp;".write_price($datas[$key][$k][$keyacc])."</td>\n";
                        $total += $datas[$key][$k][$keyacc];
                        $GNL+=$datas[$key][$k][$keyacc];
                      }///Data ekleme end
                    if($total!=0){
                      $DATA .= " <tr  BGCOLOR=$bg_color>\n";
                      $DATA .= " <td height=20 class=\"rep_td\">&nbsp;<b>".$k_x."</b> - ".$ORIG_DESCRIPTIONS[$key]."</td>\n";
                      $DATA .= $DATAROW;
                      $si  += $datas[$key][0][$keyacc];
                      $sa  += $datas[$key][1][$keyacc];
                      $gsm += $datas[$key][2][$keyacc];
                      $ua  += $datas[$key][3][$keyacc];
                      $oth += $datas[$key][4][$keyacc];
                      if($ortalama>0)
                        $yuzde = (($total-$ortalama)*100/$ortalama);
                      else
                        $yuzde;
                      $yuzde>0?$color = "#ff0000" : $color = "#008000";
                      $DATA .= " <td class=\"rep_td\" align=\"right\">&nbsp;<b>".write_price($total)."</b></td>\n";
                      //$DATA .= " <td class=\"rep_td\" align=\"right\">&nbsp;<b><font COLOR=$color>".number_format($yuzde,1,',',',')."</font></b></td>\n";
                      $DATA .= "</tr>\n";
                      $my_pr = $my_pr + $total;
                      }
                      $m++;
                    } /// Data dönme end
                    $DATA .= "
                    <tr>
                      <td  width=\"28%\" ALIGN=\"center\">(".$acc_code_arr[$keyacc].") $keyacc Toplamý</td>
                      <td width=\"12%\" ALIGN=\"right\">".write_price($si)."</td>
                      <td width=\"14%\" ALIGN=\"right\">".write_price($sa)."</td>
                      <td width=\"12%\" ALIGN=\"right\">".write_price($gsm)."</td>
                      <td width=\"12%\" ALIGN=\"right\">".write_price($ua)."</td>
                      <td width=\"10%\" ALIGN=\"right\">".write_price($oth)."</td>
                      <td width=\"12%\" ALIGN=\"right\">".write_price($GNL)."</td>
                      <td width=\"12%\" ALIGN=\"right\"></td>
                    </tr>
                    <tr>
                      <td height=3 colspan=8 BGCOLOR=#000000></td>
                    </tr>
                    ";
                }
              } ///Dahili ekran düzenleme end
              $DATA_FOOT = "
              </table>
              <TABLE width=\"100%\">
                <TR>
                  <td class=\"rep_td\" align=\"right\">
                  <B>Toplam Tutar : ".write_price($my_pr)." TL</B>
                  </TD>
                </TR>
                <TR>
                  <td class=\"rep_td\" align=\"right\">
                  <B>Departman Ortalamasý : ".write_price($ortalama)." TL</B>
                  </TD>
                </TR>
                <TR>
                  <td class=\"rep_td\" align=\"right\">
                  <B>Tüm Þirketin GSM Görüþmeler Ortalamasý : ".write_price($avg_gsm)." TL</B>
                  </TD>
                </TR>
              </TABLE>
              </td>  
            </tr>  
          </table>  
          <br><br>
          </body>
          ";
          if($DATA !=""){
            $DATA = $DATA_HEADER.$DATA.$DATA_FOOT;
            if($debug=="1"){
              echo $DATA;
              die;
            }else{
			  mail_send($rwx->DEPT_RSP_EMAIL,"Departman Ayrýntý Raporu.",$DATA);
              //echo $DATA;
              add_mailing_log('DEPT', $rwx->DEPT_RSP_EMAIL, 1);
            }
          }else{
            if($debug!="1"){
              add_mailing_log('DEPT', $rwx->DEPT_RSP_EMAIL, 0);
            }
          }
        }///Departman e-mail kontrolü end
      } ///Departman e-mail adresleri dönüþü end
    } ///Mail gün kontrolü end 
  }///Mail atýlacak mý end
} ///Site dönüþü end
if($force=="1" && $debug!="1"){
   $sql_log = "SELECT * FROM TMAILLOGS WHERE SREP_TYPE='DEPT' AND SID=".$SID." order by ISTAT ";
   if (!($cdb->execute_sql($sql_log, $rslog, $error_msg))){
     print_error($error_msg);
     exit;
   }
   echo "<table border=1>\n";
     echo "<tr><td><strong>Mail Adresi</strong></td>\n";
     echo "<td><strong>Durumu</strong></td></tr>\n";
   while($rw_log=mysql_fetch_object($rslog)){
     echo "<tr><td>".$rw_log->SMAIL."</td>\n";
     if($rw_log->ISTAT=="1")
       echo "<td>Atýldý</td></tr>\n";
     else
       echo "<td>Atýlamadý</td></tr>\n";
   }
   echo "</table>\n";
   echo "<script>alert('Mailing Tamamlandý!');</script>";
}
?>
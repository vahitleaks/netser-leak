<?   
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
  session_cache_limiter('nocache');     
  $cUtility = new Utility();
  $cdb = new db_layer(); 
  $conn = $cdb->getConnection();
    include("mail_send.php");

  if(!$conn) exit;
  $sql_str="SELECT Locationid,LocationName FROM TLocation"; 
  if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
    print_error($error_msg);
    exit;
  }
  $arr_location = array();
  while ($row=mysql_fetch_object($result)){
    $arr_location[$row->Locationid] = $row->LocationName;
  }

  $arr_contact = array();
  $arr_ext = array();
  $sql_str = "SELECT * FROM SITES ";
  if (!($cdb->execute_sql($sql_str, $rs, $error_msg))){
    print_error($error_msg);
    exit;
  }
  while($row = mysql_fetch_object($rs)){//B�t�n siteler taranmal� Site d�ng�s� start
    $SITE_ID = $row->SITE_ID;
    $SITE_NAME = $row->SITE_NAME;
    $max_acc_duration = $row->MAX_ACCE_DURATION*60;
    $PRICE_FACTOR = $row->PRICE_FACTOR;
    $MONTHLY_MAILING_DAY = $row->MONTHLY_MAILING_DAY;
    $LOC_CODE = $row->SITE_CODE;

    ///Joinden ka�mak i�in ilgili sitenin contact bilgileri diziye al�n�yor.
    $sql_str1="SELECT IF(IS_COMPANY=1,COMPANY,CONCAT(NAME,' ',SURNAME)) AS NAME,
                CONCAT(PHONES.COUNTRY_CODE,IFNULL(PHONES.CITY_CODE,''),PHONES.PHONE_NUMBER) AS PHONE_NUM  
              FROM CONTACTS
              INNER JOIN PHONES ON CONTACTS.CONTACT_ID = PHONES.CONTACT_ID
              WHERE CONTACTS.SITE_ID = ".$SITE_ID." AND IS_GLOBAL = 1 ORDER BY PHONE_NUM ASC
             "; 
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
      print_error($error_msg);
      exit;
    }
    unset($arr_contact);
    while ($row1=mysql_fetch_object($result1)){
      $arr_contact[$row1->PHONE_NUM] = $row1->NAME;
    }

    if($row->MONTHLY_MAILING_DAY > '0'){//0'dan b�y�kse ilgili g�nde mailing yap�lacak�r. Mail at�lacak m� start
      if($row->MONTHLY_MAILING_DAY == date("d") || $force==1){//Mail g�n� geldi mi start
        $report_name = "Ayl�k Dahili G�r��me Raporu";
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
        <table width=\"65%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
          <tr>
            <td colspan=\"2\" width=\"100%\" align=\"center\" class=\"rep_header\" align=\"center\">
            <TABLE BORDER=\"0\" WIDTH=\"100%\">
              <TR>
                <TD><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"cid:my-crystal\" ></a></TD>
                <TD width=\"50%\" align=center CLASS=\"header\">".$SITE_NAME . "<BR>Ayl�k Dahili Raporu<br><STRONG>D�nem</STRONG> : <!-- DONEM --></TD>
                <TD width=\"25%\" align=right><img SRC=\"cid:my-attach\"></TD>
              </TR>
            </TABLE>
            </td>
          </tr>
          <tr>
            <td colspan=\"2\" width=\"100%\" align=\"Left\" class=\"rep_header\" align=\"center\">
              Dahili : <!-- DAHILI -->
            </td>
          </tr>
          <tr>
            <td colspan=\"2\">
            <table border=\"0\"  width=100% bgcolor=\"#C7C7C7\" cellspacing=\"1\" cellpadding=\"0\">
              <tr>
                <td align=center class=\"rep_table_header\" width=\"15%\">Eri�im Kodu</td>
                <td align=center class=\"rep_table_header\" width=\"25%\">Tarih</td>
                <td  align=center class=\"rep_table_header\" width=\"15%\">S�re</td>
                <td  align=center class=\"rep_table_header\" width=\"20%\">Telefon</td>
                <td  align=center class=\"rep_table_header\" width=\"20%\">Aranan Yer</td>
                <td  align=center class=\"rep_table_header\" width=\"15%\">�cret</td>
              </tr>
              <tr>
                <td colspan=\"7\" bgcolor=\"#000000\" height=\"1\"></td>
              </tr>
        ";
        $DATA_FOOTER = "
            </table>
            </td>
          </tr>
        </table>";
        $QRY = "SELECT * FROM EXTENTIONS WHERE (EMAIL<>'' AND INSTR(EMAIL,'@')) AND SITE_ID = ".$SITE_ID;
        if (!($cdb->execute_sql($QRY, $rslt, $error_msg))){
          print_error($error_msg);
          exit;
        }
        while($rwx = mysql_fetch_object($rslt)){//Sitedeki dahililer d�ng�s� start
          $orig_dn = $rwx->EXT_NO;
          $descr   = substr($rwx->DESCRIPTION,0,50);
          $email   = $rwx->EMAIL;

	      $kriter = "";
          //Temel kriterler. Verinin h�zl� gelmesi i�in ba�a konuldu.
          $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmal�.�lgili siteyi belirliyor.
          $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmal�.Hatas�z kay�t oldu�unu g�steriyor.      
          $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmal�.D�� arama oldu�unu g�steriyor.
          $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_duration"); //Bu mutlaka olmal�.D�� arama oldu�unu g�steriyor.
          $kriter .= $cdb->field_query($kriter,   "PRICE"     ,  ">",  "0"); //Bu mutlaka olmal�.D�� arama oldu�unu g�steriyor.
          $t0 = day_of_last_month("first");
          $t1 = day_of_last_month("last");

		  //�nceki ay�n datas�ndan als�n.
	      $CDR_MAIN_DATA = getTableName($t0,$t1);
          if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  
          $local_country_code = get_country_code($SITE_ID);

	      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");

          $DATA = "";
          if ($email<>""){//Mail adresi yoksa g�ndermenin bir anlam� yok Email var m� start
            $sql_str_orig  = "SELECT LTRIM(ORIG_DN) AS ORIG_DN,ACCESS_CODE,
                                DATE_FORMAT(TIME_STAMP,\"%d.%m.%Y %H:%i:%s\") AS MY_DATE, LocationTypeid,
                                DURATION, Locationid, CountryCode, LocalCode, PURE_NUMBER, 
								CONCAT(CountryCode, LocalCode, PURE_NUMBER) AS PHONE_NUM,
                                (CDR_MAIN_DATA.PRICE*$PRICE_FACTOR) AS PRICE
                              FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                             ";
            $kriter .= $cdb->field_query($kriter,   "LTRIM(CDR_MAIN_DATA.ORIG_DN)"     ,  "=",  "'$orig_dn'");
            $sql_str_orig .= " WHERE ".$kriter."  ORDER BY MY_DATE ASC ";
			//echo $sql_str_orig;
			if (!($cdb->execute_sql($sql_str_orig,$result_orig,$error_msg))){
              print_error($error_msg);
              exit;
            }
            $my_pr=0;
            while($row_orig = mysql_fetch_object($result_orig)){//Rapor i�eri�i start
              if($row_orig->ORIG_DN != ""){//Dahili alan� dolumu start
                if ($row_orig->CountryCode == $local_country_code){
                  if ($row_orig->LocalCode == $LOC_CODE || $row_orig->LocalCode==""){
                    $TEL_NUMBER = $LOC_CODE ." ".$row_orig->PURE_NUMBER;
                  }else{
                    $TEL_NUMBER = $row_orig->LocalCode." ".$row_orig->PURE_NUMBER;
                  }
                }else{
                  $TEL_NUMBER = $row_orig->CountryCode." ".$row_orig->LocalCode." ".$row_orig->PURE_NUMBER;
                } 
                if ($arr_contact[$row_orig->PHONE_NUM]){
                  $called = "<b>".$arr_contact[$row_orig->PHONE_NUM]."</b>";
                }else{
                  $called = $arr_location[$row_orig->Locationid];
                }
                $i++;
                $bg_color = "E4E4E4";   
                if($i%2) $bg_color ="FFFFFF";
                $DATA  .= " <tr  BGCOLOR=$bg_color>\n";
                $DATA  .= " <td class=\"rep_td\" align=\"center\">&nbsp;".$row_orig->ACCESS_CODE."</td>\n";
                $DATA  .= " <td class=\"rep_td\" align=\"center\">&nbsp;".$row_orig->MY_DATE."</td>\n";
                $DATA  .= " <td class=\"rep_td\" align=\"center\">&nbsp;".calculate_all_time($row_orig->DURATION)."</td>\n";
                $DATA  .= " <td class=\"rep_td\" align=\"center\">&nbsp;".$TEL_NUMBER."</td>\n";
                $DATA  .= " <td class=\"rep_td\" align=\"center\">&nbsp;".$called."</td>\n";
                $DATA  .= " <td class=\"rep_td\" align=\"right\">".write_price($row_orig->PRICE * $PRICE_FACTOR)."</td>\n";
                $DATA  .= "</tr>\n";
                $my_pr = $my_pr + $row_orig->PRICE * $PRICE_FACTOR;
              }//Dahili alan� dolu mu end
            }//Rapor i�eri�i doldu end
            $DATA .= "
            <tr>
              <td colspan=7 height=3 BGCOLOR=#000000></td>
            </tr>
            <tr >
              <td class=\"rep_table_header\"></td>
              <td colspan=4 class=\"rep_table_header\" width=\"60%\">Toplam</td>
              <td class=\"rep_table_header\" width=\"10%\">".write_price($my_pr)."</td>
            </tr>";
            $DATA_HEAD = $DATA_HEADER;
            $DATA_HEADER = str_replace("<!-- DAHILI -->", $orig_dn . "  -- ". $descr, $DATA_HEADER);
            $DATA_HEADER = str_replace("<!-- DONEM -->", ($t0." - ".$t1) ,$DATA_HEADER);
            if($DATA !=""){
              $DATA = $DATA_HEADER.$DATA.$DATA_FOOTER;
			  //echo $DATA;
			  //echo $email."--".$SITE_ID."<br>";
			  mail_send($email,"Dahili Raporu -- $orig_dn.",$DATA);
			  $DATA=""; //Data de�i�kenini bo�alt
              $DATA_HEADER = $DATA_HEAD;
            }
            $email = "";
            $orig_dn = "";
          }//Email var m� end. 
        }//Sitedeki dahililer d�n�yor end
      }//Mail g�n� geldi mi end
    }//Mail at�lacak m� end
  }//Site d�ng�s� kapand�.
 ?>

<?
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  $conn = $cdb->getConnection();
  if(!$conn) exit;
  /////////////////////////////////////////////////////////////////

  function periodic_summary($SITE_ID,$type){
    global $cdb, $conn, $HTTP_HOST;
    $company = get_site_prm('SITE_NAME',$SITE_ID);
	$max_acc_dur = (get_site_prm('MAX_ACCE_DURATION',$SITE_ID))*60;
	
    //Joinden kaçmak için Çaðrý türündeki tablosundaki bilgiler alýnýyor.
    $sql_str="SELECT LocationTypeid, LocationType FROM TLocationType";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
    $arr_location_type = array();
    while ($row=mysql_fetch_object($result)){
        $arr_location_type[$row->LocationTypeid] = $row->LocationType;
    }

    $kriter = "";   
    //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
    $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
    $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
    
    if ($type=='day'){
      $report_name = "Günlük Görüþme Özet Raporu";
      $t0 = strftime("%Y-%m-%d", mktime(0,0,0,date("m"),date("d")-1,date("y")));
      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"=",  "'$t0'");
    }elseif($type=='week'){
      $report_name = "Haftalýk Görüþme Özet Raporu";
      $t0 = day_of_last_week(1);
      $t1 = day_of_last_week(7);
      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
    }elseif($type=='month'){
      $report_name = "Aylýk Görüþme Özet Raporu";
      $t0 = day_of_last_month("first");
      $t1 = day_of_last_month("last");
      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
    }else{
      return 0;
      exit;
    }
    //////////////////////LAST RECORD////////////////////////////// 
    $sql_str = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, CDR_MAIN_DATA.LocationTypeid, 
                       SUM(DURATION) AS DURATION, SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                FROM CDR_MAIN_DATA 
               ";
    if ($kriter != "")
      $sql_str .= " WHERE ".$kriter;

    $sql_str .= " GROUP BY CDR_MAIN_DATA.LocationTypeid";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    if($t0!="")
      $date_show = date("d/m/Y",strtotime($t0));
    if($t1!="")
      $date_show .= " - ".date("d/m/Y",strtotime($t1));

    $DATA ="
    <html>
    <head>
    <title>Crystal Info</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1254\">
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-9\">
    </head>
    <style>
      body {font-family:Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
      .homebox {font-family: Verdana, Arial, Helvetica, sans-serif;         font-size: 7pt;         font-weight: bold;         font-variant: normal;         text-transform: none;         color: FF6600;         text-decoration: none}
      .header {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #2C5783;     text-decoration: none}
      .header_beyaz2 {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: FFFFFF;        background-color:  508AC5;    text-decoration: none}
      .header_sm {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 7pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #2C5783;     text-decoration: none}
      a.a1 {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #1B4E81;     text-decoration: none}
      a.a1:hover {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
      a {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #FF6600;     text-decoration: none}
      .text {  font-family: Verdana, Arial, Helvetica, sans-serif;  font-size: 8pt;  font-weight: normal;  font-variant: normal;  text-transform: none;  color: #1b4e81 ;  text-decoration: none}
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
    <body bgcolor=\"#FFFFFF\" text=\"#000000\" leftmargin=\"0\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\">
    <table width=\"85%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
      <tr>
        <td colspan=\"2\" width=\"100%\" align=\"center\" class=\"rep_header\" align=\"center\">
        <TABLE BORDER=\"0\" WIDTH=\"100%\">
          <TR>
            <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"cid:my-crystal\" ></a></td>
            <TD align=center CLASS=\"header\">".get_site_name($SITE_ID)."<BR>".$report_name."</TD>
            <TD align=right><img SRC=\"cid:my-attach\"></TD>
          </TR>
        </TABLE>
        </td>
      </tr>
      <tr>
        <td width=\"100%\" class=\"rep_header\" align=\"right\">Tarih ($date_show)</td>
	  </tr>
      <tr>
        <td colspan=\"2\">
        <table width=\"100%\" border=\"0\" bgcolor=\"#C7C7C7\" cellspacing=\"1\" cellpadding=\"0\">
          <tr>
            <td class=\"rep_table_header\" width=\"25%\">Arama Tipi</td>
            <td class=\"rep_table_header\" width=\"20%\">Adet</td>
            <td class=\"rep_table_header\" width=\"20%\">Süre</td>
            <td class=\"rep_table_header\" width=\"20%\">Tutar(TL)</td>
          </tr>
          <tr>
            <td colspan=\"5\" bgcolor=\"#000000\" height=\"1\"></td>
          </tr>";
          //$data_cntl = -1;
          while($row = mysql_fetch_object($result)){
            $i++;
            $bgcolor = $i%2==1 ? "FFFFFF" : "E4E4E4";
            $DATA .="<tr  BGCOLOR=$bgcolor> 
                       <td class=\"rep_td\"><b>".$arr_location_type[$row->LocationTypeid]."</b></td> 
                       <td class=\"rep_td\">".number_format($row->AMOUNT,0,"",".")."</td> 
                       <td class=\"rep_td\">".calculate_all_time($row->DURATION)."</td> 
                       <td class=\"rep_td\" ALIGN=right>" .write_price($row->PRICE)."</td>
                     </tr>";
            $t_cnt += $row->AMOUNT;
            $t_dur += $row->DURATION;
            $t_pri += $row->PRICE;
            //$data_cntl = 1;
          } 
          $t_cnt = number_format($t_cnt,0,"",".");
          $t_pri = write_price($t_pri);
          $DATA .= "
          </table>
          </td>  
        </tr>  
        <tr height=\"20\">
        <td></td>
      </tr>
      <tr>
        <td height=\"22\" colspan=\"3\" width=\"100%\" align=\"right\">
        <TABLE BORDER=\"0\" WIDTH=\"100%\">
          <TR>
            <TD WIDTH=\"80%\" ALIGN=\"right\"><b>Toplam Görüþme Adedi :</b></TD>
            <TD WIDTH=\"20%\">".$t_cnt."</TD>
          </TR>
        </TABLE>
        </td>
      </tr>
      <tr>
	    <td height=\"22\" colspan=\"3\" width=\"100%\" align=\"right\">
        <TABLE BORDER=\"0\" WIDTH=\"100%\">
          <TR>
            <TD WIDTH=\"80%\" ALIGN=\"right\"><b>Toplam Süre :</b></TD>
            <TD WIDTH=\"20%\" >".calculate_all_time($t_dur)."</TD>
          </TR>
        </TABLE>
        </td>
      </tr>
      <tr>
        <td height=\"22\" colspan=\"3\" width=\"100%\" align=\"right\">
        <TABLE BORDER=\"0\" WIDTH=\"100%\">
          <TR>
            <TD WIDTH=\"80%\" ALIGN=\"right\"><b>Toplam Tutar :</b></TD>
            <TD WIDTH=\"20%\">".$t_pri."</TD>
          </TR>
        </TABLE>
        </td>
      </tr>
    </table>  
  ";  //if($data_cntl==1)
  return $DATA;
  }
?>

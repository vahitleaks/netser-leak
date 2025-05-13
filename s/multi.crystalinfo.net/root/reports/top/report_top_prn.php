<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $cUtility = new Utility();
    $cdb = new db_layer(); 
    $conn = $cdb->getConnection();
    require_valid_login();
    $usr_crt = "";
    if (right_get("SITE_ADMIN")){
      //Site admin hakký varsa herþeyi görebilir.  
      //Site id gelmemiþse kiþinin bulunduðu site raporu alýnýr.
      if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
      // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
      $SITE_ID = $SESSION['site_id'];
    }elseif(got_dept_right($SESSION["user_id"])==1){
      //Bir departmanýn raporunu görebiliyorsa kendi sitesindekileri girebilir.
      $SITE_ID = $SESSION['site_id'];
      //echo $dept_crt = get_depts_crt($SESSION["user_id"]);
      $usr_crt = get_users_crt($SESSION["user_id"],1,$SESSION['site_id']);
      $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini içerir.";
    }else{
      print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 
    ob_start();
  //Hak kontrolü sonu  

?>  
   <form name="sort_me" method="post" action="">
     <input type="hidden" name="SITE_ID" value="<?=$SITE_ID?>">  
     <input type="hidden" name="MY_DATE" value="<?=$MY_DATE?>">
     <input type="hidden" name="t0" value="<?=$t0?>">         
     <input type="hidden" name="t1" value="<?=$t1?>">         
     <input type="hidden" name="last" value="<?=$last?>">         
     <input type="hidden" name="hh0" value="<?=$hh0?>">
     <input type="hidden" name="hm0" value="<?=$hm0?>">
     <input type="hidden" name="hh1" value="<?=$hh1?>">
     <input type="hidden" name="hm1" value="<?=$hm1?>">
     <input type="hidden" name="hafta" value="<?=$hafta?>">
     <input type="hidden" name="record1" value="<?=$record1?>">
     <input type="hidden" name="type" value="<?=$type?>">
  </form>
<?
    $sql_str1="SELECT SITE_NAME, MAX_ACCE_DURATION FROM SITES WHERE SITE_ID = ".$SITE_ID; 
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if (mysql_num_rows($result1)>0){
    $row1 = mysql_fetch_object($result1);
        $company = $row1->SITE_NAME;
        $max_acc_dur =  ($row1->MAX_ACCE_DURATION)*60;
    }else{
      print_error("Site paramatreleri bulunamadý.");
      exit;
    }

    $local_country_code = get_country_code($SITE_ID);//Sitenin ülke kodu

    cc_page_meta();
    echo "<center>";

    $report_type="Genel Rapor";

    if ($act == "src") {

    $kriter = "";

     //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
     $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
     $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
     $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
     $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
     $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.

    add_time_crt();//Zaman kriteri
    if($forceMainTable)
      $CDR_MAIN_DATA = "CDR_MAIN_DATA";
    else
      $CDR_MAIN_DATA = getTableName($t0,$t1);
    if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";

   $link_it = -1;
   switch ($type){
     case 'call_amount_number':
      $sql_id="1";$grp_type="PHONE_NUMBER";$ord_type="AMOUNT DESC";
      $field1="PHONE_NUMBER";$field1_name="Numara";$width1="25%";$field1_ord="PHONE_NUMBER";
      $field2="AMOUNT";$field2_name="Adet";$width2="25%";$field2_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Çok Aranan Numaralar";
      $link_it = 1;
      $go_fld  = "DIGITS"; 
      break;
     case 'call_amount_ext':
      $sql_id="2";$grp_type="ORIG_DN";$ord_type="AMOUNT DESC";
      $field1="ORIG_DN";$field1_name="Dahili";$width1="10%";$field1_ord="ORIG_DN";
      $field2="DESCRIPTION";$field2_name="Adý Soyadý";$width2="25%";$field2_ord="ORIG_DN";
      $field3="AMOUNT";$field3_name="Adet";$width3="25%";$field3_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Çok Arama Yapan Dahililer";
      $link_it = 1;
      $go_fld  = "ORIG_DN";
      break;   
     case 'call_duration_ext':
      $sql_id="3";$grp_type="ORIG_DN";$ord_type="DURATION DESC";
      $field1="ORIG_DN";$field1_name="Dahili";$width1="25%";$field1_ord="ORIG_DN";
      $field2="DESCRIPTION";$field2_name="Adý Soyadý";$width2="25%";$field2_ord="ORIG_DN";
      $field3="AMOUNT";$field3_name="Adet";$width3="25%";$field3_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Uzun Süre Görüþme Yapan Dahililer";
      $link_it = 1;
      $go_fld  = "ORIG_DN";
      break;
     case 'call_duration_number':
      $sql_id="4";$grp_type="PHONE_NUMBER";$ord_type="DURATION DESC";
      $field1="PHONE_NUMBER";$field1_name="Telefon";$width1="25%";$field1_ord="PHONE_NUMBER";
      $field2="AMOUNT";$field2_name="Adet";$width2="25%";$field2_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Uzun Süre Görüþülen Numaralar";
      $link_it = 1;
      $go_fld  = "DIGITS"; 
      break;   
     case 'call_amount_city':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"            ,"=",    "'$local_country_code'");
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"         ,"=",    "'1'");
      $sql_id="5";$grp_type="LocalCode";$ord_type="AMOUNT DESC";
      $field1="LocalCode";$field1_name="Ýl Kodu";$width1="13%";$field1_ord="LocalCode";
      $field2="LocationName";$field2_name="Ýl Adý";$width2="25%";$field2_ord="LocationName";
      $field4="AMOUNT";$field4_name="Adet";$width4="10%";$field4_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Fazla Aranan Ýller";
      break;   
     case 'call_amount_country':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"         ,"=",    "'3'");
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"            ,"<>",    "'$local_country_code'");
      $sql_id="6";$grp_type="CountryCode";$ord_type="AMOUNT DESC";
      $field1="CountryCode";$field1_name="Ülke Kodu";$width1="12%";$field1_ord="CountryCode";
      $field2="LocationName";$field2_name="Ülke Adý";$width2="25%";$field2_ord="LocationName";
      $field4="AMOUNT";$field4_name="Adet";$width4="10%";$field4_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Fazla Aranan Ülkeler";
      break;
     case 'call_amount_time':
      $sql_id="7";$grp_type="TIME_STAMP_HOUR";$ord_type="AMOUNT DESC";
      $field1="TIME_INTERVAL";$field1_name="Saat Dilimi";$width1="10%";$field1_ord="TIME_INTERVAL";
      $field2="AMOUNT";$field2_name="Adet";$width2="10%";$field2_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="20%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="20%";$field7_ord="PRICE DESC";
      $header = "En Fazla Arama Yapýlan Saatler";
      break;   
    case 'call_amount_gsm':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"            ,"=",    "'$local_country_code'");
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"         ,"=",    "'2'");
      $sql_id="8";$grp_type="LocalCode";$ord_type="AMOUNT DESC";
      $field1="LocalCode";$field1_name="Gsm Kodu";$width1="8%";$field1_ord="LocalCode";
      $field2="LocationName";$field2_name="GSM Adý";$width2="15%";$field2_ord="LocationName";
      $field4="AMOUNT";$field4_name="Adet";$width4="8%";$field4_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE DESC";
      $header = "En Fazla Arama Yapýlan GSM Operatörleri";
      break;   
     case 'call_amount_gsm_number':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"           ,"=",    "'$local_country_code'");
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"        ,"=",    "'2'");
      $sql_id="9";$grp_type="PHONE_NUMBER";$ord_type="AMOUNT DESC";
      $field1="PHONE_NUMBER";$field1_name="Telefon No";$width1="15%";$field1_ord="PHONE_NUMBER";
      $field2="AMOUNT";$field2_name="Adet";$width2="10%";$field2_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="20%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="20%";$field7_ord="PRICE DESC";
      $header = "En Fazla Aranan GSM Numaralarý";
      $link_it = 1;
      $go_fld  = "DIGITS";
      break;   
    case 'call_amount_auth':
      $sql_id="10";$grp_type="AUTH_ID";$ord_type="PRICE DESC";
      $field1="AUTH_CODE";$field1_name="Auth.Kod";$width1="25%";$field1_ord="AUTH_CODE";
      $field2="AUTH_CODE_DESC";$field2_name="Adý Soyadý";$width2="25%";$field2_ord="AUTH_ID";
      $field3="AMOUNT";$field3_name="Adet";$width3="25%";$field3_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION DESC";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE DESC";
      $header = "En Fazla Arama Yapan Auth. Kodlarý";
      $link_it = 1;
      $go_fld  = "AUTH_CODE"; 
      break;
    default:
      echo "Hatalý Durum Oluþtu. Lütfen Tekrar Deneyiniz.";
      exit;
   }
   
  switch ($sql_id){
    case 1:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.ORIG_DN,
                    SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                    SUM(CDR_MAIN_DATA.PRICE) AS PRICE,CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'$local_country_code',CONCAT('00',CountryCode),''),
                    IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER)
                    AS PHONE_NUMBER, PURE_NUMBER AS DIGITS
                  FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  ";
      break;      
    case 2:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, CDR_MAIN_DATA.ORIG_DN, EXTENTIONS.DESCRIPTION ,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE,CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'$local_country_code',CONCAT('00',CountryCode),''),
                     IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER) AS PHONE_NUMBER, PURE_NUMBER AS DIGITS
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA  
                   LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
            ";
      break;
    case 3:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, CDR_MAIN_DATA.ORIG_DN, EXTENTIONS.DESCRIPTION ,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE,CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'$local_country_code',CONCAT('00',CountryCode),''),
                     IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER) AS PHONE_NUMBER, PURE_NUMBER AS DIGITS
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA  
                   LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                        ";
      break;
    case 4:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.ORIG_DN,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE,CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'$local_country_code',CONCAT('00',CountryCode),''),
                     IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER) AS PHONE_NUMBER, PURE_NUMBER AS DIGITS
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA  
                   ";
      break;
    case 5:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     COUNT(CDR_MAIN_DATA.LocalCode) AS CITY_AMOUNT,CDR_MAIN_DATA.LocalCode,
                     TLocation.LocationName, SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   LEFT JOIN TLocation ON CDR_MAIN_DATA.Locationid=TLocation.Locationid
                   ";
      break;
    case 6:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     COUNT(CDR_MAIN_DATA.CountryCode) AS COUNTRY_AMOUNT, CDR_MAIN_DATA.CountryCode,
                     TLocation.LocationName, SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   LEFT JOIN TLocation ON CDR_MAIN_DATA.Locationid=TLocation.Locationid
                      ";
      break;
    case 7:
      $sql_str  ="SELECT COUNT(CDR_ID) AS AMOUNT,SUM(DURATION) AS DURATION,
                    CONCAT(CONCAT(DATE_FORMAT(TIME_STAMP,'%H'),'-'),DATE_FORMAT(DATE_ADD(TIME_STAMP, INTERVAL 1 HOUR),'%H')) AS TIME_INTERVAL,
                    SUM(PRICE) AS PRICE,TIME_STAMP_HOUR
                  FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                      ";
      break;
    case 8:
      $sql_str  ="SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                    COUNT(CDR_MAIN_DATA.CountryCode) AS COUNTRY_AMOUNT, CDR_MAIN_DATA.LocalCode,
                     TLocation.LocationName, SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                  FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  LEFT JOIN TLocation ON CDR_MAIN_DATA.Locationid=TLocation.Locationid
                      ";
      break;
    case 9:
      $sql_str  ="SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                    SUM(CDR_MAIN_DATA.PRICE) AS PRICE, CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'$local_country_code',CONCAT('00',CountryCode),''),
                    IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER) AS PHONE_NUMBER, PURE_NUMBER AS DIGITS
                  FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                 ";   
      break;
    case 10:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,AUTH_CODES.AUTH_CODE_DESC,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE, CDR_MAIN_DATA.AUTH_ID AS AUTH_CODE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   LEFT JOIN AUTH_CODES ON CDR_MAIN_DATA.AUTH_ID = AUTH_CODES.AUTH_CODE
                   AND CDR_MAIN_DATA.SITE_ID = AUTH_CODES.SITE_ID
                  ";
      break;
    default:
  } 

   if ($kriter != "")
     $sql_str .= " WHERE ".$kriter." ".$usr_crt;  
       
   $sql_str .= " GROUP BY ". $grp_type;

    if ($order<>''){
      switch ($order){
      case '1':
        $sql_str .= " ORDER BY ".$field1_ord; 
        break;
      case '2':
        $sql_str .= " ORDER BY ".$field2_ord; 
        break;
      case '3':
        $sql_str .= " ORDER BY ".$field3_ord; 
        break;
      case '4':
        $sql_str .= " ORDER BY ".$field4_ord; 
        break;
      case '5':
        $sql_str .= " ORDER BY ".$field5_ord; 
        break;
      case '6':
        $sql_str .= " ORDER BY ".$field6_ord; 
        break;
      case '7':
        $sql_str .= " ORDER BY ".$field7_ord; 
        break;
      default:
           }
     }else if ($ord_type<>''){
        $sql_str .= " ORDER BY ".$ord_type;
     }
     
     if ($record1<>'' ||is_numeric($record1)) {
               $sql_str .= " LIMIT 0,". $record1 ;
         }else{
               $sql_str .= " LIMIT 0,50"; 
     }
//echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
?>
<br><br>
       <input type="hidden" name="ORDERBY" value="<?=$ORDERBY?>">

<table width="85%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td  align="center" class="rep_header" align="center">
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>logo2.gif" ></a></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>TOP RAPORLAR<br><?=$header?></TD>
              <TD width="25%" align=right><img SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
          </TABLE>
      </td>
  </tr>
    <tr>
     <td>
     <table width="100%" cellspacing=0 cellpadding=0>
    <tr>
      <td width="50%" class="rep_header" align="left">
         <?if($t0!=""){?>
          Tarih (<?=date("d/m/Y",strtotime($t0))?>
       <?if($t1!=""){?>
         <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
    )
       <?}?>
       </td><td width="50%" align="right"> 
      <table cellspacing=0 cellpadding=0>
        <tr>
          <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
          <td><a href="javascript:mailPage('top.html')"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>          
          <td><a href="javascript:history.back(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
          <td><a href="javascript:history.forward(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
          <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
          <td><a href="javascript:window.print();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
          <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
        </tr>
      </table>
      </td></tr></table>
      </td>
  </tr>
   <?
       $col_cnt = 1;
       $csv_data[0][1] = $company;
       $csv_data[0][2] = "" ;
       $csv_data[0][3] = "Top";
       $csv_data[0][4] = "Raporlar";?>
    <tr>
    <td colspan="2">
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
            <?$sort_gif = "report/down.gif";?>
          <tr>
              <td class="rep_table_header" width="<?=$width1;?>"><?echo $field1_name;?></td>
                    <?$csv_data[1][0] = $field1_name;?>
          <?if ($field2_name<>''){?>
            <td class="rep_table_header" width="<?=$width2;?>"><?echo $field2_name;?><a style="cursor:hand;" onclick="javascript:submit_form('2');"><img src="<?=IMAGE_ROOT?><?=($order=="2")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                    <?$csv_data[1][$col_cnt] = $field2_name;?>
          <?}?>
          <?if ($field3_name<>''){?>
            <td class="rep_table_header" width="<?=$width3;?>"><?echo $field3_name;?><a style="cursor:hand;" onclick="javascript:submit_form('3');"><img src="<?=IMAGE_ROOT?><?=($order=="3")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field3_name;?>
          <?}?>
          <?if ($field4_name<>''){?>
            <td class="rep_table_header" width="<?=$width4;?>"><?echo $field4_name;?><a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field4_name;?>
          <?}?>
          <?if ($field5_name<>''){?>
            <td class="rep_table_header" width="<?=$width5;?>"><?echo $field5_name;?><a style="cursor:hand;" onclick="javascript:submit_form('5');"><img src="<?=IMAGE_ROOT?><?=($order=="5")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field5_name;?>
          <?}?>
          <?if ($field6_name<>''){?>
            <td class="rep_table_header" width="<?=$width6;?>"><?echo $field6_name;?><a style="cursor:hand;" onclick="javascript:submit_form('6');"><img src="<?=IMAGE_ROOT?><?=($order=="6")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field6_name;?>
          <?}?>
          <?if ($field7_name<>''){?>
            <td class="rep_table_header" width="<?=$width7;?>"><?echo $field7_name;?><a style="cursor:hand;" onclick="javascript:submit_form('7');"><img src="<?=IMAGE_ROOT?><?=($order=="7")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field7_name;?>
          <?}?>
          </tr>
        <tr>
          <td colspan="5" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
        $i;
        $j=1;$col_cnt=1;
        $myrow = "row->$field1";
        $my_dur=0;
        $my_amount=0;
        $my_pr=0;
         if (mysql_num_rows($result)>0)
              mysql_data_seek($result,0);
         while($row = mysql_fetch_array($result)){
             $i++; $j++;$call_cnt=1;
               $bg_color = "E4E4E4";   
               if($i%2) $bg_color ="FFFFFF";
            echo " <tr  BGCOLOR=$bg_color>";
               $go_val = $row["$go_fld"];
                  
            if($link_it==1) echo "<td ><a class=\"a1\" HREF=\"javascript:drill_down('$go_fld','".$go_val."')\">";
            if($link_it==1) echo $row["$field1"];
            if($link_it==1) echo "</a></td>";
            if($link_it==-1) echo " <TD>".$row["$field1"]."</TD>";
            $csv_data[$j][0] = $row["$field1"];
                             
          if ($field2_name<>''){
            echo " <td class=\"rep_td\">".$row["$field2"]."</td>";
                        $csv_data[$j][$col_cnt] =$row["$field2"];$col_cnt++;
             }
          if ($field3_name<>''){
            if ($field3 == "DURATION"){
              echo "<td xclass=\"rep_td\">".calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk</td>";
                            $csv_data[$j][$col_cnt] =calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk";$col_cnt++;
            }else{
              echo " <td class=\"rep_td\">".$row["$field3"]."</td>";  
                            $csv_data[$j][$col_cnt] =$row["$field3"];$col_cnt++;
                        }
          }
          if ($field4_name<>''){
            echo " <td class=\"rep_td\">".$row["$field4"]."</td>";
                        $csv_data[$j][$col_cnt] =$row["$field4"];$col_cnt++;
             }
             if ($field5_name<>''){
            echo " <td class=\"rep_td\">".$row["$field5"]."</td>";
                        $csv_data[$j][$col_cnt] =$row["$field5"];$col_cnt++;
             }
            if ($field6_name<>''){
            if ($field6 = "DURATION"){
              echo " <td class=\"rep_td\">".calculate_all_time($row["$field6"],0,'','.')."</td>";
                            $csv_data[$j][$col_cnt] =calculate_all_time($row["$field6"],0,'','.');$col_cnt++;
            }else{
              echo " <td class=\"rep_td\">".$row["$field6"]."</td>";
                            $csv_data[$j][$col_cnt] =$row["$field6"];$col_cnt++;
                        }
             }
            if ($field7_name<>''){
            if ($field7 = "PRICE"){
              echo " <td class=\"rep_td\" ALIGN=right>".write_price($row["$field7"])."</td>";
                            $csv_data[$j][$col_cnt] = write_price($row["$field7"]);$col_cnt++;
            }else{
              echo " <td class=\"rep_td\">".$row["$field7"]."</td>";
                            $csv_data[$j][$col_cnt] =$row["$field7"];$col_cnt++;
                        }
             }
          echo "</tr>";
          $my_dur=$my_dur + $row["DURATION"];
          $my_amount=$my_amount + $row["AMOUNT"];
          $my_pr=$my_pr + $row["PRICE"];
          
         }
      ?>
      </table>
    </td>
  </tr>
  <tr height="20">
    <td></td>
  </tr>
   <tr>
    <td height="22" colspan="1"  align="right">
           <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Görüþme Adedi :</b></TD>
              <TD WIDTH="20%" ><?=number_format($my_amount,0,'','.')?></TD>
<?                            $j++;$csv_data[$j][0] = "Toplam Görüþme Adedi :";?>
<?                            $csv_data[$j][1] = number_format($my_amount,0,'','.');?>
            </TR>
            </TABLE>
      </td>
  </tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
           <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Süre :</b></TD>
              <TD WIDTH="20%"><?=calculate_time($my_dur,"hour")."  Saat  ".calculate_time($my_dur,"min")."  Dk";?></TD>
<?                            $j++;$csv_data[$j][0] = "Toplam Süre :";?>
<?                            $csv_data[$j][1] = calculate_time($my_dur,"hour");?>
            </TR>
            </TABLE>
  </tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
           <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Tutar :</b></TD>
              <TD WIDTH="20%"><?=write_price($my_pr)?></TD>
<?                            $j++;$csv_data[$j][0] = "Toplam Tutar :";?>
<?                            $csv_data[$j][1] = write_price($my_pr);?>
            </TR>
            </TABLE>
      </td>
  </tr>
    <tr>
        <td><?echo $alert;?></td>
    </tr>
<?}?>
</table>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_top_prn.php?act=src&type=<?=$type?>&order=' + sortby;    
    document.all('sort_me').submit();
   }
  function drill_down(n_name,n_id){
     document.all('sort_me').action='/reports/outbound/report_outb_prn.php?act=src&type=<?=$type?>&' + n_name + '=' + n_id;    
    document.all('sort_me').submit();
   }

function CheckEmail (strng) {
    var error="";
    var emailFilter=/^.+@.+\..{2,3}$/;
    if (!(emailFilter.test(strng))) { 
       alert("Lütfen geçerli bir e-mail adresi giriniz.\n");
       return 0;
    }
    else {
       var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/
       if (strng.match(illegalChars)) {
             alert("Girdiðiniz e-mail geçersiz karakterler içermektedir.\n");
             return 0;
       }
    }
    return 1;
}   
 function mailPage(page){
      var keyword = prompt("Lütfen bir mail adresi giriniz.", "")
      if(CheckEmail(keyword)){
          var pagename = "/reports/htmlmail.php?page=/temp/"+page+  "&email="+ keyword;
//          this.location.reload(true);
          this.location.href = pagename;

      }    
   }   
        
</script>  
<?
 
 $fd = fopen($DOCUMENT_ROOT."/temp/top.html", w);
 fwrite($fd,ob_get_contents());
 
 ob_end_flush();

csv_out($csv_data, "../../temp/top_reports.csv"); 
if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=top_reports.csv" WIDTH=0 HEIGHT=0 ></iframe>
 <a HREF="/temp/top_reports.csv">CSV Download</a>
 <?}?>


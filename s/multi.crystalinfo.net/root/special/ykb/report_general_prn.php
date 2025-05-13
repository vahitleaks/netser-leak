<? require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer(); 
   $conn = $cdb->getConnection();
   $show_chart=false;
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
     $usr_crt = get_users_crt($SESSION["user_id"]);
     $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini içerir.";
    }else{
      print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 

    //Cache lenecek sayfalar için kullanýlan yapýdýr.
	$cache_status = call_cache("reports/cache/ozet");
    ob_start();

    cc_page_meta();
      echo "<center>";

    //Joinden kaçmak için Lokasyon tablosundaki bilgiler alýnýyor.
  $sql_str="SELECT Locationid, LocationName FROM TLocation"; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
    $arr_location = array();     
    while ($row=mysql_fetch_object($result)){
        $arr_location[$row->Locationid] = $row->LocationName;
    }
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
    //Joinden kaçmak için Auth_Code tablosundaki bilgiler alýnýyor.
    $sql_str="SELECT AUTH_CODE,AUTH_CODE_DESC FROM AUTH_CODES"; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
    $arr_auth_code = array();
    while ($row = mysql_fetch_object($result)){
        $arr_auth_code["$row->AUTH_CODE"] = $row->AUTH_CODE_DESC;
    }

    function get_def($fld1,$fld2){
        global $cdb,$arr_location_type,$arr_location,$arr_auth_code;
        switch ($fld1){
            case 'LocationTypeid':
                $ret_val = $arr_location_type[$fld2];
                break;
            case 'Locationid':
                $ret_val = $arr_location[$fld2];
                break;
            case 'AUTH_ID':
                $ret_val = $arr_auth_code["$fld2"];
                break;
            default: 
        }
        return $ret_val;
    }

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
  
  $local_country_code = get_country_code($SITE_ID);
  if($CSV_EXPORT != 2){
 ?>
   <form name="sort_me" method="post" action="">
         <input type="hidden" name="SITE_ID" value="<?=$SITE_ID?>">
         <input type="hidden" name="MY_DATE" value="<?=$MY_DATE?>">
         <input type="hidden" name="ACCESS_CODE" value="<?=$ACCESS_CODE?>">
         <input type="hidden" name="t0" value="<?=$t0?>">         
         <input type="hidden" name="t1" value="<?=$t1?>">         
         <input type="hidden" name="last" value="<?=$last?>">         
         <input type="hidden" name="hh0" value="<?=$hh0?>">
         <input type="hidden" name="hm0" value="<?=$hm0?>">
         <input type="hidden" name="hh1" value="<?=$hh1?>">
         <input type="hidden" name="hm1" value="<?=$hm1?>">
         <input type="hidden" name="hafta" value="<?=$hafta?>">
         <input type="hidden" name="record" value="<?=$record?>">
         <input type="hidden" name="type" value="<?=$type?>">  
         <input type="hidden" name="forceMainTable" VALUE="<?=$forceMainTable?>" >         
         <input type="hidden" class="cache1" name="withCache" value="<?=$withCache?>" >
         <input type="hidden" name="sort_type" value="<?=($sort_type=="desc")?"asc":"desc"?>">  
  </form>
<?}?>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_general_prn.php?act=src&type=<?=$type?>&order=' + sortby;    
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
          this.location.href = pagename;
      }    
   }
</script>
  
<?   
   $report_type="GENEL RAPOR";
   
   if ($act == "src") {
      $kriter = "";   

        //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
    $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
    $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ACCESS_CODE"     ,  "=",  "$ACCESS_CODE");
     
    add_time_crt();//Zaman kriteri
	$link  ="";

     if($forceMainTable)
       $CDR_MAIN_DATA = "CDR_MAIN_DATA";
     else
       $CDR_MAIN_DATA = getTableName($t0,$t1);
      
     if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  

   switch ($type){
     case 'general':
      $sql_id="1";$grp_type="LocationTypeid";
      $field1="LocationTypeid";$field1_name="Tip Kodu";$width1="10%";$field1_ord="LocationTypeid";
      $field2="LocationTypeid";$field2_name="Çaðrý Tipi";$width2="25%";$field2_ord="LocationTypeid";
      $field3="AMOUNT";$field3_name="Adet";$width3="20%";$field3_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="20%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="20%";$field7_ord="PRICE";
      $header="Çaðrýlarýn Arama Türüne Göre Daðýlýmlarý";
      break;
     case 'gsm':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"            ,"=",    $local_country_code);
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"         ,"=",    "2");
      $sql_id="4";$grp_type="Locationid";
      $field1="LocalCode";$field1_name="Kodu";$width1="8%";$field1_ord="LocalCode";
      $field2="Locationid";$field2_name="Þebeke Adý";$width2="20%";$field2_ord="LocalCode";
      $field3="AMOUNT";$field3_name="Adet";$width3="8%";$field3_ord="AMOUNT";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE ";
      $header="GSM Operatör Çaðrýlarý";
      break;   
     case 'nat':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"            ,"=",    $local_country_code);
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"         ,"=",    "1");
      $sql_id="5";$grp_type="Locationid";
      $field1="LocalCode";$field1_name="Ýl Kodu";$width1="8%";$field1_ord="LocalCode";
      $field2="Locationid";$field2_name="Ýl Adý";$width2="20%";$field2_ord="LocalCode";
      $field3="AMOUNT";$field3_name="Adet";$width3="8%";$field3_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE ";
      $header="Þehirlerarasý Çaðrýlarýn Ýllere Daðýlýmý";
      break;   
     case 'int':
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"      ,"=",    "3");
      $kriter.= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"         ,"<>",    $local_country_code);
      $sql_id="6";$grp_type="CountryCode";
      $field1="CountryCode";$field1_name="Kodu";$width1="8%";$field1_ord="CountryCode";
      $field2="Locationid";$field2_name="Ülke Adý";$width2="20%";$field2_ord="CountryCode";
      $field3="AMOUNT";$field3_name="Adet";$width3="8%";$field3_ord="AMOUNT DESC";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE ";
      $header="Uluslararasý Çaðrýlarýn Ülkelere Daðýlýmý";
      break;
     case 'hour':
      $sql_id="7";$grp_type="TIME_STAMP_HOUR";
      $ext_crt="";//get_def için extra kriter
      $field1="TIME_INTERVAL";$field1_name="Saat Dilimi";$width1="10%";$field1_ord="TIME_STAMP_HOUR";
      $field3="AMOUNT";$field3_name="Adet";$width3="8%";$field3_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="20%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="20%";$field7_ord="PRICE ";
      $header="Çaðrýlarýn Günün Saatlerine Göre Daðýlýmý";
      break;   
    case 'day':
      $sql_id="8";$grp_type="TIME_STAMP_DAY";
            $ext_crt="";//get_def için extra kriter
      $field1="TIME_STAMP_DAY";$field1_name="Günler";$width1="8%";$field1_ord="TIME_STAMP_DAY";
            $field3="AMOUNT";$field3_name="Adet";$width3="10%";$field3_ord="AMOUNT";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE ";
      $header="Çaðrýlarýn Günlere Göre Daðýlýmý";
      break;   
    case 'month':
      $sql_id="9";$grp_type="TIME_STAMP_MONTH";
            $ext_crt="";//get_def için extra kriter
      $field1="TIME_STAMP_MONTH";$field1_name="Aylar";$width1="8%";$field1_ord="TIME_STAMP_MONTH";
            $field3="AMOUNT";$field3_name="Adet";$width3="10%";$field3_ord="AMOUNT";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE ";
      $header="Çaðrýlarýn Aylara Göre Daðýlýmý";
      break;   
     case 'auth':
      $sql_id="10";$grp_type="AUTH_ID";
            $ext_crt="";//get_def için extra kriter
      $field1="AUTH_ID";$field1_name="Auth. Kodu";$width1="12%";$field1_ord="AUTH_ID";
            $field2="AUTH_ID";$field2_name="Açýklama";$width2="22%";$field2_ord="AUTH_ID";
      $field3="AMOUNT";$field3_name="Adet";$width3="8%";$field3_ord="AMOUNT ";
            $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE ";
      $header="Çaðrýlarýn Auth. Kodlarýna Göre Daðýlýmý";
      break;
    default:
      echo "Hatalý Durum Oluþtu";
      exit;
   }

  switch ($sql_id){
    case 1:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, CDR_MAIN_DATA.LocationTypeid,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION, SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  ";
      break;
    case 4:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.LocalCode,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,CDR_MAIN_DATA.Locationid,
                     COUNT(CDR_MAIN_DATA.LocalCode) AS CITY_AMOUNT,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   ";
      break;
    case 5:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.LocalCode,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,CDR_MAIN_DATA.Locationid,
                     COUNT(CDR_MAIN_DATA.LocalCode) AS CITY_AMOUNT,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                    ";
      break;
    case 6:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.CountryCode,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,CDR_MAIN_DATA.Locationid,
                     COUNT(CDR_MAIN_DATA.CountryCode) AS COUNTRY_AMOUNT,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   ";
      break;
    case 7:
      $sql_str  ="SELECT COUNT(CDR_ID) AS AMOUNT,SUM(DURATION) AS DURATION,
                    CONCAT(CONCAT(DATE_FORMAT(TIME_STAMP,'%H'),'-'),DATE_FORMAT(DATE_ADD(TIME_STAMP, INTERVAL 1 HOUR),'%H')) AS TIME_INTERVAL,
                    SUM(PRICE) AS PRICE
                  FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  ";
      break;
    case 8:
      $sql_str  ="SELECT COUNT(CDR_ID) AS AMOUNT,SUM(DURATION) AS DURATION,
                    TIME_STAMP_DAY,  SUM(PRICE) AS PRICE
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                 ";
      break;
    case 9:
      $sql_str  ="SELECT COUNT(CDR_ID) AS AMOUNT,SUM(DURATION) AS DURATION,
                    TIME_STAMP_MONTH,  SUM(PRICE) AS PRICE
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                 ";
      break;
    case 10:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     CDR_MAIN_DATA.AUTH_ID,SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   ";
      break;
    default:
  } 
  if ($kriter != "")
    $sql_str .= " WHERE ".$kriter." ".$usr_crt;  
       
  $sql_str .= " GROUP BY ". $grp_type ;
//echo $sql_str;exit;
    switch ($order){
    case '1':
      $sql_str .= " ORDER BY ".$field1_ord." ".$sort_type; 
      break;
    case '2':
      $sql_str .= " ORDER BY ".$field2_ord." ".$sort_type; 
      break;
    case '3':
      $sql_str .= " ORDER BY ".$field3_ord." ".$sort_type; 
      break;
    case '4':
      $sql_str .= " ORDER BY ".$field4_ord." ".$sort_type; 
      break;
    case '5':
      $sql_str .= " ORDER BY ".$field5_ord." ".$sort_type; 
      break;
    case '6':
      $sql_str .= " ORDER BY ".$field6_ord." ".$sort_type; 
      break;
    case '7':
      $sql_str .= " ORDER BY ".$field7_ord." ".$sort_type; 
      break;
    default:
         }

     if ($record<>'' ||is_numeric($record)) {
               $sql_str .= " LIMIT 0,". $record ;
         }
//echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
?>

<br><br>
<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center">
<?  if($CSV_EXPORT != 2){?>
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>logo2.gif"></a></TD>
              <TD width="50%" align=center CLASS="header"><?=$company?><BR><?=$report_type?><br><?=$header?></TD>
              <TD width="25%" align=right><img SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>
<?}ELSE{?><?=$company?><BR><?=$report_type?><br><?ECHO $header;}?>
      </td>
  </tr>
  <tr>
    <td width="100%" class="rep_header" align="left">
        <table  width="100%" cellspacing=0 cellpadding=0>
          <tr>
          <td width="50%" class="rep_header" align="left">
        <table width="50%" cellspacing="0" cellpadding="0" border="0">
          <tr <?if($record=="") echo "style=\"display:none;\""?>>
            <td class="rep_header" align="left" nowrap width="40%">Kayýt Adedi :</td>
            <td width="60%" align="right"><?echo $record;?></td>            
          </tr>
          <tr <?if($hafta=="3") echo "style=\"display:none;\""?>>
            <td class="rep_header" align="left" nowrap width="40%" colspan="2"><?=($hafta=="1")?"Hafta Ýçi":"Hafta Sonu"?></td>
          </tr>
          <tr <?if($hh0==-1 && $hh1 ==-1) echo "style=\"display:none;\""?>>
            <td class="rep_header" align="left" nowrap width="40%">Saat Dilimi :</td>
            <td width="60%" align="right"><?=($hh0==-1)?"":$hh0.":".$hm0?> - <?=($hh1==-1)?"":$hh1.":".$hm1?></td>            
          </tr>
            </table>    
          </td><td></td>
          </tr>
          <tr>
          <td width="50%" class="rep_header" align="left">
   <?if($t0!=""){?>
        Tarih (<?=date("d/m/Y",strtotime($t0))?>
       <?if($t1!=""){?>
         <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
    )   <?}?></td>
          <td width="50%" align="right">
<?            if($CSV_EXPORT<>2){?>
                  <table cellspacing=0 cellpadding=0>
                    <tr>
                      <td><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
                       <td><a href="javascript:mailPage('general.html')"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
                       <td><a href="javascript:history.back(1);"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
                      <td><a href="javascript:history.forward(1);"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
                      <td><a href="javascript:document.all('sort_me').submit();"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
                      <td><a href="javascript:window.print();"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
                      <td><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
                    </tr>
                  </table>
<?}?>   
          </td>
        </tr>
        </table>
        </td>
  </tr>
   
<?                          $col_cnt = 1;
                            $csv_data[0][1] = $company;
                            $csv_data[0][2] = "" ;
                            $csv_data[0][3] = "Genel";
                            $csv_data[0][4] = "Rapor";?>
  <tr>
    <td colspan="2">
            <?
            if($sort_type=="desc")
                $sort_gif = "report/down.gif";    
            else
                $sort_gif = "report/top.gif";
            ?>
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
          <tr>
              <td class="rep_table_header" width="<?=$width1;?>"><?echo $field1_name;?></td>
                    <?$csv_data[1][0] = $field1_name;?>
          <?if ($field2_name<>''){?>
            <td class="rep_table_header" width="<?=$width2;?>"><?echo $field2_name;?></td>
                    <?$csv_data[1][$col_cnt] = $field2_name;?>
          <?}?>
          <?if ($field3_name<>''){?>
            <td class="rep_table_header" width="<?=$width3;?>"><?echo $field3_name;?></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field3_name;?>
          <?}?>
          <?if ($field4_name<>''){?>
            <td class="rep_table_header" width="<?=$width4;?>"><?echo $field5_name;?>
            <?if($CSV_EXPRT != 2){?><a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a><?}?></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field4_name;?>
          <?}?>
          <?if ($field5_name<>''){?>
            <td class="rep_table_header" width="<?=$width5;?>"><?echo $field5_name;?>
            <?if($CSV_EXPRT != 2){?><a style="cursor:hand;" onclick="javascript:submit_form('5');"><img src="<?=IMAGE_ROOT?><?=($order=="5")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a><?}?></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field5_name;?>
          <?}?>
          <?if ($field6_name<>''){?>
            <td class="rep_table_header" width="<?=$width6;?>"><?echo $field6_name;?>
            <?if($CSV_EXPRT != 2){?><a style="cursor:hand;" onclick="javascript:submit_form('6');"><img src="<?=IMAGE_ROOT?><?=($order=="6")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a><?}?></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field6_name;?>
          <?}?>
          <?if ($field7_name<>''){?>
            <td class="rep_table_header" width="<?=$width7;?>"><?echo $field7_name;?>
            <?if($CSV_EXPRT != 2){?><a style="cursor:hand;" onclick="javascript:submit_form('7');"><img src="<?=IMAGE_ROOT?><?=($order=="7")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a><?}?></td>
                    <?$col_cnt++; $csv_data[1][$col_cnt] = $field7_name;?>
          <?}?>
          </tr>
        <tr>
          <td colspan="5" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
           $i=0;
           $j=1;
           $myrow = "row->$field1";
           $my_dur=0;
        $my_amount=0; 
        $my_pr=0; 
         if (mysql_num_rows($result)>0)
           mysql_data_seek($result,0);
         
         $row_count = mysql_num_rows($result); //added by Yagmur
         
         while($row = mysql_fetch_array($result)){
          $col_cnt=1;
                    $i++;                   $j++;
               $bg_color = "E4E4E4";   
               if($i%2) $bg_color ="FFFFFF";
            echo " <tr  BGCOLOR=$bg_color>";
             echo " <td class=\"rep_td\">&nbsp;<b>".$row["$field1"]."</b></td>";
                    $csv_data[$j][0] = $row["$field1"];
          if ($field2_name<>''){
                        $def = get_def($field2,$row["$field2"]);
            echo " <td class=\"rep_td\">".$def."</td>";
                        $csv_data[$j][$col_cnt] =$def;$col_cnt++;
             }
          if ($field3_name<>''){
            echo " <td class=\"rep_td\">".$row["$field3"]."</td>";
                        $csv_data[$j][$col_cnt] = $row["$field3"];$col_cnt++;
             }
             if ($field4_name<>''){
            echo " <td class=\"rep_td\">".$row["$field4"]."</td>";
                        $csv_data[$j][$col_cnt] = $row["$field4"];$col_cnt++;
             }
            if ($field5_name<>''){
            echo " <td class=\"rep_td\">".$row["$field5"]."</td>";
                        $csv_data[$j][$col_cnt] = $row["$field5"];$col_cnt++;
             }
            if ($field6_name<>''){
              if ($field6 = "DURATION"){
                echo " <td class=\"rep_td\">".calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk</td>";
                $csv_data[$j][$col_cnt] = calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk";$col_cnt++;
              }else{
                echo " <td class=\"rep_td\">".$row["$field6"]."</td>";  
                $csv_data[$j][$col_cnt] = $row["$field6"];$col_cnt++;
              }
            }
            if ($field7_name<>''){
              if ($field7 = "PRICE"){
                echo " <td class=\"rep_td\" ALIGN=right>".write_price($row["$field7"])."</td>";
                $csv_data[$j][$col_cnt] = write_price($row["$field7"]);$col_cnt++;
              }else{
                echo " <td class=\"rep_td\">".$row["$field7"]."</td>";
                $csv_data[$j][$col_cnt] = $row["$field7"];$col_cnt++;
              }
             }
          echo "</tr>";
          $my_dur=$my_dur + $row["DURATION"];
          $my_amount=$my_amount + $row["AMOUNT"];
          $my_pr=$my_pr + $row["PRICE"];
          
         }
      ?>
      </table>
  <tr height="20"><td></td></tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
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
  <tr><td height="22" colspan="3" width="100%" align="right">
           <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Süre :</b></TD>
              <TD WIDTH="20%" ><?=calculate_time($my_dur,"hour")."  Saat  ".calculate_time($my_dur,"min")."  Dk";?></TD>
<?                            $j++;$csv_data[$j][0] = "Toplam Süre :";?>
<?                            $csv_data[$j][1] = calculate_time($my_dur,"hour");?>
            </TR>
            </TABLE>
      </td>
  </tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
         <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Tutar :</b></TD>
              <TD WIDTH="20%" ><?=write_price($my_pr)?></TD>
<?                            $j++;$csv_data[$j][0] = "Toplam Tutar :";?>
<?                            $csv_data[$j][1] = write_price($my_pr);?>
            </TR>
         </TABLE>
      </td>
  </tr>
    <tr>
        <td><?echo $alert;?></td>
    </tr>
</table>  
<?}?>
 <?

 make_cache("reports/cache/ozet", $cache_status, $row_count);
 if($CSV_EXPORT == 2){
   $fd = fopen($DOCUMENT_ROOT."/temp/general.xls", w);
   fwrite($fd,ob_get_contents());
 }else{
   $fd = fopen($DOCUMENT_ROOT."/temp/general.html", w);
   fwrite($fd,ob_get_contents());
 }
 
 ob_end_flush();
 csv_out($csv_data, "../../temp/general_calls.csv"); 

if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=general_calls.csv" WIDTH=0 HEIGHT=0 ></iframe>
<?}else if($CSV_EXPORT==2){?>
 <iframe SRC="/csv_download.php?filename=general.xls" WIDTH=0 HEIGHT=0 ></iframe>
 <?}?>
<br>
<br>



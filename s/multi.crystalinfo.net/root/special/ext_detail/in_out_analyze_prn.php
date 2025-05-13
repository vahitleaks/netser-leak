<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $cUtility = new Utility();
    $cdb = new db_layer(); 
    $conn = $cdb->getConnection();
    require_valid_login();
    //Kullanýcýlar için hak kontrolü olmalý 
    //echo $DEPT_ID[0];echo "/".$DEPT_ID[1];echo "/".$DEPT_ID[2]."---";ECHO COUNT($DEPT_ID)."<br>";
    $kriter2 = "";
    $check_origs = false;
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
        $dept_crt = get_depts_crt($SESSION["user_id"],$SESSION["site_id"]);
        $usr_crt  = get_users_crt($SESSION["user_id"],1,$SESSION["site_id"]);
        $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini içerir.";
    }else{
      if($AUTH_CODE_CNTL == 1){
        $usr_crt  = get_auth_crt($SESSION["user_id"]);
      }else{
        $usr_crt  = get_ext($SESSION["user_id"]);
      }
    }
  
    //Raporlanmak istenmeyen dahililer alýnýyor.
    $unrep_exts_crt = get_unrep_exts_crt($SITE_ID);
    //Hak Kontrolü Burada Bitiyor
    $max_outb_count = get_system_prm(MAX_OUTBOUND_COUNT);
	if ($max_outb_count=='0'){$max_outb_count=5000;}
    ob_start();
   
    cc_page_meta();
    echo "<center>";
    if($ORIG_DN==''){
      echo "Hatalý Durum Oluþtu";
      die;
    }
    ?>
<script>

function CheckEmail (strng) {
    var error="";
    var emailFilter=/^.+@.+\..{2,3}$/;
    if (!(emailFilter.test(strng))) { 
       alert("Lütfen geçerli bir e-mail adresi giriniz.\n");
       return 0;
    }
    else {
       var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/;
       if (strng.match(illegalChars)) {
             alert("Girdiðiniz e-mail geçersiz karakterler içermektedir.\n");
             return 0;
       }
    }
    return 1;
}

   function mailPage(){
      var keyword = prompt("Lütfen bir mail adresi giriniz.", "")
      if(CheckEmail(keyword)){
          var pagename = "../../reports/htmlmail.php?page=/temp/in_out_analyze.htm&email="+ keyword;
          this.location.href = pagename;
      }    
   }

</script>
<?if($CSV_EXPORT != 2){?>
    <form name="sort_me" method="post" action="">
           <input type="hidden" name="SITE_ID" value="<?=$SITE_ID?>">   
           <input type="hidden" name="MY_DATE" value="<?=$MY_DATE?>">
           <input type="hidden" name="TelProviderid" value="<?=$TelProviderid?>">
           <input type="hidden" name="FROM_PROVIDER_ID" value="<?=$FROM_PROVIDER_ID?>">
           <input type="hidden" name="t0" value="<?=$t0?>">        
           <input type="hidden" name="t1" value="<?=$t1?>">        
           <input type="hidden" name="last" value="<?=$last?>">        
           <input type="hidden" name="hh0" value="<?=$hh0?>">
           <input type="hidden" name="hm0" value="<?=$hm0?>">
           <input type="hidden" name="hh1" value="<?=$hh1?>">
           <input type="hidden" name="hm1" value="<?=$hm1?>">
           <input type="hidden" name="hafta" value="<?=$hafta?>">
           <input type="hidden" name="ORIG_DN" value="<?=$ORIG_DN?>">
           <input type="hidden" name="CSV_EXPORT" value="<?=$CSV_EXPORT?>">
           <input type="hidden" name="MEMBER_NO" value="<?=$MEMBER_NO?>">
           <input type="hidden" name="LocationTypeid" value="<?=$LocationTypeid?>">
           <input type="hidden" name="LocalCode" value="<?=$LocalCode?>">
           <input type="hidden" name="CountryCode" value="<?=$CountryCode?>">
           <input type="hidden" name="AUTH_CODE" value="<?=$AUTH_CODE?>">
           <input type="hidden" name="AUTH_CODE_CNTL" value="<?=$AUTH_CODE_CNTL?>">
           <input type="hidden" name="ACCESS_CODE" value="<?=$ACCESS_CODE?>">
           <input type="hidden" name="DURATION" value="<?=$DURATION?>">
           <input type="hidden" name="record" value="<?=$record?>">
           <input type="hidden" name="DIGITS" value="<?=$DIGITS?>">        
           <input type="hidden" name="type" value="<?=$type?>">        
           <input type="hidden" name="TRUNK" value="<?=$TRUNK?>">   
           <input type="hidden" name="SUMM" value="<?=$SUMM?>"> 
           <input type="hidden" name="PRICE" value="<?=$PRICE?>">   
           <input type="hidden" name="CONTACT_TYPE" value="<?=$CONTACT_TYPE?>"> 
           <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>"> 
       <div id="dept" style="display:none">
            <select name="DEPT_ID" class="select1" style="width:250;" multiple>
              <?foreach($DEPT_ID as $value){
                        echo "<OPTION value='$value'  selected ></OPTION>";
               }?>
            </select>
        </div>
    </form>
    <?}
    //Joinden kaçmak için Lokasyon tablosundaki bilgiler alýnýyor.
  $sql_str="SELECT Locationid,LocationName FROM TLocation"; 
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
    $sql_str="SELECT AUTH_CODE,AUTH_CODE_DESC FROM AUTH_CODES WHERE SITE_ID=".$SITE_ID; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
    $arr_auth_code = array();
    while ($row = mysql_fetch_object($result)){
        $arr_auth_code[$row->AUTH_CODE] = $row->AUTH_CODE_DESC;
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
    $local_country_code = get_country_code($SITE_ID);//Lokal ülke kodu.
	
   $report_type="Giden Çaðrý Raporu";

   if ($act == "src") {
     
      $kriter = "";

      //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
      $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.        
      $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.

     
      //**Bunlar birlikte olmalý ve bu sýrada olmalý.
      add_time_crt();//Zaman kriteri 
      if($forceMainTable)
        $CDR_MAIN_DATA = "CDR_MAIN_DATA"; 
      else
	    $CDR_MAIN_DATA = getTableName($t0,$t1);
      if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";
      //**
  	 
	 //Genel raporlardan dahili çaðrýlarýn detaylarý için gelindiðinde dahili kýsmý boþ olanlar için.
     if ($ORIG_DN == "-2"){
        if ($kriter == ""){
            $kriter = " CDR_MAIN_DATA.ORIG_DN = ''" ;
        }else if ($kriter <> ""){
            $kriter = $kriter. " AND CDR_MAIN_DATA.ORIG_DN = ''";
            $ORIG_DN = "";
        }
    }

     //Genel Arama Bilgileri. Bu alanlar baþlýk basýlmasýnda kullanýlacaktýr.
    if ($ORIG_DN <> '') 
        $orig = 'Yes';

    $provider_join="";//Ne olur ne olmaz bu alan burada boþaltýlsýn.
    if (($LocationTypeid <> '-1') && ($LocationTypeid<>'')) 
        $code_type='Yes';
    if ($LocalCode <> '')
        $local='Yes';
    if ($DIGITS <> '')
        $digits='Yes';
    if ($CountryCode <> '' && $CountryCode<>$local_country_code)
        $country='Yes';
    if ($ACCESS_CODE <> '')
        $access='Yes';
    if (($TelProviderid <> '-1') && ($TelProviderid<>'')){
        $tel_provider='Yes';
        $provider_join =" LEFT JOIN TTelProvider ON CDR_MAIN_DATA.TO_PROVIDER_ID = TTelProvider.TelProviderid ";
    }
    if ($DURATION <> ''){
        $dur='Yes';
        $DURATION_SN = $DURATION*60;
     }

     if($kriter == ""){
        $kriter .= " CDR_MAIN_DATA.ORIG_DN = '".$ORIG_DN."'";
     }else{
        $kriter .= " AND CDR_MAIN_DATA.ORIG_DN ='".$ORIG_DN."'";
     }



        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"   ,"=",        "$LocationTypeid");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.TO_PROVIDER_ID"   ,"=",        "$TelProviderid");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocalCode"        ,"=",        "'$LocalCode'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"      ,"=",        "'$CountryCode'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.PURE_NUMBER"      ,"LIKE",     "'%$DIGITS%'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.ACCESS_CODE"      ,"=",        "'$ACCESS_CODE'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.PRICE"            ,">=",       "'$PRICE'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.DURATION"         ,">",        "'$DURATION_SN'");
        if($FROM_PROVIDER_ID != -1){
          $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.FROM_PROVIDER_ID"          ,"=",       "'$FROM_PROVIDER_ID'");
        }
        
        
    $sql_loc="SELECT SITE_CODE,SITE_NAME, PRICE_FACTOR FROM SITES WHERE SITE_ID = $SITE_ID"; 
    if (!($cdb->execute_sql($sql_loc,$rslt_loc,$error_msg))){
        print_error($error_msg);
        exit;
    }
    $row_loc=mysql_fetch_object($rslt_loc);
    $LOC_CODE = $row_loc->SITE_CODE;
    $company  = $row_loc->SITE_NAME;
    $prc_fct  = $row_loc->PRICE_FACTOR;

   $write_to_csv = "";
         $sql_str  = "SELECT LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN, COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, 
                            SUM(CDR_MAIN_DATA.DURATION) AS DURATION, 
                            CONCAT(CDR_MAIN_DATA.CountryCode, '-',  CDR_MAIN_DATA.LocalCode,'-', CDR_MAIN_DATA.PURE_NUMBER) AS CALLED_NO, 
                            EXTENTIONS.DESCRIPTION, SUM(CDR_MAIN_DATA.PRICE*$prc_fct) AS PRICE 
              ";
                        $sql_str1 = " FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                            LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                            ";

         if ($kriter != ""){
               $sql_str2 .= "  WHERE ".$kriter;
               if ($dept_crt)
                    $sql_str2 .= $dept_crt;
               if ($usr_crt)
                    $sql_str2 .= $usr_crt;
               if ($unrep_exts_crt)
                    $sql_str2 .= $unrep_exts_crt;
        }else{
            echo "Lütfen Kriter Seçiniz";
            exit;
        } 
//     }
     $sql_str2 = $sql_str2." GROUP BY ORIG_DN, CALLED_NO ";
//echo $sql_str.$sql_str1.$sql_str2;exit;
      $sql_outbound  = $sql_str.$sql_str1.$sql_str2;
      if (!($cdb->execute_sql($sql_outbound,$result,$error_msg))){
           print_error($error_msg);
           exit;
      }
      $kriter  = ""; //Gelen çaðrýlar için kriterleri temizle
      //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
      $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.        
      $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "2"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "=",  "'$ORIG_DN'"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.

     
      //**Bunlar birlikte olmalý ve bu sýrada olmalý.
      add_time_crt();//Zaman kriteri 
      $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.DURATION"         ,">",        "'$DURATION_SN'");
      if($DIGITS <> ""){
        if($CountryCode <> $local_country_code){
          $CLID = $CountryCode;
        }elseif($CountryCode == $local_country_code && $LocalCode <> ""){
          $CLID = "0";
        }elseif($CountryCode == "" && $LocalCode <> ""){
          $CLID = "0";
        }
        if($LocalCode <> "" && $LocalCode <> $LOC_CODE){
          $CLID = $CLID.$LocalCode;
        }
        $CLID = $CLID.$DIGITS;
        $kriter .= $cdb->field_query($kriter,   "REPLACE(CDR_MAIN_DATA.CLID, 'X', '')"     ,  "LIKE",  "'%$CLID%'"); //.
      }
         $sql_inb  = "SELECT LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN, COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, 
                            SUM(CDR_MAIN_DATA.DURATION) AS DURATION, 
                            REPLACE(CDR_MAIN_DATA.CLID, 'X', '') AS CLID, 
                            EXTENTIONS.DESCRIPTION
              ";
                        $sql_inb1 = " FROM CDR_MAIN_INB AS CDR_MAIN_DATA
                            LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                            ";
         if ($kriter != ""){
               $sql_inb2 .= "  WHERE ".$kriter;
               if ($dept_crt)
                    $sql_inb2 .= $dept_crt;
               if ($usr_crt)
                    $sql_inb2 .= $usr_crt;
               if ($unrep_exts_crt)
                    $sql_inb2 .= $unrep_exts_crt;
        }else{
            echo "Lütfen Kriter Seçiniz";
            exit;
        } 
//     }
     $sql_inb2 = $sql_inb2." GROUP BY ORIG_DN, CLID ";
//echo $sql_str.$sql_str1.$sql_str2;exit;

      $kriter  = ""; //Dahili çaðrýlar için kriterleri temizle
      //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
      $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.        
      $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "0"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.TER_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "=",  "'$ORIG_DN'"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.

     
      //**Bunlar birlikte olmalý ve bu sýrada olmalý.
      add_time_crt();//Zaman kriteri 
      $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.DURATION"         ,">",        "'$DURATION_SN'");
         $sql_loc1  = "SELECT LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN, COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, 
                            SUM(CDR_MAIN_DATA.DURATION) AS DURATION, 
                            CDR_MAIN_DATA.TER_DN, 
                            EXTENTIONS.DESCRIPTION
                       FROM CDR_MAIN_INB AS CDR_MAIN_DATA
                            LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                            ";
         if ($kriter != ""){
               $sql_loc1 .= "  WHERE ".$kriter;
               if ($dept_crt)
                    $sql_loc1 .= $dept_crt;
               if ($usr_crt)
                    $sql_loc1 .= $usr_crt;
               if ($unrep_exts_crt)
                    $sql_loc1 .= $unrep_exts_crt;
        }else{
            echo "Lütfen Kriter Seçiniz";
            exit;
        } 
//     }
     $sql_localout = $sql_loc1." GROUP BY ORIG_DN, TER_DN ";


      $kriter  = ""; //Dahili çaðrýlar için kriterleri temizle
      //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
      $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.        
      $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "0"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.TER_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.TER_DN"     ,  "=",  "'$ORIG_DN'"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.

     
      //**Bunlar birlikte olmalý ve bu sýrada olmalý.
      add_time_crt();//Zaman kriteri 
      $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.DURATION"         ,">",        "'$DURATION_SN'");
         $sql_loc1  = "SELECT LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN, COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, 
                            SUM(CDR_MAIN_DATA.DURATION) AS DURATION, 
                            CDR_MAIN_DATA.TER_DN, 
                            EXTENTIONS.DESCRIPTION
                       FROM CDR_MAIN_INB AS CDR_MAIN_DATA
                            LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.TER_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                            ";
         if ($kriter != ""){
               $sql_loc1 .= "  WHERE ".$kriter;
               if ($dept_crt)
                    $sql_loc1 .= $dept_crt;
               if ($usr_crt)
                    $sql_loc1 .= $usr_crt;
               if ($unrep_exts_crt)
                    $sql_loc1 .= $unrep_exts_crt;
        }else{
            echo "Lütfen Kriter Seçiniz";
            exit;
        } 
//     }
     $sql_localin = $sql_loc1." GROUP BY TER_DN, ORIG_DN ";

?>
<div style="width: 77; height: 28; position: absolute; left: 709; top: 3; display: none">
  <input type="button" value="Anasayfa" onclick="javascript:history.go(-1);">
</div>

<table width="95%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="100%" align="center" class="rep_header" align="center">
           <?if($CSV_EXPORT != 2){?>
            <TABLE BORDER="0" WIDTH="100%">
            <TR>
                <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>logo2.gif" ></a></TD>
                <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>GÝDEN-GELEN ÇAÐRI ANALÝZÝ</TD>
                <TD width="25%" align=right><img SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>
          <?}?>  
        </td>
    </tr>


    <tr>
        <td width="100%" class="rep_header" align="right">
        <table width="100%" border="0">
            <tr>
   <?if($t0!=""){?>
                <td width="50%" class="rep_header" align="left">Tarih (<?=date("d/m/Y",strtotime($t0))?>
                    <?if($t1!=""){?>
                    <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
                    )
                </td>
   <?}?>
                <td width="50%" class="rep_header" align="right" cellspacing=0 cellpadding=0>
                </td>
            </tr>
        </table>
        </td>
    </tr>
    <tr>
        <td>
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td width="50%" valign="top">
                        <table>
                            <tr  <?if ($orig<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" nowrap width="20%" valign="top">Dahili</td>
                                <td width="80%" valign="top">
                                <?=$ORIG_DN."-".get_ext_name2($ORIG_DN,$SITE_ID)?></td>
                            </tr>                        
                            <tr <?if($FROM_PROVIDER_ID=="" || $FROM_PROVIDER_ID=="-1") echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Çýkýþ Þebekesi :</td>
                                <td width="60%"><?echo get_tel_provider($FROM_PROVIDER_ID);?></td>               
                            </tr>
                            <tr <?if ($access<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Çýkýþ Kodu:</td>
                                <td width="60%"><?echo $ACCESS_CODE;?></td>
                            </tr>
                        </table>    
                    </td>
                    <td width="50%">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                        <tr><td width="50%" valign="top">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr  <?if ($code_type<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Arama Tipi:</td>
                                <td width="60%"><?echo $arr_location_type[$LocationTypeid];?></td>
                            </tr>
                            <tr  <?if ($local<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Opt. Kodu:</td>
                                <td width="60%"><?echo $LocalCode;?></td>
                            </tr>
                            <tr  <?if ($country<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Ülke Adý:</td>
                                <td width="60%"><?echo $CountryCode;?></td>
                            </tr>
                            <tr  <?if ($digits<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Tel No:</td>
                                <td width="60%"><?echo $DIGITS;?></td>
                            </tr>
                            <tr <?if ($dur<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Min. Süre:</td>
                                <td width="60%"><?echo $DURATION;?> dk</td>
                            </tr>
                            <tr <?if ($tel_provider<>'Yes') echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Aranan Þebeke:</td>
                                <td width="60%"><?echo get_tel_provider($TelProviderid);?></td>
                            </tr>
                            <tr <?if ($PRICE==1) echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Min. Ücret:</td>
                                <td width="60%"><?echo $PRICE;?></td>
                            </tr>
                        </table></td>
                        <td width="50%" valign="top">
                        <table width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr <?if($hafta=="3") echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%" colspan="2"><?=($hafta=="1")?"Hafta Ýçi":"Hafta Sonu"?></td>
                            </tr>
                            <tr <?if($hh0==-1 && $hh1 ==-1) echo "style=\"display:none;\""?>>
                                <td class="rep_header" align="left" nowrap width="40%">Saat Dilimi :</td>
                                <td width="60%" align="right"><?=($hh0==-1)?"":$hh0.":".$hm0?> - <?=($hh1==-1)?"":$hh1.":".$hm1?></td>              
                            </tr>
                        </table>    
                        </td></tr>
                        <tr><td colspan=2 align=right>
                        <?if($CSV_EXPORT<>2){?>
                          <table cellspacing=0 cellpadding=0>
                            <tr>
                              <td><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
                              <td><a href="javascript:mailPage()"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
                              <td><a href="javascript:history.back(1);"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
                              <td><a href="javascript:history.forward(1);"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
                              <td><a href="javascript:document.all('sort_me').submit();"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
                              <td><a href="javascript:window.print();"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
                              <td><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
                            </tr>
                          </table>
                          <?}?>
                        </td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>   
    </tr>
      </td>
<!--    </tr>
  </table>
<TABLE WIDTH="95%">
-->
    <tr>
        <td>
            <table width="100%" border="0" bgcolor="C0C0C0" cellspacing="1" cellpadding="0" >
                <tr>
                <td colspan=4><b>Giden Çaðrýlar</b></td>
                </tr>
                <tr>
                    <td class="rep_table_header" width="30%" ALIGN="center">Dahili</td>
                    <td class="rep_table_header" width="20%" ALIGN="center">Aranan No</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Adet</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Süre</td>
                </tr>
                <tr>
                    <td colspan="10" bgcolor="#000000" height="1"></td>
                </tr>
            <?
    
      ///////////////////////////////////////////////////////////////77
            $my_dur=0;
            $my_amount=0; 
            $my_pr=0; 
            $i;
            if (mysql_num_rows($result)>0)
                mysql_data_seek($result,0);
                $cnt=0;
                $numeric=0;
                function cmp ($a, $b) {
                    global $order1,$numeric;
                    if($numeric){
                        if ($a[$order1] < $b[$order1]) 
                            return  1;
                        else 
                            return -1;
                       }    
                       return strcmp($a[$order1], $b[$order1]);
                }

                function cmp_desc ($a, $b) {
                    global $order1,$numeric;
                    if($numeric){
                        if ($a[$order1] > $b[$order1]) 
                            return  1;
                        else 
                            return -1;
                       }    
                       return strcmp($b[$order1], $a[$order1]);
                }

                $file_name = "outboudrep.csv";
                if($CSV_EXPORT==1)
                    $fp = fopen(dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", "w+");
                if($CSV_EXPORT==1)
                    fwrite($fp, " ");
                    $m=0;
                    $csv_data[0] = $company;
                    $csv_data[1] = "Giden";
                    $csv_data[2] = "Çagrý";
                    $csv_data[3] = "Raporu";
                    $m++;
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                    $csv_data[0] = "Dahili";
                    $csv_data[1] = "Aranan No";
                    $csv_data[2] = "Adet" ;
                    $csv_data[3] = "Süre";
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);


                while($row_array[$cnt] = mysql_fetch_array($result)){$cnt++;}
                
                    foreach($row_array as $row){
                        $m++;

                        $csv_data[0] = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18); // Dahili ve Adý

                        $csv_data[2] = $row["AMOUNT"] ;
                        $csv_data[3] = calculate_all_time($row["DURATION"]);
                    if(is_array($row)) {
                        $i++;
                        $bg_color = "E4E4E4";   
                        if($i%2) $bg_color ="FFFFFF";
                        
                        $TEL_ARRAY = split("-", $row["CALLED_NO"]);
                        
                        
                        if ($TEL_ARRAY[0] == $local_country_code){
                            if ($TEL_ARRAY[1] == $LOC_CODE || $TEL_ARRAY[1]==""){//Þehiriçi Kabul edilen kod.
                              $TEL_NUMBER = $LOC_CODE ." ".substr($TEL_ARRAY[2],0,7);
                            }else{
                              $TEL_NUMBER = $TEL_ARRAY[1]." ".substr($TEL_ARRAY[2],0,7);
                            }
                        }else{
                            $TEL_NUMBER = $TEL_ARRAY[0]." ".$TEL_ARRAY[1]." ".$TEL_ARRAY[2];
                        } 


                        $csv_data[1] = $TEL_NUMBER;
                        if($CSV_EXPORT==1)
                          csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);

                        if($CSV_EXPORT<>1){
                            $name = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18);
                            echo " <tr  BGCOLOR=$bg_color>";
                            echo " <td class=\"rep_td\">".$name."</td>";
                            echo " <td class=\"rep_td\" align=\"right\">".$TEL_NUMBER."</td>";
                            echo " <td class=\"rep_td\" align=\"center\">" .$row["AMOUNT"]."</td>";                 
                            echo " <td class=\"rep_td\" align=\"right\">".calculate_all_time($row["DURATION"])."</td>";     

                            echo "</tr>";
                        }
                        $my_amount=$my_amount + $row["AMOUNT"];
                        $my_dur=$my_dur + $row["DURATION"];
                        $my_pr=$my_pr + $row["PRICE"];
                    }
                }
                if($CSV_EXPORT<>1){
                    echo " <tr>";
                    echo " <td  class=\"rep_td\" colspan=2><b>Toplam</b></td>";
                    echo " <td  class=\"rep_td\" align=\"center\"><b>" .$my_amount."</b></td>";                 
                    echo " <td  class=\"rep_td\" align=\"right\"><b>".calculate_all_time($my_dur)."</b></td>";     

                    echo "</tr>";
                }
                $m++;  
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = "Toplam Görüþme Adedi";
                $csv_data[3] = "Toplam Süre";
                $m++;

                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = $my_amount;
                $csv_data[3] = $my_dur;
                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
            ?>
                <tr>
                <td colspan=4 bgcolor=#FFFFFF height="22"></td>
                </tr>
                <tr>
                <td colspan=4><b>Giden Dahili Çaðrýlar</b></td>
                </tr>
                <tr>
                    <td class="rep_table_header" width="30%" ALIGN="center">Dahili</td>
                    <td class="rep_table_header" width="20%" ALIGN="center">Aranan Dahili</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Adet</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Süre</td>
                </tr>

<?
      if (!($cdb->execute_sql($sql_localout,$resultout,$error_msg))){
           print_error($error_msg);
           exit;
      }
      ///////////////////////////////////////////////////////////////77
      unset($row_array);
            $my_dur=0;
            $my_amount=0; 
            $my_pr=0; 
            $i;
            if (mysql_num_rows($resultout)>0)
                mysql_data_seek($resultout,0);
                $cnt=0;
                $numeric=0;
                    $m=0;
                    $csv_data[0] = $company;
                    $csv_data[1] = "Giden Dahili";
                    $csv_data[2] = "Çagrý";
                    $csv_data[3] = "Raporu";
                    $m++;
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                    $csv_data[0] = "Dahili";
                    $csv_data[1] = "Aranan Dahili";
                    $csv_data[2] = "Adet" ;
                    $csv_data[3] = "Süre";
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);


                while($row_array[$cnt] = mysql_fetch_array($resultout)){$cnt++;}
                
                    foreach($row_array as $row){
                        $m++;

                        $csv_data[0] = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18); // Dahili ve Adý

                        $csv_data[2] = $row["AMOUNT"] ;
                        $csv_data[3] = calculate_all_time($row["DURATION"]);
                    if(is_array($row)) {
                        $i++;
                        $bg_color = "E4E4E4";   
                        if($i%2) $bg_color ="FFFFFF";
                        $csv_data[1] = $row["TER_DN"];
                        if($CSV_EXPORT==1)
                          csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);

                        if($CSV_EXPORT<>1){
                            $name = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18);
                            echo " <tr  BGCOLOR=$bg_color>";
                            echo " <td class=\"rep_td\">".$name."</td>";
                            echo " <td class=\"rep_td\" align=\"right\">".$row["TER_DN"]."</td>";
                            echo " <td class=\"rep_td\" align=\"center\">" .$row["AMOUNT"]."</td>";                 
                            echo " <td class=\"rep_td\" align=\"right\">".calculate_all_time($row["DURATION"])."</td>";     

                            echo "</tr>";
                        }
                        $my_amount=$my_amount + $row["AMOUNT"];
                        $my_dur=$my_dur + $row["DURATION"];
                    }
                }
                if($CSV_EXPORT<>1){
                    echo " <tr>";
                    echo " <td  class=\"rep_td\" colspan=2><b>Toplam</b></td>";
                    echo " <td  class=\"rep_td\" align=\"center\"><b>" .$my_amount."</b></td>";                 
                    echo " <td  class=\"rep_td\" align=\"right\"><b>".calculate_all_time($my_dur)."</b></td>";     

                    echo "</tr>";
                }
                $m++;  
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = "Toplam Görüþme Adedi";
                $csv_data[3] = "Toplam Süre";
                $m++;

                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = $my_amount;
                $csv_data[3] = $my_dur;
                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
?>
                <tr>
                <td colspan=4 bgcolor=#FFFFFF height="22"></td>
                </tr>
                <tr>
                <td colspan=4><b>Gelen Çaðrýlar</b></td>
                </tr>
                <tr>
                    <td class="rep_table_header" width="30%" ALIGN="center">Dahili</td>
                    <td class="rep_table_header" width="20%" ALIGN="center">Arayan No</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Adet</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Süre</td>
                </tr>


            <?
         if (!($cdb->execute_sql($sql_inb.$sql_inb1.$sql_inb2, $result,$error_msg))){
           print_error($error_msg);
           exit;
      }
      unset($row_array);
      ///////////////////////////////////////////////////////////////77
            $m++;
            $my_dur=0;
            $my_amount=0; 
            $my_pr=0; 
            if (mysql_num_rows($result)>0)
                mysql_data_seek($result,0);
                $cnt=0;
                $numeric=0;

                if($CSV_EXPORT==1)
                    fwrite($fp, " ");
                    $csv_data[0] = $company;
                    $csv_data[1] = "Gelen";
                    $csv_data[2] = "Çagrý";
                    $csv_data[3] = "Raporu";
                    $m++;
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                    $csv_data[0] = "Dahili";
                    $csv_data[1] = "Aranan No";
                    $csv_data[2] = "Adet" ;
                    $csv_data[3] = "Süre";
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);


                while($row_array[$cnt] = mysql_fetch_array($result)){$cnt++;}
                
                    if($sort_type=="desc"){
                      usort($row_array, "cmp_desc");
                    }else{
                      usort($row_array, "cmp");
                    }
                    foreach($row_array as $row){
                        $m++;

                        $csv_data[0] = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18); // Dahili ve Adý
                        $csv_data[1] = $row["CLID"] ;
                        $csv_data[2] = $row["AMOUNT"] ;
                        $csv_data[3] = calculate_all_time($row["DURATION"]);
                    if(is_array($row)) {
                        $i++;
                        $bg_color = "E4E4E4";   
                        if($i%2) $bg_color ="FFFFFF";
                        if($CSV_EXPORT==1)
                          csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);



                        if($CSV_EXPORT<>1){
                            $name = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18);
                            echo " <tr  BGCOLOR=$bg_color>";
                            echo " <td class=\"rep_td\">".$name."</td>";
                            echo " <td class=\"rep_td\" align=\"right\">".$row["CLID"]."</td>";
                            echo " <td class=\"rep_td\" align=\"center\">" .$row["AMOUNT"]."</td>";                 
                            echo " <td class=\"rep_td\" align=\"right\">".calculate_all_time($row["DURATION"])."</td>";     

                            echo "</tr>";
                        }
                        $my_amount=$my_amount + $row["AMOUNT"];
                        $my_dur=$my_dur + $row["DURATION"];
                        $my_pr=$my_pr + $row["PRICE"];
                    }
                }
                if($CSV_EXPORT<>1){
                    echo " <tr>";
                    echo " <td  class=\"rep_td\" colspan=2><b>Toplam</b></td>";
                    echo " <td  class=\"rep_td\" align=\"center\"><b>" .$my_amount."</b></td>";                 
                    echo " <td  class=\"rep_td\" align=\"right\"><b>".calculate_all_time($my_dur)."</b></td>";     

                    echo "</tr>";
                }
                $m++;  
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = "Toplam Görüþme Adedi";
                $csv_data[3] = "Toplam Süre";
                $m++;

                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = $my_amount;
                $csv_data[3] = $my_dur;
                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
            ?>

                <tr>
                <td colspan=4 bgcolor=#FFFFFF height="22"></td>
                </tr>
                <tr>
                <td colspan=4><b>Gelen Dahili Çaðrýlar</b></td>
                </tr>
                <tr>
                    <td class="rep_table_header" width="30%" ALIGN="center">Dahili</td>
                    <td class="rep_table_header" width="20%" ALIGN="center">Arayan Dahili</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Adet</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Süre</td>
                </tr>

<?
      if (!($cdb->execute_sql($sql_localin,$resultout,$error_msg))){
           print_error($error_msg);
           exit;
      }
      ///////////////////////////////////////////////////////////////77
      unset($row_array);
            $my_dur=0;
            $my_amount=0; 
            $my_pr=0; 
            $i;
            if (mysql_num_rows($resultout)>0)
                mysql_data_seek($resultout,0);
                $cnt=0;
                $numeric=0;
                    $m=0;
                    $csv_data[0] = $company;
                    $csv_data[1] = "Gelen Dahili";
                    $csv_data[2] = "Çagrý";
                    $csv_data[3] = "Raporu";
                    $m++;
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                    $csv_data[0] = "Dahili";
                    $csv_data[1] = "Aranan Dahili";
                    $csv_data[2] = "Adet" ;
                    $csv_data[3] = "Süre";
                if($CSV_EXPORT==1)
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);


                while($row_array[$cnt] = mysql_fetch_array($resultout)){$cnt++;}
                
                    foreach($row_array as $row){
                        $m++;

                        $csv_data[0] = $row["TER_DN"]." - ".substr($row["DESCRIPTION"],0,18); // Dahili ve Adý

                        $csv_data[2] = $row["AMOUNT"] ;
                        $csv_data[3] = calculate_all_time($row["DURATION"]);
                    if(is_array($row)) {
                        $i++;
                        $bg_color = "E4E4E4";   
                        if($i%2) $bg_color ="FFFFFF";
                        $csv_data[1] = $row["ORIG_DN"];
                        if($CSV_EXPORT==1)
                          csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);

                        if($CSV_EXPORT<>1){
                            $name = $row["TER_DN"]." - ".substr($row["DESCRIPTION"],0,18);
                            echo " <tr  BGCOLOR=$bg_color>";
                            echo " <td class=\"rep_td\">".$name."</td>";
                            echo " <td class=\"rep_td\" align=\"right\">".$row["ORIG_DN"]."</td>";
                            echo " <td class=\"rep_td\" align=\"center\">" .$row["AMOUNT"]."</td>";                 
                            echo " <td class=\"rep_td\" align=\"right\">".calculate_all_time($row["DURATION"])."</td>";     

                            echo "</tr>";
                        }
                        $my_amount=$my_amount + $row["AMOUNT"];
                        $my_dur=$my_dur + $row["DURATION"];
                    }
                }
                if($CSV_EXPORT<>1){
                    echo " <tr>";
                    echo " <td  class=\"rep_td\" colspan=2><b>Toplam</b></td>";
                    echo " <td  class=\"rep_td\" align=\"center\"><b>" .$my_amount."</b></td>";                 
                    echo " <td  class=\"rep_td\" align=\"right\"><b>".calculate_all_time($my_dur)."</b></td>";     

                    echo "</tr>";
                }
                $m++;  
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = "Toplam Görüþme Adedi";
                $csv_data[3] = "Toplam Süre";
                $m++;

                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
                $csv_data[0] = "";
                $csv_data[1] = "";
                $csv_data[2] = $my_amount;
                $csv_data[3] = $my_dur;
                if($CSV_EXPORT==1){
                    csv_write_line($csv_data, dirname($DOCUMENT_ROOT)."/root/temp/$file_name.csv", $fp);
                }
?>
                
                <tr>
                    <td colspan="10" bgcolor="#000000" height="1"></td>
                </tr>

            </table>
        </td>
    </tr>
    <tr height="20">
        <td></td>
    </tr>
     <tr>
        <td><?echo $alert;?></td>
    </tr>
<tr  <?if ($dept<>'Yes') echo "style=\"display:none;\""?>>
<td>
<?if($dept == "yes"){?>
  <table width ="100%">
   <tr>
    <td class="rep_header" nowrap valign="top">Seçili Departmanlar:</td>
    <td valign="top" colspan=2>
<? if (is_array($DEPT_ID))   
        for($i=0;$i<count($DEPT_ID);$i++){
            if($DEPT_ID[$i] != "-1" && $DEPT_ID[$i] != "")
               echo get_dept_name($DEPT_ID[$i],$SITE_ID)."<br> ";
     }?>
    </td>
   </tr>
  </table> 
<?}?>
</td>                        
</tr>
</table>                
</table>    
<?}
  if($CSV_EXPORT == 2){
    $fd = fopen($DOCUMENT_ROOT."/temp/outbound.xls", w);
    fwrite($fd,ob_get_contents());
  }else{
    $fd = fopen($DOCUMENT_ROOT."/temp/in_out_analyze.htm", w);
    fwrite($fd,ob_get_contents());
  }
  ob_end_flush();
 
  if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=<?=$file_name?>.csv" WIDTH=0 HEIGHT=0 ></iframe>
 <a HREF="/temp/<?=$file_name?>.csv">CSV Download</a>
<?}else if($CSV_EXPORT==2){?>
 <iframe SRC="/csv_download.php?filename=outbound.xls" WIDTH=0 HEIGHT=0 ></iframe>
 <a HREF="/temp/outbound.xls">XLS Download</a>
 <?}?>
<script language="JavaScript">
    function submit_form(sortby){
        document.all('sort_me').action='in_out_analyze_prn.php?act=src&order=' + sortby;       
        document.all('sort_me').submit();
   }
    function drill_down_pn(pure_n){
        document.all('DIGITS').value = pure_n;
        document.all('sort_me').action='in_out_analyze_prn.php?act=src';       
        document.all('sort_me').submit();
    }   
   window.setInterval('document.all(\'sort_me\').submit()', 300000);
</script>

<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer(); 
   $conn = $cdb->getConnection();
   require_valid_login();

   if (right_get("SITE_ADMIN")){
     //Site admin hakký varsa hesþeyi orebilir.  
     //Site id gelmemiþse ksiþinin bulundgu site raporu aliýiýr.
     if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
   }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
     // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
     $SITE_ID = $SESSION['site_id'];
   }else{
     print_error("Bu sayfayý görme yetkiniz yoktur.");
   }
   //Hak Kontroluüburada bitiyor
   ob_start();
   cc_page_meta();
   echo "<center>";
  setlocale(LC_TIME, 'tr_TR');    
?>
  <form name="sort_me" method="post" action="">
    <input type="hidden" name="SITE_ID" value="<?=$SITE_ID?>">  
    <input type="hidden" name="type" value="<?=$type?>">  
    <input type="hidden" name="MY_DATE" value="<?=$MY_DATE?>">
    <input type="hidden" name="t0" value="<?=$t0?>">         
    <input type="hidden" name="t1" value="<?=$t1?>">         
    <input type="hidden" name="DEPT_ID" value="<?=$DEPT_ID?>">         
    <input type="hidden" name="mymonth" value="<?=$mymonth?>">
    <input type="hidden" name="myyear" value="<?=$myyear?>">
    <input type="hidden" name="hh1" value="<?=$hh1?>">
    <input type="hidden" name="hm1" value="<?=$hm1?>">
    <input type="hidden" name="hafta" value="<?=$hafta?>">
    <input type="hidden" name="TER_DN" value="<?=$TER_DN?>">
    <input type="hidden" name="DURATION" value="<?=$DURATION?>">
    <input type="hidden" name="record" value="<?=$record?>">
    <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
<?
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
    
    $report_type = "Gelen Çaðrý Rapor";
    if ($act == "src") {
       $kriter = "";
       $kriter .= $cdb->field_query($kriter,"SITE_ID",               "=",      "$SITE_ID"); 

       $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'dahili'"); 
       if($DEPT_ID){
         $kriter .= $cdb->field_query($kriter,"DEPT_ID",            "=",      "$DEPT_ID"); 
        }else{echo "Hatalý Durum Oluþtu";exit;}
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

      //echo $sql_str;exit;
      //echo $sql_voip;exit;
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      if(mysql_num_rows($result)==0){
        echo "Kayýt Bulunamadý";
        exit;
      }
   ?>
<?if($CSV_EXPORT != 1){?>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_inb_prn.php?act=src&order=' + sortby;    
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
//     this.location.reload(true);
       this.location.href = pagename;

    }    
  }   
     
</script>
<?}?>
<table width="1000" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" align="center" class="rep_header" align="center">
      <TABLE BORDER="0" WIDTH="100%">
        <TR>
          <TD>
<?if($CSV_EXPORT==1){?>
          <img border="0" SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>logo2.gif" >
<?}else{?>
          <a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>logo2.gif" ></a>
<?}?>
          </TD>
          <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>AYLIK DEPARTMAN ÇAÐRI PROFÝLÝ<br><BR><?=get_site_name($SITE_ID)?></TD>
          <TD width="25%" align=right><img SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>company.gif"></TD>
        </TR>
      </TABLE>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
          <td width="50%" colspan="10">
            <table>
            <tr>
              <td class="rep_header" nowrap width="20%" valign="top"><?=$header_text?>
              </td>
           </tr>
         </table>  
       </td>
     </tr>
   </table>
  </td>  
 </tr>
 <tr>
   <td align="right">
     <table width="1000" cellspacing=0 cellpadding=0>
        <tr>
          <td width="50%" class="rep_header" align="left">
            Tarih : <?=strftime("%B", mktime(0, 0, 0, $mymonth-1 ,32, $myyear))." ".$myyear?>
          </td>
          <td width="50%" class="rep_header" align="right">
<? if($CSV_EXPORT != 1){?>
          <table cellspacing=0 cellpadding=0>
            <tr>
              <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
              <td><a href="javascript:mailPage('dept_calls_prn.html')"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
              <td><a href="javascript:history.back(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
              <td><a href="javascript:history.forward(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
              <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
              <td><a href="javascript:window.print();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
              <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
            </tr>
          </table>
<?}?>
         </td></tr>
        </table>
        </td>
    </tr>    
  <tr>
    <td>
      <table border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0" width="100%">
          <tr>
              <td class="rep_table_header" rowspan=3 align=center width="200">Dahili</td>
              <td class="rep_table_header" colspan=3 rowspan=2 align=center>Giden</td>
              <td class="rep_table_header" colspan=2 rowspan=2 align=center>Gelen</td>
              <td class="rep_table_header" colspan=4 align=center>Dahili</td>
              <td class="rep_table_header" colspan=3 rowspan=2 align=center>Genel Toplam</td>
              <td class="rep_table_header" colspan=3 rowspan=2 align=center>Ortalama/Gün</td>
          </tr>
          <tr>
              <td class="rep_table_header" colspan=2>Gelen</td>
              <td class="rep_table_header" colspan=2>Giden</td>
          </tr>
          <tr>
              <td class="rep_table_header" width="35">Adet</td>
              <td class="rep_table_header" width="55">Süre</td>
              <td class="rep_table_header" width="50">Tutar</td>
              <td class="rep_table_header" width="35">Adet</td>
              <td class="rep_table_header" width="55">Süre</td>
              <td class="rep_table_header" width="35">Adet</td>
              <td class="rep_table_header" width="55">Süre</td>
              <td class="rep_table_header" width="35">Adet</td>
              <td class="rep_table_header" width="55">Süre</td>
              <td class="rep_table_header" width="35">Adet</td>
              <td class="rep_table_header" width="55">Süre</td>
              <td class="rep_table_header" width="50">Tutar</td>
              <td class="rep_table_header" width="35">Adet</td>
              <td class="rep_table_header" width="55">Süre</td>
              <td class="rep_table_header" width="50">Tutar</td>
          </tr>
<?if($CSV_EXPORT!=1){?>
          <tr>
          <td colspan="16" bgcolor="#000000" height="1"></td>
        </tr>
<?}?>
<?//        RAPOR ÇIKTISI           ?>
<?
      $i=0;
      while($row = mysql_fetch_object($result)){

         $bg_color = "E4E4E4";   
         if($i%2) $bg_color ="FFFFFF";
         $i++;
         $sql_voip = "SELECT COUNT(CDR_ID) AS AMOUNT, SUM(DURATION) AS DURATION 
                      FROM ".$CDR_MAIN_DATA." AS CDR_MAIN_DATA 
                     LEFT JOIN TRUNKS ON CDR_MAIN_DATA.TER_TRUNK_MEMBER = TRUNKS.MEMBER_NO
                     WHERE  TRUNKS.TRUNK_TYPE = 3 
         ";
         $voipkriter = " AND CALL_TYPE=1 AND CDR_MAIN_DATA.SITE_ID=".$SITE_ID." ";
         $voipkriter = $voipkriter." AND ORIG_DN ='".$row->ORIG_DN."' ";
         $voipkriter .= $cdb->field_query($voipkriter,"TIME_STAMP_MONTH",      "=",      "$mymonth");
         $voipkriter .= $cdb->field_query($voipkriter,"TIME_STAMP_YEAR",       "=",      "$myyear");
         $sql_voip .= $voipkriter;
         if (!($cdb->execute_sql($sql_voip, $rsltVoip, $error_msg))){
            print_error($error_msg);
            exit;
          }
          $row_voip = mysql_fetch_object($rsltVoip);

         $sql_inb = "SELECT COUNT(CDR_ID) AS AMOUNT, SUM(DURATION) AS DURATION 
                      FROM CDR_MAIN_INB AS CDR_MAIN_DATA 
                     WHERE  CALL_TYPE=0 AND ERR_CODE=0 AND DURATION>0 AND  SITE_ID=".$SITE_ID;
         $inbkriter = " AND TER_DN ='".$row->ORIG_DN."' ";
         $inbkriter .= $cdb->field_query($voipkriter,"TIME_STAMP_MONTH",      "=",      "$mymonth");
         $inbkriter .= $cdb->field_query($voipkriter,"TIME_STAMP_YEAR",       "=",      "$myyear");
         $sql_inb .= $inbkriter;
         if (!($cdb->execute_sql($sql_inb, $rsltinb, $error_msg))){
            print_error($error_msg);
            exit;
          }
          $row_inb = mysql_fetch_object($rsltinb);

          $OUT_TOTAL_AMOUNT = $row->LOC_AMOUNT+$row->NAT_AMOUNT+$row->GSM_AMOUNT+$row->INT_AMOUNT+$row->OTH_AMOUNT+$row_voip->AMOUNT;
          $OUT_TOTAL_DUR = $row->LOC_DUR+$row->NAT_DUR+$row->GSM_DUR+$row->INT_DUR+$row->OTH_DUR+$row_voip->DURATION;
          $OUT_TOTAL_PRICE = $row->LOC_PRICE+$row->NAT_PRICE+$row->GSM_PRICE+$row->INT_PRICE+$row->OTH_PRICE;
          $INB_TOTAL_AMOUNT = $row->INB_AMOUNT;
          $INB_TOTAL_DUR = $row->INB_DUR;
          $INTERNAL_TOTAL_AMOUNT = $row->INTERNAL_AMOUNT;
          $INTERNAL_TOTAL_DUR = $row->INTERNAL_DUR;
          $INTERNAL_INB_TOTAL_AMOUNT = $row_inb->AMOUNT;
          $INTERNAL_INB_TOTAL_DUR = $row_inb->DURATION;
          $EXT_TOTAL_AMOUNT = $OUT_TOTAL_AMOUNT+$INB_TOTAL_AMOUNT+$INTERNAL_TOTAL_AMOUNT+$INTERNAL_INB_TOTAL_AMOUNT;
          $EXT_TOTAL_DUR = $OUT_TOTAL_DUR+$INB_TOTAL_DUR+$INTERNAL_TOTAL_DUR+$INTERNAL_INB_TOTAL_DUR;
          $EXT_TOTAL_PRICE = $OUT_TOTAL_PRICE;
          $EXT_AVG_AMOUNT = round($EXT_TOTAL_AMOUNT/$job_days, 2);
          $EXT_AVG_DUR = round($EXT_TOTAL_DUR/$job_days, 2);
          $EXT_AVG_PRICE = round($EXT_TOTAL_PRICE/$job_days, 2);

          $tOUT_TOTAL_AMOUNT             = $tOUT_TOTAL_AMOUNT+$OUT_TOTAL_AMOUNT;
          $tOUT_TOTAL_DUR                = $tOUT_TOTAL_DUR+$OUT_TOTAL_DUR;
          $tOUT_TOTAL_PRICE              = $tOUT_TOTAL_PRICE+$OUT_TOTAL_PRICE;
          $tINB_TOTAL_AMOUNT             = $tINB_TOTAL_AMOUNT+$INB_TOTAL_AMOUNT;
          $tINB_TOTAL_DUR                = $tINB_TOTAL_DUR+$INB_TOTAL_DUR;
          $tINTERNAL_TOTAL_AMOUNT        = $tINTERNAL_TOTAL_AMOUNT+$INTERNAL_TOTAL_AMOUNT;
          $tINTERNAL_TOTAL_DUR           = $tINTERNAL_TOTAL_DUR+$INTERNAL_TOTAL_DUR;
          $tINTERNAL_INB_TOTAL_AMOUNT    = $tINTERNAL_INB_TOTAL_AMOUNT+$INTERNAL_INB_TOTAL_AMOUNT;
          $tINTERNAL_INB_TOTAL_DUR       = $tINTERNAL_INB_TOTAL_DUR+$INTERNAL_INB_TOTAL_DUR;
          $tEXT_TOTAL_AMOUNT             = $tEXT_TOTAL_AMOUNT+$EXT_TOTAL_AMOUNT;
          $tEXT_TOTAL_DUR                = $tEXT_TOTAL_DUR+$EXT_TOTAL_DUR;
          $tEXT_TOTAL_PRICE              = $tEXT_TOTAL_PRICE+$EXT_TOTAL_PRICE;
          ?>
        <tr  BGCOLOR="<?=$bg_color?>">

          <td class="rep_td"><?=$row->ORIG_DN?> - <?=get_ext_name2($row->ORIG_DN, $SITE_ID)?></td>
<?
          echo " <td class=\"rep_td\">".number_format($OUT_TOTAL_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($OUT_TOTAL_DUR)."</td>";
          echo " <td class=\"rep_td\">".write_price($OUT_TOTAL_PRICE)."</td>";
          echo " <td class=\"rep_td\">".number_format($INB_TOTAL_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($INB_TOTAL_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($INTERNAL_TOTAL_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($INTERNAL_TOTAL_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($INTERNAL_INB_TOTAL_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($INTERNAL_INB_TOTAL_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($EXT_TOTAL_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($EXT_TOTAL_DUR)."</td>";
          echo " <td class=\"rep_td\">".write_price($EXT_TOTAL_PRICE)."</td>";
          echo " <td class=\"rep_td\">".number_format($EXT_AVG_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($EXT_AVG_DUR)."</td>";
          echo " <td class=\"rep_td\">".write_price($EXT_AVG_PRICE)."</td>";
?>
        </tr>
        <?}
         $bg_color = "E4E4E4";   
         if($i%2) $bg_color ="FFFFFF";
?>
        <tr bgcolor="<?=$bg_color?>">
          <td class="rep_td"><b>Genel Toplam</b></td>
<?
          $tEXT_AVG_AMOUNT = round($tEXT_TOTAL_AMOUNT/$job_days, 2);
          $tEXT_AVG_DUR = round($tEXT_TOTAL_DUR/$job_days, 2);
          $tEXT_AVG_PRICE = round($tEXT_TOTAL_PRICE/$job_days, 2);
          echo " <td class=\"rep_td\"><b>".number_format($tOUT_TOTAL_AMOUNT,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".calculate_all_time($tOUT_TOTAL_DUR)."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price($tOUT_TOTAL_PRICE)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($tINB_TOTAL_AMOUNT,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".calculate_all_time($tINB_TOTAL_DUR)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($tINTERNAL_TOTAL_AMOUNT,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".calculate_all_time($tINTERNAL_TOTAL_DUR)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($tINTERNAL_INB_TOTAL_AMOUNT,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".calculate_all_time($tINTERNAL_INB_TOTAL_DUR)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($tEXT_TOTAL_AMOUNT,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".calculate_all_time($tEXT_TOTAL_DUR)."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price($tEXT_TOTAL_PRICE)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($tEXT_AVG_AMOUNT,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".calculate_all_time($tEXT_AVG_DUR)."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price($tEXT_AVG_PRICE)."</b></td>";
?>
        </tr>
      </table>
    </td>
  </tr>
<?
    $SQL_SITETOTAL = "SELECT LOC_PRICE, NAT_PRICE, GSM_PRICE, INT_PRICE, OTH_PRICE 
                       FROM MONTHLY_ANALYSE WHERE SITE_ID=1 AND TYPE='general' 
                       AND TIME_STAMP_YEAR = '".$myyear."' AND TIME_STAMP_MONTH='".$mymonth."'";
    if (!($cdb->execute_sql($SQL_SITETOTAL, $rslttotal, $error_msg))){
      print_error($error_msg);
      exit;
    }
    $row_total = mysql_fetch_object($rslttotal);
    $SITE_TOTAL = $row_total->LOC_PRICE+$row_total->NAT_PRICE+$row_total->GSM_PRICE+$row_total->INT_PRICE+$row_total->OTH_PRICE;
?>  
  <tr height="20">
    <td>
    <b>Departmanýn Site içerisindeki maliyet oraný %: <?=round(($tEXT_TOTAL_PRICE/$SITE_TOTAL)*100,2)?></b> 
    </td>
  </tr>
  <tr height="20">
    <td><BR><BR>
    <b>Not : <?=strftime("%B", mktime(0, 0, 0, $mymonth-1 ,32, $myyear))?> ayý iþ günü sayýsý <?=$job_days?> gün olarak hesaplanmýþtýr.</b> 
    <br><br><br>
    </td>
  </tr>
</table>        
<?
 if($CSV_EXPORT==1){
   $fd = fopen($DOCUMENT_ROOT."/temp/dept_calls_prn.xls", w);
 }else{
   $fd = fopen($DOCUMENT_ROOT."/temp/dept_calls_prn.html", w);
 }  
  fwrite($fd,ob_get_contents());
  ob_end_flush();
?>
<?if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=dept_calls_prn.xls" WIDTH=0 HEIGHT=0 ></iframe>
              <br><br><br>
              <p align="center"> <a HREF="/temp/dept_calls_prn.xls">Excel Download</a></p>
 <?}?>
 <?}?>

 

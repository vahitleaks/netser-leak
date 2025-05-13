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
    <input type="hidden" name="last" value="<?=$last?>">         
    <input type="hidden" name="hh0" value="<?=$hh0?>">
    <input type="hidden" name="hm0" value="<?=$hm0?>">
    <input type="hidden" name="hh1" value="<?=$hh1?>">
    <input type="hidden" name="hm1" value="<?=$hm1?>">
    <input type="hidden" name="hafta" value="<?=$hafta?>">
    <input type="hidden" name="TER_DN" value="<?=$TER_DN?>">
    <div id="dept" style="display:none">
      <select name="DEPT_ID" class="select1" style="width:250;" multiple></select>
    </div>
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
        $lastday = strftime ("%d", mktime(0,0,0,$tmon-1,0,$tyear));
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
       $sql_voip = "SELECT COUNT(CDR_ID) AS AMOUNT, SUM(DURATION) AS DURATION 
                    FROM ".$CDR_MAIN_DATA." AS CDR_MAIN_DATA 
                   LEFT JOIN TRUNKS ON CDR_MAIN_DATA.TER_TRUNK_MEMBER = TRUNKS.MEMBER_NO
                   WHERE  TRUNKS.TRUNK_TYPE = 3 
       ";
    
    $report_type = "Gelen Çaðrý Rapor";
    if ($act == "src") {
       $kriter = "";
       $voipkriter = " AND CALL_TYPE=1 AND CDR_MAIN_DATA.SITE_ID=".$SITE_ID." ";
       $kriter .= $cdb->field_query($kriter,"SITE_ID",               "=",      "$SITE_ID"); 
       switch ($type) {
        case 'site';
          $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'general'"); 
          $header_text = "SITE BAZINDA";
          break;
        case 'dept';
          $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'department'"); 
          if($DEPT_ID){
            $kriter .= $cdb->field_query($kriter,"DEPT_ID",            "=",      "$DEPT_ID"); 
            $voipkriter = $voipkriter." AND ORIG_DN IN".get_dept_extnos($DEPT_ID, $SITE_ID);}
          else{echo "Hatalý Durum Oluþtu";exit;}
          $header_text = "DEPARTMAN BAZINDA : ".get_dept_name($DEPT_ID, $SITE_ID);
          break;
        case 'ext';
          $kriter .= $cdb->field_query($kriter,"TYPE",               "=",      "'dahili'"); 
          if($ORIG_DN){
            $kriter .= $cdb->field_query($kriter,"ORIG_DN",            "=",      "'$ORIG_DN'"); 
            $voipkriter = $voipkriter." AND ORIG_DN='".$ORIG_DN."'"; }
          else{echo "Hatalý Durum Oluþtu";exit;}
          $header_text = "DAHÝLÝ BAZINDA : ".$ORIG_DN." - ".get_ext_name2($ORIG_DN, $SITE_ID);
          break;
        default:
          echo "Hatalý Durum Oluþtu";exit;
         }

       //Bu mutlaka olmali ilgili siteyi belirliyor.
       $kriter .= $cdb->field_query($kriter,"TIME_STAMP_MONTH",      "=",      "$mymonth");
       $kriter .= $cdb->field_query($kriter,"TIME_STAMP_YEAR",       "=",      "$myyear");

       //Tarih kriterleri (Voip çaðrýlarý için)
       $voipkriter .= $cdb->field_query($voipkriter,"TIME_STAMP_MONTH",      "=",      "$mymonth");
       $voipkriter .= $cdb->field_query($voipkriter,"TIME_STAMP_YEAR",       "=",      "$myyear");


       $sql_str  = "SELECT * 
                      FROM MONTHLY_ANALYSE 
                     ";
       $sql_str .= " WHERE ".$kriter;
       
       $sql_voip .= $voipkriter;

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
      $row = mysql_fetch_object($result);
   ?>
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
<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" align="center" class="rep_header" align="center">
      <TABLE BORDER="0" WIDTH="100%">
        <TR>
          <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>logo2.gif" ></a></TD>
          <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>AYLIK ÇAÐRI PROFÝLÝ<br><BR><?=get_site_name($SITE_ID)?></TD>
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
     <table width="100%" cellspacing=0 cellpadding=0>
        <tr>
          <td width="50%" class="rep_header" align="left">
            Tarih : <?=strftime("%B", mktime(0, 0, 0, $mymonth-1 ,32, $myyear))." ".$myyear?>
          </td>
          <td width="50%" class="rep_header" align="right">
          <table cellspacing=0 cellpadding=0>
            <tr>
              <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
              <td><a href="javascript:mailPage('inbound.html')"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
              <td><a href="javascript:history.back(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
              <td><a href="javascript:history.forward(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
              <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
              <td><a href="javascript:window.print();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
              <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
            </tr>
          </table>
         </td></tr>
        </table>
        </td>
    </tr>    
  <tr>
    <td>
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
          <tr>
              <td class="rep_table_header" width="15%">Giden Çaðrýlar</td>
              <td class="rep_table_header" width="35%">Süre</td>
              <td class="rep_table_header" width="25%">Adet</td>
              <td class="rep_table_header" width="25%">Ücret</td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
<?//        RAPOR ÇIKTISI           ?>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td">Þehir Ýçi</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->LOC_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->LOC_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price($row->LOC_PRICE)."</td>";?>
        </tr>
        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td">Þehirler Arasý</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->NAT_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->NAT_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price($row->NAT_PRICE)."</td>";?>
        </tr>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td">GSM</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->GSM_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->GSM_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price($row->GSM_PRICE)."</td>";?>
        </tr>
        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td">Uluslar Arasý</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->INT_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->INT_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price($row->INT_PRICE)."</td>";?>
        </tr>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td">Voip</td>
<?        if (!($cdb->execute_sql($sql_voip, $rsltVoip, $error_msg))){
            print_error($error_msg);
            exit;
          }
          $row_voip = mysql_fetch_object($rsltVoip);
          echo " <td class=\"rep_td\">".calculate_all_time($row_voip->DURATION)."</td>";
          echo " <td class=\"rep_td\">".number_format($row_voip->AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price(0)."</td>";?>
        </tr>
        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td">Diðer</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->OTH_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->OTH_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price($row->OTH_PRICE)."</td>";?>
        </tr>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td"><b>Toplam</b></td>
<?        $total_dur = $row->LOC_DUR+$row->NAT_DUR+$row->GSM_DUR+$row->INT_DUR+$row->OTH_DUR+$row_voip->DURATION;
          $total_amount = $row->LOC_AMOUNT+$row->NAT_AMOUNT+$row->GSM_AMOUNT+$row->INT_AMOUNT+$row->OTH_AMOUNT+$row_voip->AMOUNT;
          $total_price = $row->LOC_PRICE+$row->NAT_PRICE+$row->GSM_PRICE+$row->INT_PRICE+$row->OTH_PRICE;
          echo " <td class=\"rep_td\"><b>".calculate_all_time($total_dur)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($total_amount,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price($total_price)."</b></td>";?>
        </tr>

        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td"><b>Ortalama</b></td>
<?        
          $job_days = get_work_days($mymonth, $myyear);
          echo " <td class=\"rep_td\"><b>";
          echo calculate_all_time(round($total_dur/$job_days, 2))."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format(round($total_amount/$job_days, 2),0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price(round($total_price/$job_days, 2))."</b></td>";?>
        </tr>


        <tr>
          <td colspan="7" bgcolor="#FFFFFF" height="24"></td>
        </tr>

          <tr>
              <td class="rep_table_header" width="15%">Gelen Çaðrýlar</td>
              <td class="rep_table_header" width="35%">Süre</td>
              <td class="rep_table_header" width="25%">Adet</td>
              <td class="rep_table_header" width="25%">Ücret</td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td">Toplam</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->INB_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->INB_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price(0)."</td>";?>
        </tr>
        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td"><b>Ortalama</b></td>
<?        
          echo " <td class=\"rep_td\"><b>".calculate_all_time(round($row->INB_DUR/$job_days, 2))."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format(round($row->INB_AMOUNT/$job_days, 2),0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price(round(0/$job_days, 2))."</b></td>";?>
        </tr>

        <tr>
          <td colspan="7" bgcolor="#FFFFFF" height="24"></td>
        </tr>

          <tr>
              <td class="rep_table_header" width="15%">Dahili Çaðrýlar</td>
              <td class="rep_table_header" width="35%">Süre</td>
              <td class="rep_table_header" width="25%">Adet</td>
              <td class="rep_table_header" width="25%">Ücret</td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td">Toplam</td>
<?
          echo " <td class=\"rep_td\">".calculate_all_time($row->INTERNAL_DUR)."</td>";
          echo " <td class=\"rep_td\">".number_format($row->INTERNAL_AMOUNT,0,'','.')."</td>";
          echo " <td class=\"rep_td\">".write_price(0)."</td>";?>
        </tr>

        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td"><b>Ortalama</b></td>
<?        
          echo " <td class=\"rep_td\"><b>".calculate_all_time(round($row->INTERNAL_DUR/$job_days, 2))."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format(round($row->INTERNAL_AMOUNT/$job_days, 2),0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price(round(0/$job_days, 2))."</b></td>";?>
        </tr>

        <tr>
          <td colspan="7" bgcolor="#FFFFFF" height="24"></td>
        </tr>
        <tr  BGCOLOR="E4E4E4">
          <td class="rep_td"><b>Genel Toplam</b></td>
<?        $total_dur = $total_dur+$row->INB_DUR+$row->INTERNAL_DUR;
          $total_amount = $total_amount+$row->INB_AMOUNT+$row->INTERNAL_AMOUNT;
          echo " <td class=\"rep_td\"><b>".calculate_all_time($total_dur)."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format($total_amount,0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price($total_price)."</b></td>";?>
        </tr>
        <tr  BGCOLOR="FFFFFF">
          <td class="rep_td"><b>Genel Ortalama</b></td>
<?        
          echo " <td class=\"rep_td\"><b>".calculate_all_time(round($total_dur/$job_days, 2))."</b></td>";
          echo " <td class=\"rep_td\"><b>".number_format(round($total_amount/$job_days, 2),0,'','.')."</b></td>";
          echo " <td class=\"rep_td\"><b>".write_price(round($total_price/$job_days, 2))."</b></td>";?>
        </tr>
      </table>
    </td>
  </tr>
  <tr height="20">
    <td>
    </td>
  </tr>
</table>        
 <?if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=inbound_calls_sum.csv" WIDTH=0 HEIGHT=0 ></iframe>
              <br><br><br>
              <p align="center"> <a HREF="/temp/inbound_calls_sum.csv">CSV Download</a></p>
 <?}?>
 <? }?>

 

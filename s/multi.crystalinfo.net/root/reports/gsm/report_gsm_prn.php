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
      $usr_crt = get_users_crt($SESSION["user_id"],1,$SESSION["site_id"]);
      $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini içerir.";
    }else{
      print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 
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
         <input type="hidden" name="record" value="<?=$record?>">
       <input type="hidden" name="type" value="<?=$type?>">
         <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
 
 <?
    cc_page_meta();
    echo "<center>";

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
    
    $report_type="GSM Raporu";

   if ($act == "src") {
     
    $kriter = "";
      
    //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
    $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
    $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
    $kriter.= $cdb->field_query($kriter,    "CDR_MAIN_DATA.CountryCode"            ,"=", "$local_country_code");//Bulunulan ülkedeki GSM çaðrýlarý
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.LocationTypeid"  ,"=",      "2");//GSM aramalarý için kullanýlýyor.

    //Zaman kriteri ve tablo ismi seçimi. Bunlar ardý ardýna gelmeli
    add_time_crt();//Zaman kriteri 
    if($forceMainTable)
      $CDR_MAIN_DATA = "CDR_MAIN_DATA";
    else
      $CDR_MAIN_DATA = getTableName($t0,$t1);
      
    if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  
    //Zaman kriteri ve tablo ismi seçimisonu

   //Genel Arama Bilgileri. Bu alanllar baþlýk basýlmasýnda kullanýlacaktýr.
   switch ($type){
     case 'opt':
      $sql_id="1";$grp_type="TelProvider";
      $field1="TelProvider";$field1_name="Þebeke";$width1="25%";$field1_ord="TelProvider";
      $field2="AMOUNT";$field2_name="Adet";$width2="25%";$field2_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE ";
      $header = "Operatörlere Göre Çaðrýlar";
      break;
     case 'max_num':
      $sql_id="3";$grp_type="PHONE_NUMBER";$ord_type="AMOUNT";
      $field1="PHONE_NUMBER";$field1_name="Numara";$width1="25%";$field1_ord="PHONE_NUMBER";
      $field2="AMOUNT";$field2_name="Adet";$width2="15%";$field2_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE ";
      $header = "En Fazla Aranan GSM Numaralarý";
      break;
     case 'max_dep':
      $sql_id="4";$grp_type="DEPT_ID";$ord_type="AMOUNT";
      $field1="DEPT_NAME";$field1_name="Departman Adý";$width1="25%";$field1_ord="DEPT_NAME";
      $field2="AMOUNT";$field2_name="Adet";$width2="20%";$field2_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE ";
      $header = "En Fazla GSM Çaðrýsý Yapan Departmanlar";
      break;   
     case 'gsm_div':
      $sql_id="5";$grp_type="LocalCode";$ord_type="AMOUNT";
      $field1="LocalCode";$field1_name="GSM Kodu";$width1="15%";$field1_ord="LocalCode";
      $field2="TelProvider";$field2_name="Þebeke Adý";$width2="20%";$field2_ord="TelProvider";
      $field3="AMOUNT";$field3_name="Adet";$width3="15%";$field3_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="25%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE ";
      $header = "Çaðrýlarýn GSM Kodlarýna Daðýlýmý";
      break;   
    default:
      echo "Hatalý Durum Oluþtu";
      exit;
   }

     switch ($sql_id){
    case 1:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.LocationTypeid,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE, TTelProvider.TelProvider
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   LEFT JOIN TLocation ON CDR_MAIN_DATA.Locationid = TLocation.Locationid
                     LEFT JOIN TTelProvider ON TLocation.TelProviderid = TTelProvider.TelProviderid
                  ";
      break;
    case 3:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE,CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'$local_country_code',CONCAT('00',CountryCode),''),
                     IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER)AS PHONE_NUMBER
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  ";
      break;
    case 4:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.LocationTypeid,
                     DEPTS.DEPT_NAME,DEPTS.DEPT_ID, SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.ORIG_DN = EXTENTIONS.EXT_NO AND CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                     LEFT JOIN DEPTS ON DEPTS.DEPT_ID = EXTENTIONS.DEPT_ID
                  ";
      break;
    case 5:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT,CDR_MAIN_DATA.LocationTypeid,
                     SUM(CDR_MAIN_DATA.DURATION) AS DURATION,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE, TTelProvider.TelProvider, CDR_MAIN_DATA.LocalCode
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                   LEFT JOIN TLocation ON CDR_MAIN_DATA.Locationid = TLocation.Locationid
                     LEFT JOIN TTelProvider ON TLocation.TelProviderid = TTelProvider.TelProviderid
                  ";
      break;
    default:
  }   

    if ($kriter != "")
      $sql_str .= " WHERE ".$kriter." ".$usr_crt;  
       
    $sql_str .= " GROUP BY ". $grp_type ; 
     
    if ($order<>''){
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
     }else if ($ord_type) {
       $sql_str .= " ORDER BY ". $ord_type." DESC" ; 
     }
     
     if ($record<>'' ||is_numeric($record)) {
               $sql_str .= " LIMIT 0,". $record ;      
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
<table width="85%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" class="rep_header" align="center">
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=IMAGE_ROOT?>logo2.gif" ></a></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR> GSM RAPORU<br><?=$header?></TD>
              <TD width="25%" align=right><img SRC="<?=IMAGE_ROOT?>company.gif"></TD>
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
          <td><img src="<?=IMAGE_ROOT?>report/top02.gif" border=0></td>
          <td><a href="javascript:history.back(1);"><img src="<?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
          <td><a href="javascript:history.forward(1);"><img src="<?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
          <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
          <td><a href="javascript:window.print();"><img src="<?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
          <td><img src="<?=IMAGE_ROOT?>report/top01.gif" border=0></td>
        </tr>
      </table>
      </td></tr></table>
      </td>
  </tr>

<?
                            $col_cnt = 1;
                            $csv_data[0][1] = $company;
                            $csv_data[0][2] = "" ;
                            $csv_data[0][3] = "GSM";
                            $csv_data[0][4] = "Raporlarý";?>
  <tr>
    <td>
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
            <?
            if($sort_type=="asc")
                $sort_gif = "report/top.gif";    
            else
                $sort_gif = "report/down.gif";
            ?>
          <tr>
              <td class="rep_table_header" width="<?=$width1;?>"><?echo $field1_name;?><a style="cursor:hand;" onclick="javascript:submit_form('1')"><img src="<?=IMAGE_ROOT?><?=($order=="1")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
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
           $i;$j=1;
           $my_dur=0;
        $my_amount=0;
        $my_pr=0;
           while($row = mysql_fetch_array($result)){
             $i++;$j++;$call_cnt=1;
             $bg_color = "E4E4E4";   
             if($i%2) $bg_color ="FFFFFF";
               echo " <tr  BGCOLOR=$bg_color>";
               echo " <td class=\"rep_td\">&nbsp;<b>".$row["$field1"]."</b></td>";
               $csv_data[$j][0] = $row["$field1"];
             if ($field2_name<>''){
               echo " <td class=\"rep_td\">".$row["$field2"]."</td>";
               $csv_data[$j][$col_cnt] =$row["$field2"];$col_cnt++;
             }
             if ($field3_name<>''){
               echo " <td class=\"rep_td\">".$row["$field3"]."</td>";
               $csv_data[$j][$col_cnt] =$row["$field3"];$col_cnt++;
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
                 echo " <td class=\"rep_td\">".calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk</td>";
                 $csv_data[$j][$col_cnt] =calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk";$col_cnt++;
               }else{
                 echo " <td class=\"rep_td\">".$row["$field6"]."</td>";  
                 $csv_data[$j][$col_cnt] =$row["$field6"];$col_cnt++;
               }
              }
              if ($field7_name<>''){
                if ($field6 = "PRICE"){
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
</table>  
<?}?>      
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_gsm_prn.php?act=src&type=<?=$type?>&order=' + sortby;    
    document.all('sort_me').submit();
     }
</script>  
<?
csv_out($csv_data, "../../temp/gsm_reports.csv"); 
if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=gsm_reports.csv" WIDTH=0 HEIGHT=0 ></iframe>
<a HREF="/temp/gsm_reports.csv">CSV Download</a>
 <?}?>


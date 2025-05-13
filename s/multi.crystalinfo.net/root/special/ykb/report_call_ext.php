<?    
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
     $cache_status = call_cache("reports/cache/ozet");
     ob_start();      

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
     $my_time0 = $t0;
     $my_time1 = $t1;

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
  
   $local_country_code = get_country_code($SITE_ID);//Ýlgili sitenin ülke kodu.

    function write_me($MyVal,$calc_type){
     if ($calc_type == 1){
       $MyRetVal = write_price($MyVal);
     }elseif($calc_type==2){
       $MyRetVal = calculate_all_time($MyVal);
     }else{
       print_error("Hatalý Durum Oluþtu. Lütfen Tekrar Deneyiniz.");
      exit;
     }
     return $MyRetVal;
  }
  if($CSV_EXPORT != 2){
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
         <input type="hidden" name="calc_type" value="<?=$calc_type?>">       
         <input type="hidden" name="DEPT_ID" value="<?=$DEPT_ID?>">  
        <input type="hidden" class="cache1" name="withCache" value="<?=$withCache?>" >
        <input type="hidden" name="forceMainTable" VALUE="<?=$forceMainTable?>" >         
         <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
         <input type="hidden" name="ACCESS_CODE" value="<?=$ACCESS_CODE?>">
  </form>
  <?}?>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_call_ext.php?act=src&type=<?=$type?>&SITE_ID=<?=$SITE_ID?>&order=' + sortby;    
    document.all('sort_me').submit();
     }
  function drill_down(o_id){
    document.all('sort_me').action='/reports/outbound/report_outb_prn.php?act=src&type=<?=$type?>&SITE_ID=<?=$SITE_ID?>&ORIG_DN=' + o_id;    
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
   if($DEPT_ID==-2){$D_ID = $DEPT_ID;$DEPT_ID = "";}
   
   $report_type="Dahili Özet Raporu";
   if (($type=='ext') || ($type=='dept')){
       $myfld= "PRICE";
       $calc_type =1;   
   }elseif(($type=='ext_time') || ($type=='dept_time')){
       $myfld= "DURATION";
       $calc_type =2;   
   }else{
      print_error("Hatalý Durum Oluþu. Lütfen Tekrar Deneyiniz");
   exit;
   }

   if ($act == "src") {

             //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
    $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
    $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
     $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ACCESS_CODE"     ,  "=",  "$ACCESS_CODE");

    //Zaman kriterleri ve tablo ismi seçimi baþlangýç
    add_time_crt();//Zaman kriteri
	$link  ="";

     if($forceMainTable)
       $CDR_MAIN_DATA = "CDR_MAIN_DATA";
     else
       $CDR_MAIN_DATA = getTableName($t0,$t1);
      
     if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  

    //Zaman kriterleri ve tablo ismi seçimi bitiþ
	
    $header="Çaðrýlarýn Dahililere Göre Daðýlýmý";
    $sql_str  = "SELECT CDR_MAIN_DATA.LocationTypeid AS TYPE, LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN, SUM(CDR_MAIN_DATA.$myfld) AS TOTAL
                 FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                 ";

      if ($kriter != "")
            $sql_str .= " WHERE ".$kriter." ".$usr_crt;
     $sql_str .= " GROUP BY ORIG_DN, TYPE";

    switch ($order){
      case '0':
             $z='0';
             break;
          case '1':
             $z = 1; 
        break;
      case '2':
             $z = 2; 
        break;
      case '3':
             $z = 3; 
        break;
      case '4':
             $z = 4; 
        break;
      case '5':
             $z = 5; 
        break;
      case '6':
             $z = 6; 
        break;
      case '7':
             $z = 7; 
        break;
      default:
         }

     if ($record<>'' ||is_numeric($record)) {
               $sql_str .= " LIMIT 0,". $record ;
         }
//    echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
      $row_count = mysql_num_rows($result); //added by Yagmur
      while($row = mysql_fetch_object($result)){  
            $my_dept = get_orig_dept_id($row->ORIG_DN, $SITE_ID);
            if ($my_dept=="")     $my_dept = 0;
            if(($DEPT_ID && $DEPT_ID != '-1') || $D_ID){
                if($my_dept == $DEPT_ID){
                    $datas[$my_dept][$row->ORIG_DN][5]=1;
                    if($row->TYPE == 0){
                        $datas[$my_dept][$row->ORIG_DN][0] += $row->TOTAL;
                    }else if($row->TYPE == 1){
                        $datas[$my_dept][$row->ORIG_DN][1] += $row->TOTAL;
                    }else if($row->TYPE == 2){
                        $datas[$my_dept][$row->ORIG_DN][2] += $row->TOTAL;
                    }else if($row->TYPE == 3){
                        $datas[$my_dept][$row->ORIG_DN][3] += $row->TOTAL;
                    }else{
                        $datas[$my_dept][$row->ORIG_DN][4] += $row->TOTAL;
                    }
                    $datas[$my_dept][$row->ORIG_DN][6] += $row->TOTAL;
                }
            }else{
                if($row->ORIG_DN != ""){
                    $datas[$my_dept][$row->ORIG_DN][5]=1;
                    if($row->TYPE == 0){
                        $datas[$my_dept][$row->ORIG_DN][0] += $row->TOTAL;
                    }else if($row->TYPE == 1){
                        $datas[$my_dept][$row->ORIG_DN][1] += $row->TOTAL;
                    }else if($row->TYPE == 2){
                        $datas[$my_dept][$row->ORIG_DN][2] += $row->TOTAL;
                    }else if($row->TYPE == 3){
                        $datas[$my_dept][$row->ORIG_DN][3] += $row->TOTAL;
                    }else{
                        $datas[$my_dept][$row->ORIG_DN][4] += $row->TOTAL;
                    }
                    $datas[$my_dept][$row->ORIG_DN][6] += $row->TOTAL;
                }else{
                    if($row->TYPE == 0){
                        $datas[0][0][0] += $row->TOTAL;
                    }else if($row->TYPE == 1){
                        $datas[0][0][1] += $row->TOTAL;
                    }else if($row->TYPE == 2){
                        $datas[0][0][2] += $row->TOTAL;
                    }else if($row->TYPE == 3){
                        $datas[0][0][3] += $row->TOTAL;
                    }else{
                        $datas[0][0][4] += $row->TOTAL;
                    }
                    $datas[0][0][6] += $row->TOTAL;
                }
            }
        }
if (is_array($datas)){

///////////////////////////////////////////////////////////
//                  SORT Array according to wanted column
///////////////////////////////////////////////////////////
    if($z!='0' &&  !is_int($z)) $z = 8;
    function docmp($a,$b) { 
            global $z;
    // test score in a versus score in b 
            if ($a[$z] > $b[$z]) return -1; /* a.score > b.score */ 
            if ($a[$z] < $b[$z]) return 1; /* a.score < b.score */ 
    // well, they are the same, so say so. 
            return 0; 
         } 

            function cmp_desc ($a, $b) {
              global $z;
            if ($a[$z] < $b[$z]) return -1; /* a.score < b.score */ 
            if ($a[$z] > $b[$z]) return 1; /* a.score > b.score */ 
            return 0; 
            }
   if($z<7){
        if ($sort_type=="desc"){
          foreach($datas as $keycmp=>$valuecmp){
            uasort($datas[$keycmp],cmp_desc); 
          }
        }else{
          foreach($datas as $keycmp=>$valuecmp){
            uasort($datas[$keycmp],docmp); 
          }  
        }  
   }       
///////////////////////////////////////////////////////////
//                  END OF SORT
//////////////////////////////////////////////////////////
}
?>

<br><br>
<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center">
<?if($CSV_EXPORT != 2){?>
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>logo2.gif" ></a></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR><?=$report_type?><br><?=$header?></TD>
              <TD width="25%" align=right><img SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>
<?}?>
      </td>
  </tr>
   <?if($t0!=""){?>
  <tr>
    <td width="100%" class="rep_header" align="right">Tarih (<?=date("d/m/Y",strtotime($t0))?>
       <?if($t1!=""){?>
         <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
    )</td>
  </tr>
   <?}?>
   <?if($DEPT_ID || $D_ID == -2){?>
  <tr>
    <td width="100%" class="rep_header" align="left">Departman :
         <?echo get_dept_name($DEPT_ID,$SITE_ID);
            if($D_ID == -2 ) echo "Bir Departmana Baðlý Olmayan Dahililer";
            ?>
    </td>
  </tr>
   <?}?>
    <tr><td colspan=2 align=right>
    <?if($CSV_EXPORT<>2){?>
      <table cellspacing=0 cellpadding=0>
        <tr>
          <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
          <td><a href="javascript:mailPage('general.html')"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
          <td><a href="javascript:history.back(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
          <td><a href="javascript:history.forward(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
          <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
          <td><a href="javascript:window.print();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
          <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
        </tr>
      </table>
    <?}?>        
    </td></tr>
  <tr>
    <td colspan="2">
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
            <?
            if($sort_type=="desc")
                $sort_gif = "report/top.gif";    
            else
                $sort_gif = "report/down.gif";
            ?>
          <tr>
<?if($CSV_EXPORT==2){?>
                <td class="rep_table_header" width="28%">Dahili</td>
                <td class="rep_table_header" width="12%">Þehir Ýçi</td>
                <td class="rep_table_header" width="14%">Þehirler Arasý</td>
                <td class="rep_table_header" width="12%">GSM</td>
                <td class="rep_table_header" width="12%">Uluslar Arasý</td>
                <td class="rep_table_header" width="10%">Diðer </td>
                <td class="rep_table_header" width="12%">Toplam</td>
<?}else{?>
                <td class="rep_table_header" width="28%">Dahili<a style="cursor:hand;" onclick="javascript:submit_form('8');"><img src="<?=IMAGE_ROOT?><?=($order=="8")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">Þehir Ýçi<a style="cursor:hand;" onclick="javascript:submit_form('0');"><img src="<?=IMAGE_ROOT?><?=($order=="0")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="14%">Þehirler Arasý<a style="cursor:hand;" onclick="javascript:submit_form('1');"><img src="<?=IMAGE_ROOT?><?=($order=="1")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">GSM<a style="cursor:hand;" onclick="javascript:submit_form('2');"><img src="<?=IMAGE_ROOT?><?=($order=="2")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">Uluslar Arasý<a style="cursor:hand;" onclick="javascript:submit_form('3');"><img src="<?=IMAGE_ROOT?><?=($order=="3")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="10%">Diðer <a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">Toplam<a style="cursor:hand;" onclick="javascript:submit_form('6');"><img src="<?=IMAGE_ROOT?><?=($order=="6")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
<?}?>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
         $i = 0;;
        $my_pr=0;
                $csv_data[0][0] = "Dahili";
                $csv_data[0][1] = "Þehir Ýçi";
                $csv_data[0][2] = "Þehirler Arasý" ;
                $csv_data[0][3] = "GSM";
                $csv_data[0][4] = "Uluslar Arasý";
                $csv_data[0][5] = "Diðer";
                $csv_data[0][6] = "Toplam";
             
            $m = 0;
            $j = 0;
         if(is_array($datas)){    
            foreach($datas as $key=>$value){
            if ($key>0) $my_dept_name = get_dept_name($key,$SITE_ID);
            else $my_dept_name = "Kaydedilmemiþ Dahililer";
            echo  " <tr  bgcolor=\"FFFFFF\"><td class=\"rep_td\" colspan=7>&nbsp;<b>".$my_dept_name."</b></td></tr>\n";
            unset($dept_totals);
            $i = 0;
            $j++;
            $csv_data[$j][0] = $my_dept_name;
            foreach($value as $keynew=>$valuenew){
                   $i++;$j++;
                   $bg_color = "E4E4E4";   
                   if($i%2) $bg_color ="FFFFFF";
                 echo " <tr  BGCOLOR=$bg_color>\n";
                   $k_x = ($keynew=="0"?"Dahili Yok":$keynew);
                   $k_y = ($keynew=="0"?"-2":$keynew);
                   
                   echo  " <td class=\"rep_td\">&nbsp;";
                   if($CSV_EXPORT==2){
                     echo  "<b>".$k_x."</b> - ".get_ext_name2($keynew, $SITE_ID)."</td>\n";
                   }else{
                     echo  "<a class=\"a1\" HREF=\"javascript:drill_down('$k_y')\"><b>".$k_x."</b> - ".get_ext_name2($keynew, $SITE_ID)."</a></td>\n";
                   }  
                   $total = 0;
             $csv_data[$j][0] =  $k_x." - ".get_ext_name2($keynew, $SITE_ID);
                   for($k=0;$k<=4;$k++){
                       echo " <td class=\"rep_td\" align=\"right\">".write_me($value[$keynew][$k],$calc_type)."</td>\n";
                       $total += $value[$keynew][$k];
                       $csv_data[$j][$k+1] =  write_me($value[$keynew][$k],$calc_type);
                       $dept_totals[$k] += $value[$keynew][$k];
                   }
                    $dept_totals[5] += $total;
                    echo  " <td class=\"rep_td\" align=\"right\"><b>".write_me($total,$calc_type)."</b></td>\n";
                 echo "</tr>\n";
              $csv_data[$j][6] =  write_me($total,$calc_type);
                 $my_pr = $my_pr + $total;
                   $m++;
            }       
            
            $j++;
            $csv_data[$j][0] = "Alt Toplamlar";         
            echo  " <tr bgcolor=\"E4E4E4\"><td class=\"rep_td\">&nbsp;<b>Alt Toplamlar</b></td>\n";
               for($k=0;$k<=5;$k++){
                   echo " <td class=\"rep_td\" align=\"right\"><b>".write_me($dept_totals[$k],$calc_type)."</b></td>\n";
               $csv_data[$j][$k+1] =  write_me($dept_totals[$k],$calc_type);
               }
            echo "</tr>";   

            echo  " <tr bgcolor=\"000000\"><td colspan=7 height=\"1\" align=center valign=center></td></tr>\n";   
      }
          }  
          $j++;
      ?>
      </table>
  <tr height="20">
    <td></td>
  </tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
         <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Genel Toplam :</b></TD>
              <TD WIDTH="20%" ><?=write_me($my_pr,$calc_type)?></TD>
                <?$i++;$csv_data[$j][0] =  "Toplam";?>
                <?$csv_data[$j][1] =  write_me($my_pr,$calc_type)?>
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
 
 if($CSV_EXPORT==2){
   $fd = fopen($DOCUMENT_ROOT."/temp/general.xls", w);
   fwrite($fd,ob_get_contents());
 }else{
   $fd = fopen($DOCUMENT_ROOT."/temp/general.html", w);
   fwrite($fd,ob_get_contents());
 }
 ob_end_flush();

 csv_out($csv_data, "../../temp/ext_disps.csv"); 
 if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=ext_disps.csv" WIDTH=0 HEIGHT=0 ></iframe>
 <?}else if($CSV_EXPORT==2){?>
 <iframe SRC="/csv_download.php?filename=general.xls" WIDTH=0 HEIGHT=0 ></iframe>
 <?}?>
<br>
<br>



<?
    require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $cUtility = new Utility();
    $cdb = new db_layer(); 
    $conn = $cdb->getConnection();
    $show_chart=false;
    require_valid_login();

    $usr_crt = "";
    if (right_get("SITE_ADMIN")){
      //Site admin hakk� varsa her�eyi g�rebilir.  
      //Site id gelmemi�se ki�inin bulundu�u site raporu al�n�r.
      if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
      // Admin vaye ALL_REPORT hakk� varsa kendi sitesindeki her�eyi g�rebilir.
      $SITE_ID = $SESSION['site_id'];
    }elseif(got_dept_right($SESSION["user_id"])==1){
      //Bir departman�n raporunu g�rebiliyorsa kendi sitesindekileri girebilir.
      $SITE_ID = $SESSION['site_id'];
      //echo $dept_crt = get_depts_crt($SESSION["user_id"]);
       $usr_crt = get_users_crt($SESSION["user_id"], 1, $SITE_ID);
      $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini i�erir.";
    }else{
      print_error("Bu sayfay� G�rme Hakk�n�z Yok!!!");
      exit;
    }

    $cache_status = call_cache("reports/cache/ozet");
    ob_start();
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
    print_error("Site paramatreleri bulunamad�.");
    exit;
  }
   
    function write_me($MyVal,$calc_type){
     if ($calc_type == 1){
       $MyRetVal = write_price($MyVal);
     }elseif($calc_type==2){
       $MyRetVal = calculate_all_time($MyVal);
     }else{
       print_error("Hatal� Durum Olu�tu. L�tfen Tekrar Deneyiniz.");
      exit;
     }
     return $MyRetVal;
  }
    if($type=='site'){
      $type = 'dept';
    }elseif($type=='site_time'){
      $type=='dept_time';
    }  
if($CSV_EXPORT!=2){
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
        <input type="hidden" class="cache1" name="withCache" value="<?=$withCache?>" >
        <input type="hidden" name="forceMainTable" VALUE="<?=$forceMainTable?>" >         
         <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
<?}?>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action= '/reports/general/report_call_dept.php?act=src&type=<?=$type?>&order=' + sortby;    
    document.all('sort_me').submit();
   }
  function drill_down(d_id){
    document.all('sort_me').action= '/reports/general/report_call_ext.php?act=src&type=<?=$type?>&DEPT_ID=' + d_id;    
    document.all('sort_me').submit();
   }
function CheckEmail (strng) {
    var error="";
    var emailFilter=/^.+@.+\..{2,3}$/;
    if (!(emailFilter.test(strng))) { 
       alert("L�tfen ge�erli bir e-mail adresi giriniz.\n");
       return 0;
    }
    else {
       var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/
       if (strng.match(illegalChars)) {
             alert("Girdi�iniz e-mail ge�ersiz karakterler i�ermektedir.\n");
             return 0;
       }
    }
    return 1;
}   
 function mailPage(page){
      var keyword = prompt("L�tfen bir mail adresi giriniz.", "")
      if(CheckEmail(keyword)){
          var pagename = "/reports/htmlmail.php?page=/temp/"+page+  "&email="+ keyword;
          this.location.href = pagename;
      }    
   }   
   
</script>
  
<?   
//   $report_type="GENEL RAPOR";
     if ($act == "src") {
       if ($type=='dept' || $tip=='dept'){ //tip site'dan gelen de�i�ken. Oradaki type ile kar��t��� i�in konuldu.
         $myfld= "PRICE";
         $calc_type =1;   
       }elseif($type=='dept_time' || $tip=='dept_time'){
         $myfld= "DURATION";
         $calc_type =2;   
       }else{
          print_error("Hatal� Durum Olu�u. L�tfen Tekrar Deneyiniz.");
          exit;
       }
       $kriter = "";   

        //Temel kriterler. Verinini h�zl� gelmesi i�in ba�a konuldu.
     $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,   "=",  "$SITE_ID"); //Bu mutlaka olmal�.�lgili siteyi belirliyor.
     $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,   "=",  "0"); //Bu mutlaka olmal�.Hatas�z kay�t oldu�unu g�steriyor.      
     $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmal�.D�� arama oldu�unu g�steriyor.
     $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmal�.D�� arama oldu�unu g�steriyor.
     $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmal�.Hatas�z kay�t oldu�unu g�steriyor.
     
    //Zaman kriterleri ve tablo ismi se�imi ba�lang��
    add_time_crt();//Zaman kriteri
	$link  ="";

     if($forceMainTable)
       $CDR_MAIN_DATA = "CDR_MAIN_DATA";
     else
       $CDR_MAIN_DATA = getTableName($t0,$t1);
      
     if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  

    //Zaman kriterleri ve tablo ismi se�imi biti�

    $header="�a�r�lar�n Departmanlara G�re Da��l�m�";

      $sql_str  = "SELECT LocationTypeid AS TYPE, LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN, SUM(CDR_MAIN_DATA.$myfld) AS TOTAL
	               FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  ";
      if ($kriter != "")
        $sql_str .= " WHERE ".$kriter." ".$usr_crt;  
      $sql_str .= " GROUP BY ORIG_DN, TYPE";
       
    switch ($order){
      case '0':
             $z = 0;
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
             $z = 8;
         }

     if ($record<>'' ||is_numeric($record)) {
               $sql_str .= " LIMIT 0,". $record ;
         }
//echo $sql_str;//exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
      $row_count = mysql_num_rows($result); //added by Yagmur
      while($row = mysql_fetch_object($result)){
            $my_dept = get_orig_dept_id($row->ORIG_DN, $SITE_ID);
            if($my_dept != ""){
                $datas[$my_dept][5]=1;
                if($row->TYPE == 0){
                    $datas[$my_dept][0] += $row->TOTAL;
                }else if($row->TYPE == 1){
                    $datas[$my_dept][1] += $row->TOTAL;
                }else if($row->TYPE == 2){
                    $datas[$my_dept][2] += $row->TOTAL;
                }else if($row->TYPE == 3){
                    $datas[$my_dept][3] += $row->TOTAL;
                }else{
                    $datas[$my_dept][4] += $row->TOTAL;
                }
                $datas[$my_dept][6] += $row->TOTAL;
            }    
            if($my_dept == ""){
                $datas[0][5]=1;
                if($row->TYPE == 0){
                    $datas[0][0] += $row->TOTAL;
                }else if($row->TYPE == 1){
                    $datas[0][1] += $row->TOTAL;
                }else if($row->TYPE == 2){
                    $datas[0][2] += $row->TOTAL;
                }else if($row->TYPE == 3){
                    $datas[0][3] += $row->TOTAL;
                }else{
                    $datas[0][4] += $row->TOTAL;
                }
                $datas[0][6] += $row->TOTAL;
            }
        }
         
if (is_array($datas)){
    //array_multisort ($datas, SORT_DESC, array_keys ($datas));
    //asort($datas);

///////////////////////////////////////////////////////////
//                  SORT Array according to wanted column
///////////////////////////////////////////////////////////
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
                if ($sort_type=="desc")
                  uasort($datas,cmp_desc); 
                else
                  uasort($datas,docmp); 
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
<?if($CSV_EXPORT!=2){?>
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
    <tr>
     <td width="100%" class="rep_header" align="left">
     <?if($t0!=""){?>
        Tarih (<?=date("d/m/Y",strtotime($t0))?>
       <?if($t1!=""){?>
         <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
    )<?}?></td>
    <td colspan= align=right>
<?            if($CSV_EXPORT!=2){?>
      <table cellspacing=0 cellpadding=0>
        <tr>
          <td><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
          <td><a href="javascript:mailPage('general.html')"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
          <td><a href="javascript:history.back(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
          <td><a href="javascript:history.forward(1);"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="�leri"></a></td>
          <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
          <td><a href="javascript:window.print();"><img src="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazd�r"></a></td>
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
              <td class="rep_table_header" width="28%">Departman</td>
                <td class="rep_table_header" width="12%">�ehir ��i</td>
                <td class="rep_table_header" width="14%">�ehirler Aras�</td>
                <td class="rep_table_header" width="12%">GSM</td>
                <td class="rep_table_header" width="12%">Uluslar Aras�</td>
                <td class="rep_table_header" width="10%">Di�er</td>
                <td class="rep_table_header" width="12%">Toplam</td>
<?}else{?>
              <td class="rep_table_header" width="28%">Departman<a style="cursor:hand;" onclick="javascript:submit_form('8');"><img src="<?=IMAGE_ROOT?><?=($order=="8")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">�ehir ��i<a style="cursor:hand;" onclick="javascript:submit_form('0');"><img src="<?=IMAGE_ROOT?><?=($order=="0")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="14%">�ehirler Aras�<a style="cursor:hand;" onclick="javascript:submit_form('1');"><img src="<?=IMAGE_ROOT?><?=($order=="1")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">GSM<a style="cursor:hand;" onclick="javascript:submit_form('2');"><img src="<?=IMAGE_ROOT?><?=($order=="2")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">Uluslar Aras�<a style="cursor:hand;" onclick="javascript:submit_form('3');"><img src="<?=IMAGE_ROOT?><?=($order=="3")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="10%">Di�er<a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
                <td class="rep_table_header" width="12%">Toplam<a style="cursor:hand;" onclick="javascript:submit_form('6');"><img src="<?=IMAGE_ROOT?><?=($order=="6")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
<?}?>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
           $i = 0;;
        $my_pr=0; 
                $csv_data[0][0] = "Departman";
                $csv_data[0][1] = "�ehir ��i";
                $csv_data[0][2] = "�ehirler Aras�" ;
                $csv_data[0][3] = "GSM";
                $csv_data[0][4] = "Uluslar Aras�";
                $csv_data[0][5] = "Di�er";
                $csv_data[0][6] = "Toplam";
                
         if(is_array($datas)){   
            foreach($datas as $key=>$value){
                   $i++;
                   $bg_color = "E4E4E4";   
                   if($i%2) $bg_color ="FFFFFF";

                   $key_x = get_dept_name($key,$SITE_ID);
                   $key_y = $key;
                   if($key=="0") $key_x = "Tan�ms�z Departman";
                   if($key=="0") $key_y = -2;
                                      
             echo " <tr  BGCOLOR=$bg_color>\n";
             if($CSV_EXPORT==2){
                   echo  " <td class=\"rep_td\">&nbsp;<b>".$key_x."</b></td>\n";
             }else{
                   echo  " <td class=\"rep_td\">&nbsp;<b><a class=\"a1\" href=\"javascript:drill_down('".$key_y."')\">".$key_x."</a></b></td>\n";
             }      
             $csv_data[$i][0] =  $key_x;
                   $total = 0;
             for($k=0;$k<=4;$k++){
                       echo " <td class=\"rep_td\" align=\"right\">".write_me($datas[$key][$k],$calc_type)."</td>\n";
                 $csv_data[$i][$k+1] =  write_me($datas[$key][$k],$calc_type);
                       $total += $datas[$key][$k];
                   }
                   echo  " <td class=\"rep_td\" align=\"right\"><b>".write_me($total,$calc_type)."</b></td>\n";
             echo "</tr>\n";
                $csv_data[$i][6] =  write_me($total,$calc_type);
             $my_pr = $my_pr + $total;
         }
    }
          ?>
      </table>
  <tr height="20">
    <td></td>
  </tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
         <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam :</b></TD>
              <TD WIDTH="20%" ><?=write_me($my_pr,$calc_type)?></TD>
                <?$i++;$csv_data[$i][0] =  "Toplam";?>
                <?$csv_data[$i][1] =  write_me($my_pr,$calc_type);?>
            </TR>
         </TABLE>
    <tr>
        <td><?echo $alert;?></td>
    </tr>
      </td>
  </tr>
</table>  
<?}?>
<br>
<br>
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
 
 csv_out($csv_data, "../../temp/dept_disps.csv"); 
 if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=dept_disps.csv" WIDTH=0 HEIGHT=0 ></iframe>
 <?}else if($CSV_EXPORT==2){?>
 <iframe SRC="/csv_download.php?filename=general.xls" WIDTH=0 HEIGHT=0 ></iframe>
 <?}?>


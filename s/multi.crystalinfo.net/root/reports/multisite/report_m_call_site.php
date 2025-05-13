<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $cUtility = new Utility();
    $cdb = new db_layer(); 
    $conn = $cdb->getConnection();
    $show_chart=false;
    require_valid_login();

    check_right("SITE_ADMIN");
    cc_page_meta();
    echo "<center>";
    
    $sql_str1="SELECT NAME, VALUE FROM SYSTEM_PRM WHERE NAME = 'COMPANY_NAME' OR NAME='MAX_ACCE_DURATION'";
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if (mysql_num_rows($result1)>0){
    while($row1 = mysql_fetch_object($result1)){
      switch ($row1->NAME){
        case 'COMPANY_NAME':
              $company = $row1->VALUE;
          break;
        case 'MAX_ACCE_DURATION':
              $max_acc_dur =  ($row1->VALUE)*60;
          break;
      }
    }
  }else{
    print_error("Site parametreleri bulunamadý.");
    exit;
  }
   
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

 ?>
   <form name="sort_me" method="post" action="">
      <input type="hidden" name="MY_DATE" value="<?=$MY_DATE?>">
      <input type="hidden" name="t0" value="<?=$t0?>">         
      <input type="hidden" name="t1" value="<?=$t1?>">         
      <input type="hidden" name="last" value="<?=$last?>">         
      <input type="hidden" name="hh0" value="<?=$hh0?>">
      <input type="hidden" name="hm0" value="<?=$hm0?>">
      <input type="hidden" name="hh0" value="<?=$hh1?>">
      <input type="hidden" name="hm0" value="<?=$hm1?>">
      <input type="hidden" name="hafta" value="<?=$hafta?>">
      <input type="hidden" name="record" value="<?=$record?>">
      <input type="hidden" name="type" value="<?=$type?>">  
      <input type="hidden" name="dept_type" value="<?if($type=="site"){echo "dept";}elseif($type=="site_time"){echo "dept_time";}?>">  
      <input type="hidden" name="calc_type" value="<?=$calc_type?>">       
      <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_m_call_site.php?act=src&type=<?=$type?>&order=' + sortby;    
    document.all('sort_me').submit();
   }
  function drill_down(s_id){
      var dept_type;
    dept_type = document.all('dept_type').value;
    document.all('sort_me').action='/reports/general/report_call_dept.php?act=src&tip=' + dept_type + '&SITE_ID=' + s_id;    
    document.all('sort_me').submit();
   }
</script>
  
<?   
   $report_type="SITE RAPORU";
   
     if ($act == "src") {
   
     if ($type=='site'){
       $myfld= "PRICE";
       $calc_type =1;   
     }elseif($type=='site_time'){
       $myfld= "DURATION";
       $calc_type =2;   
     }else{
       print_error("Hatalý Durum Oluþu. Lütfen Tekrar Deneyiniz");
       exit;
     }

       $kriter = "";   

        //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
     $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,   "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
     $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
     $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
     $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
     
     //**Bunlar birlikte olmalý ve bu sýrada olmalý.
     add_time_crt();//Zaman kriteri 
     $CDR_MAIN_DATA = getTableName($t0,$t1);
     if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";
     //**	
    
    $sql_id="3";$grp_type="SITE_ID, TYPE";
    $field1="SITE_ID";$field1_name="Site Adý";$width1="20%";$field1_ord="SITE_ID";
    $field2="AMOUNT";$field2_name="Adet";$width2="8%";$field2_ord="AMOUNT DESC";
    $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION DESC";
    $field7="PRICE";$field7_name="Tutar";$width7="15%";$field7_ord="PRICE DESC";
    $header="Çaðrýlarýn Sitelere Göre Daðýlýmý";
   
    $sql_str  = "SELECT LocationTypeid AS TYPE, SITE_ID, SUM(CDR_MAIN_DATA.$myfld) AS TOTAL
                 FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                ";
   
    if ($kriter != "")
      $sql_str .= " WHERE ".$kriter." ".$usr_crt;  
    $sql_str .= " GROUP BY ".$grp_type;

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
             $z = 1;
         }

     if ($record<>'' ||is_numeric($record)) {
               $sql_str .= " LIMIT 0,". $record ;
         }
//echo $sql_str;exit;
     if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
       print_error($error_msg);
       exit;
     }
     while($row = mysql_fetch_object($result)){
       $my_site = $row->SITE_ID;
       if($my_site != ""){
         $datas[$my_site][5]=1;
         if($row->TYPE == 0){
           $datas[$my_site][0] += $row->TOTAL;
         }else if($row->TYPE == 1){
           $datas[$my_site][1] += $row->TOTAL;
         }else if($row->TYPE == 2){
           $datas[$my_site][2] += $row->TOTAL;
         }else if($row->TYPE == 3){
           $datas[$my_site][3] += $row->TOTAL;
         }else{
           $datas[$my_site][4] += $row->TOTAL;
         }
         $datas[$my_site][6] += $row->TOTAL;
       }    
       if($my_site == ""){
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
    function cmp_desc($a,$b) { 
            global $z;
    // test score in a versus score in b 
            if ($a[$z] < $b[$z]) return -1; /* a.score > b.score */ 
            if ($a[$z] > $b[$z]) return 1; /* a.score < b.score */ 
    // well, they are the same, so say so. 
            return 0; 
         } 
     if($z<7){
        if($sort_type=="desc"){
      usort($datas, cmp_desc);
        }else{
        uasort($datas, docmp); 
        }    
     }                 
///////////////////////////////////////////////////////////
//                  END OF SORT
//////////////////////////////////////////////////////////
}
?>

<br><br>
<table width="85%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center">
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=IMAGE_ROOT?>logo2.gif" ></a></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR><?=$report_type?><br><?=$header?></TD>
              <TD width="25%" align=right><img SRC="<?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>

      </td>
  </tr>
  <tr>
    <td align="right">

       <table width="100%" cellspacing=0 cellpadding=0>
        <tr><td width="50%" class="rep_header" align="left">
           <?if($t0!=""){?>
           Tarih (<?=date("d/m/Y",strtotime($t0))?>
               <?if($t1!=""){?>
                 <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
            )<?}?>
         </td><td width="50%" class="rep_header" align="right">
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
         </td></tr>
        </table>
        </td>
    </tr>    
  <tr>
    <td colspan="2">
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
<?           if($sort_type=="asc")
                $sort_gif = "report/top.gif";    
            else
                $sort_gif = "report/down.gif";?>
          <tr>
<td>No</td>
              <td class="rep_table_header" width="28%">Site Adý<a style="cursor:hand;" onclick="javascript:submit_form('8');"><img src="<?=IMAGE_ROOT?><?=($order=="8")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
                <td class="rep_table_header" width="12%">Þehir Ýçi<a style="cursor:hand;" onclick="javascript:submit_form('0');"><img src="<?=IMAGE_ROOT?><?=($order=="0")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
                <td class="rep_table_header" width="14%">Þehirler Arasý<a style="cursor:hand;" onclick="javascript:submit_form('1');"><img src="<?=IMAGE_ROOT?><?=($order=="1")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
                <td class="rep_table_header" width="12%">GSM<a style="cursor:hand;" onclick="javascript:submit_form('2');"><img src="<?=IMAGE_ROOT?><?=($order=="2")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
                <td class="rep_table_header" width="12%">Uluslar Arasý<a style="cursor:hand;" onclick="javascript:submit_form('3');"><img src="<?=IMAGE_ROOT?><?=($order=="3")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
                <td class="rep_table_header" width="10%">Diðer<a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
                <td class="rep_table_header" width="12%">Toplam<a style="cursor:hand;" onclick="javascript:submit_form('6');"><img src="<?=IMAGE_ROOT?><?=($order=="6")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
           $i = 0;;
        $my_pr=0; 
                $csv_data[0][0] = "Site Adý";
                $csv_data[0][1] = "Þehir Ýçi";
                $csv_data[0][2] = "Þehirler Arasý" ;
                $csv_data[0][3] = "GSM";
                $csv_data[0][4] = "Uluslar Arasý";
                $csv_data[0][5] = "Diðer";
                $csv_data[0][6] = "Toplam";

         if(is_array($datas)){   
            foreach($datas as $key=>$value){
           $i++;
                   $bg_color = "E4E4E4";   
                   if($i%2) $bg_color ="FFFFFF";
                   $key_x = get_site_name($key);
           $key_y = $key;
                   if($key=="0") $key_x = "Tanýmsýz Site";
                   if($key=="0") $key_y = -2;
                                      
             echo " <tr  BGCOLOR=$bg_color>\n";
                   echo  " <td>$i</td><td class=\"rep_td\">&nbsp;<a class=\"a1\" HREF=\"javascript:drill_down('$key')\"<b>".$key_x."</b></a></td>\n";
             $csv_data[$i][0] =  $key_x;
                   $total = 0;
             for($k=0;$k<=4;$k++){
                       echo " <td class=\"rep_td\" align=\"right\">&nbsp;".write_me($datas[$key][$k],$calc_type)."</td>\n";
                 $csv_data[$i][$k+1] =  write_me($datas[$key][$k],$calc_type);
                       $total += $datas[$key][$k];
                   }
                   echo  " <td class=\"rep_td\" align=\"right\">&nbsp;<b>".write_me($total,$calc_type)."</b></td>\n";
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
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Tutar :</b></TD>
              <TD WIDTH="20%" ><?=write_me($my_pr,$calc_type)?></TD>
                <?$i++;$csv_data[$i][0] =  "Toplam Tutar";?>
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
 csv_out($csv_data, "../../temp/site_disps.csv"); 
 if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=site_disps.csv" WIDTH=0 HEIGHT=0 ></iframe>
 <?}?>


<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $header = "Hat Aktivite Raporu";
     $cUtility = new Utility();
  $cdb = new db_layer(); 
    $conn = $cdb->getConnection();
     require_valid_login();
    if (right_get("SITE_ADMIN")){
        //Site admin hakký varsa herþeyi görebilir.  
    //Site id gelmemiþse kiþinin bulunduðu site raporu alýnýr.
      if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
    // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
      $SITE_ID = $SESSION['site_id'];
    }else{
            print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 
     cc_page_meta();
  echo "<center>";
  ?>
   <form name="sort_me" method="post" action="report_trn_act.php">
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
     <input type="hidden" name="z" value="">  
     <input type="hidden" name="act" value="<?=$act?>">  
   </form>
  
<?
  function trunk_type($val){
    switch ($val){
      case 1:
        $my_val='Giriþ';
        break;
      case 2:
        $my_val='Çýkýþ';
        break;
      case 3:
        $my_val='Giriþ-Çýkýþ';
        break;
    }
    return $my_val;
  }
    
    function trunk_prop($trunk,$field,$SITE_ID){
        global $cdb;
        $sql_str="SELECT TRUNK_NAME,TRUNK_IO_TYPE FROM TRUNKS WHERE MEMBER_NO= '$trunk' AND SITE_ID = '".$SITE_ID."'"; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
            print_error($error_msg);
            exit;
        }
        $row = mysql_fetch_object($result);
        return $row->$field;
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

   $report_type="Hat Aktivite Raporu";
   
   if ($act == "src") {

     $kriter = "";
     $kriter2 = "";
     $kriter3 = "";
     $kriter4 = "";
     //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
     $kriter4 .= $cdb->field_query($kriter4,   "SITE_ID"      ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
     $kriter2 .= $cdb->field_query($kriter2,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
     $kriter3 .= $cdb->field_query($kriter3,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
   
    add_time_crt();//Zaman kriteri
    if($forceMainTable)
      $CDR_MAIN_DATA = "CDR_MAIN_DATA";
    else
      $CDR_MAIN_DATA = getTableName($t0,$t1);
    if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";

////////////////////////////////////////////////////////    
    $kriter1 = $kriter;//Zaman kriterini kaybetmeyelim.

    //Gelen Çaðrýlarýn Toplamý
    $sql_str  = "SELECT COUNT(CDR_ID) AS AMOUNT, CALL_TYPE FROM CDR_MAIN_INB";
    if($kriter!="")
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=2 AND ".$kriter3." AND ".$kriter;
    else 
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=2 AND ".$kriter3;

    if ($kriter != "")
      $sql_str .= " WHERE " .$kriter;

    if ($record<>'' ||is_numeric($record)) {
      $sql_str .= " LIMIT 0,". $record ;
    }
    $sql_str .=" GROUP BY CALL_TYPE";
    //echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $row = mysql_fetch_object($result);
      $INB_CNT = $row->AMOUNT;

    $kriter = $kriter1;//Zaman kriteri tekrar alýndý.
    //Giden Çaðrýlarýn Toplamý
    $sql_str  = "SELECT COUNT(CDR_ID) AS AMOUNT, CALL_TYPE FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA";
    if($kriter!="")
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=1 AND ".$kriter3." AND ".$kriter;
    else 
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=1 AND ".$kriter3;

    if ($kriter != "")
      $sql_str .= " WHERE " .$kriter;

    if ($record<>'' ||is_numeric($record)) {
      $sql_str .= " LIMIT 0,". $record ;
    }
    $sql_str .=" GROUP BY CALL_TYPE";
    //echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $row = mysql_fetch_object($result);
      $OUTB_CNT = $row->AMOUNT;
////////////////////////////////////////////////////////////

    $sql_str  = "SELECT MEMBER_NO AS TRUNK FROM TRUNKS WHERE SITE_ID = $SITE_ID";
    if (!($cdb->execute_sql($sql_str,$rs_t,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $kriter = $kriter1;
    while($row_t = mysql_fetch_object($rs_t)){
      $trunks[$row_t->TRUNK] = Array($row_t->TRUNK);
    }
    
    //Gelen Çaðrýlarýn hatlara Daðýlýmý
    $sql_str  = "SELECT COUNT(CDR_ID) AS AMOUNT, TER_TRUNK_MEMBER FROM CDR_MAIN_INB";

    if($kriter!="")
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=2 AND ".$kriter3." AND ".$kriter;//Index'e uygun olmalý.
    else
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=2 AND ".$kriter3;//Index'e uygun olmalý.
     
    if ($kriter != "")
      $sql_str .= " WHERE " .$kriter;  
       
    $sql_str .= " GROUP BY TER_TRUNK_MEMBER ORDER BY AMOUNT DESC";
          
    if ($record<>'' ||is_numeric($record)) {
      $sql_str .= " LIMIT 0,". $record ;
    }
    //echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result_inb,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $kriter = $kriter1;
    $i=0;
    while($row = mysql_fetch_object($result_inb)){
      $trunks[$row->TER_TRUNK_MEMBER][2] = $row->AMOUNT;
      $i++;
    }
    $kriter = $kriter1;

    //Giden Çaðrýlarýn Trunklara Daðýlýmý
    $sql_str  = "SELECT COUNT(CDR_ID) AS AMOUNT,TER_TRUNK_MEMBER FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA";
    if($kriter!="")
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=1 AND ".$kriter3." AND ".$kriter;//Index'e uygun olmalý.
    else
      $kriter = $kriter4." AND ".$kriter2." AND CALL_TYPE=1 AND ".$kriter3;//Index'e uygun olmalý.
        
    if ($kriter != "")
      $sql_str .= " WHERE " .$kriter;  
       
    $sql_str .= " GROUP BY TER_TRUNK_MEMBER ORDER BY AMOUNT DESC";
    
    if ($record<>'' ||is_numeric($record)) {
      $sql_str .= " LIMIT 0,". $record ;
    }
    //echo $sql_str;exit;
    if (!($cdb->execute_sql($sql_str,$result_outb,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $kriter = $kriter1;
    $i=0;
    while($row = mysql_fetch_object($result_outb)){
      $trunks[$row->TER_TRUNK_MEMBER][3] = $row->AMOUNT;
      $i++;
    }

////////Get the total counts, inbound and outbounds
    while($k < sizeof($trunks)){
      $t = key($trunks);
      $trunks[$t][4] = $trunks[$t][2] + $trunks[$t][3];
      next($trunks);
      $k++;
    }
?>
<br><br>
<table width="85%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center">
            <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD width="25%"><img SRC="<?=IMAGE_ROOT?>logo2.gif" ></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>HAT RAPORU<br><?=$header?></TD>
              <TD width="25%" align=right><img SRC="<?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>
        </td>
  </tr>
  <tr>
    <td colspan="2" width="100%"  align="right">&nbsp;</td>
  </tr>
    <tr>
     <td colspan="2">
     <table width="100%" cellspacing=0 cellpadding=0>
    <tr>
      <td width="50%" class="rep_header" align="left">
         <?if($t0!=""){?>
         Tarih Aralýðý(<?=date("d/m/Y",strtotime($t0))." - ".date("d/m/Y",strtotime($t1));?>)
         <?}?>
       </td><td width="50%" align="right"> 
      <table cellspacing=0 cellpadding=0>
        <tr>
          <td><img src="<?=IMAGE_ROOT?>report/top02.gif" border=0></td>
          <td><a href="javascript:history.back(1);"><img src="<?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
          <td><a href="javascript:history.forward(1);"><img src="<?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
          <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
          <td><a href="../main.php"><img src="<?=IMAGE_ROOT?>report/home.gif" border=0 title="Ana Sayfa"></a></td>
          <td><img src="<?=IMAGE_ROOT?>report/top01.gif" border=0></td>
        </tr>
      </table>
      </td></tr></table>
      </td>
  </tr>
  <tr>
    <td width="100%" valign="top">
      <table width="100%" border="0" bgcolor="#C0C0C0" cellspacing="1" cellpadding="0">
        <tr>
          <td class="rep_table_header" width="15%">Hat Kodu</td>
              <td class="rep_table_header" width="25%">Hat Adý</td>
              <td class="rep_table_header" width="15%">Tipi</td>
              <td class="rep_table_header" width="15%">Gelen Çaðrý</td>
              <td class="rep_table_header" width="15%">Giden Çaðrý</td>
              <td class="rep_table_header" width="15%">Toplam Çaðrý</td>
          </tr>
      <? 
        $k=0;
        if (is_array($trunks)){
///////////////////////////////////////////////////////////
//                  SORT Array according to wanted column
///////////////////////////////////////////////////////////
          if(!$z) $z = 4;
          function docmp($a,$b) { 
                  global $z;
          // test score in a versus score in b 
                  if ($a[$z] > $b[$z]) return -1; /* a.score > b.score */ 
                  if ($a[$z] < $b[$z]) return 1; /* a.score < b.score */ 
          // well, they are the same, so say so. 
                  return 0; 
               } 
          uasort($trunks,docmp); 
///////////////////////////////////////////////////////////
//                  END OF SORT
//////////////////////////////////////////////////////////
          $tot_cnt = 0;
             while($k < sizeof($trunks)){
              $t = key($trunks);
                       $bg_color = "E4E4E4";
            $trunk_id = $trunks[$t][0];
            if ($trunk_id==""){$trunk_id="<font color=red>".$t."</font>";}
            $trunk_name = trunk_prop($trunks[$t][0],'TRUNK_NAME',$SITE_ID);
            if ($trunk_name == ""){$trunk_name = "<font color=red>Bu Hat Tanýmlanmamýþ</font>";}
            if($i%2) $bg_color = "FFFFFF"; $i++;
            echo " <tr bgcolor=$bg_color>";
               echo " <td class=\"rep_td\">".$trunk_id."</td>";
               echo " <td class=\"rep_td\">".$trunk_name."</td>";
               echo " <td class=\"rep_td\"><b>".trunk_type(trunk_prop($trunks[$t][0],'TRUNK_IO_TYPE',$SITE_ID))."</b></td>";
               echo " <td class=\"rep_td\"><b>".number_format($trunks[$t][2],0,'','.')."</b></td>";
               echo " <td class=\"rep_td\"><b>".number_format($trunks[$t][3],0,'','.')."</b></td>";
               echo " <td class=\"rep_td\"><b>".number_format($trunks[$t][4],0,'','.')."</b></td>";
            echo " </tr>";
            $tot_cnt = $tot_cnt + $trunks[$t][4];
            next($trunks);
           $k++;
          }
         }
      ?>
      
      </table>
    </td>  
  </tr>
  <tr>
    <td  colspan="2" width="50%" class="rep_header" align="right">Toplam Akan Çaðrý= <?=number_format($tot_cnt,0,'','.')?></td>
  </tr>
</table>  

<?}?>
</form>

<script>
function submit_f(val){
      document.all('z').value = val;
    document.all('sort_me').submit();
    document.sort_me.submit();
}
</script>
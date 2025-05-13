<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
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
  
    cc_page_meta();
    echo "<center>";
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
       <input type="hidden" name="TRUNK" value="<?=$TRUNK?>">  
       <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
  
<?   
    function get_trunk_name($trunk,$SITE_ID){
        global $cdb;
        $sql_str="SELECT TRUNK_NAME FROM TRUNKS WHERE MEMBER_NO= '$trunk' AND SITE_ID = ".$SITE_ID; 
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
            print_error($error_msg);
            exit;
        }
        $row = mysql_fetch_object($result);
        return $row->TRUNK_NAME;
    }
	
   $report_type="Hat Raporu";
   if ($act == "src") {
     
      $kriter = "";  

    //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
    $kriter .= $cdb->field_query($kriter,   "SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
    $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
    $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.

    add_time_crt();//Zaman kriteri
    if($forceMainTable)
      $CDR_MAIN_DATA = "CDR_MAIN_DATA";
    else
      $CDR_MAIN_DATA = getTableName($t0,$t1);
    if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";

   switch ($type){
     case 'trunk_outb':
      $kriter.= $cdb->field_query($kriter, "CALL_TYPE"         ,"=",    "1");
      $sql_id="3";$grp_type="TRUNK";
      $field1="TRUNK";$field1_name="Hat";$width1="10%";$field1_ord="TRUNK ";
      $field2="TRUNK";$field2_name="Hat Adý";$width2="20%";$field2_ord="TRUNK ";
      $field3="AMOUNT";$field3_name="Adet";$width3="10%";$field3_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $field7="PRICE";$field7_name="Tutar";$width7="25%";$field7_ord="PRICE ";
      $field8="TRUNK";
      $header = "Giden Çaðrýlarýn Hatlara Göre Daðýlýmý";
      break;
     case 'trunk_inb':
      $kriter.= $cdb->field_query($kriter, "CALL_TYPE"         ,"=",    "'2'");
      $sql_id="4";$grp_type="TRUNK";
      $field1="TRUNK";$field1_name="Hat";$width1="10%";$field1_ord="TRUNK ";
      $field2="TRUNK";$field2_name="Hat Adý";$width2="20%";$field2_ord="TRUNK ";
      $field3="AMOUNT";$field3_name="Adet";$width3="10%";$field3_ord="AMOUNT ";
      $field6="DURATION";$field6_name="Süre";$width6="15%";$field6_ord="DURATION ";
      $header = "Gelen Çaðrýlarýn Hatlara Göre Daðýlýmý";
      break;   
    default:
      echo "Hatalý Durum Oluþtu";
      exit;
   }

    switch ($sql_id){
    case 3:
      $sql_str  = "SELECT COUNT(CDR_MAIN_DATA.CDR_ID) AS AMOUNT, SUM(DURATION) AS DURATION ,
                     SUM(CDR_MAIN_DATA.PRICE) AS PRICE, TER_TRUNK_MEMBER AS TRUNK
                   FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                  ";
      break;
    case 4:
      $sql_str  = "SELECT COUNT(CDR_ID) AS AMOUNT, SUM(DURATION) AS DURATION ,
                     TER_TRUNK_MEMBER AS TRUNK
                   FROM CDR_MAIN_INB
                  ";
      break;
    default:
    
  } 
    if ($kriter != "")
      $sql_str .= " WHERE ".$kriter;  
       
    $sql_str .= " GROUP BY ". $grp_type ;  

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
<table width="85%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center">
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="<?=IMAGE_ROOT?>logo2.gif" ></a></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company;?><BR> HAT RAPORU<br><?=$header?></TD>
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
         Tarih Aralýðý(<?=date("d/m/Y",strtotime($t0))." - ".date("d/m/Y",strtotime($t1));?>)
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
   
  <tr>
    <td colspan="2">
      <table width="100%" border="0" bgcolor="#C7C7C7" cellspacing="1" cellpadding="0">
<?           if($sort_type=="asc")
                $sort_gif = "report/top.gif";    
            else
                $sort_gif = "report/down.gif";?>
          <tr>
              <td class="rep_table_header" width="<?=$width1;?>"><?echo $field1_name;?><a style="cursor:hand;" onclick="javascript:submit_form('1');"><img src="<?=IMAGE_ROOT?><?=($order=="1")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?if ($field2_name<>''){?>
            <td class="rep_table_header" width="<?=$width2;?>"><?echo $field2_name;?><a style="cursor:hand;" onclick="javascript:submit_form('2');"><img src="<?=IMAGE_ROOT?><?=($order=="2")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?}?>
          <?if ($field3_name<>''){?>
            <td class="rep_table_header" width="<?=$width3;?>"><?echo $field3_name;?><a style="cursor:hand;" onclick="javascript:submit_form('3');"><img src="<?=IMAGE_ROOT?><?=($order=="3")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?}?>
          <?if ($field4_name<>''){?>
            <td class="rep_table_header" width="<?=$width4;?>"><?echo $field5_name;?><a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?}?>
          <?if ($field5_name<>''){?>
            <td class="rep_table_header" width="<?=$width5;?>"><?echo $field5_name;?><a style="cursor:hand;" onclick="javascript:submit_form('5');"><img src="<?=IMAGE_ROOT?><?=($order=="5")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?}?>
          <?if ($field6_name<>''){?>
            <td class="rep_table_header" width="<?=$width6;?>"><?echo $field6_name;?><a style="cursor:hand;" onclick="javascript:submit_form('6');"><img src="<?=IMAGE_ROOT?><?=($order=="6")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?}?>
          <?if ($field7_name<>''){?>
            <td class="rep_table_header" width="<?=$width7;?>"><?echo $field7_name;?><a style="cursor:hand;" onclick="javascript:submit_form('7');"><img src="<?=IMAGE_ROOT?><?=($order=="7")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
          <?}?>
          </tr>
        <tr>
          <td colspan="5" bgcolor="#000000" height="1"></td>
        </tr>
      <? 
           $i;
           $myrow = "row->$field1";
           $my_dur=0;
        $my_amount=0; 
        $my_pr=0; 
         if (mysql_num_rows($result)>0)
           mysql_data_seek($result,0);
         while($row = mysql_fetch_array($result)){
          $i++;
               $bg_color = "E4E4E4";   
               if($i%2) $bg_color ="FFFFFF";
            echo " <tr  BGCOLOR=$bg_color>";

             echo " <td class=\"rep_td\"><b>";
                    if(!empty($field8)){
                        echo "<a class=\"a1\" href=\"javascript:drill_down_pn('".$row["$field1"]."')\">".$row["$field1"]."</a>";
                    }else{
                        echo $row["$field1"];
                    }
                    echo"</b></td>";
          if ($field2_name<>''){
            if ($field2_name = 'TRUNK')
              echo " <td class=\"rep_td\">".get_trunk_name($row["$field2"],$SITE_ID)."</td>";
            else
              echo " <td class=\"rep_td\">".$row["$field2"]."</td>";
            }
          if ($field3_name<>''){
            echo " <td class=\"rep_td\">".number_format($row["$field3"],0,'','.')."</td>";
          }
          if ($field4_name<>''){
            echo " <td class=\"rep_td\">".$row["$field4"]."</td>";
          }
          if ($field5_name<>''){
            echo " <td class=\"rep_td\">".$row["$field5"]."</td>";
          }
          if ($field6_name<>''){
            if ($field6 = "DURATION")
              echo " <td class=\"rep_td\">".calculate_time($row["DURATION"],"hour")."  Saat  ".calculate_time($row["DURATION"],"min")."  Dk</td>";
            else
              echo " <td class=\"rep_td\">".$row["$field6"]."</td>";  
          }    
          if ($field7_name<>''){
            if ($field6 = "PRICE")
              echo " <td class=\"rep_td\" ALIGN=right>".write_price($row["$field7"])."</td>";
            else
              echo " <td class=\"rep_td\">".$row["$field7"]."</td>";
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
            </TR>
            </TABLE>
  </tr>
  <tr>
    <td height="22" colspan="3" width="100%" align="right">
           <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD WIDTH="80%" ALIGN="right"><b>Toplam Tutar :</b></TD>
              <TD WIDTH="20%"><?=write_price($my_pr)?></TD>
            </TR>
            </TABLE>
      </td>
  </tr>  
</table>  
<?}?>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_trunk_prn.php?act=src&type=<?=$type?>&order=' + sortby;    
    document.all('sort_me').submit();
     }
  function drill_down_pn(trunk){
    document.all('TRUNK').value = trunk;
    document.all('sort_me').action='/reports/outbound/report_outb_prn.php?act=src&SUMM=trunk';    
    document.all('sort_me').submit();
   }
</script>


<? require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer(); 
   $conn = $cdb->getConnection();
   require_valid_login();
   
    if (right_get("SITE_ADMIN")){
      //Site admin hakký varsa herþeyi görebilir.  
      //Site id gelmemiþse kiþinin bulunduðu site raporu alýnýr.
      if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
      //Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
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

   if ($act == "src") {
     
       $kriter = "";   
     
        //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
    $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,"=",  "'$SITE_ID'");
    $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     , "=",  "1"); //Bu mutlaka olmalý.Giden Çaðrý olduðunu gösteriyor.
    $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Giden Çaðrý olduðunu gösteriyor.
     
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
         <input type="hidden" name="forceMainTable" VALUE="<?=$forceMainTable?>" >         
         <input type="hidden" class="cache1" name="withCache" value="<?=$withCache?>" >
  </form>
<?
    //Zaman kriterleri ve tablo ismi seçimi baþlangýç
    add_time_crt();//Zaman kriteri
	$link  ="";

    $CDR_MAIN_DATA = getTableName($t0,$t1);
    if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  

    //Zaman kriterleri ve tablo ismi seçimi bitiþ

    $sql_str = "SELECT TelProviderid, TelProvider FROM TTelProvider";
    if (!($cdb->execute_sql($sql_str,$result_row,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $i=1;
    $rw_ubnd = 0;
    while($res_row = mysql_fetch_array($result_row)){
      $row_arr[$res_row["TelProviderid"]] = $res_row["TelProvider"];
      $i = $i + 1;
      if($rw_ubnd<$res_row["TelProviderid"]){$rw_ubnd=$res_row["TelProviderid"];}
    }

   function write_me($MyVal,$calc_type){
     if ($calc_type == 1){
       $MyRetVal = write_price($MyVal);
     }elseif($calc_type==2){
       $MyRetVal = calculate_all_time($MyVal);
     }elseif($calc_type==3){
       $MyRetVal = number_format($MyVal,0,'','.');
     }else{
       print_error("Hatalý Durum Oluþtu. Lütfen Tekrar Deneyiniz.");
      exit;
     }
     return $MyRetVal;
   }
   
   $sql_str = "SELECT FROM_PROVIDER_ID, TO_PROVIDER_ID, SUM(DURATION) AS SURE, SUM(PRICE) AS TUTAR, COUNT(CDR_ID) AS ADET
               FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
			  ";
   
   if ($kriter != "")
     $sql_str .= " WHERE ".$kriter;
   $sql_str .= " GROUP BY FROM_PROVIDER_ID, TO_PROVIDER_ID
			     ORDER BY FROM_PROVIDER_ID ASC";

   //echo $sql_str."<br>";
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
     print_error($error_msg);
     exit;
   }
   if (mysql_num_rows($result)==0){
     print_error("Kayýtlara Ulaþýlamadý");
	 exit;
   }
  $report_type="Operatörlerin Çaðrý Analiz Matrisi";
  cc_page_meta();
  echo "<center>";
?>
  
<script language="JavaScript">
  function drill_down(o_id, d_id){
    if(d_id=='-1'){
      document.all('sort_me').action='/reports/outbound/report_outb_prn.php?act=src&type=<?=$type?>&SITE_ID=<?=$SITE_ID?>&FROM_PROVIDER_ID=' + o_id;    
    }else{
      document.all('sort_me').action='/reports/outbound/report_outb_prn.php?act=src&type=<?=$type?>&SITE_ID=<?=$SITE_ID?>&FROM_PROVIDER_ID=' + o_id + '&TelProviderid=' + d_id;
    }  
    document.all('sort_me').submit();
   }
</script>   
<br><br>
<table width="80%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan="2" width="100%" align="center" class="rep_header" align="center">
          <TABLE BORDER="0" WIDTH="100%">
            <TR>
              <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>logo2.gif"></a></TD>
              <TD width="50%" align=center CLASS="header"><?echo $company?><BR><?=$report_type?></TD>
              <TD width="25%" align=right><img SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>company.gif"></TD>
            </TR>
            </TABLE>

      </td>
  </tr>
  <tr>
    <td width="100%" class="rep_header" align="left">
    <table  width="100%" cellspacing=0 cellpadding=0>
      <tr>
        <td width="70%" class="rep_header" align="left">
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
      </tr>
      <tr>
        <td width="50%" class="rep_header" align="left">
         <?if($t0!=""){?> Tarih (<?=date("d/m/Y",strtotime($t0))?>
           <?if($t1!=""){?> <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>)
        <?}?></td>
        <td width="50%" align="right"></td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td width="100%">
	<table  width="100%" cellspacing=0 cellpadding=0>
    <?for($i = 1;$i<= $rw_ubnd;$i++){
        if($i==6){continue;}
        if($row_arr[$i]==""){continue;}
        ?>
	<tr>
      <td bgcolor="#B3CAE3" width="3%" valign="center" align="center"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
	  <td height="18" class="homebox" bgcolor="#B3CAE3" valign="center"><a href="javascript:drill_down('<?=$i?>', '-1')">Operatör : <?=$row_arr[$i]?></a></td>
	</tr>
    <tr> 
      <td bgcolor="#508AC5"></td>
	  <td height="22" class="header_beyaz2" width="30%" bgcolor="#508AC5">Aranan Þebeke</td>
	  <td height="22" class="header_beyaz2" bgcolor="#508AC5">Çaðrý Adedi</td>
	  <td height="22" class="header_beyaz2" bgcolor="#508AC5">Toplam Süre</td>
	  <td height="22" class="header_beyaz2" bgcolor="#508AC5">Toplam Tutar</td>	  	  
    </tr>
    <?mysql_data_seek($result,0);
      while ($row = mysql_fetch_object($result)){
	    if ($row->FROM_PROVIDER_ID ==  $i){
		  if($row->FROM_PROVIDER_ID == $row->TO_PROVIDER_ID)
		    $my_bgcolor = '#FFCC00';
		  else
		    $my_bgcolor = '#BED3E9';
		  ?>
          <tr height="20">
            <td bgcolor="<?=$my_bgcolor?>"></td>
            <td bgcolor="<?=$my_bgcolor?>" width="30%" class="text"><a href="javascript:drill_down('<?=$i?>', '<?=$row->TO_PROVIDER_ID?>')" class=a1><?=$row_arr[$row->TO_PROVIDER_ID]?></a></td>
			<td bgcolor="<?=$my_bgcolor?>" width="20%" class="text"><?=write_me($row->ADET,3)?></td>
			<td bgcolor="<?=$my_bgcolor?>" width="20%" class="text"><?=write_me($row->SURE,2)?></td>
			<td bgcolor="<?=$my_bgcolor?>" width="25%" class="header"><?=write_me($row->TUTAR,1)?></td>
          </tr>    
    <?  }
	  }?>
         <tr valign="bottom"> 
	       <td height="5" colspan="5" class="header_beyaz2" bgcolor="#508AC5"></td>
          </tr>
          <tr valign="bottom"> 
            <td height="30" colspan="5" class="header"></td>
         </tr>
    <?}?>
	</table>
	</td>
  </tr>
</table>
<?}?>




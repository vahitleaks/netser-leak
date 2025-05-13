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

    $report_type = "Gelen Çaðrý Rapor";
    if ($act == "src") {
       $kriter = "";

       //Temel kriterler verinin hýzlý gmesi icin basa konuldu.
       //Bu mutlaka olmali ilgili siteyi belirliyor.
       $kriter .= $cdb->field_query($kriter,"CDR_MAIN_INB.SITE_ID",  "=",      "$SITE_ID"); 
       $kriter .= $cdb->field_query($kriter,"ERR_CODE",              "=",      "0");
       $kriter .= $cdb->field_query($kriter,"CALL_TYPE",             "=",      "2");
       $kriter .= $cdb->field_query($kriter,"DURATION",              "<",      "$max_acc_dur");
       $kriter .= $cdb->field_query($kriter,"DURATION",              ">",      "0");
       $kriter .= $cdb->field_query($kriter,"CDR_MAIN_INB.TER_DN",   "<>",     "''");
       //Bu mutlaka olmaliý.Hataiýz kaiýt oldgunu gosteriyor.

       add_time_crt();  //Zaman kriteri 

       //Aranan dahili kriteri olusþturuluyor.

       $sql_str  = "SELECT CDR_MAIN_INB.TER_DN, SUM(CDR_MAIN_INB.DURATION) AS DURATION,
		                COUNT(CDR_MAIN_INB.CDR_ID) AS ADET
                      FROM CDR_MAIN_INB
                     ";
       $sql_str .= " WHERE ".$kriter." 	GROUP BY TER_DN ASC ";

       switch ($order) {
        case 'sure';
          $order ='DURATION';
          break;
        case 'adet';
          $order ='ADET';
          break;
        default:
          $order='';
      }
     
      if ($order) {
        $sql_str .= " ORDER BY ".$order." ".$sort_type; 
      }

      //echo $sql_str;exit;
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
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
          <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>GELEN ÇAÐRI RAPORU<br><BR><?=get_site_name($SITE_ID)?></TD>
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
            <tr  <?if ($dept<>'Yes') echo "style=\"display:none;\""?>>
              <td class="rep_header" nowrap width="20%" valign="top">Departman:</td>
              <td width="80%" valign="top">
              <?get_dept_name($DEPT_ID,$SITE_ID);?>
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
            <?if($t0!=""){?>
            Tarih (<?=date("d/m/Y",strtotime($t0))?>
            <?if($t1!=""){?>
            <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>
            )<?}?>
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
           <? if ($sort_type=="asc")
                 $sort_gif = "report/top.gif";
              else
                 $sort_gif = "report/down.gif";
              
			  if ($type=='dept'){
     		    $idrow  = "Departman No";
     		    $idname = "Departman Adý";
			  }else{
     		    $idrow  = "Dahili No";
     		    $idname = "Dahili Adý";
    		  }
           $csv_data[0][0] = $idrow;
           $csv_data[0][1] = $idname;
           $csv_data[0][2] = "Süre" ;
           $csv_data[0][3] = "Adet";
		   ?>
          <tr>
              <td class="rep_table_header" width="15%"><?=$idrow?></td>
              <td class="rep_table_header" width="35%"><?=$idname?></td>
              <td class="rep_table_header" width="25%">Süre<a style="cursor:hand;" onclick="javascript:submit_form('sure');"><img src="<?=IMAGE_ROOT?><?=($order=="DURATION")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="25%">Adet<a style="cursor:hand;" onclick="javascript:submit_form('adet');"><img src="<?=IMAGE_ROOT?><?=($order=="ADET")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
          </tr>
        <tr>
          <td colspan="7" bgcolor="#000000" height="1"></td>
        </tr>
      <?
        $my_dur = 0;
        $my_amount = 0; 
        $i = 0;
		$arr_data = array();
        while ($row = mysql_fetch_object($result)) {
		  if ($type=='dept'){
		    $arr_data[get_orig_dept_id($row->TER_DN, $SITE_ID)][1] += $row->ADET;
		    $arr_data[get_orig_dept_id($row->TER_DN, $SITE_ID)][2] += $row->DURATION;
		  }else{
			$arr_data[$row->TER_DN][1] = $row->ADET;
		    $arr_data[$row->TER_DN][2] = $row->DURATION;
		  }
		  $my_dur += $row->DURATION;
		  $my_amount += $row->ADET;
		}
      $m =0;
		for($i=0;$i < sizeof($arr_data);$i++){
		  $key = key($arr_data);
          if($type=='dept'){
		    $name =  get_dept_name($key,$SITE_ID);
		  }else{
		    $name =  get_ext_name($key,$SITE_ID);
		  }
		  $bg_color = "E4E4E4";
           $m++;
           $csv_data[$m][0] = $key;
           $csv_data[$m][1] = $name;
           $csv_data[$m][2] = calculate_all_time($arr_data[$key][2]);
           $csv_data[$m][3] = number_format($arr_data[$key][1],0,'','.');
          if ($i % 2) $bg_color ="FFFFFF";
          echo " <tr  BGCOLOR=$bg_color>";
          echo " <td class=\"rep_td\">".$key."</td>";
          echo " <td class=\"rep_td\">".$name."</td>";
          echo " <td class=\"rep_td\">".calculate_all_time($arr_data[$key][2]);
          echo " <td class=\"rep_td\">".number_format($arr_data[$key][1],0,'','.');
          echo "</tr>";
		  next($arr_data);
        }
            $m++;
            $csv_data[$m][2] = "Toplam Görüþme Adedi";
            $csv_data[$m][3] = $my_amount;
            $m++;
            $csv_data[$m][2] = "Toplam Süre Adedi";
            $csv_data[$m][3] = calculate_time($my_dur,"hour")."  Saat  ".calculate_time($my_dur,"min")."  Dk";
            csv_out($csv_data, "../../temp/inbound_calls_sum.csv"); 
      ?>
      </table>
    </td>
  </tr>
  <tr height="20">
    <td>
    </td>
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
</table>        
 <?if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=inbound_calls_sum.csv" WIDTH=0 HEIGHT=0 ></iframe>
              <br><br><br>
              <p align="center"> <a HREF="/temp/inbound_calls_sum.csv">CSV Download</a></p>
 <?}?>
 <? }?>

 

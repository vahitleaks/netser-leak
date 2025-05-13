<? 
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");

   $cUtility = new Utility();
   $cdb = new db_layer(); 
   $conn = $cdb->getConnection();
   require_valid_login();

   //Kullanýiýla§in hak kontrolü oali
   //echo $DEPT_ID[0];echo "/".$DEPT_ID[1];echo "/".$DEPT_ID[2]."---";ECHO COUNT($DEPT_ID)."<br>";
   $kriter2 = "";
   $check_origs = false;
   $usr_crt = "";
   if (right_get("SITE_ADMIN")){
     //Site admin hakký varsa hesþeyi orebilir.  
     //Site id gelmemiþse ksiþinin bulundgu site raporu aliýiýr.
     if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
   }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
     // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
     $SITE_ID = $SESSION['site_id'];
   }elseif(got_dept_right($SESSION["user_id"])==1){
     //Bir departmanýn raporunu orebiliyorsa kendi sitesindekileri girebilir.
     $SITE_ID = $SESSION['site_id'];
     $dept_crt = get_depts_crt($SESSION["user_id"],$SITE_ID);
     $usr_crt  = get_users_crt($SESSION["user_id"],2,$SITE_ID);
     $alert = "Bu rapor sadece sizin yetkinizde olan departmanlara ait dahililerin bilgilerini icerir.";
    }else{
     $usr_crt  = get_ext($SESSION["user_id"]);
    }
    $unrep_exts_crt = get_unrep_exts_crt($SITE_ID);
   ob_start();
   //Hak Kontroluüburada bitiyor
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
    <input type="hidden" name="TER_DN" value="<?=$TER_DN?>">
   <div id="dept" style="display:none">
      <select name="DEPT_ID[]" class="select1" style="width:250;" multiple>
      <?
      if (is_array($DEPT_ID)){
         if ((($DEPT_ID[0] == '-1') || ($DEPT_ID[0]=='')) && count($DEPT_ID)==1){
            //Nothing to do
         }else{
           for ($i = 0; $i < count($DEPT_ID); $i++){
             ECHO "<option value=\"".$DEPT_ID[$i]."\" selected>".$DEPT_ID[$i]."</opiton>";
           }
         }
      }

      ?>
      
      </select>
    </div>
    <input type="hidden" name="DURATION" value="<?=$DURATION?>">
    <input type="hidden" name="CLID" value="<?=$CLID?>">
    <input type="hidden" name="show_clid" value="<?=$show_clid?>">
    <input type="hidden" name="record" value="<?=$record?>">
    <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
  <?

    function get_contact($myclid){
       $cdb = new db_layer(); 
       $kriter1 ='';
       $myclid = str_replace("X", "", $myclid);
       if ($myclid<>''){
          if (substr($myclid,0,1)=='0') {
             $myclid = substr($myclid,1,strlen($myclid));
          } 
          if (strlen($myclid)<=10){
             $kriter1 .= $cdb->field_query($kriter1,"CONCAT(PHONES.CITY_CODE,PHONES.PHONE_NUMBER)" ,"=","'$myclid'");
          }else{
             $kriter1 .= $cdb->field_query($kriter1,"CONCAT(PHONES.COUNTRY_CODE,PHONES.CITY_CODE,PHONES.PHONE_NUMBER)","=",  "'$myclid'");
          }
          if($kriter1 == ""){ return "";}
          $sql_str2 = "SELECT CONCAT(CONTACTS.NAME,' ',CONTACTS.SURNAME)AS CONTACT
                         FROM CONTACTS 
                       INNER JOIN PHONES ON CONTACTS.CONTACT_ID=PHONES.CONTACT_ID
                       WHERE ".$kriter1;
          if (!($cdb->execute_sql($sql_str2,$result2,$error_msg))){
             print_error($error_msg);
             exit;
          }
//          echo $sql_str2;die;
          if(mysql_num_rows($result2) == 0){
            $contact ='';
          }else{
            $row2=mysql_fetch_object($result2);
            $contact = $row2->CONTACT;
          }
       }else{
          $contact ='';
       }
       return $contact;
    }
    //end of get_contact func.
   
    // maximum acceptable duration aliniyor...
    $max_acc_dur = get_site_prm('MAX_ACCE_DURATION',$SITE_ID) * 60;

    $report_type = "Gelen Cagriý Rapor";
    if ($act == "src") {
       $kriter = "";

       //Temel kriterler verinin hiýziý gmesi icin basa konuldu.
       //Bu mutlaka olmali ilgili siteyi belirliyor.
       $kriter .= $cdb->field_query($kriter,"CDR_MAIN_DATA.SITE_ID","=","$SITE_ID"); 
       //Bu mutlaka olmali; hatasiz kayit durumunu gosteriyor.
       $kriter .= $cdb->field_query($kriter,"ERR_CODE","=","0");
       //Bu mutlaka olmaliý Disýþ arama olgunu gosteriyor.
       $kriter .= $cdb->field_query($kriter,"CALL_TYPE","=","2");
       //Bu mutlaka olmaliý.Dýþ arama ogunu gosteriyor.
       $kriter .= $cdb->field_query($kriter,"DURATION","<","$max_acc_dur");
       $kriter .= $cdb->field_query($kriter,"CDR_MAIN_DATA.TER_DN","<>","''");
       //Bu mutlaka olmaliý.Hataiýz kaiýt oldgunu gosteriyor.

       add_time_crt();  //Zaman kriteri 

      //Genel Arama Bilgileri. Bu alanlar baþlýk basýlmasýnda kullanýlacaktýr.
      if ($TER_DN <> '') 
         $ter = 'Yes';
      if ( (($DEPT_ID[0] == '-1') || ($DEPT_ID[0] == '')) && count($DEPT_ID) <= 1){
         $dept = '';
      }else{
         $dept = 'Yes';
      }
      if ($CLID <> '') 
         $clid = 'Yes';
      if ($DURATION <> ''){
         $dur = 'Yes';
         $DURATION_SN = $DURATION * 60;
      }
      //Aranan dahili kriteri olusþturuluyor.
      if($TER_DN){
         $TER_ARRAY = explode(",", $TER_DN);
         $in_str = "";
         for ( $i = 0; $i < count($TER_ARRAY); $i++){
            if ($in_str == ""){
               if ($TER_ARRAY[$i] != ""){
                  $in_str .= "'".$TER_ARRAY[$i]."'";
               }    
            }else{
               if ($TER_ARRAY[$i] != ""){
                  $in_str .= ", '".$TER_ARRAY[$i]."'";
               }
            }
        }
      }

      if ($in_str != ""){
         if ($kriter == ""){
            $kriter .= " CDR_MAIN_DATA.TER_DN IN (".$in_str.")";
         }else{
            $kriter .= " AND CDR_MAIN_DATA.TER_DN IN (".$in_str.")";
         }
      }else{
         $ter = '';
      }

      $in_str = "";
      if (is_array($DEPT_ID)){
         if ((($DEPT_ID[0] == '-1') || ($DEPT_ID[0]=='')) && count($DEPT_ID)==1){
            //Nothing to do
         }else{
           for ($i = 0; $i < count($DEPT_ID); $i++){
               if ($in_str != ""){
                  if ($DEPT_ID[$i] != "-1" && $DEPT_ID[$i] != "")
                     $in_str .= ", ".$DEPT_ID[$i];
               }else{
                  if ($DEPT_ID[$i] != "-1" && $DEPT_ID[$i] != "")
                     $in_str .= $DEPT_ID[$i];
               }
          }
         }
      }

      if ($in_str != ""){
         if ($kriter == ""){
            $kriter .= " EXTENTIONS.DEPT_ID IN (".$in_str.")";
         }else{
            $kriter .= " AND EXTENTIONS.DEPT_ID IN (".$in_str.")";
         }
      }
   
      $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.CLID", "LIKE", "'%$CLID%'");
      $kriter .= $cdb->field_query($kriter, "(CDR_MAIN_DATA.DURATION)", ">", "'$DURATION_SN'");
          
      $sql_str  = "SELECT CDR_MAIN_DATA.CDR_ID, CDR_MAIN_DATA.TER_DN, DATE_FORMAT(TIME_STAMP,\"%d.%m.%Y\") AS MY_DATE, 
                     CDR_MAIN_DATA.TER_DN, DATE_FORMAT(TIME_STAMP,\"%H:%i:%s\") AS MY_TIME, EXTENTIONS.DESCRIPTION,
                     CDR_MAIN_DATA.DURATION AS DURATION, CDR_MAIN_DATA.ORIG_TRUNK_MEMBER,CDR_MAIN_DATA.CLID, EXTENTIONS.DEPT_ID,DEPTS.DEPT_NAME,  
                     EXTENTIONS.DESCRIPTION
                   FROM CDR_MAIN_INB as CDR_MAIN_DATA 
                   LEFT JOIN EXTENTIONS ON CDR_MAIN_DATA.TER_DN = EXTENTIONS.EXT_NO AND 
                                           CDR_MAIN_DATA.SITE_ID = EXTENTIONS.SITE_ID
                   LEFT JOIN DEPTS ON DEPTS.DEPT_ID = EXTENTIONS.DEPT_ID
				   ";
       if ($dept_crt)
            $kriter2 .= str_replace("CDR_MAIN_DATA","CDR_MAIN_DATA",str_replace("ORIG_DN","TER_DN",$dept_crt));
       if ($usr_crt)
            $kriter2 .= str_replace("CDR_MAIN_DATA","CDR_MAIN_DATA",str_replace("ORIG_DN","TER_DN",$usr_crt));
       if ($unrep_exts_crt)
            $kriter2 .= str_replace("CDR_MAIN_DATA","CDR_MAIN_DATA",str_replace("ORIG_DN","TER_DN",$unrep_exts_crt));
      
      $sql_str .= " WHERE ".$kriter." ".$kriter2;
       //echo $sql_str;exit;
      switch ($order) {
        case 'Dahili':
          $order ='TER_DN';
          break;  
           case 'HatNo':
          $order ='ORIG_TRUNK_MEMBER';
          break;  
        case 'Arayan':
          $order ='CLID';
          break;
        case 'tarih':
          $order ='TIME_STAMP';
          break;
        case 'saat':
          $order ='MY_TIME';
          break;
        case 'sure';
          $order ='DURATION ';
          break;
        default:
          $order='TIME_STAMP';
      }
     
      if ($order) {
         $sql_str .= " ORDER BY ".$order." ".$sort_type; 
      }

      if ($record <> '' || is_numeric($record)) {
         $sql_str .= " LIMIT 0,". $record ;
      }
      //echo $sql_str;exit;
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      $sql_str2 = "SELECT UNREP_EXT_ID ,UNREP_EXT_NO FROM UNREP_EXTS";
      if (!($cdb->execute_sql($sql_str2,$result2,$error_msg))){
         print_error($error_msg);
        exit;
      }
      $arr_ext = array();
      while ($res2 = mysql_fetch_array($result2)){
         $arr_ext[] = $res2["UNREP_EXT_NO"];
      }
	  
	  $sql_str3 = "SELECT SITE_NAME FROM SITES WHERE SITE_ID=$SITE_ID";
	  $cdb->execute_sql ($sql_str3,$result3,$error_msg);
	  $row3=mysql_fetch_object($result3);
            $SITE_NAME = $row3->SITE_NAME;
	  
?>
<br>
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
		  <TD width="50%" align=center CLASS="header"><?echo $SITE_NAME;?><BR>GELEN ÇAÐRI RAPORU</TD>
		  <TD width="25%" align=right><img SRC="<?=$SERVER_ROOT?><?=IMAGE_ROOT?>company.gif"></TD>
        </TR>
      </TABLE>
    </td>
  </tr>
  <tr>
    <td>
      <table width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
          <td width="50%">
            <table>
              <tr  <?if ($ter<>'Yes') echo "style=\"display:none;\""?>>
                <td class="rep_header" nowrap width="20%" valign="top">Dahili:</td>
                <td width="80%" valign="top">
                <?if (is_array($TER_ARRAY)){ 
                     for ($i = 0; $i < count($TER_ARRAY); $i++){
                         if (is_numeric($TER_ARRAY[$i])) {?>
                           <?echo $TER_ARRAY[$i];?> - <? echo get_ext_name($TER_ARRAY[$i]).";";
                         }
                     }
                 }
               ?>
              </td>
            </tr> 
            <tr  <?if ($dept<>'Yes') echo "style=\"display:none;\""?>>
              <td class="rep_header" nowrap width="20%" valign="top">Departman:</td>
              <td width="80%" valign="top">
              <?if (is_array($DEPT_ID)) { 
                  for ($i = 0; $i < count($DEPT_ID); $i++) {
                      if(is_numeric($DEPT_ID[$i])) {?>
                      <? echo get_dept_name($DEPT_ID[$i],$SITE_ID).";";}
                      }
                  }?>
              </td>
           </tr>
           <tr <?if ($clid<>'Yes') echo "style=\"display:none;\""?>>
              <td class="rep_header" nowrap width="20%">Arayan:</td>
              <td width="80%"><? echo $CLID;?>
           </tr>
         </table>  
       </td>
       <td width="50%">
          <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr <?if ($dur<>'Yes') echo "style=\"display:none;\""?>>
              <td class="rep_header" align="right" nowrap width="40%">Süre:</td>
              <td width="60%"><?echo $DURATION;?></td>
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
           ?>
          <tr>
              <td class="rep_table_header" width="25%">Dahili<a style="cursor:hand;" onclick="javascript:submit_form('Dahili');"><img src="<?=IMAGE_ROOT?><?=($order=="TER_DN")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="8%">Hat No<a style="cursor:hand;" ><img src="<?=IMAGE_ROOT?><?=($order=="ORIG_TRUNK_MEMBER")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="15%">Arayan No<a style="cursor:hand;"><img src="<?=IMAGE_ROOT?><?=($order=="CLID")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="17%">Arayan<a style="cursor:hand;"><img src="<?=IMAGE_ROOT?><?=($order=="CLID")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="15%">Tarih<a style="cursor:hand;" onclick="javascript:submit_form('tarih');"><img src="<?=IMAGE_ROOT?><?=($order=="TIME_STAMP")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="10%">Saat<a style="cursor:hand;" onclick="javascript:submit_form('saat');"><img src="<?=IMAGE_ROOT?><?=($order=="MY_TIME")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
              <td class="rep_table_header" width="10%">Süre<a style="cursor:hand;" onclick="javascript:submit_form('sure');"><img src="<?=IMAGE_ROOT?><?=($order=="DURATION")?$sort_gif:"sort.gif"?>" align="absmiddle" ></a></td>
          </tr>
        <tr>
          <td colspan="8" bgcolor="#000000" height="1"></td>
        </tr>
      <?
        $my_dur = 0;
        $my_amount = 0; 
        $i = 0;
        $csv_data[0][0] = "";
            
        if (mysql_num_rows($result)>0)
           mysql_data_seek($result,0);
           $csv_data[0][0] = "Dahili";
           $csv_data[0][1] = "HatNo";
           $csv_data[0][2] = "Arayan No";
           $csv_data[0][3] = "Arayan" ;
           $csv_data[0][4] = "Tarih";
           $csv_data[0][5] = "Saat";
           $csv_data[0][6] = "Süre";

           while ($row = mysql_fetch_object($result)){
                if (!in_array ($row->TER_DN, $arr_ext)){ 
                   $i++;
                   $csv_data[$i][0] = "$row->TER_DN - $row->DESCRIPTION";
                   $csv_data[$i][1] = $row->ORIG_TRUNK_MEMBER;
                   $csv_data[$i][2] = $row->CLID;
                   $csv_data[$i][3] = get_contact($row->CLID);
                   $csv_data[$i][4] = $row->MY_DATE;
                   $csv_data[$i][5] = $row->MY_TIME;
                   $csv_data[$i][6] = calculate_all_time($row->DURATION);

                   $bg_color = "E4E4E4";   
                   if ($i % 2) $bg_color ="FFFFFF";
                   if ($show_clid <> 1){
                      echo " <tr  BGCOLOR=$bg_color>";
                      echo " <td class=\"rep_td\">$row->TER_DN - $row->DESCRIPTION</td>";
                      echo " <td class=\"rep_td\">$row->ORIG_TRUNK_MEMBER</td>";
                      echo " <td class=\"rep_td\" ALIGN=right>".str_replace("F","",$row->CLID)."</td>";
                      echo " <td class=\"rep_td\">".get_contact(str_replace("F","",$row->CLID))."</td>";
                      echo " <td class=\"rep_td\">$row->MY_DATE</td>";
                      echo " <td class=\"rep_td\">$row->MY_TIME</td>";
                      echo " <td class=\"rep_td\">".calculate_all_time($row->DURATION);
                      echo "</tr>";
                      $my_dur = $my_dur + $row->DURATION;
                      $my_amount = $my_amount + 1;
                   }else if ($show_clid == 1){
                      if ($row->CLID <> ''){
                         echo " <tr  BGCOLOR=$bg_color>";
                         echo " <td class=\"rep_td\">$row->TER_DN - $row->DESCRIPTION</td>";
                         echo " <td class=\"rep_td\">$row->ORIG_TRUNK_MEMBER</td>";
                         echo " <td class=\"rep_td\" ALIGN=right>".str_replace("X","",$row->CLID)."</td>";
                         echo " <td class=\"rep_td\">".get_contact(str_replace("X","",$row->CLID))."</td>";
                         echo " <td class=\"rep_td\">$row->MY_DATE</td>";
                         echo " <td class=\"rep_td\">$row->MY_TIME</td>";
                         echo " <td class=\"rep_td\">".calculate_all_time($row->DURATION);
                         echo "</tr>";
                         $my_dur = $my_dur + $row->DURATION;
                         $my_amount=$my_amount + 1;
                      }
                   }
                }
            }
            $i++;
            $csv_data[$i][4] = "Toplam Görüþme Adedi";
            $csv_data[$i][5] = $my_amount;
            $i++;
            $csv_data[$i][4] = "Toplam Süre Adedi";
            $csv_data[$i][5] = $my_dur;
            csv_out($csv_data, "../../temp/inbound_calls.csv"); 
                   
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
<? } // End of if ($act == 'src')
?>

<?
###################################
# CSV OUT 
# Date: 
###################################
function xcsv_out($data, $filename) {
  if(is_array($data))
  {
  foreach ($data as $k1 => $v1) {
    foreach($data[$k1] as $k2 => $v2) {
      $file .= $v2.";";
    }
    $file = substr($file, 0, -1);
    $file .= "\n";
  }

  //Header ( "Content-Type: application/octet-stream");
  //Header ( "Content-Length: ".filesize(2000)); 
  //Header( "Content-Disposition: attachment; filename=$filename"); 
  //echo $file;
      $fp = fopen($filename, "w+");
      fwrite($fp, $file);
      return true;
  }
}


 $fd = fopen($DOCUMENT_ROOT."/temp/inbound.html", w);
 fwrite($fd,ob_get_contents());
 
?>
 <?if($CSV_EXPORT==1){?>
 <iframe SRC="/csv_download.php?filename=inbound_calls.csv" WIDTH=0 HEIGHT=0 ></iframe>
 <?}?>
 

<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $cUtility = new Utility();
    $cdb = new db_layer(); 
    $conn = $cdb->getConnection();
     function require_valid_login1(){
       global $my_s;
       global $DOCROOT;
       global $SCRIPT_NAME;
       global $HTTP_HOST;
       $my_s = new Session;
       $my_s->test_login();
       if (!$my_s->logged_in){   
         header("Location:/special/login_frm.php.php");
         exit;
       }
       $my_s->pconnect();
     }
     require_valid_login1();

    //AUth Code kontrölü
    $sql_str="SELECT USERNAME,PASSWORD FROM USERS WHERE USERNAME='".$SESSION["username"]."' AND PASSWORD=PASSWORD('".$AUTH_CODE."')"; 
    //echo $sql_str;exit;
	if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    if (!mysql_num_rows($result)>0){
          cc_page_meta();
          echo "<center><br>\n";
          echo("Bu Otorizasyon kodu size ait deðil gözüküyor. Lütfen sistem yöneticinize baþvurunuz.<br><br>\n");
          echo "<a href=\"javascript:history.go(-1)\"><img src=\"".IMAGE_ROOT."/geri_don.gif\" border=0></a>";
          echo "<center>\n";
          exit;
	}
	
    $kriter2 = "";
    $check_origs = false;
    $usr_crt = "";
    $AUTH_CODE_CNTL = 1;
	$SITE_ID = $SESSION['site_id'];
    if(!$SITE_ID){$SITE_ID = 1;}
//    $usr_crt  = get_auth_crt($SESSION["user_id"]);
    ob_start();
    cc_page_meta();
    echo "<center>";

    //Joinden kaçmak için Lokasyon tablosundaki bilgiler alýnýyor.
    $sql_str="SELECT Locationid,LocationName FROM TLocation"; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $arr_location = array();
    while ($row=mysql_fetch_object($result)){
        $arr_location[$row->Locationid] = $row->LocationName;
    }
    //Joinden kaçmak için Çaðrý türündeki tablosundaki bilgiler alýnýyor.
    $sql_str="SELECT LocationTypeid, LocationType FROM TLocationType";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
    $arr_location_type = array();
    while ($row=mysql_fetch_object($result)){
        $arr_location_type[$row->LocationTypeid] = $row->LocationType;
    }

    //Joinden kaçmak için Auth_Code tablosundaki bilgiler alýnýyor.
    $sql_str="SELECT AUTH_CODE,AUTH_CODE_DESC FROM AUTH_CODES WHERE SITE_ID=".$SITE_ID; 
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
    $arr_auth_code = array();
    while ($row = mysql_fetch_object($result)){
        $arr_auth_code[$row->AUTH_CODE] = $row->AUTH_CODE_DESC;
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
    $local_country_code = get_country_code($SITE_ID);//Lokal ülke kodu.
	
    $report_type="Giden Çaðrý Raporu";

    if ($act == "src") {
     
      $kriter = "";

      //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
      $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.        
      $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
      $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.

     
      //**Bunlar birlikte olmalý ve bu sýrada olmalý.
      add_time_crt();//Zaman kriteri 
      $CDR_MAIN_DATA = getTableName($t0,$t1);
      if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";
      //**

     //Genel Arama Bilgileri. Bu alanlar baþlýk basýlmasýnda kullanýlacaktýr.
     if ($ORIG_DN <> '') 
       $orig = 'Yes';
     if ((($DEPT_ID[0] == '-1') || ($DEPT_ID[0]=='')) && count($DEPT_ID)<=1){
       $dept = '';
     }else{
       $dept = 'Yes';
     }

    $provider_join="";//Ne olur ne olmaz bu alan burada boþaltýlsýn.
    if (($LocationTypeid <> '-1') && ($LocationTypeid<>'')) 
        $code_type='Yes';
    if ($LocalCode <> '')
        $local='Yes';
    if ($DIGITS <> '')
        $digits='Yes';
    if ($CountryCode <> '' && $CountryCode<>$local_country_code)
        $country='Yes';
    if ($AUTH_CODE <> '')
        $auth='Yes';
    if ($ACCESS_CODE <> '')
        $access='Yes';
    //if (($TelProviderid <> '-1') && ($TelProviderid<>'')){
    //    $tel_provider='Yes';
    //    $provider_join =" LEFT JOIN TTelProvider ON CDR_MAIN_DATA.TO_PROVIDER_ID = TTelProvider.TelProviderid ";
    //}
    if ($DURATION <> ''){
        $dur='Yes';
        $DURATION_SN = $DURATION*60;
     }

/*     if($ORIG_DN){
        $ORIG_ARRAY = explode(",", $ORIG_DN);
        $in_str = "";
        for($i=0;$i<count($ORIG_ARRAY);$i++){
            if($in_str == ""){
                if(($ORIG_ARRAY[$i])!=""){
                     $in_str .= "'".$ORIG_ARRAY[$i]."'";
                }    
            }else{
                if(($ORIG_ARRAY[$i])!=""){
                    $in_str .= ", '".$ORIG_ARRAY[$i]."'";
                }
            }
        }
    }

    if($in_str != ""){
        if($kriter == ""){
            $kriter .= " CDR_MAIN_DATA.ORIG_DN IN (".$in_str.")";
        }else{
            $kriter .= " AND CDR_MAIN_DATA.ORIG_DN IN (".$in_str.")";
        }
    }else{
        $orig = '';
    }

    $in_str = "";
    if(is_array($DEPT_ID)){
        if ((($DEPT_ID[0] == '-1') || ($DEPT_ID[0]=='')) && count($DEPT_ID)==1){
        //Nothing to do
        }else{
            for($i=0;$i < count($DEPT_ID);$i++){
                if($in_str != ""){
                    if($DEPT_ID[$i] != "-1" && $DEPT_ID[$i] != "")
                        $in_str .= ", ".$DEPT_ID[$i];
                }else{
                    if($DEPT_ID[$i] != "-1" && $DEPT_ID[$i] != "")
                        $in_str .= $DEPT_ID[$i];
                }
            }
        }
    }

    if($in_str != ""){
        if($kriter == ""){
            $kriter .= " EXTENTIONS.DEPT_ID IN (".$in_str.")";
        }else{
            $kriter .= " AND  EXTENTIONS.DEPT_ID IN (".$in_str.")";
        }
    }
*/
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocationTypeid"   ,"=",        "$LocationTypeid");
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.TO_PROVIDER_ID"   ,"=",        "$TelProviderid");
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.LocalCode"        ,"=",        "'$LocalCode'");
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.CountryCode"      ,"=",        "'$CountryCode'");
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.PURE_NUMBER"      ,"LIKE",     "'%$DIGITS%'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.AUTH_ID"          ,"=",          "'$AUTH_CODE'");
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.ACCESS_CODE"      ,"=",        "'$ACCESS_CODE'");
        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.PRICE"            ,">",          "'0'");
//        $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.DURATION"         ,">",          "'$DURATION_SN'");
/*        if($MEMBER_NO != -1){
          $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.TER_TRUNK_MEMBER"          ,"=",       "'$MEMBER_NO'");
        }*/
//        if($FROM_PROVIDER_ID != -1){
//          $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.FROM_PROVIDER_ID"          ,"=",       "'$FROM_PROVIDER_ID'");
//        }
        
        
//////////////////////Auth code control ////////////////////////////      
      if($AUTH_CODE_CNTL==1){
            $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.AUTH_ID"          ,"<>",       "' '");
      }else if($AUTH_CODE_CNTL==2){
            $kriter .= $cdb->field_query($kriter, "CDR_MAIN_DATA.AUTH_ID"          ,"=",       "' '");
      }          

    $sql_loc="SELECT SITE_CODE,SITE_NAME, PRICE_FACTOR FROM SITES WHERE SITE_ID = $SITE_ID"; 
    if (!($cdb->execute_sql($sql_loc,$rslt_loc,$error_msg))){
        print_error($error_msg);
        exit;
    }
    $row_loc=mysql_fetch_object($rslt_loc);
    $LOC_CODE = $row_loc->SITE_CODE;
    $company  = $row_loc->SITE_NAME;
    $prc_fct  = $row_loc->PRICE_FACTOR;
    $fih_join_prm = " LEFT ";
    $fih_where_prm = "";
    //Fihrist kayýtlaý isteniyorsa Contacts ile INNER JOIN olmalý
    $fihrist_state = "";
    //Country code mutlaka dolu olduðundan bu alan boþsa fihrist karþýlýðý yoktur.
    $write_to_csv = "";
    //Trunk raporlarý üzerinden gelmekte. Bir trunktan yapýlan aramalar.
    $sql_str  = "SELECT CDR_MAIN_DATA.CDR_ID,LTRIM(CDR_MAIN_DATA.ORIG_DN) AS ORIG_DN,DATE_FORMAT(CDR_MAIN_DATA.MY_DATE,\"%d/%m/%Y\") AS MY_DATE,
                   DATE_FORMAT(TIME_STAMP,\"%H:%i:%s\") AS MY_TIME,CDR_MAIN_DATA.LocationTypeid,
                   CDR_MAIN_DATA.DURATION, CDR_MAIN_DATA.Locationid,
                   CDR_MAIN_DATA.CountryCode, CDR_MAIN_DATA.LocalCode, CDR_MAIN_DATA.PURE_NUMBER, 
                   CDR_MAIN_DATA.AUTH_ID, (CDR_MAIN_DATA.PRICE*$prc_fct) AS PRICE, TIME_STAMP
              ";
   $sql_str1 = " FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA ";
   if ($kriter != ""){
     $sql_str2 .= "  WHERE ".$kriter.$fih_where_prm;
   }else{
     echo "Lütfen Kriter Seçiniz";
     exit;
   } 

//echo $sql_str.$sql_str1.$sql_str2;exit;
   if (!($cdb->execute_sql($sql_str.$sql_str1.$sql_str2,$result,$error_msg))){
     print_error($error_msg);
     exit;
   }
?>
<div style="width: 77; height: 28; position: absolute; left: 709; top: 3; display: none">
  <input type="button" value="Anasayfa" onclick="javascript:history.go(-1);">
</div>
<table width="95%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" align="center" class="rep_header" align="center">
    <TABLE BORDER="0" WIDTH="100%">
      <TR>
        <TD><a href="http://www.crystalinfo.net" target="_blank"><img border="0" SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>logo2.gif" ></a></TD>
        <TD width="50%" align=center CLASS="header"><?echo $company;?><BR>GÝDEN ÇAÐRI RAPORU</TD>
        <TD width="25%" align=right><img SRC="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>company.gif"></TD>
     </TR>
   </TABLE>
   </td>
 </tr>
 <tr>
   <td width="100%" class="rep_header" align="right">
   <table width="100%" border="0">
     <tr>
     <?if($t0!=""){?>
       <td width="50%" class="rep_header" align="left">Tarih (<?=date("d/m/Y",strtotime($t0))?>
         <?if($t1!=""){?>
         <?echo (" - ".date("d/m/Y",strtotime($t1)));}?>)
       </td>
     <?}?>
       <td width="50%" class="rep_header" align="right" cellspacing=0 cellpadding=0></td>
     </tr>
   </table>
   </td>
 </tr>
 <tr>
   <td>
   <table width="100%" cellspacing="0" cellpadding="0" border="0">
     <tr>
       <td width="50%" valign="top">
       <table>
         <tr  <?if ($orig<>'Yes') echo "style=\"display:none;\""?>>
           <td class="rep_header" nowrap width="20%" valign="top">
           <?if($AUTH_CODE_CNTL==1)
             echo "Dahili :";
             else
             echo "Dahili :";?>
           </td>
         </tr>
         <tr <?if ($auth<>'Yes') echo "style=\"display:none;\""?>>
           <td class="rep_header" align="left" nowrap width="40%">Auth. Kodu:</td>
           <td width="60%"><?echo $AUTH_CODE." - ".$arr_auth_code[$AUTH_CODE];?></td>
         </tr>
       </table>    
       </td>
       <td width="50%">
       <table width="100%" cellspacing="0" cellpadding="0" border="0">
         <tr>
           <td width="50%" valign="top">
           <table width="100%" cellspacing="0" cellpadding="0" border="0">
             <tr <?if($hafta=="3") echo "style=\"display:none;\""?>>
               <td class="rep_header" align="left" nowrap width="40%" colspan="2"><?=($hafta=="1")?"Hafta Ýçi":"Hafta Sonu"?></td>
             </tr>
             <tr <?if($hh0==-1 && $hh1 ==-1) echo "style=\"display:none;\""?>>
               <td class="rep_header" align="left" nowrap width="40%">Saat Dilimi :</td>
               <td width="60%" align="right"><?=($hh0==-1)?"":$hh0.":".$hm0?> - <?=($hh1==-1)?"":$hh1.":".$hm1?></td>              
             </tr>
           </table>    
           </td>
		 </tr>
         <tr>
		  <td colspan=2 align=right>
          <table cellspacing=0 cellpadding=0>
            <tr>
              <td><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/top02.gif" border=0></td>
              <td><a href="javascript:mailPage()"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/mail.gif" border=0 title="Mail"></a></td>
              <td><a href="javascript:history.back(1);"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
              <td><a href="javascript:history.forward(1);"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/ileri.gif" border=0 title="Ýleri"></a></td>
              <td><a href="javascript:document.all('sort_me').submit();"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
              <td><a href="javascript:window.print();"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazdýr"></a></td>
              <td><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?>report/top01.gif" border=0></td>
            </tr>
          </table>
          </td>
		</tr>
      </table>
      </td>
    </tr>
  </table>
  </td>   
</tr>
</td>
<tr>
  <td>
  <table width="100%" border="0" bgcolor="C0C0C0" cellspacing="1" cellpadding="0" >
            <?
            if($sort_type=="desc")
                $sort_gif = "report/top.gif";    
            else
                $sort_gif = "report/down.gif";
            ?>
                <tr>
<?//f($CSV_EXPORT == 2){?>
                    <td class="rep_table_header" width="30%" ALIGN="center"><?echo $AUTH_CODE_CNTL==1?"Dahili":"Dahili"?></td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Tarih</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Saat</td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Süre</td>
                    <td class="rep_table_header" width="15%" ALIGN="center">Telefon</td>
                    <td class="rep_table_header" width="20%" ALIGN="center">Aranan</td>
                    <td class="rep_table_header" width="12%" ALIGN="center">Ücret</td>
<?//}else{?>
<!--                    <td class="rep_table_header" width="30%" ALIGN="center"><?echo $AUTH_CODE_CNTL==1?"Auth Code":"Dahili"?><a style="cursor:hand;" onclick="javascript:submit_form('Dahili');"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?><?=($order=="Dahili")?$sort_gif:"sort.gif"?>" align="absmiddle" Title="Dahiliye Göre Sýrala"></a></td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Tarih<a style="cursor:hand;" onclick="javascript:submit_form('tarih');"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?><?=($order=="tarih")?$sort_gif:"sort.gif"?>" align="absmiddle" Title="Tarihe Göre Sýrala"></a></td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Saat<a style="cursor:hand;" onclick="javascript:submit_form('saat');"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?><?=($order=="saat")?$sort_gif:"sort.gif"?>" align="absmiddle" Title="Saat Göre Sýrala"></a></td>
                    <td class="rep_table_header" width="10%" ALIGN="center">Süre<a style="cursor:hand;" onclick="javascript:submit_form('sure');"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?><?=($order=="sure")?$sort_gif:"sort.gif"?>" align="absmiddle" Title="Süreye Göre Sýrala"></a></td>
                    <td class="rep_table_header" width="15%" ALIGN="center">Telefon<a style="cursor:hand;" onclick="javascript:submit_form('number');"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?><?=($order=="number")?$sort_gif:"sort.gif"?>" align="absmiddle" Title="Numaraya Göre Sýrala"></a></td>
                    <td class="rep_table_header" width="20%" ALIGN="center">Aranan</td>
                    <td class="rep_table_header" width="12%" ALIGN="center">Ücret<a style="cursor:hand;" onclick="javascript:submit_form('ucret');"><img src="http://<?=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT']?><?=IMAGE_ROOT?><?=($order=="ucret")?$sort_gif:"sort.gif"?>" align="absmiddle" Title="Tutara Göre Sýrala"></a></td>
<?//}?>-->
                </tr>
                <tr>
                    <td colspan="10" bgcolor="#000000" height="1"></td>
                </tr>
            <?
///////////////////////////////////////////////////////////////77
      $my_dur=0;
      $my_amount=0; 
      $my_pr=0; 
      $i;
      if (mysql_num_rows($result)>0)
        mysql_data_seek($result,0);
        $cnt=0;
        $numeric=0;
        switch ($order){
          case 'Dahili':
            $order1 ='ORIG_DN';
            break;  
          case 'tarih':
            $order1 ='TIME_STAMP';
            break;
          case 'saat':
            $order1 ='MY_TIME';
            break;
          case 'sure':
            $order1 ='DURATION';
            $numeric=1;
            break;
          case 'contact':
            $order1 ='CONTACT_ID';
            break;
          case 'local':
            $order1 ='LocalCode';
            break;
          case 'number':
            $order1 ='PURE_NUMBER';
            break;
          case 'ucret':
            $order1 ='PRICE';
            $numeric=1;
            break;
          default:
            $order1='MY_DATE';
        }

        function cmp ($a, $b) {
          global $order1,$numeric;
          if($numeric){
            if ($a[$order1] < $b[$order1]) 
              return  1;
            else 
              return -1;
          }
          return strcmp($a[$order1], $b[$order1]);
        }

        function cmp_desc ($a, $b) {
          global $order1,$numeric;
          if($numeric){
            if ($a[$order1] > $b[$order1]) 
              return  1;
            else 
              return -1;
          }    
          return strcmp($b[$order1], $a[$order1]);
        }

        while($row_array[$cnt] = mysql_fetch_array($result)){$cnt++;}
          if($sort_type=="desc"){
            usort($row_array, "cmp_desc");
          }else{
            usort($row_array, "cmp");
          }
          foreach($row_array as $row){
            $m++;
            if(is_array($row)){
              $i++;
              $bg_color = "E4E4E4";   
              if($i%2) $bg_color ="FFFFFF";
              if ($row["CountryCode"] == $local_country_code){
                $my_place = $arr_location[$row["Locationid"]];
                if ($row["LocalCode"] == $LOC_CODE || $row["LocalCode"]==""){//Þehiriçi Kabul edilen kod.
                  $my_place = "Þehiriçi";
                  $TEL_NUMBER = $LOC_CODE ." ".substr($row["PURE_NUMBER"],0,7);
                }else{
                  $TEL_NUMBER = $row["LocalCode"]." ".substr($row["PURE_NUMBER"],0,7);
                }
              }else{
                $my_place = $arr_location[$row["Locationid"]];
                $TEL_NUMBER = $row["CountryCode"]." ".$row["LocalCode"]." ".$row["PURE_NUMBER"];
              } 
              $called = $arr_location[$row["Locationid"]];
              //if($AUTH_CODE_CNTL==1){
              //  $name = substr($arr_auth_code[$row["AUTH_ID"]],0,18)."(".$row["ORIG_DN"].")";
              //}else{
                $name = $row["ORIG_DN"]." - ".substr($row["DESCRIPTION"],0,18);
              //}
              echo " <tr  BGCOLOR=$bg_color>";
              echo " <td class=\"rep_td\">".$name."</td>";
              echo " <td class=\"rep_td\"><a class=\"a1\" style=\"cursor:hand;\" title=\"Kayýt Detayý\"><span onclick=\"javascript:popup('/audit/cdr_detail.php?id=".$row["CDR_ID"]."','report_outb_prn',600,300)\">".$row["MY_DATE"]."</span></a></td>";
              echo " <td class=\"rep_td\">" .$row["MY_TIME"]."</td>";                 
              echo " <td class=\"rep_td\">".calculate_all_time($row["DURATION"])."</td>";     
              echo " <td class=\"rep_td\" align=\"right\"><a class=\"a1\" href=\"javascript:drill_down_pn('". $row["PURE_NUMBER"]."')\">$TEL_NUMBER</a></td>";
              echo " <td class=\"rep_td\" >&nbsp&nbsp ".$called."</td>";
              echo " <td align=right class=\"rep_td\">".write_price($row["PRICE"])."</td>";
              echo "</tr>";
            }
            $my_dur=$my_dur + $row["DURATION"];
            $my_amount=$my_amount + 1;
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
     <tr>
        <td><?echo $alert;?></td>
    </tr>
</table>    
<?}?>
<script language="JavaScript">
    function submit_form(sortby){
        document.all('sort_me').action='report_outb_prn.php?act=src&order=' + sortby;       
        document.all('DEPT_ID').name = 'DEPT_ID[]';     
        document.all('sort_me').submit();
   }
    function drill_down_pn(pure_n){
        document.all('DIGITS').value = pure_n;
        document.all('sort_me').action='report_outb_prn.php?act=src';       
        document.all('sort_me').submit();
    }
   
   window.setInterval('document.all(\'sort_me\').submit()', 300000);
</script>

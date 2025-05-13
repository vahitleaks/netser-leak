<?
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  require_valid_login();
  session_cache_limiter('nocache');
  $conn = $cdb->getConnection();
  cc_page_meta(0);
  check_right("SITE_ADMIN");
  ob_start();
  $cmp = get_system_prm("COMPANY_NAME");
  $server_ip = get_system_prm("SERVER_IP");
  if (!right_get("SITE_ADMIN"))
     $SITE_ID = $SESSION['site_id'];
  if (right_get("SITE_ADMIN") && ($SITE_ID=="" || $SITE_ID=="-1"))
     $SITE_ID = $SESSION['site_id'];
  setlocale(LC_TIME, 'tr_TR');

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
  
	     $DATA1="";
        $DATA1 .= "<center>
             <form name='acc_code' method='post' action='report_acc_code.php'>
             <table border=0 width=70% height=20%>
               <tr>
                 <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"http://".$server_ip.IMAGE_ROOT."logo2.gif\"></a></TD>
                 <td align=center class=header>".$cmp."<br><br>Eriþim Kodu Özet Raporu<br><br>".
                 strftime("%B", mktime(0,0,0,date("m")-1,date("d"),date("y")))."</td>";
        $DATA1 .= " <td align=right><img src=\"http://".$server_ip.IMAGE_ROOT."company.gif\"></td>
                    </tr>
                    <tr class='form'>
                     <td class='td1_koyu' colspan='3' width='30%' align='center'>Site Adý&nbsp;&nbsp;&nbsp;
                       <select name='SITE_ID' class='select1' style='width:125' ";
        if (!right_get("SITE_ADMIN")){
          $DATA1 .= "disabled";
        }
        $DATA1 .= "onchange=\"javascript:submit_me()\">";
        $DATA1 .=  "    <option ";
        if ($site_id == -1 || !$site_id){
           $DATA1 .= " selected ";
        }
        $DATA1 .= " value='-1'>Tüm Siteler</option>";
        $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES";
        $DATA1 .= $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $site_id);
        $DATA1 .= "        </select>
                      </td>
                     </tr>   
                    </table>
                    <table cellspacing=1 cellpadding=2 width= 70%>
                      <tr align=center>
                       <td width=70% height=22 colspan=4 class=\"header\"></td>
                      </tr>";
        $t0 = strftime("%m_%Y", mktime(0,0,0,date("m")-1,date("d"),date("y")));
        $CDR_MAIN_DATA = "CDR_MAIN_".$t0;
        if(!checkTable($CDR_MAIN_DATA))
            $CDR_MAIN_DATA = "CDR_MAIN_DATA";

         $strsql = "SELECT SITE_ID, ACCESS_CODE, SUM(PRICE) AS TUTAR, SUM(DURATION) AS SURE, COUNT(CDR_ID) AS ADET 
                      FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                    WHERE ";

      $kriter = "";
      //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
         if ($site_id && $site_id != -1){                   
               $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$site_id"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
         }
        $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
        $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
        $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
        $strsql .= $kriter; 
        $strsql  .= " GROUP BY SITE_ID,ACCESS_CODE";
//	     echo $strsql;exit;
         
         $cdb->execute_sql($strsql, $result, $errmsg);
         $tutar = $sure = $adet = "";
         while($row = mysql_fetch_array($result)){
	        $tutar[$row['SITE_ID']][$row['ACCESS_CODE']] = $row['TUTAR'];
           $sure[$row['SITE_ID']][$row['ACCESS_CODE']] = $row['SURE'];
           $adet[$row['SITE_ID']][$row['ACCESS_CODE']] = $row['ADET'];
	      }

		   $DATA1 .= "<tr bgcolor=\"#88ACD5\" class='rep2_header' height='22' align=center>
		              <td>Eriþim Kodu</td>
		              <td>Tutar</td>
	                 <td>Süre</td>
		              <td>Adet</td>
		           </tr>";
	      $ii = 0;
	      $gen_price = $gen_sure = $gen_adet = 0;
	      foreach ($tutar as $key => $value){                 
            $DATA1.= "<tr bgcolor='#FFCC00' height=20><td>".get_site_name($key)."</td>
                        <td></td><td></td><td></td> 
                      </tr>";
    	      foreach ($value as $key2 => $value2){
              $gen_price += $value2;
              $gen_sure += $sure[$key][$key2];
              $gen_adet += $adet[$key][$key2];
              if (!$key2)  // Erisim Kodu "" geldiyse gosterme
                  continue;
              $bgcolor = "#B3CAE3";
	           if ($ii%2 == 0)
	             $bgcolor = "#D8E4F1";
	           $ii++;
	           $DATA1.= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
		        $DATA1.="<td align='right'>";
		        $DATA1.= $key2;
	           $DATA1.="</td>";
	           $DATA1.="<td align='right'>".write_price($value2)."</td>";
	           $DATA1.="<td align='right'>".calculate_all_time($sure[$key][$key2])."</td>";
  		        $DATA1.="<td align='right'>".$adet[$key][$key2]."</td>";
              $DATA1.="</tr>";              
           }
         }
             
	      $DATA1 .= "<tr bgcolor='#FFCC00' class='header_sm1' height='22' align=center>
	                   <td>Genel Toplam</td>
		   	          <td align='right'>".write_price($gen_price)."</td>
			             <td align='right'>".calculate_all_time($gen_sure)."</td>
	                   <td align='right'>".$gen_adet."</td>
	                 </tr>";
         $DATA1 .= "</tr>
	                </table>
                   <tr><td hright='15'></td></tr>
                  </table>
                  <script language='javascript'>
                     function submit_me(){
                        document.acc_code.action = 'report_acc_code.php?site_id=' + document.all('SITE_ID').value;
                        document.acc_code.submit();
                     }
                 </script>
                  </form>
                  </center>
                 ";
        echo $DATA1; 
?>




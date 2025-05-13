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
  $field_dur = Array("LOC_DUR", "NAT_DUR", "GSM_DUR", "INT_DUR", "OTH_DUR", "INTERNAL_DUR", "INB_DUR");
  $field_amount = Array("LOC_AMOUNT", "NAT_AMOUNT", "GSM_AMOUNT", "INT_AMOUNT", "OTH_AMOUNT", "INTERNAL_AMOUNT", "INB_AMOUNT");
  $field_price = Array("LOC_PRICE", "NAT_PRICE", "GSM_PRICE", "INT_PRICE", "OTH_PRICE");

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
  
/*                  $DATA1 .=      "<td class='td1_koyu' colspan='3' width='30%' align='center'>Site Adý&nbsp;&nbsp;&nbsp; 
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
        $DATA1 .= "        </select>"*/
		$SQL_SITE = "SELECT SITE_ID, SITE_NAME FROM SITES";
        if(!$cdb->execute_sql($SQL_SITE, $result1, $errmsg)){
		  print_error($errmsg);
		  exit;
		}
        $t_mon = strftime("%m", mktime(0,0,0,date("m")-1,date("d"),date("y")));
        $t_year = strftime("%Y", mktime(0,0,0,date("m")-1,date("d"),date("y")));
        while($row_site = mysql_fetch_object($result1)){
          $SQL_DEPT = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS WHERE SITE_ID=".$row_site->SITE_ID;
          if(!$cdb->execute_sql($SQL_DEPT, $result2, $errmsg)){
            print_error($errmsg);
		    exit;
          }
          while($row_dept = mysql_fetch_object($result2)){
            $DATA1="";
            $DATA1 .= "<center>
             <table border=0 width=70% height=20%>
               <tr>
                <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"http://".$server_ip.IMAGE_ROOT."logo2.gif\"></a></TD>
                 <td align=center class=header>".$row_site->SITE_ID." - ".$row_site->SITE_NAME."<br><br>Aylýk Telefon Çaðrý Profili<br><br>
                 Departman Adý : ".get_dept_name($row_dept->DEPT_ID,$row_site->SITE_ID)."<br><br>".
                 strftime("%B", mktime(0,0,0,date("m")-1,date("d"),date("y")))."</td>";
            $DATA1 .= " <td align=right><img src=\"http://".$server_ip.IMAGE_ROOT."company.gif\"></td>
                    </tr>
                    <tr class='form'>";
            $DATA1 .= "   </td>
                     </tr>   
                    </table>
                    <table cellspacing=1 cellpadding=2 width= 70%>
                      <tr align=center>
                       <td width=70% height=22 colspan=4 class=\"header\"></td>
                      </tr>";

            $strsql = "SELECT * FROM MONTHLY_ANALYSE 
            WHERE SITE_ID=".$row_site->SITE_ID." AND DEPT_ID=".$row_dept->DEPT_ID." 
            AND TIME_STAMP_MONTH=".$t_mon." AND TIME_STAMP_YEAR=".$t_year.";";
	        //echo $strsql;exit;
            if(!$cdb->execute_sql($strsql, $result, $errmsg)){
               print_error($errmsg);
		       exit;
            }
            if(mysql_num_rows($result)==0){
              continue;
            }
            $tutar = $sure = $adet = "";
            unset($dept_arr);unset($data_arr);
            while($row = mysql_fetch_array($result)){
              if($row["TYPE"]=="department"){
                for($i=0;$i<=sizeof($field_amount)-1;$i++){
                  $dept_arr[$field_amount[$i]] = $row[$field_amount[$i]];
                }  
                for($i=0;$i<=sizeof($field_dur)-1;$i++){
                  $dept_arr[$field_dur[$i]] = $row[$field_dur[$i]];
                }  
                for($i=0;$i<=sizeof($field_price)-1;$i++){
                  $dept_arr[$field_price[$i]] = $row[$field_price[$i]];
                }  
              }elseif($row["TYPE"]=="dahili"){
                for($i=0;$i<=sizeof($field_amount)-1;$i++){
                   $data_arr[$row["ORIG_DN"]][$field_amount[$i]] = $row[$field_amount[$i]];
                }
                for($i=0;$i<=sizeof($field_dur)-1;$i++){
                  $data_arr[$row["ORIG_DN"]][$field_dur[$i]] = $row[$field_dur[$i]];
                }  
                for($i=0;$i<=sizeof($field_price)-1;$i++){
                  $data_arr[$row["ORIG_DN"]][$field_price[$i]] = $row[$field_price[$i]];
                }  
              }
            }
/*          GÖRÜÞME ÜCRETLERÝ         */            
            $DATA1.= "<tr bgcolor='#FFCC00' height=20><td colspan=9 align=center><b>Görüþme Ücretleri</b></td>
                      </tr>";
            $DATA1 .= "<tr bgcolor=\"#88ACD5\" class='rep2_header' height='22' align=center>
		              <td width='28%'></td>
		              <td width='9%'>Þ. Ýçi</td>
		              <td width='9%'>Þ Arasý</td>
	                  <td width='9%'>GSM</td>
		              <td width='9%'>U. Arasý</td>
		              <td width='9%'>Diðer</td>
		              <td width='27%' colspan=3>Toplam</td>
              </tr>";
            $ii = 0;
            $bgcolor = "#B3CAE3";
            if ($ii%2 == 0)
              $bgcolor = "#D8E4F1";
            $ii++;  
            unset($sub_total);
            $DATA1 .= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
            $DATA1.="<td align='left'>Departman Genel</td>";
            $total = 0;
            for($i=0;$i<=sizeof($field_price)-1;$i++){
              $DATA1.="<td align='right'>".write_price($dept_arr[$field_price[$i]])."</td>";
              $total = $total+$dept_arr[$field_price[$i]];
            }
            $DATA1.="<td align='right' colspan=3>".write_price($total)."</td>";
	        foreach ($data_arr as $key => $value){                 
              $bgcolor = "#B3CAE3";
              if ($ii%2 == 0)
                $bgcolor = "#D8E4F1";
              $total = 0;
              $DATA1.= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
              $DATA1.="<td align='left'>".$key." - ".get_ext_name2($key,$row_site->SITE_ID)."</td>";
              for($i=0;$i<=sizeof($field_price)-1;$i++){
                $DATA1.="<td align='right'>".write_price($value[$field_price[$i]])."</td>";
                $total = $total+$value[$field_price[$i]];
              }
              $DATA1.="<td align='right' colspan=3>".write_price($total)."</td>";
              $DATA1.="</tr>";
              $ii++;
            }
            $DATA1.= "<tr bgcolor='#FFFFFF' height=20><td colspan=9 align=center><b></b></td>
                      </tr>";
/*          GÖRÜÞME ÜCRETLERÝ SONU                        */

/*          GÖRÜÞME ADETLERÝ        */            
            $DATA1.= "<tr bgcolor='#FFCC00' height=20><td colspan=9 align=center><b>Görüþme Adetleri</b></td>
                      </tr>";
            $DATA1 .= "<tr bgcolor=\"#88ACD5\" class='rep2_header' height='22' align=center>
		              <td width='28%'></td>
		              <td width='9%'>Þ. Ýçi</td>
		              <td width='9%'>Þ Arasý</td>
	                  <td width='9%'>GSM</td>
		              <td width='9%'>U. Arasý</td>
		              <td width='9%'>Diðer</td>
		              <td width='9%'>Dahili</td>
		              <td width='9%'>Gelen</td>
		              <td width='9%'>Toplam</td>
              </tr>";
            $ii = 0;
            $bgcolor = "#B3CAE3";
            if ($ii%2 == 0)
              $bgcolor = "#D8E4F1";
            $ii++;  
            unset($sub_total);
            $DATA1 .= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
            $DATA1.="<td align='left'>Departman Genel</td>";
            $total = 0;
            for($i=0;$i<=sizeof($field_amount)-1;$i++){
              $DATA1.="<td align='right'>".$dept_arr[$field_amount[$i]]."</td>";
              $total = $total+$dept_arr[$field_amount[$i]];
            }
            $DATA1.="<td align='right'>".$total."</td>";
	        foreach ($data_arr as $key => $value){                 
              $bgcolor = "#B3CAE3";
              if ($ii%2 == 0)
                $bgcolor = "#D8E4F1";
              $total = 0;
              $DATA1.= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
              $DATA1.="<td align='left'>".$key." - ".get_ext_name2($key,$row_site->SITE_ID)."</td>";
              for($i=0;$i<=sizeof($field_amount)-1;$i++){
                $DATA1.="<td align='right'>".$value[$field_amount[$i]]."</td>";
                $total = $total+$value[$field_amount[$i]];
              }
              $DATA1.="<td align='right'>".$total."</td>";
              $DATA1.="</tr>";
              $ii++;
            }
            $DATA1.= "<tr bgcolor='#FFFFFF' height=20><td colspan=9 align=center><b></b></td>
                      </tr>";
/*          GÖRÜÞME ADETLERÝ SONU                        */

/*          GÖRÜÞME SÜRELERÝ                        */
            $DATA1.= "<tr bgcolor='#FFCC00' height=20><td colspan=9 align=center><b>Görüþme Süreleri</b></td>
                      </tr>";
            $DATA1 .= "<tr bgcolor=\"#88ACD5\" class='rep2_header' height='22' align=center>
		              <td></td>
		              <td>Þ. Ýçi</td>
		              <td>Þ Arasý</td>
	                  <td>GSM</td>
		              <td>U. Arasý</td>
		              <td>Diðer</td>
		              <td>Dahili</td>
		              <td>Gelen</td>
		              <td>Toplam</td>
              </tr>";
            $ii = 0;
            $bgcolor = "#B3CAE3";
            if ($ii%2 == 0)
              $bgcolor = "#D8E4F1";
            $ii++;  
            unset($sub_total);
            $DATA1 .= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
            $DATA1.="<td align='left'>Departman Genel</td>";
            $total = 0;
            for($i=0;$i<=sizeof($field_dur)-1;$i++){
              $DATA1.="<td align='right'>".calculate_all_time($dept_arr[$field_dur[$i]])."</td>";
              $total = $total+$dept_arr[$field_dur[$i]];
            }
            $DATA1.="<td align='right'>".calculate_all_time($total)."</td>";
	        foreach ($data_arr as $key => $value){                 
              $bgcolor = "#B3CAE3";
              if ($ii%2 == 0)
                $bgcolor = "#D8E4F1";
              $total = 0;
              $DATA1.= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
              $DATA1.="<td align='left'>".$key." - ".get_ext_name2($key,$row_site->SITE_ID)."</td>";
              for($i=0;$i<=sizeof($field_dur)-1;$i++){
                $DATA1.="<td align='right'>".calculate_all_time($value[$field_dur[$i]])."</td>";
                $total = $total+$value[$field_dur[$i]];
              }
              $DATA1.="<td align='right'>".calculate_all_time($total)."</td>";
              $DATA1.="</tr>";
              $ii++;
            }
/*          GÖRÜÞME SÜRELERÝ SONU                       */


            $DATA1 .= "</table>
                   <tr><td hright='15'></td></tr>
                  </table><br><br>
                  <script language='javascript'>
                     function submit_me(){
                        document.acc_code.action = 'report_acc_code.php?site_id=' + document.all('SITE_ID').value;
                        document.acc_code.submit();
                     }
                 </script>
                  </center>
                 ";
            echo $DATA1; 
          }    
        }  
?>




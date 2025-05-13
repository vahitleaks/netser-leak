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

//departmanýn dahili numaralarýný alarak bir kriter oluþturmak için kullanýlýr.
    function get_dept_extnos($DPT_ID, $ST_ID){
      global $cdb;
      global $conn;
      $dept_qry = "SELECT EXT_NO FROM EXTENTIONS WHERE DEPT_ID=".$DPT_ID." AND SITE_ID=".$ST_ID;
      if (!($cdb->execute_sql($dept_qry,$rsltdpt,$error_msg))){
         print_error($error_msg);
         exit;
      }
      if(mysql_num_rows($rsltdpt)>0){
        while($rowDept = mysql_fetch_object($rsltdpt)){
          if($retVal == ""){
            $retVal = "'".$rowDept->EXT_NO."'";
          }else{
            $retVal = $retVal.", '".$rowDept->EXT_NO."'";
          }
        }
        return "(".$retVal.")";
      }else{
        return "('-1')";
      }
    }


    $SQL_SITE = "SELECT SITE_ID, SITE_NAME, MAX_ACCE_DURATION FROM SITES WHERE SITE_ID=1";
    if(!$cdb->execute_sql($SQL_SITE, $result1, $errmsg)){
      print_error($errmsg);
      exit;
	}
    


    $t0 = strftime("%Y-%m-%d %T", mktime(0,0,0,date("m")-1,1,date("y")));
    $t1 = strftime("%Y-%m-%d %T", mktime(23,59,59,date("m"),0,date("y")));
    $CDR_MAIN_DATA = getTableName($t0,$t1);
    if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";
    while($row_site = mysql_fetch_object($result1)){

        $SITE_ID = $row_site->SITE_ID;
        $max_acc_dur =  ($row_site->MAX_ACCE_DURATION)*60;
        $SQL_DEPT = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS WHERE SITE_ID=".$SITE_ID;
        if(!$cdb->execute_sql($SQL_DEPT, $result2, $errmsg)){
          print_error($errmsg);
          exit;
    	}
        $DEPT_CNT = 0;
        while($row_dept = mysql_fetch_object($result2)){
          //$DEPT_CNT = $DEPT_CNT+1;
          //if($DEPT_CNT==5){die;}
            $DATA1="";
            $DATA1 .= "<center>
             <table border=0 width=70% height=20%>
               <tr>
                <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"http://".$server_ip.IMAGE_ROOT."logo2.gif\"></a></TD>
                 <td align=center class=header>".$row_site->SITE_ID." - ".$row_site->SITE_NAME."<br><br>Aylýk Telefon Çaðrý Profili
                 <br><br>".get_dept_name($row_dept->DEPT_ID, $SITE_ID)."<br><br>"
                 .strftime("%B", mktime(0,0,0,date("m")-1,date("d"),date("y")))."</td>";
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

            $local_country_code = get_country_code($SITE_ID);//Sitenin ülke kodu
            $kriter = "";
            $DEPT_CRT = get_dept_extnos($row_dept->DEPT_ID, $SITE_ID); 
             //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
             $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
             $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
             $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "1"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
             $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
             $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
             $kriter .= " AND ORIG_DN IN ".$DEPT_CRT." ";
             $MY_DATE = "g";
             add_time_crt();//Zaman kriteri 
             
            $strsql = "SELECT CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'".$local_country_code."',CONCAT('00',CountryCode),''),
                    IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER)
                    AS PHONE_NUMBER, 
                    COUNT(CDR_ID) AS AMOUNT, SUM(DURATION) AS DURATION, SUM(PRICE) AS PRICE
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA";
	        $strsql = $strsql." WHERE ".$kriter." 
            GROUP BY PHONE_NUMBER 
            ORDER BY AMOUNT DESC
            LIMIT 10";
            
            $kriter = "";
        
             //Temel kriterler. Verinini hýzlý gelmesi için baþa konuldu.
             $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.SITE_ID"     ,  "=",  "$SITE_ID"); //Bu mutlaka olmalý.Ýlgili siteyi belirliyor.
             $kriter .= $cdb->field_query($kriter,   "ERR_CODE"     ,  "=",  "0"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.      
             $kriter .= $cdb->field_query($kriter,   "CALL_TYPE"     ,  "=",  "2"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
             $kriter .= $cdb->field_query($kriter,   "DURATION"     ,  "<",  "$max_acc_dur"); //Bu mutlaka olmalý.Dýþ arama olduðunu gösteriyor.
             $kriter .= $cdb->field_query($kriter,   "CDR_MAIN_DATA.ORIG_DN"     ,  "<>",  "''"); //Bu mutlaka olmalý.Hatasýz kayýt olduðunu gösteriyor.
             $kriter .= " AND TER_DN IN ".$DEPT_CRT." ";
             $MY_DATE = "g";
             add_time_crt();//Zaman kriteri 
            $sql_inb = "SELECT REPLACE(CLID,'X','') AS PHONE_NUMBER, COUNT(CDR_ID) AS AMOUNT, 
                SUM(DURATION) AS DURATION, SUM(PRICE) AS PRICE 
                FROM CDR_MAIN_INB AS CDR_MAIN_DATA ";

	        $sql_inb = $sql_inb." WHERE ".$kriter." 
            GROUP BY PHONE_NUMBER  ORDER BY AMOUNT DESC
            LIMIT 10";
            
            /*echo $strsql."<br>";
            echo $sql_inb;exit;*/
            if(!$cdb->execute_sql($strsql, $result, $errmsg)){
               print_error($errmsg);
		       exit;
            }
            if(mysql_num_rows($result)==0){
 //             continue;
            }

            
/*          GÝDEN ÇAÐRILAR         */            
            $DATA1.= "<tr bgcolor='#FFCC00' height=20><td colspan=4 align=center><b>Giden Çaðrýlar</b></td>
                      </tr>";
            $DATA1 .= "<tr bgcolor=\"#88ACD5\" class='rep2_header' height='22' align=center>
		              <td width='30%'>Aranan Numara</td>
		              <td width='20%'>Adet</td>
		              <td width='20%'>Süre</td>
	                  <td width='20%'>Ücret</td>
              </tr>";
            $ii = 0;
	        while($row=mysql_fetch_object($result)){                 
              $bgcolor = "#B3CAE3";
              if ($ii%2 == 0)
                $bgcolor = "#D8E4F1";
              $total = 0;
              $DATA1.= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
              $DATA1.="<td align='left'>".$row->PHONE_NUMBER."</td>";
              $DATA1.="<td align='right'>".$row->AMOUNT."</td>";
              $DATA1.="<td align='right'>".calculate_all_time($row->DURATION)."</td>";
              $DATA1.="<td align='right'>".write_price($row->PRICE)."</td>";
              $DATA1.="</tr>";
              $ii++;
            }
            $DATA1.= "<tr bgcolor='#FFFFFF' height=20><td colspan=9 align=center><b></b></td>
                      </tr>";

/*          GELEN ÇAÐRILAR         */            
            if(!$cdb->execute_sql($sql_inb, $resulti, $errmsg)){
               print_error($errmsg);
		       exit;
            }
            $DATA1.= "<tr bgcolor='#FFCC00' height=20><td colspan=4 align=center><b>Gelen Çaðrýlar</b></td>
                      </tr>";
            $DATA1 .= "<tr bgcolor=\"#88ACD5\" class='rep2_header' height='22' align=center>
		              <td width='30%'>Arayan Numara</td>
		              <td width='20%'>Adet</td>
		              <td width='20%'>Süre</td>
	                  <td width='20%'>Ücret</td>
              </tr>";
            $ii = 0;
	        while($row1=mysql_fetch_object($resulti)){                 
              $bgcolor = "#B3CAE3";
              if ($ii%2 == 0)
                $bgcolor = "#D8E4F1";
              $total = 0;
              $DATA1.= "<tr class='cigate_header1' bgcolor='$bgcolor' height=18>";
              $DATA1.="<td align='left'>".$row1->PHONE_NUMBER."</td>";
              $DATA1.="<td align='right'>".$row1->AMOUNT."</td>";
              $DATA1.="<td align='right'>".calculate_all_time($row1->DURATION)."</td>";
              $DATA1.="<td align='right'>".write_price($row1->PRICE)."</td>";
              $DATA1.="</tr>";
              $ii++;
            }
            $DATA1.= "<tr bgcolor='#FFFFFF' height=20><td colspan=9 align=center><b></b></td>
                      </tr>";

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
            echo $DATA1; //die;
        }  
    }    
?>




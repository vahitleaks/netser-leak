<? require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   require_valid_login();
   //Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Bu sayfaya eriþim hakkýnýz yok!");
    exit;
   }
  //Admin Hakký versa ve Site Admin hakký yoksa sadece kendi sitesine ait bilgiyi görebilmeli
     if (right_get("ADMIN")){
         $site_crt = "SITE_ID = ".$SESSION['site_id'];
  }
  if (right_get("SITE_ADMIN")){
         $site_crt = "SITE_ID = ".$SITE_ID;
  }
   $conn = $cdb->getConnection();
?>

<div id="bekle" name="bekle" style="position:absolute;left:;top:;">
<table align="center" border="0" cellspacing=0 cellpadding="0">
<tr>
<td align="center">
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="180" height="30">
    <param name=movie value="/images/bekleyiniz.swf">
    <param name=quality value=high>
  <param name=menu value=false>
  <param name=wmode value=transparent>
    <embed src="/images/bekleyiniz.swf" quality=high pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="180" height="30">
    </embed> 
  </object></td>
</tr>
</table>   
</div>
<script>
   wid = screen.width-180;
   hei = screen.height-30;
   document.all("bekle").style.left=(wid/2);
   document.all("bekle").style.top=(hei/2);
   document.all("bekle").style.display='';
</script>
<?
  cc_page_meta();
     echo "<center>";
     page_header();
     echo "<center>";
?>
<script language="javascript">
  function submit_me(){
    document.site_src.action = 'audit_forw.php?site_id=' + document.all('SITE_ID').value;
    document.site_src.submit();
  }
</script>
<form name="site_src" method="post" action="audit_forw.php">
<BR>
<table width="75%" border="0" align="center" cellspacing="0" cellpadding="0">  
  <tr class="form">
       <td class="td1_koyu" width="30%" align="right">Site Adý</td>
       <td class="td1_koyu" width="10%"></td>
      <td width="60%">
           <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")){echo disabled;}?> onchange="javascript:submit_me();">
          <?
            $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES";
            echo $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $SITE_ID);
          ?>
           </select>
       </td>
  </tr>
</table>
</form>
<table cellspacing="0" cellpadding="0" border="0" width="90%" >
  <tr>
       <td width=""60%>
      <?table_header("Anormal Durumlar","80%");?>
      <center>
      <table cellspacing="0" cellpadding="0" border="0">
        <?
        //Sorgulamalar içinde bulunulan ay için yapýlacaktýr
        $t0 = date("Y-m-01");
        $t1 = date("Y-m-d");
      
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");

       $CDR_MAIN_DATA = getTableName($t0,$t1);
       if(!checkTable($CDR_MAIN_DATA)) $CDR_MAIN_DATA = "CDR_MAIN_DATA";  
       $local_country_code = get_country_code($SITE_ID);  
        //Giriþ Hattý Olarak Tanýmlanmýþ Hatlardan Çýkýþ alýnmasý Anormal Durumu
        $sql_str = "SELECT CDR_MAIN_DATA.CDR_ID, CDR_MAIN_DATA.TER_TRUNK_MEMBER AS CDR_TRUNK, TRUNKS.MEMBER_NO AS TRUNK
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                    INNER JOIN TRUNKS ON CDR_MAIN_DATA.TER_TRUNK_MEMBER = TRUNKS.MEMBER_NO 
                    WHERE CDR_MAIN_DATA.".$site_crt." AND CDR_MAIN_DATA.ERR_CODE='0' AND CDR_MAIN_DATA.CALL_TYPE='1' AND TRUNKS.TRUNK_IO_TYPE='1'
                   ";
        $sql_str .= "AND ".$kriter." GROUP BY CDR_TRUNK";
        if (!($cdb->execute_sql($sql_str,$result_trout,$error_msg))){
              print_error($error_msg);
              exit;
           }
        ?>
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('result_trout')">Giriþ Hatlarýndan Çaðrý Yapýlmasý</td>
          <td width="10%" align="right" class="td1_koyu"><?if (mysql_num_rows($result_trout)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('result_trout')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>
        </tr>

        <?      
        //Çýkýþ Olarak Tanýmlanmýþ Hatlara Çaðrý Düþmesi Durumu
        $sql_str = "SELECT CDR_MAIN_INB.CDR_ID, CDR_MAIN_INB.TER_TRUNK_MEMBER AS CDR_TRUNK, TRUNKS.MEMBER_NO AS TRUNK
                    FROM CDR_MAIN_INB
                    INNER JOIN TRUNKS ON CDR_MAIN_INB.TER_TRUNK_MEMBER = TRUNKS.MEMBER_NO
                    WHERE CDR_MAIN_INB.".$site_crt." AND CDR_MAIN_INB.ERR_CODE='0' AND  CDR_MAIN_INB.CALL_TYPE='2'  AND TRUNKS.TRUNK_IO_TYPE='2'
                   ";
        $sql_str .= " AND ".$kriter." GROUP BY CDR_TRUNK";
    
        if (!($cdb->execute_sql($sql_str,$result_trin,$error_msg))){
              print_error($error_msg);
              exit;
           }
        ?>
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('result_trin')">Çýkýþ Hatlarýna Çaðrý Gelmesi
          <td width="10%" align="right" class="td1_koyu"><?if (mysql_num_rows($result_trin)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('result_trin')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>
        </tr>

        <?      
        //Telekom Hattýndan Cep Tel Aramalarý
        $sql_str = "SELECT CDR_MAIN_DATA.CDR_ID, TLocation.TelProviderid, CONCAT(CDR_MAIN_DATA.LocalCode,CDR_MAIN_DATA.PURE_NUMBER) AS PHONE,
                      CDR_MAIN_DATA.TER_TRUNK_MEMBER AS CDR_TRUNK, CDR_MAIN_DATA.ORIG_DN
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                    LEFT JOIN TLocation ON CDR_MAIN_DATA.Locationid = TLocation.Locationid
                    WHERE CDR_MAIN_DATA.".$site_crt." AND CDR_MAIN_DATA.ERR_CODE='0' AND CDR_MAIN_DATA.CALL_TYPE=1 
			        AND CDR_MAIN_DATA.FROM_PROVIDER_ID=1 AND CDR_MAIN_DATA.CountryCode = ".$local_country_code." AND TLocation.LocationTypeid=2 ";

        $sql_str .= " AND ".$kriter." GROUP BY ORIG_DN";
        if (!($cdb->execute_sql($sql_str,$result_fct,$error_msg))){
              print_error($error_msg);
              exit;
           }
        ?>
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('result_fct')">Telekom Hatlarýndan GSM Çaðrýsý</td>
          <td width="10%" align="right" class="td1_koyu"><?if (mysql_num_rows($result_fct)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('result_fct')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>
        </tr>

        <?      
        //GSM Aramalarýnda Operatör Farklýlýklarý
        $sql_str = "SELECT CDR_MAIN_DATA.CDR_ID,CONCAT(CDR_MAIN_DATA.LocalCode,CDR_MAIN_DATA.PURE_NUMBER) AS PHONE,
                      CDR_MAIN_DATA.TER_TRUNK_MEMBER AS CDR_TRUNK, TTelProvider.TelProvider, CDR_MAIN_DATA.ORIG_DN
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                    INNER JOIN TTelProvider ON CDR_MAIN_DATA.FROM_PROVIDER_ID=TTelProvider.TelProviderid
                    WHERE CDR_MAIN_DATA.".$site_crt."  AND CDR_MAIN_DATA.ERR_CODE='0' AND CDR_MAIN_DATA.CALL_TYPE=1 
			        AND TTelProvider.OptTypeid = 2 AND CDR_MAIN_DATA.FROM_PROVIDER_ID <> CDR_MAIN_DATA.TO_PROVIDER_ID";
        $sql_str .= " AND ".$kriter;
        if (!($cdb->execute_sql($sql_str,$result_opt,$error_msg))){
              print_error($error_msg);
              exit;
           }
        ?>
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('result_opt')">Verimsiz GSM Operatör Kullanýmý</td>
          <td width="10%" align="right" class="td1_koyu"><?if (mysql_num_rows($result_opt)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>        
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('result_opt')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>
        </tr>

        <?      
        //Tanýmlanmamýþ Trunklara Çaðrý Gelmesi
        $sql_str = "SELECT TER_TRUNK_MEMBER AS CDR_TRUNK
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA 
                    WHERE ".$site_crt." AND TER_TRUNK_MEMBER <> '' AND CALL_TYPE=1
                    GROUP BY CDR_TRUNK";
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
              print_error($error_msg);
              exit;
           }

        $sql_str2 = "SELECT ORIG_TRUNK_MEMBER AS CDR_TRUNK
                     FROM CDR_MAIN_INB
                     WHERE ".$site_crt." AND ORIG_TRUNK_MEMBER <> '' AND CALL_TYPE= 2
                     GROUP BY CDR_TRUNK
                    ";
        if (!($cdb->execute_sql($sql_str2,$result2,$error_msg))){
              print_error($error_msg);
              exit;
           }

        $cdr_route_cnt = mysql_num_rows($result);
        $cdr_route_cnt2 = mysql_num_rows($result2);
        $i=0;
        while($row = mysql_fetch_array($result)){
          $cdr_route_arr2[$i] = $row["CDR_TRUNK"];
          $i = $i + 1;
        }
        $i=0;    
        while($row = mysql_fetch_array($result2)){
          $cdr_route_arr3[$i] = $row["CDR_TRUNK"];
          $i = $i + 1;
        }
        if(!is_array($cdr_route_arr2) && is_array($cdr_route_arr3)){
          $cdr_route_arr4 = $cdr_route_arr3; 
        }else if(is_array($cdr_route_arr2) && !is_array($cdr_route_arr3)){
          $cdr_route_arr4 = $cdr_route_arr2; 
        }else if(is_array($cdr_route_arr2) && is_array($cdr_route_arr3)){
          $cdr_route_arr4 = array_merge($cdr_route_arr2, $cdr_route_arr3); 
        }
        $cdr_route_arr = array_unique($cdr_route_arr4);

        $cdr_route_cnt = sizeof($cdr_route_arr);

        $sql_str1 = "SELECT TRUNKS.MEMBER_NO AS TRUNK
                     FROM TRUNKS WHERE ".$site_crt." GROUP BY TRUNKS.MEMBER_NO
                    ";
        if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
              exit;
           }

        $sys_route_cnt = mysql_num_rows($result1);

        $i = 0;
        while($row = mysql_fetch_array($result1)){
          $sys_route_arr[$i] = $row["TRUNK"];
          $i = $i + 1;
        }
        if (is_array($cdr_route_arr) && is_array($sys_route_arr)){
          $non_matching_trunks = array_diff ($cdr_route_arr, $sys_route_arr); 
          $non_matching_trunks = array_unique($non_matching_trunks); 
        }
	    ?>     
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('non_matching_trunk')">CrystalInfo Sisteminde Kayýtlý Olmayan Hatlara Çaðrý</td>
          <td width="10%" align="right" class="td1_koyu"><?if (sizeof($non_matching_trunks)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>          
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('non_matching_trunk')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>
        </tr>

        <?  
        //Tanýmlanmamýþ Dahililere Çaðrý Gelmesi
        $sql_str = "SELECT TER_DN FROM CDR_MAIN_INB
                    WHERE ".$site_crt." AND TER_DN <> ''
                    GROUP BY TER_DN";

        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
              print_error($error_msg);
              exit;
           }

        $sql_str2 = "SELECT ORIG_DN FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA 
                     WHERE ".$site_crt." AND ORIG_DN <> ''
                     GROUP BY ORIG_DN";

        if (!($cdb->execute_sql($sql_str2,$result2,$error_msg))){
              print_error($error_msg);
              exit;
           }
    
        $i=0;
        while($row = mysql_fetch_array($result)){
          $cdr_dn_arr2[$i] = $row["TER_DN"];
          $i = $i + 1;
        }
    
        $i=0;    
        while($row = mysql_fetch_array($result2)){
          $cdr_dn_arr3[$i] = $row["ORIG_DN"];
          $i = $i + 1;
        }
        if(is_array($cdr_dn_arr2) && is_array($cdr_dn_arr3)){
          $cdr_dn_arr4 = array_merge($cdr_dn_arr2, $cdr_dn_arr3); 
          $cdr_dn_arr = array_unique($cdr_dn_arr4);
        }else if(!is_array($cdr_dn_arr2) && is_array($cdr_dn_arr3)){
          $cdr_dn_arr4 = $cdr_dn_arr3; 
        }else if(is_array($cdr_dn_arr2) && !is_array($cdr_dn_arr3)){
          $cdr_dn_arr4 = $cdr_dn_arr2; 
        }
    
        $sql_str1 = "SELECT EXT_NO FROM EXTENTIONS
                     WHERE ".$site_crt." GROUP BY EXT_NO";
        
        if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
              exit;
           }
        
        $i = 0;
        while($row = mysql_fetch_array($result1)){
          $sys_dn_arr[$i] = $row["EXT_NO"];
          $i = $i + 1;
        }
        $non_matching_dns = array();
        if (is_array($cdr_dn_arr) && is_array($sys_dn_arr)){
          $non_matching_dns = array_diff ($cdr_dn_arr, $sys_dn_arr);
          $non_matching_dns = array_unique($non_matching_dns);
        }
		?>     
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('non_matching_dn')">CrystalInfo Sisteminde Kayýtlý Olmayan Dahililerde Çaðrý</td>
          <td width="10%" align="right" class="td1_koyu"><?if (sizeof($non_matching_dns)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>          
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('non_matching_dn')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>        
        </tr>
        
        <?
        //Yarým Saat'ten Fazla Tutan Konuþmalar
        $sql_str = "SELECT CDR_ID,ORIG_DN,DURATION, CONCAT(CONCAT(IF(CountryCode <>'' AND CountryCode<>'90',CONCAT('00',CountryCode),''),
                    IF(LocalCode <>'',CONCAT('0',LocalCode),'')),PURE_NUMBER) AS PHONE
                    FROM $CDR_MAIN_DATA AS CDR_MAIN_DATA
                    WHERE ".$site_crt."  AND ERR_CODE='0' AND CALL_TYPE=1 AND DURATION < 7200 AND DURATION > 1800";
        $sql_str .= " AND ".$kriter;
        if (!($cdb->execute_sql($sql_str,$result_time,$error_msg))){
              print_error($error_msg);
              exit;
           }
        ?>
        <tr>
          <td width="80%" class="td1_koyu" style="cursor:hand;" onclick="open_div('result_time')">Yarým Saatten Fazla Süren Konuþmalar</td>
          <td width="10%" align="right" class="td1_koyu"><?if (mysql_num_rows($result_time)>0){echo "<b>VAR</b>"; }else{ echo "YOK";}?></td>    
          <td width="10%" class="td1_koyu" align="center" style="cursor:hand;" onclick="open_div('result_time')"><img border="0" src="<?=IMAGE_ROOT?>detay_gor.gif" alt="Detay Gör"></td>                      
        </tr>
      </table>
      <?table_footer();?>
    </td>  
  </tr>
  <tr height="10">
    <td></td>
  </tr>
  <tr>
    <td width="80%">
      <?table_header("Detaylar","60%")?>
      
        <div id="result_trout" style="display:none;">
          <table>
            <tr>
              <td colspan="4" width="80%" align="center" class="td1_koyu">Hat</td>
            </tr>
            <?while ($row = mysql_fetch_object($result_trout)){?>   
                <tr>
              <td width="20%" align="center" class="td1"><a href="#"><span onclick="javascript:popup('cdr_detail.php?id=<?=$row->CDR_ID?>','audit',600,300)"><?=$row->CDR_ID?></span></a></td>
                   <td colspan="3" width="80%" align="center" class="td1"><?=$row->CDR_TRUNK?></td>
                </tr>
              <?}?>
          </table>
        </div>
    
        <div id="result_trin" style="display:none;">
          <table>
            <tr>
              <td colspan="4" width="80%" align="center" class="td1_koyu">Hat</td>
            </tr>
            <?while ($row = mysql_fetch_object($result_trin)){?>   
                <tr>
              <td width="20%" align="center" class="td1"><a href="#"><span onclick="javascript:popup('cdr_detail.php?id=<?=$row->CDR_ID?>','audit',600,300)"><?=$row->CDR_ID?></span></a></td>
                   <td colspan="3" width="80%" align="center" class="td1"><?=$row->CDR_TRUNK?></td>
                </tr>
              <?}?>
          </table>
        </div>

        <div id="result_fct" style="display:none;">
          <table>
            <tr>
              <td width="20%" align="center" class="td1_koyu">Kayýt No</td>
              <td width="20%" align="center" class="td1_koyu">Dahili</td>
              <td width="30%" align="center" class="td1_koyu">Hat</td>
              <td width="30%" align="center" class="td1_koyu">Telefon</td>
            </tr>
            <?while ($row = mysql_fetch_object($result_fct)){?>   
                <tr>
              <td width="20%" align="center" class="td1"><a href="#"><span onclick="javascript:popup('cdr_detail.php?id=<?=$row->CDR_ID?>','audit',600,300)"><?=$row->CDR_ID?></span></a></td>                   
              <td width="20%" align="center" class="td1"><?=$row->ORIG_DN?></td>
              <td width="30%" align="center" class="td1"><?=$row->CDR_TRUNK?></td>
              <td width="30%" align="center" class="td1"><?=$row->PHONE?></td>
                </tr>
              <?}?>
          </table>
        </div>

        <div id="result_opt" style="display:none;">
          <table>
            <tr>
              <td width="15%" align="center" class="td1_koyu">Kayýt No</td>
              <td width="10%" align="center" class="td1_koyu">Dahili</td>
              <td width="20%" align="center" class="td1_koyu">Çýkýþ Hattý</td>
              <td width="25%" align="center" class="td1_koyu">Aranan Yer</td>
              <td width="30%" align="center" class="td1_koyu">Telefon</td>
            </tr>
            <?while ($row = mysql_fetch_object($result_opt)){?>   
                <tr>
              <td width="15%" align="center" class="td1"><a href="#"><span onclick="javascript:popup('cdr_detail.php?id=<?=$row->CDR_ID?>','audit',600,300)"><?=$row->CDR_ID?></span></a></td>                   
                   <td width="10%" align="center" class="td1"><?=$row->ORIG_DN?></td>
              <td width="20%" align="center" class="td1"><?=$row->CDR_TRUNK?></td>
              <td width="25%" align="center" class="td1"><?=$row->TelProvider?></td>
              <td width="30%" align="center" class="td1"><?=$row->PHONE?></td>
                </tr>
              <?}?>
          </table>
        </div>

        <div id="non_matching_trunk" style="display:none;">
          <table>
            <tr>
              <td colspan="4" width="80%" align="center" class="td1_koyu">Hat</td>
            </tr>
            <?$i=0;
            $tr_arr_size = sizeof($non_matching_trunks);
            while ($i<$tr_arr_size){
              echo "<tr>";
              echo "<td colspan=\"4\" width=\"80%\" align=\"center\">".$non_matching_trunks[$i]."</td>";
              echo "</tr>";
              $i++;
              }?>
          </table>        
        </div>

        <div id="non_matching_dn" style="display:none;">
          <table>
            <tr>
              <td colspan="4" width="80%" align="center" class="td1_koyu">Dahili</td>
            </tr>
            <?$i=0;
            $dn_arr_size = sizeof($non_matching_dns);
            while ($i<$dn_arr_size){
              echo "<tr>";
              echo "<td colspan=\"4\" width=\"80%\" align=\"center\">".$non_matching_dns[$i]."</td>";
              echo "</tr>";
              $i++;
              }?>  
          </table>      
        </div>
        
        <div id="result_time" style="display:none;">
          <table>
            <tr>
              <td width="20%" align="center" class="td1_koyu">Kayýt No</td>
              <td width="20%" align="center" class="td1_koyu">Dahili</td>
              <td width="30%" align="center" class="td1_koyu">Süre</td>
              <td width="30%" align="center" class="td1_koyu">Telefon</td>
            </tr>
            <?while ($row = mysql_fetch_object($result_time)){?>   
            <tr>
              <td width="15%" align="center" class="td1"><a href="#"><span onclick="javascript:popup('cdr_detail.php?id=<?=$row->CDR_ID?>','audit',600,300)"><?=$row->CDR_ID?></span></a></td>                                      
              <td width="20%" align="center" class="td1"><?=$row->ORIG_DN?></td>
              <td width="30%" align="center" class="td1"><?=calculate_time($row->DURATION,"hour")."  Saat  ".calculate_time($row->DURATION,"min")." Dk "?></td>
              <td width="30%" align="center" class="td1"><?=$row->PHONE?></td>
                </tr>
              <?}?>
          </table>
        </div>
      <?table_footer()?>
    </td>  
  </tr>
</table>    
      
<script language="JavaScript">
  function open_div(divname){
      var stat = document.all(divname).style.display;
    
    document.all('result_trout').style.display = 'none';    
    document.all('result_trin').style.display = 'none';    
    document.all('result_fct').style.display = 'none';    
    document.all('result_opt').style.display = 'none';    
    document.all('non_matching_trunk').style.display = 'none';    
    document.all('non_matching_dn').style.display = 'none';                        
    document.all('result_time').style.display = 'none';                        
    
    if(stat == "none"){
       document.all(divname).style.display = '';
    }else{
       document.all(divname).style.display = 'none';    
    }
        
     }
   document.all("bekle").style.display='none';  
</script>

</body>
</html>   
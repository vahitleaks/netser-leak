<?
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  include(dirname($DOCUMENT_ROOT)."/root/crons/mail_send.php");
  session_cache_limiter('nocache');
  $conn = $cdb->getConnection();
  cc_page_meta(0);
  $cmp = get_system_prm("COMPANY_NAME");
  $server_ip = get_system_prm("SERVER_IP");
  $DATA_HEAD ="
               <html>
               <head>
               <title>Crystal Info</title>
               <meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1254\">
               <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-9\">
               <style>
                 body {font-family:Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
                 .homebox {font-family: Verdana, Arial, Helvetica, sans-serif;         font-size: 7pt;         font-weight: bold;         font-variant: normal;         text-transform: none;         color: FF6600;         text-decoration: none}
                 .header {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #2C5783;     text-decoration: none}
                 .header_beyaz2 {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: FFFFFF;        background-color:  508AC5;    text-decoration: none}
                 .header_sm {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 7pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #2C5783;     text-decoration: none}
                 a.a1 {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #1B4E81;     text-decoration: none}
                 a.a1:hover {font-family: Verdana,Ariel,Helvatica, san-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
                 a{font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #FF6600;     text-decoration: none}
                 .text {font-family: Verdana, Arial, Helvetica, sans-serif;  font-size: 8pt;  font-weight: normal;  font-variant: normal;  text-transform: none;  color: #1b4e81 ;  text-decoration: none}
                 a:hover {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #FF9000;     text-decoration: none}
                 .copyright {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 7pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #0099CC;     text-decoration: none}
                 .table_header {font-family: Geneva, Arial, Helvetica, san-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #ECF9FF;     text-decoration: none}
                 td.td1 {font-size: 8pt;    border-style: solid ;     border-width: 0};
                 td.header1 {background-color: #0099CC;     font-size: 8pt;     font-weight: Bold;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
                 td.td1_koyu {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: Bold;     font-variant: normal;     text-transform: none;     color: #1B4E81;     border:0;    height:22px;    text-decoration: none}
                 tr.header1 {background-color: #0099CC;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #000000;     text-decoration: none}
                 tr.bgc1 {background-color: #B1CBE4}
                 tr.bgc2 {background-color: #C6D9EC}
                 table {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: normal;     font-variant: normal;     text-transform: none;     color: #000000; text-decoration: none}
                 .header_beyaz{font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 9pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: F0F8FF;     text-decoration: none;background-color:#6699CC}
                 .font_beyaz {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 9pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: F0F8FF;     height:22px;    text-decoration: none;}
                 .header_mavi {font-family: Verdana, Arial, Helvetica, sans-serif;     font-size: 8pt;     font-weight: bold;     font-variant: normal;     text-transform: none;     color: #1B4E81;     text-decoration: none}
                 .rep_td {font-size: 9pt;     font-family: Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     color: #000000;     height:22px;}  
                 .rep_header {font-size: 8pt;     font-family: Verdana,Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     font-weight:bold;    color: #000000;     height:20px;    background-color:#FFFFFF}
                 .rep_table_header {font-size: 9pt;     font-family: Verdana,Courier New, Courier, mono;     font-variant: normal;     text-transform: none;     font-weight:bold;    color: #ffffff;     height:25px;    background-color:#959595      }
               </style>
               </head>
             ";
?>
<form name="man_rep" method="post" action="man_rep.php">
<center>
<br><br>

<?
  setlocale(LC_TIME, 'tr_TR');
  $onceki = strftime("%B", mktime(0, 0, 0, date("m")-2, date("d"),date("y")));
  $gecen  = strftime("%B", mktime(0, 0, 0, date("m")-1, date("d"),date("y")));
?>


<?$DATA = "<table width=900 border=0 cellspacing=2 cellpadding=0>
             <tr>
               <td colspan=2 width=100% align=center align=center>
               <table border=0 width=100%>
                 <tr>
                     <td><a href=\"http://www.crystalinfo.net\" target=\"_blank\"><img border=0 SRC=\"http://".$server_ip.IMAGE_ROOT."logo2.gif\"></a></TD>
                     <td align=center class=header>".$cmp."
            	     <br><br>ŞİRKET GENEL ANALİZ RAPORU
		           </td>
                   <td align=right><img SRC=\"http://".$server_ip.IMAGE_ROOT."company.gif\"></TD>
                 </tr>
               </table>
               </td>
             </tr>
             <tr><td height=15 colspan=2></td></tr>
             <tr>
               <td align=center><img src=\"http://".$server_ip."/special/yonetim/prc_count.php\" border=0></td>
               <td align=center><img src=\"http://".$server_ip."/special/yonetim/cmpMonth.php\" border=0></td>
             </tr>
             <tr><td height=10 colspan=2></td></tr>
             <tr align=center>
               <td width=100% colspan=2><img src=\"http://".$server_ip."/special/yonetim/cmpSites.php\" border=0></td>
             </tr>
             <tr><td height=15 colspan=2></td></tr>
             <tr>
	           <td height=22 colspan=2 align=center>
               <table cellspacing=1 cellpadding=2 width=100%>
                 <tr align=center>
                   <td width=90% height=22 colspan=10 bgcolor=\"#88ACD5\" class=\"header\">".$onceki."-".$gecen." Aylarının Karşılaştırmalı Analiz Raporu</td>
                 </tr>
          ";
                 
                 $t0 = strftime("%Y-%m", mktime(0,0,0,date("m")-1,date("d"),date("y")));
                 $t1 = strftime("%Y-%m", mktime(0,0,0,date("m")-2,date("d"),date("y")));
                 $strsql = "SELECT SITE_ID, SITE_NAME FROM SITES";
	             $cdb->execute_sql($strsql, $result, $errmsg);
	             while($row = mysql_fetch_array($result)){
	               $sites[$row['SITE_ID']] = $row['SITE_NAME'];
	             }
	             $DATA1="";
				 $DATA1= "<tr bgcolor='#FFCC00' class='header_sm' height='22' align=center>
	                       <td>SİTE ADI</td>
			               <td>ŞEHİR İÇİ</td>
			               <td>ŞEHİRLERARASI</td>
	                       <td>GSM</td>
			               <td>ULUSLARARASI</td>
			               <td>DİĞER</td>
			               <td>TOPLAM</td>
		                   <td>ÖNCEKİ AY</td>
			               <td>DEĞİŞİM</td>
		                </tr>";
	            $ii = 0;
	            $gen_tot = array();
	            $gen_tot[$i] = 0;
	            $gen_last_prc = 0;
	            foreach ($sites as $value => $key){
                $bgcolor = "#B3CAE3";
	            if ($ii%2 == 0)
	            $bgcolor = "#D8E4F1";
	            $ii++;
	            $strsql2 = " SELECT LOC_PRICE, NAT_PRICE, GSM_PRICE, INT_PRICE, OTH_PRICE, 
		                       CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) AS MONTH
		                     FROM MONTHLY_ANALYSE
		                     WHERE SITE_ID = ".$value." AND TYPE = 'general' 
				               AND (CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t0."' OR
					           CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t1."')
				             ORDER BY MONTH DESC";
             	$cdb->execute_sql($strsql2, $result2, $errmsg);
                if ($result2){
                  ///İlk fetch. Geçan ay alınıyor
	              $row2 = mysql_fetch_row($result2);
	              $DATA1.= "\n<tr bgcolor='$bgcolor' height=18>\n";
	              $DATA1.= "<td class='header_sm'>".$key."</td>\n";
	              $total = 0;
		          for ($i = 0; $i < 5; $i++){
		            $DATA1.="<td align='right'>";
		            $DATA1.= write_price($row2[$i]);
	                $DATA1.="</td>\n";
		            $total += $row2[$i];
		            $gen_tot[$i] = $gen_tot[$i] + $row2[$i];
            	  }
	              $DATA1.="<td align='right' class='header_sm'>".write_price($total)."</td>\n";
                  ///İkinci fetch. Bir önceki ay alınıyor
	              $row2 = mysql_fetch_row($result2);
	              $totalLM =  $row2[0]+$row2[1]+$row2[2]+ $row2[3]+$row2[4];
	              $gen_last_prc = $gen_last_prc + $totalLM;
		          $DATA1.="<td align='right' class='header_sm'>".write_price($totalLM)."</td>\n";
	              if ($totalLM > 0){
	              $var = (($total-$totalLM)/$totalLM)*100;
	              if ($var < 0)
	                $bcol = "#66C105"; /* yesil */
		            else $bcol = "#FC3636"; /* Kirmizi */
                  }else{
	                $var = 0;
	              }
                  $DATA1.="<td bgcolor = $bcol>%";
	              $DATA1.= sprintf("%.1f",$var);
	              $DATA1.="</td>";
	              $DATA1.="</tr>";
               }
             }
	  $DATA1.="<tr bgcolor='#FFCC00' class='header_sm' height='22' align=center>
	             <td>Genel Toplam</td>
			     <td align='right'>".write_price($gen_tot[0])."</td>\n
			     <td align='right'>".write_price($gen_tot[1])."</td>\n
	             <td align='right'>".write_price($gen_tot[2])."</td>\n
			     <td align='right'>".write_price($gen_tot[3])."</td>\n
			     <td align='right'>".write_price($gen_tot[4])."</td>\n";
			     $gen_this_prc = $gen_tot[0]+ $gen_tot[1]+$gen_tot[2]+$gen_tot[3]+$gen_tot[4];
	             $DATA1.="<td align='right'>".write_price($gen_this_prc)."</td>
		                  <td align='right'>".write_price($gen_last_prc)."</td>";
	                      if ($gen_last_prc > 0){
	                        $var = (($gen_this_prc-$gen_last_prc)/$gen_last_prc)*100;
	                        if ($var < 0)
	                          $bcol = "#66C105"; /* yesil */
		                    else 
			                  $bcol = "#FC3636"; /* Kirmizi */
                          }else{
	                        $var = 0;
	                      }
                 $DATA1.="<td bgcolor = $bcol>%";
	             $DATA1.= sprintf("%.1f",$var);
			     $DATA1.="</td></tr>";

	 if ($act == "send"){
	   $CMD = "<tr bgcolor='#807040' height='22' align=center>
	             <td colspan=10><font color=white><b>YORUMLAR</b></font></td>
			   </tr>
			   <tr>
				 <td colspan=10>".nl2br($comment)."</td>
			   </tr>
			  </table>
			  </td>
			</tr>
	      </table>
			  ";
	   $S_DATA = $DATA.$DATA1.$CMD;
       mail_send($e_mail,"Aylık Yönetim Analiz Raporu.",$S_DATA,0);
	 }else{
	   echo $DATA.$DATA1;?>
	       <tr height='22' align=center>
	         <td colspan="10">
               <input type="hidden" name="act">
			   <input type="hidden" name="e_mail">
               <TEXTAREA name="comment" class="text" rows="10" cols="153"><?if ($comment)echo $comment; else echo "Rapora eklemek istediginiz yorumlari buraya yazabilirisiniz..."; ?></TEXTAREA>
               <br><INPUT type="submit" value="Yazdir" onclick="javascript:print();">
               <INPUT type="submit" onClick="javascript:sendPage();" value="E-mail Yolla">
               <INPUT type="submit" value="Düzelt">
	         </td>
           </tr>
	     </table>
	    </td>
       </tr>
	  </table> 
     <?}?>
	</table>
  </tr>
  <tr><td hright="15"></td></tr>
</table>
</center>
</form>
<script language="JavaScript">
  function sendPage(){
    var keyword = prompt("Lütfen bir mail adresi giriniz.", "")
    if(CheckEmail(keyword)){
  	  document.all('act').value='send'; 
      document.all('e_mail').value=keyword; 
      document.all('man_rep').submit();
   }
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
             alert("Girdiğiniz e-mail geçersiz karakterler içermektedir.\n");
             return 0;
       }
    }
    return 1;
}   
 function mailPage(page){
      var keyword = prompt("Lütfen bir mail adresi giriniz.", "")
      if(CheckEmail(keyword)){
          var pagename = "../../reports/htmlmail.php?page=/temp/"+page+  "&email="+ keyword;
          this.location.href = pagename;
      }    
   }
   
</script>



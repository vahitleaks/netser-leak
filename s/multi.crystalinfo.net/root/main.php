<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   if (!defined("IMAGE_ROOT")){ // Note that it should be quoted
      define("IMAGE_ROOT", "/images/");
   }  
   session_cache_limiter('nocache');
   require_valid_login();
   $cUtility = new Utility();
   $cdb = new db_layer();
   $conn = $cdb->getConnection();
   page_track();
   cc_page_meta(0);
  page_header("");
  $SITE_ID = $SESSION['site_id'];
  ?>
   <div id="clockDiv" class="homebox" >
     <?=date('d.m.Y H:i:s'); ?>
   </div>
   <script>
     function placeDiv(){
       clockDiv.style.position='absolute';
       clockDiv.style.left=630;
       clockDiv.style.top=99;
     }
     placeDiv();
   </script>

<script language=javascript>
    function src_form_submit(){
        if(document.main_search.src_str.value==''){
            alert("Arama Kriteri Girmediniz!..");
        }else{
            document.main_search.submit();
        }
    }
</script>
 
  <table width="769" border="0" cellspacing="2" cellpadding="1">
      <tr> 
        <td colspan="3" height="0" width="145"></td>
      </tr>
      <tr> 
        <td valign="top" width="145"> 
      <table width="145" border="0" cellspacing="1" cellpadding="1" bgcolor="F0F8FF">
            <tr> 
              <td colspan="2" height="20" bgcolor="#91B5D9" class="header"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">HABERLER</td>
            </tr>

			            <tr> 
              <td valign="center" height="90 class="text" colspan="2"> 
            

<?

$haberler="SELECT SIRA_NO, BASLIK FROM NEWS ORDER BY INSERT_DATE DESC LIMIT 10 ";

    if (!($cdb->execute_sql($haberler, $rs_haberler, $error_msg))){
        print_error($error_msg);
                exit;
}
   echo "<marquee align='middle' scrollamount='1' height='88' width='80%' direction='up' scrolldelay='0'><font color='navy'>";      
   while($row = mysql_fetch_array($rs_haberler))
   echo "{$row['BASLIK']}<br>------------------------ ";
   echo "</font></marquee>";

?>

            </tr>
            <tr> 
            <?$sql = "SELECT USERS.*, A.EXT_NO AS DAHILI1, B.EXT_NO AS DAHILI2, C.EXT_NO AS DAHILI3, AUTH_CODES.AUTH_CODE AS AUTH_CODE
                      FROM USERS
                        LEFT JOIN EXTENTIONS A ON (USERS.EXT_ID1 = A.EXT_ID)
                        LEFT JOIN EXTENTIONS B ON (USERS.EXT_ID2 = B.EXT_ID)
                        LEFT JOIN EXTENTIONS C ON (USERS.EXT_ID3 = C.EXT_ID)
                        LEFT JOIN AUTH_CODES ON USERS.AUTH_CODE_ID = AUTH_CODES.AUTH_CODE_ID
                      WHERE USER_ID = '".$SESSION['user_id']."'";
              $cdb->execute_sql($sql, $result, $error);
              $row = mysql_fetch_object($result);
              $AUTH_CODE = $row->AUTH_CODE;
              $DAHILI1 = $row->DAHILI1;
              $DAHILI2 = $row->DAHILI2;
              $DAHILI3 = $row->DAHILI3;
            ?>
            <tr> 
              <td height="20" colspan="2" bgcolor="#91B5D9" class="header"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">Kiþisel Bilgiler</td>
            </tr>
            <tr>
              <td colspan="2" bgcolor="#BED3E9" class="header"><? echo $SESSION["adi"]."   ".$SESSION["soyadi"]."<br>";?></td>
            </tr>
            <tr>
              <td class="Header" bgcolor="#BED3E9" height="18">Dahili</td>
              <td class="text" bgcolor="#D8E4F1"><?echo $DAHILI1?$DAHILI1:"";echo $DAHILI2?"-".$DAHILI2:"";echo $DAHILI3?"-".$DAHILI3:""?></td>
            <tr>
              <td class="Header" bgcolor="#BED3E9" height="18">GSM </td>
              <td class="text" bgcolor="#D8E4F1"><?=$row->GSM ?></td>
            </tr>
            <tr>
              <td align="left" colspan="2"><?echo "<a href=\"/users/user.php?act=upd&id=".$SESSION['user_id']."\"><img border=\"0\" src=\"/images/guncelle.gif\"></a><BR></TD>";?>
            </tr>
            <tr> 
              <td height="20" colspan="2" bgcolor="#91B5D9" class="header"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">Son Mesajýnýz</td>
            </tr>
            <?
            $sql = "SELECT * FROM MESSAGES WHERE TRG_USER_ID = '".$SESSION["user_id"]."' ORDER BY SIRA_NO DESC LIMIT 1";
            $cdb->execute_sql($sql, $result, $error);
            $row = mysql_fetch_object($result);
            if($row->KONU){?>
            <tr>
              <td colspan="2"  height="18" bgcolor="#BED3E9" class="text">+ <?=substr($row->KONU,0,30)?>
                <a href="javascript:popup('/messages/mesaj_win.php?frm_sira_no=<?=$row->SIRA_NO?>', 'yenipencere',550,450);">>>></a>
              </td>
            </tr>
            <tr> 
              <td colspan="2" bgcolor="#91B5D9" class="header"><img src="<?=IMAGE_ROOT?>resim_tech1.jpg"></td>
            </tr>
            <?}else{?>         
            <tr> 
              <td colspan="2" bgcolor="#91B5D9" class="header"><img src="<?=IMAGE_ROOT?>resim_tech.jpg"></td>
            </tr>
            <?}?>         
          </table>
        </td>
        <td height="100" valign="top" width="425"> 
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr> 
              <td height="6"> 
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr valign="middle" align="center"> 
                    <td height="195"><img border="0" id="topten" src="last_week_chart.php"></td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr> 
              <td valign="top"> 
              <?
                $res_arr = get_heavy_sql_result(10);
        $total_mn = 0;$total_price=0;$total_amn=0;
        if (is_array($res_arr)){
                  for($i=0;$i<=sizeof($res_arr);$i++){//Gelen dizi içinde benim sitem bulunmalý
            if ($res_arr[$i][0][1]==$SITE_ID){ //Ýlgili kaydýn ilk alanýnýn deðeri
                      $total_mn    += $res_arr[$i][3][1]; //Toplam Süre.Diðer aramalar için eklenmeli.
                      $total_price += $res_arr[$i][4][1]; //Toplam Tutar
                      $total_amn   += $res_arr[$i][5][1];  // Toplam Adet
            switch ($res_arr[$i][2][1]){ //Ýlgili kaydýn arama tipi alanýnýn deðeri
            case '0':
                $SI[] = $res_arr[$i][3][1]; // Þehir içi Süre
                          $SI[] = $res_arr[$i][4][1]; // Þehir içi Tutar
                          $SI[] = $res_arr[$i][5][1]; // Þehir içi Adet
              break;
              case 1 :
                          $SA[] = $res_arr[$i][3][1]; //Þehirlerarasý Süre
                          $SA[] = $res_arr[$i][4][1]; //Þehirlerarasý Tutar
                          $SA[] = $res_arr[$i][5][1]; //Þehirlerarasý Adet
              break;
              case 2:
                          $GSM[] = $res_arr[$i][3][1]; //GSM Süre
                          $GSM[] = $res_arr[$i][4][1]; //GSM Tutar
                          $GSM[] = $res_arr[$i][5][1]; //GSM Adet
              break;
                        case 3:
                          $UA[] = $res_arr[$i][3][1];  // Uluslararasý Süre
                          $UA[] = $res_arr[$i][4][1];  // Uluslararasý Tutar
                          $UA[] = $res_arr[$i][5][1];  // Uluslararasý Adet
              break;
                        default :
            }
            }
          }
                }
          $M_LEN = date("t");//Ayda bulunan gün adedi
        $M_DAY = date("j");//Ayýn bulunulan günü
              ?>
<script>
  function chg_chart(val){
    document.all('topten').src =  val;
  }
</script>
<?//Kiþisel forecast için dahili kriteri oluþturuluyor.
  if(!$DAHILI1) $DAHILI1 = "99999";
  if($DAHILI2) $DAHILI1 .= ",". $DAHILI2;
  if($DAHILI3) $DAHILI1 .= ",". $DAHILI3;
?>

             <table width="100%" border="0" cellspacing="2" cellpadding="0" height="100%">
               <tr> 
                 <td valign="top" class="text"> 
                 <table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="F0F8FF">
                   <tr> 
                    <td height="30" colspan="5" bgcolor="#91B5D9" align="center" class="header">Þirket Genel Durumu ve Ay Sonu Tahmini</td>
                   </tr>
                   <tr> 
                     <td rowspan="2" height="41" bgcolor="#508AC5" align="center" onclick="javascript:popup('/reports/forecast_pers.php?DAHILI1=<?=$DAHILI1?>','forecast',450,200)" style="cursor:hand">
                       <img src="<?=IMAGE_ROOT?>genel.gif" title="Þirket Genel"></td>
                     <td colspan="2" align="center" valign="bottom" height="25" bgcolor="#FFCC00" class="header_sm">BU AY</td>
                     <td colspan="2" align="center" valign="bottom" height="25" bgcolor="#FFCC00" class="header_sm">AY SONU TAHMÝNÝ</td>
                   </tr>
                   <tr> 
                     <td height="25" bgcolor="#508AC5" width="21%" align="center" class="header_beyaz2">Süre</td>
                     <td bgcolor="#508AC5" width="19%" align="center" class="header_beyaz2">Tutar(TL)</td>
                     <td bgcolor="#508AC5" width="21%" align="center" class="header_beyaz2">Süre</td>
                     <td bgcolor="#508AC5" width="22%" align="center" class="header_beyaz2">Tutar(TL)</td>
                   </tr>
                   <tr> 
                     <td height="22" bgcolor="#FFCC00" width="17%" class="header">Þ.Ýçi</td>
                     <td bgcolor="#91B5D9" width="21%" align="right" class=text"><?=calculate_all_time($SI[0]) ?></td>
                     <td bgcolor="#91B5D9" width="19%" align="right" class=text"><?=write_price($SI[1]) ?></font></td>
                     <td bgcolor="#91B5D9" width="21%" align="right" class=text"><?=calculate_all_time(($SI[0] * $M_LEN) / $M_DAY)?></td>
                     <td bgcolor="#91B5D9" width="22%" align="right" class=text"><?=write_price((($SI[1] * $M_LEN) / $M_DAY))?></td>
                   </tr>
                   <tr> 
                     <td height="22" bgcolor="ECBD00" width="17%" class="header">Þ.Arasý</td>
                     <td bgcolor="#BED3E9" width="21%" align="right" class=text"><?=calculate_all_time($SA[0]) ?></td>
                     <td bgcolor="#BED3E9" width="19%" align="right" class=text"><?=write_price($SA[1]) ?></td>
                     <td bgcolor="#BED3E9" width="21%" align="right" class=text"><?=calculate_all_time(($SA[0] * $M_LEN) / $M_DAY) ?></td>
                     <td bgcolor="#BED3E9" width="22%" align="right" class=text"><?=write_price((($SA[1] * $M_LEN) / $M_DAY))?></td>
                   </tr>
                   <tr> 
                     <td height="22" bgcolor="FFCC00" width="17%" class="header">GSM</td>
                     <td bgcolor="#91B5D9" width="21%" align="right" class=text"><?=calculate_all_time($GSM[0]) ?></td>
                     <td bgcolor="#91B5D9" width="19%" align="right" class=text"><?=write_price($GSM[1]) ?></td>
                     <td bgcolor="#91B5D9" width="21%" align="right" class=text"><?=calculate_all_time(($GSM[0] * $M_LEN) / $M_DAY)?></td>
                     <td bgcolor="#91B5D9" width="22%" align="right" class=text"><?=write_price((($GSM[1] * $M_LEN) / $M_DAY))?></td>
                   </tr>
                   <tr> 
                     <td height="22" bgcolor="#ECBD00" width="17%" class="header">U.Arasý</td>
                     <td bgcolor="#BED3E9" width="21%" align="right" class=text"><?=calculate_all_time($UA[0]) ?></td>
                     <td bgcolor="#BED3E9" width="19%" align="right" class=text"><?=write_price($UA[1]) ?></td>
                     <td bgcolor="#BED3E9" width="21%" align="right" class=text"><?=calculate_all_time(($UA[0] * $M_LEN) / $M_DAY)?></td>
                     <td bgcolor="#BED3E9" width="22%" align="right" class=text"><?=write_price((($UA[1] * $M_LEN) / $M_DAY))?></td>
                   </tr>
                   <tr> 
                     <td height="25" width="17%" bgcolor="#508AC5" class="header_beyaz2">Toplam</td>
                     <td width="21%" align="right" bgcolor="#508AC5" class="header_beyaz2"><?=calculate_all_time($total_mn) ?></td>
                     <td width="19%" align="right" bgcolor="#508AC5" class="header_beyaz2"><?=write_price($total_price) ?></td>
                     <td width="21%" align="right" bgcolor="#508AC5" class="header_beyaz2"><?=calculate_all_time(($total_mn * $M_LEN) / $M_DAY) ?></td>
                     <td width="22%" align="right" bgcolor="#508AC5" class="header_beyaz2"><?=write_price((($total_price * $M_LEN) / $M_DAY))?></td>
                   </tr>
                 </table>
                 </td>
               </tr>
             </table>
           </div>                              
           </td>
         </tr>
         <tr> 
           <td height="6"></td>
         </tr>
       </table>
       </td>
       <td height="56" valign="top" width="175"> 
       <table width="100%" border="0" cellspacing="0" cellpadding="0">
         <tr> 
           <td valign="top" height="7"> 
           <table width="175"  border="0" cellspacing="0" cellpadding="0" bgcolor="358BCF">
           <form name="main_search" action="main_src.php?act=src" method=post>
             <tr> 
               <td width="20%"><img src="<?=IMAGE_ROOT?>ara1.gif" width="29" height="28"></td>
               <td width="60%"><input type="text" name="src_str" size="12" CLASS="input1"></td>
         <td width="20%"><a href="javascript:src_form_submit();"><img src="<?=IMAGE_ROOT?>ara2.gif" border="0"></a></td>
             </tr>
           </form>
           </table>
           </td>
         </tr>
         <tr> 
           <td valign="top" height="1"></td>
         </tr>
         <tr> 
           <td valign="top"> 
           <table width="100%" border="0" cellspacing="1" cellpadding="3" bgcolor="#FFFFFF">
             <tr valign="bottom"> 
               <td height="22" colspan="2" class="header" bgcolor="#88ACD5"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">Bu Ay Giden Çaðrýlar</td>
             </tr>
             <tr> 
               <td height="15" width="20%" class="text_beyaz" align="center" bgcolor="#B3CAE3"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
               <td height="15" class="text_beyaz" width="80%" bgcolor="#D8E4F1">Þehir Ýçi : <?=$SI[2];?></td>
             </tr>
             <tr> 
               <td height="15" width="20%" class="text_beyaz" align="center" bgcolor="#B3CAE3"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
               <td height="15" class="text_beyaz" width="80%" bgcolor="#E6EEF7">Þehirlerarasý : <?=$SA[2];?></td>
             </tr>
             <tr> 
               <td height="15" width="20%" class="text_beyaz" align="center" bgcolor="#B3CAE3"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
               <td height="15" class="text_beyaz" width="80%" bgcolor="#D8E4F1">GSM : <?=$GSM[2];?></td>
             </tr>
             <tr> 
               <td height="15" width="20%" class="text_beyaz" align="center" bgcolor="#B3CAE3"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
               <td height="15" class="text_beyaz" width="80%" bgcolor="#E6EEF7">Uluslararasý : <?=$UA[2];?></td>
             </tr>
             <tr valign="bottom"> 
               <td height="22" colspan="2" class="header" bgcolor="#88ACD5"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">Bu Ay Giden Çaðrýlar</td>
             </tr>
             <tr> 
               <td height="18" colspan="2" class="text_beyaz" align="CENTER" bgcolor="#BED1E7"><br>
                 <img id="chart_outbound" src="/chart_outbound.php"><!-- OutBound End -->                      
                 <img src="<?=IMAGE_ROOT?>sure.gif"  style="cursor:hand" Value="Süre" CLASS="buton_ch" onclick="document.all('chart_outbound').src='chart_outbound.php?p=sure'">
                 <img src="<?=IMAGE_ROOT?>tutar.gif" style="cursor:hand" Value="price" CLASS="buton_ch" onclick="document.all('chart_outbound').src='chart_outbound.php?p=price'">
                 <img src="<?=IMAGE_ROOT?>adet.gif"  style="cursor:hand" Value="Adet" CLASS="buton_ch" onclick="document.all('chart_outbound').src='chart_outbound.php?p=adet'">
                 <br>
               </td>
             </tr>
       <tr bgcolor="#9BBADB"> 
               <td height="22" colspan="2" class="header" bgcolor="#88ACD5"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">Çaðrý Baþýna Ortalama</td>
             </tr>
             <tr> 
               <td height="18" width="20%" class="text_beyaz" align="center" bgcolor="#B3CAE3"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
               <td height="18" class="text_beyaz" width="80%" bgcolor="#D8E4F1">Süre: <?if($total_amn > 0){echo calculate_all_time(round($total_mn/$total_amn));}else{echo '0';}?></td>
             </tr>
             <tr> 
               <td height="18" width="20%" class="text_beyaz" align="center" bgcolor="#B3CAE3"><img src="<?=IMAGE_ROOT?>ok2.gif" border="0"></td>
               <td height="18" class="text_beyaz" width="80%" bgcolor="#E6EEF7">Tutar: <?if($total_amn > 0){echo write_price(round($total_price/$total_amn));}else{echo '0';}?> TL</td>
             </tr>
             <tr> 
               <td height="22" colspan="2" bgcolor="#91B5D9" class="header"><img src="<?=IMAGE_ROOT?>ok_header.gif" width="9" height="8">CRYSTALINFO ' dan</td>
             </tr>
             <tr> 
               <td valign="top" height="18" colspan="2" bgcolor="#BED3E9" class="text"> 
               <?     ////////////////COMPANY AVERAGE
               $res_arr = get_heavy_sql_result(8);
               if (is_array($res_arr)){
                 for($i=0;$i<=sizeof($res_arr);$i++){//Gelen dizi içinde benim sitem bulunmalý
                   if ($res_arr[$i][0][1]==$SITE_ID){ //Ýlgili kaydýn ilk alanýnýn deðeri
                     $COMP_AVG = $res_arr[$i][1][1];
                   }
                 }
               }
               $res_arr = get_heavy_sql_result(9);
               if (is_array($res_arr)){
                 for($i=0;$i<=sizeof($res_arr);$i++){//Gelen dizi içinde benim sitem bulunmalý
                   if ($res_arr[$i][0][1]==$SITE_ID){ //Ýlgili kaydýn ilk alanýnýn deðeri
                     $EXT_COUNT = $res_arr[$i][1][1];
                   }
                 }
               }
               if($EXT_COUNT > 0) {
                 $PRICE = ($COMP_AVG / $EXT_COUNT);
                 $PRICE = write_price($PRICE);
               echo " <b> Dünkü kullanýmýnýz :</b><br>Dahili baþýna ortalama <br>".$PRICE." TL'dir.";
               }
               ?>
               </td>
             </tr>
           </table>
         </td>
       </tr>
       <tr> 
         <td valign="top" height="5">
         <TABLE>
   </TABLE>         
         </td>
       </tr>
    </table>
    </td>
 </tr>
</table>
<?page_footer("");?>
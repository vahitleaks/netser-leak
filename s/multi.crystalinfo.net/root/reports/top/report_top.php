<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
     session_cache_limiter('nocache');     
     require_valid_login();
     //Hak Kontrolü
    if (right_get("SITE_ADMIN")){
        if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
        //Site admin hakký varsa herþeyi görebilir.  
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
    // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
      $SITE_ID = $SESSION['site_id'];
    }elseif(got_dept_right($SESSION["user_id"])==1){
    //Bir departmanýn raporunu görebiliyorsa kendi sitesindekileri girebilir.
      $SITE_ID = $SESSION['site_id'];
    }else{
      print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 
 
  $start = $cUtility->myMicrotime();
     cc_page_meta();
  echo "<center>";
     page_header();
?>
  <script LANGUAGE="javascript" src="/scripts/popup.js"></script>
  <form name="top_search" method="post" action="report_top_prn.php?act=src">
<table cellpadding="0" cellspacing="0" align="center" border="0" width="98%">
  <tr height="8"><td colspan="3"></td></tr>
  <tr>
    <td colspan="3">
      <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
      <tr>  
         <td width="35%" align="right" class="td1_koyu">Site Adý</td>
           <td width="5%"></td>
             <td>
                 <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?>>
                     <?
                    if(right_get("ADMIN") || right_get("ALL_REPORT")){
						$strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES WHERE SITE_ID=$SITE_ID ORDER BY SITE_NAME";
						echo $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $SITE_ID);
					}if (right_get("SITE_ADMIN")){
						$strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ORDER BY SITE_NAME";
						echo $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $SITE_ID);
					}
                     ?>
                 </select>
             </td>
      </tr>
     </table>
     </td>
     </tr>
  <tr height="8"><td colspan="3"></td></tr>   
  <tr>
    <td width="50%">
      <?table_header("Top Raporlar","100%");?>
      <center>
        <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
          <input type="hidden" name="myrep_type" value="">
        <tr height="22"> 
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_number" onclick="javascript:set_me('call_amount_number')"></td>
          <td width="90%" class="td1_acik" id="call_amount_number">En Çok <b>Aranan Numaralar</b></td>
        </tr>
        <tr height="22"> 
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_ext" onclick="javascript:set_me('call_amount_ext')"></td>
          <td width="90%" class="td1_acik" id="call_amount_ext">En Çok <b>Arama Yapan Dahililer</b></td>
        </tr>
        <tr height="22">
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_duration_ext" onclick="javascript:set_me('call_duration_ext')"></td>
          <td width="90%" class="td1_acik" id="call_duration_ext">En <b>Uzun Süre Görüþme Yapan Dahililer</b></td>
        </tr>    
        <tr height="22"> 
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_duration_number" onclick="javascript:set_me('call_duration_number')"></td>
          <td width="90%" class="td1_acik" id="call_duration_number">En <b>Uzun Süre Görüþülen Numaralar</b></td>
               </tr>
        <tr height="22">
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_city" onclick="javascript:set_me('call_amount_city')"></td>
          <td width="90%" class="td1_acik" id="call_amount_city">En Fazla <b>Aranan Ýller</b></td>
               </tr>
        <tr height="22">
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_country" onclick="javascript:set_me('call_amount_country')"></td>
          <td width="90%" class="td1_acik" id="call_amount_country">En Fazla <b>Aranan Ülkeler</b></td>
        </tr>
        <tr height="22"> 
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_time" onclick="javascript:set_me('call_amount_time')"></td>
          <td width="90%" class="td1_acik" id="call_amount_time">En Fazla <b>Arama Yapýlan Saatler</b></td>
        </tr>
        <tr height="22">
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_gsm" onclick="javascript:set_me('call_amount_gsm')"></td>
          <td width="90%" class="td1_acik" id="call_amount_gsm">En Fazla <b>Arama Yapýlan GSM Operatörleri</b></td>
        </tr>
         <tr height="22">  
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_gsm_number" onclick="javascript:set_me('call_amount_gsm_number')"></td>
          <td width="90%" class="td1_acik" id="call_amount_gsm_number">En Fazla <b>Aranan GSM Numaralarý</b></td>
             </tr>
        <tr height="22">  
          <td width="10%"><input type="radio" class="input1" name="top_rep" value="call_amount_auth" onclick="javascript:set_me('call_amount_auth')"></td>
          <td width="90%" class="td1_acik" id="call_amount_auth">En Fazla <b>Arama Yapan Otorizasyon Kodlarý</b></td>
             </tr>
                <tr> 
                   <td width="10%"><input class="input1" type="checkbox" name="CSV_EXPORT" VALUE="1" size="8"></td>
            <td width="90%" class="td1_koyu">Csv Export</td>
          </tr>         
      </table>
      <?table_footer();?>
    </td>
    <td width="2%"></td>
    <td nowrap width="48%" valign="top">
      <?table_header("Kriterler","100%");?>
      <center>
      <table cellspacing="0" cellpadding="0" border="0">
        <tr>
          <td colspan="1" class="td1_koyu">Kayýt Adedi</td>
          <td colspan="6"><input type="text" class="input1" size="5" name="record1" VALUE="<?echo $record1?>"></td>
        </tr>
        <tr>
          <td colspan="1" class="td1_koyu">Son</td>
          <td colspan="6">
            <select name ="last" style="width:45" class="select1" onchange="javascript:set_date1(this.value);" >
              <option value="-1"></option>
              <?for ($i=1;$i<21;$i++){
                echo "<option value=\"$i\">".$i."</option>";
              }?>
            </select> gün
          </td>
        </tr>
        <tr align="left">
          <td colspan="1" align="left" class="td1_koyu">Tarih:&nbsp&nbsp
                <td colspan="6">
            <select name="MY_DATE" style="width:100;" class="select1" onchange="javascript:set_date(this.value);">
              <option  value="-1" selected></option>            
                     <option  value="b">Bugün</option>
            <option  value="f">Dün</option>
            <option  value="h">Bu Hafta</option>
            <option  value="i">Geçen Hafta</option>            
            <option  value="c" selected>Bu Ay</option>
            <option  value="g">Geçen Ay</option>
            <option  value="d">------</option>
            <option  value="e">Tarih Seç</option>
                  </select>
               </td>
        </tr>
        <tr height="60">
          <td colspan="7">
            <table>
              <tr id="tarih_bas" style="display:none;"> 
                         <td width="50%" colspan="1" class="td1_koyu">Baþ. Tarihi:(gg/aa/yyyy)</td>
                <td width="50%" colspan="6">
                    <input type="text" size=17 name="t0" VALUE="<?echo $t0?>" ><a href="javascript://"><img align="absmiddle" src="<?=IMAGE_ROOT?>takvim_icon.gif" onclick="javascript:show_calendar(document.all('t0').name,null,null,null,window.event.screenX,window.event.screenY,1);" border="0"></a>
                </td>
              </tr>
              <tr id="tarih_bit" style="display:none;">
                <td width="50%" colspan="1" class="td1_koyu">Bit. Tarihi:(gg/aa/yyyy)</td>
                <td width="50%" colspan="6">
                  <input type="text" size=17 name="t1" VALUE="<?echo $t1?>" ><a href="javascript://"><img align="absmiddle" src="<?=IMAGE_ROOT?>takvim_icon.gif" onclick="javascript:show_calendar(document.all('t1').name,null,null,null,window.event.screenX,window.event.screenY,1);" border="0"></a>
                </td>
              </tr>  
            </table>  
          </td>        
        </tr>
        <tr>
          <td colspan="1" class="td1_koyu">Hafta Ýçi:</td>
          <td colspan="1">
            <input type="radio" name="hafta" value="1">
          </td>  
          <td colspan="2" class="td1_koyu" nowrap>Hafta Sonu</td>
          <td colspan="1">
            <input type="radio" name="hafta" value="2">
          </td>
          <td colspan="1" class="td1_koyu">Tümü</td>
          <td colspan="1">
            <input type="radio" name="hafta" value="3" checked>
          </td>
        </tr>
        <tr>
          <td colspan="1" class="td1_koyu">Mesai Ýçi:</td>
          <td colspan="1">
            <input type="radio" name="mesai" value="1" onclick="javascript:set_hours(this.value)">
          </td>  
          <td colspan="2" class="td1_koyu">Mesai Dýþý</td>
          <td colspan="1">
            <input type="radio" name="mesai" value="2" onclick="javascript:set_hours(this.value)">
          </td>
          <td colspan="1" class="td1_koyu">Tümü</td>
          <td colspan="1">
            <input type="radio" name="mesai" value="3" onclick="javascript:set_hours(this.value)" checked>
          </td>
        </tr>
        <tr>
          <td width="25%" class="td1_koyu">Saat Dilimi:</td>
            <td width="10%">
            <select name ="hh0" style="width:40" class="select1" onchange="javascript:set_my_min('a')">
              <option value="-1"></option>
              <?for ($i=0;$i<24;$i++){
                echo "<option value=\"$i\">".format_time($i)."</option>";
              }?>
            </select>
          </td>
          <td width="10%">  
            <select name ="hm0" style="width:40" class="select1" onchange="javascript:set_my_hour('a')">
              <option value="-1"></option>
              <?for ($i=0;$i<=59;$i++){
                echo "<option value=\"$i\">".format_time($i)."</option>";
              }?>
            </select>
          </td>
          <td width="10%" class="td1_koyu">'dan</td>
            <td width="10%">
            <select name ="hh1" style="width:40" class="select1" onchange="javascript:set_my_min('b')">
              <option value="-1"></option>
              <?for ($i=0;$i<24;$i++){
                echo "<option value=\"$i\">".format_time($i)."</option>";
              }?>
            </select>
          </td>  
          <td width="10%">  
            <select name ="hm1" style="width:40" class="select1" onchange="javascript:set_my_hour('b')">
              <option value="-1"></option>
              <?for ($i=0;$i<=59;$i++){
                echo "<option value=\"$i\">".format_time($i)."</option>";
              }?>
            </select>
          </td>
          <td width="10%" class="td1_koyu">'a</td>      
        </tr>
      </table>
      <?table_footer();?>
    </td>
  </tr>
  <tr>
      <td colspan=4 align=center><br>
           <a href="javascript:submit_form('top_search');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>raporal.gif"></a>
         </td>
     </tr>  
</table>    
</form>
   <a href="report_top.php"><img border="0" src="<?=IMAGE_ROOT?>kriter_temizle.gif"></a>
<?page_footer(0);?>
<script language="javascript" src="/scripts/form_validate.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
  
  //Time functions starts here
  function set_my_hour(my_val){
    if (my_val=='a'){
      if ((document.all('hm0').value == '-1') && (document.all('hh0').value != '-1'))
        document.all('hh0').value = '-1';
      else if ((document.all('hm0').value != '-1')&&(document.all('hh0').value == '-1'))
        document.all('hh0').value = '0';
    }else if (my_val=='b'){
        if ((document.all('hm1').value == '-1')&&(document.all('hh1').value != '-1'))
          document.all('hh1').value = '-1'
        else if ((document.all('hm1').value != '-1')&&(document.all('hh1').value == '-1'))
          document.all('hh1').value = '0';  
    }
  }  

  function set_my_min(my_val){
    if (my_val=='a'){
      if ((document.all('hh0').value == '-1')&&(document.all('hm0').value != '-1'))
        document.all('hm0').value = '-1';
      else if ((document.all('hh0').value != '-1')&&(document.all('hm0').value == '-1'))
        document.all('hm0').value = '0';
    }else if (my_val=='b'){
      if ((document.all('hh1').value == '-1')&&(document.all('hm1').value != '-1'))
        document.all('hm1').value = '-1';
      else if ((document.all('hh1').value != '-1')&&(document.all('hm1').value == '-1'))
        document.all('hm1').value = '0';
    }
  }

  function set_hours(my_val){
    if (my_val=='1') {
      document.all('hh0').value = '9';
      document.all('hm0').value = '0';
      document.all('hh1').value = '18';
      document.all('hm1').value = '0';
      }
    else if (my_val=='2') {
      document.all('hh0').value = '18';
      document.all('hm0').value = '0';
      document.all('hh1').value = '9';
      document.all('hm1').value = '0';
      }
    else if (my_val=='3') {
      document.all('hh0').value = '-1';
      document.all('hm0').value = '-1';
      document.all('hh1').value = '-1';
      document.all('hm1').value = '-1';
      }
      else return;                      
    }

  
    function set_date(n_value){
      document.all("tarih_bas").style.display='none';
      document.all("tarih_bit").style.display='none';
      document.all('last').value='-1'
      if (n_value == 'e'){
        document.all("tarih_bas").style.display='';
        document.all("tarih_bit").style.display='';
      }else if (n_value > 0){
        document.all('MY_DATE').value='-1'
      }else return;
    }  
    function set_date1(n_value){
      document.all("tarih_bas").style.display='none';
      document.all("tarih_bit").style.display='none';
      if (n_value > 0){
        document.all('MY_DATE').value='-1'
      }else return;
    }
//Time functions ends here

  
  function set_me(mytype){
     var myoldtype; 
	 if (document.all('myrep_type').value!='')
	   myoldtype = document.all('myrep_type').value;
     if (myoldtype == mytype){return;}
	 document.all('myrep_type').value = mytype;
	 document.all(mytype).className = "header_beyaz3";
     if(myoldtype != ''){
	   document.all(myoldtype).className = "td1_acik";
     }
  }
  
  function submit_form(){
    var mytype = document.all('myrep_type').value;
        popup('','report_screen',800,600)
    document.all('SITE_ID').disabled=false;    
    document.all('top_search').action = 'report_top_prn.php?act=src&type=' + mytype;
        document.all('top_search').target= 'report_screen';
        document.all('top_search').submit();
  }
//-->
</script>
<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
     session_cache_limiter('nocache');
     require_valid_login();
    //Hak Kontrolü
    if (right_get("SITE_ADMIN")){
        //Site admin hakký varsa herþeyi görebilir.  
      if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
      $site_crt="SITE_ID =".$SITE_ID;
      $dept_crt = " WHERE 1=1";
      $strSiteSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ORDER BY SITE_NAME";
    }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
    // Admin vaye ALL_REPORT hakký varsa kendi sitesindeki herþeyi görebilir.
      $SITE_ID = $SESSION['site_id'];
      $site_crt="SITE_ID =".$SITE_ID;
      $dept_crt = " WHERE 1=1";
      $strSiteSQL = "SELECT  SITES.SITE_ID, SITE_NAME FROM SITES 
                        left join DEPT_REP_RIGHTS on DEPT_REP_RIGHTS.SITE_ID=SITES.SITE_ID 
                        where USER_ID= ".$SESSION['user_id']." group by SITES.SITE_ID, SITE_NAME ORDER BY SITE_NAME";
    }elseif(got_dept_right($SESSION["user_id"])==1){
    //Bir departmanýn raporunu görebiliyorsa kendi sitesindekileri girebilir.
      $SITE_ID = $SESSION['site_id'];
      $site_crt="SITE_ID =".$SITE_ID;
      $dept_id_string = get_dept_list($SESSION["user_id"]);
      $dept_crt = " WHERE DEPT_ID IN($dept_id_string)";
      $strSiteSQL = "SELECT  SITES.SITE_ID, SITE_NAME FROM SITES
                        left join DEPT_REP_RIGHTS on DEPT_REP_RIGHTS.SITE_ID=SITES.SITE_ID
                        where USER_ID= ".$SESSION['user_id']." group by SITES.SITE_ID, SITE_NAME ORDER BY SITE_NAME";
    }else{
            print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    } 
  //Hak kontrolü sonu  
   $kriter = $dept_crt." AND ".$site_crt;
   $start = $cUtility->myMicrotime();

  cc_page_meta();
  fillsecondcombo();
  echo "<center>";
  page_header();
?>
<script LANGUAGE="javascript" src="/scripts/popup.js"></script>
<form name="general_search" method="post" action="report_general_prn.php?act=src">
<table cellpadding="0" cellspacing="0" align="center" border="0" width="98%">
  <tr height="8"><td colspan="3"></td></tr>
  <tr>
    <td colspan="3">
      <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
      <tr>  
         <td width="35%" align="right" class="td1_koyu">Site Adý</td>
           <td width="5%"></td>
             <td>
                 <select name="SITE_ID" class="select1" style="width:200" onchange="FillSecondCombo('DEPT_ID', 'DEPT_NAME', '01SITE_ID='+ this.value , '' , 'DEPT_ID' , this.value)">
                     <?
                         //$strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ORDER BY SITE_NAME";
                         echo $cUtility->FillComboValuesWSQL($conn, $strSiteSQL, false,  $SITE_ID);
                     ?>
                 </select>
             </td>
      </tr>
     </table>
     </td>
     </tr>
  <tr height="8"><td colspan="3"></td></tr>   
  <tr>
    <input type="hidden" name="myrep_type" value="">
    <td width="50%" valign=top>
    <?table_header("Özel Sicil Raporu","100%");?>
    <center>
      <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
      <tr id="dept_select"> 
        <td width="45%" class="td1_acik" colspan=2>Departman
          <select name="DEPT_ID" class="select1" style="width:250;">
          <?  
            $strSQL = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS ".$kriter. " ORDER BY DEPT_NAME";
            echo  $cUtility->FillComboValuesWSQL($conn, $strSQL, true,  $DEPT_ID);
          ?>
          </select>
        </td>  
      <tr> 
        <td colspan="2">
        <table border=0 width="100%" cellspacing=0 cellpadding=0>
          <tr>
            <td  width="29%" class="td1_koyu">Çýktý Formatý</td>
            <td width="16%"><input type="radio" name="CSV_EXPORT" VALUE="0" size="15" CHECKED>Html</td>
            <td width="25%"><input type="radio" name="CSV_EXPORT" VALUE="1" size="15">Csv Export</td>
            <td width="30%"><input type="radio" name="CSV_EXPORT" VALUE="2" size="15">Excel Export</td>
          </tr>
        </table>        
        </td>
      <tr>
        <td width="10%"><image id=DivImg  src=/images/arti.gif onclick="openDiv(this)"  value="eksi"></td>
        <td width="90%" class="td1_koyu"></td>
      </tr>
      <tr id=div1>
        <td width="10%"><input type="radio" class="cache1" name="withCache" value="DBwriteCache" CHECKED></td>
        <td width="90%" class="td1_koyu">Veritabanýndan Al Hýzlý Hafýzaya Yaz</td>
      </tr>
      <tr id=div2>
        <td width="10%"><input type="radio" class="cache1"name="withCache" value="DBnotCache"></td>
        <td width="90%" class="td1_koyu">Veritabanýndan Al Hýzlý Hafýzaya Yazma</td>
      </tr>
      <tr id=div3>
        <td width="10%"><input type="radio" class="cache1" name="withCache" value="callCache" ></td>
        <td width="90%" class="td1_koyu">Hýzlý Hafýzadan Çaðýr</td>
      </tr>
      <tr id=div4> 
        <td width="10%"><input type="checkbox" name="forceMainTable" VALUE="1" size="15"></td>
        <td width="60%" class="td1_koyu">Ana Tablodan Al</td>
      </tr>
    </table>
    <script>
      var DivCnt=4
      function openDiv(objimg){
        if(objimg.value=='arti'){
          for(k=1;k<=DivCnt;k++)
            document.all('div'+k).style.display="";
          objimg.value='eksi';
          objimg.src='/images/eksi.gif'
        }else{
          for(k=1;k<=DivCnt;k++)
            document.all('div'+k).style.display="none";
          objimg.value='arti';
          objimg.src='/images/arti.gif'
        }
      }
      openDiv(document.all('DivImg'));
    </script> 
    <?table_footer();?>
    </td>
  <td width="2%"></td>
  <td nowrap width="48%" valign="top">
    <?table_header("Kriterler","100%");?>
    <center>
    <table cellspacing="0" cellpadding="0" border="0">
      <tr>
        <td colspan="1" class="td1_koyu">Kayýt Adedi</td>
        <td colspan="6"><input type="text" class="input1" size="5" name="record" VALUE="<?echo $record?>"></td>
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
           <a href="javascript:submit_form('general_search');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>raporal.gif"></a>
         </td>
     </tr>      
</table>    
   </form>
   <a href="report_general.php"><img border="0" src="<?=IMAGE_ROOT?>kriter_temizle.gif"></a>
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
      myoldtype = document.all('myrep_type').value;
	  if (myoldtype == mytype){return;}
	  document.all('myrep_type').value = mytype;
	  document.all(mytype).className = "header_beyaz2";
      if(myoldtype != ''){
	    document.all(myoldtype).className = "td1_acik";
      }
      if(mytype=='ext' || mytype=='ext_time'){
        document.all('dept_select').style.display = '';
      }else{
        document.all('dept_select').style.display = 'none';
      }
     }

  function submit_form(){
    popup('','report_screen',800,600)    
     if(document.all('SITE_ID').disabled){
           document.all('SITE_ID').disabled=false; 
           var closeIt=2;
     }   
    document.all('general_search').target= 'report_screen';
    document.all('general_search').action = 'report_call_ext.php?act=src&type=ext';
    document.all('general_search').submit();
    if(closeIt==2){
      document.all('SITE_ID').disabled=true; 
    }
  } 
</script>


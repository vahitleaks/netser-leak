<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
     session_cache_limiter('nocache');     
     require_valid_login();
     cc_page_meta();
     $SITE_ID = $SESSION['site_id'];
     fillsecondcombo();
     echo "<center>";
     page_header();
     $kriter = " WHERE SITE_ID=$SITE_ID";
?>
<form name="call_search" method="post" action="fortis_dept_mailing.php?act=src">
<table cellpadding="0" cellspacing="0" align="center" border="0" width="98%">
  <tr height="8"><td colspan="3"></td></tr>
  <tr>
    <td colspan="3">
      <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
      <tr>  
         <td width="35%" align="right" class="td1_koyu">Site Adý</td>
           <td width="5%"></td>
             <td>
                 <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?> onchange="Fillothercombos(this.value)">
                     <?
                         $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ORDER BY SITE_NAME";
                         echo $cUtility->FillComboValuesWSQL($conn, $strSQL, false,  $SITE_ID);
                     ?>
                 </select>
             </td>
      </tr>
     </table>
     </td>
     </tr>
  <tr height="8"><td colspan="3"></td></tr>   
  <tr>
    <td nowrap width="100%" valign="top">
    <?table_header("Departman Çaðrý Analizi","60%");?>
    <center>
      <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
        <tr id="dept_select"> 
        <td width="5%" class="td1_koyu"><b>Departman</b></td>
               <td width="95%">
                    <select name="DEPT_ID" id="selDeptId" class="select1" style="width:250;">
		      <OPTION VALUE="-1">--Seciniz--</OPTION>
                      <?  
                        $strSQL = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS ".$kriter. " ORDER BY DEPT_NAME";
                          echo  $cUtility->FillComboValuesWSQL($conn, $strSQL, FALSE,  $DEPT_ID);
                        ?>
                   </select>&nbsp;
                    <input type="button" value="..." onclick="openSelectDept()">
                    <script>
                      function openSelectDept(){
                        if(document.all('SITE_ID').value!='-1')
                          popup('/special/depts/select_dept.php?SITE_ID='+document.all('SITE_ID').value,'select_screen',800,600);
                        else
                          alert('Lütfen Önce Site seçiniz!');
                      }
                    </script>
</td>
      </tr>     
        <tr> 
        <td width="20%" align=right colspan=2>&nbsp;</td>
        <tr id="dept_select"> 
        <td width="25%" align=right>
            <input  type="checkbox" name="CSV_EXPORT" VALUE="1" size="15">
         </td>
        <td width="75%" class="td1_koyu">&nbsp;&nbsp;<b>Excel Export</b></td>
        </tr>     
      </table>
    <?table_footer();?>
    <br><br>
    <?table_header("Kriterler","55%");?>
    <center>
    <table cellspacing="0" cellpadding="0" border="0" width="100%">
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
            <a href="javascript:submit_form('call_search');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>raporal.gif"></a>
       </td>
    </tr>    
</table>    
   </form>
   <a href="dept_calls.php"><img border="0" src="<?=IMAGE_ROOT?>kriter_temizle.gif"></a>
<?page_footer(0);?>
<script LANGUAGE="javascript" src="/scripts/popup.js"></script>
<script language="javascript" src="/scripts/form_validate.js"></script>
<script language="JavaScript" type="text/javascript">
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
  function Fillothercombos(my_val){
    FillSecondCombo('DEPT_ID',      'DEPT_NAME',    '01SITE_ID='+ my_val               , ''                   , 'DEPT_ID' , '');
  }

  function set_action(type){
    document.all('call_search').action = 'fortis_dept_mailing.php?act=src';
  }
  
  function submit_form(form_name){
     if(document.all('DEPT_ID').value!='' && document.all('DEPT_ID').value!='-1' ){
        if(document.all('CSV_EXPORT').checked)
          document.all(form_name).action='fortis_mailing_xls_export.php?act=src';  
        else
          document.all(form_name).action='fortis_dept_mailing.php?act=src';  
        popup('','report_screen',800,600)
        document.all('SITE_ID').disabled=false;  
        document.all(form_name).target= 'report_screen';
        document.all(form_name).submit();
	}
  }
  
//-->
</script>

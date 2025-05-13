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
?>
<form name="call_search" method="post" action="dept_calls_prn.php?act=src">
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
                    <select name="DEPT_ID" class="select1" style="width:250;">
                      <?  
                        $strSQL = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS ".$kriter. " ORDER BY DEPT_NAME";
                          echo  $cUtility->FillComboValuesWSQL($conn, $strSQL, FALSE,  $DEPT_ID);
                        ?>
                   </select>
</td>
      </tr>     
        <tr> 
        <td width="20%" align=right colspan=2>&nbsp;</td>
        <tr> 
        <td width="20%" align=right>
          <input class="input1" type="radio" name="type" value="dept" checked onclick="javascript:set_action(this.value)">    
         </td>
        <td width="80%" class="td1_koyu">&nbsp;&nbsp;<b>Departman çaðrý analizi</b></td>
        </tr>     
        <tr id="dept_select"> 
        <td width="25%" align=right>
          <input class="input1" type="radio" name="type" value="top_rep" onclick="javascript:set_action(this.value)">    
         </td>
        <td width="75%" class="td1_koyu">&nbsp;&nbsp;<b>Departmanlara göre en çok aranan numaralar</b></td>
        </tr>     

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
      <tr>
        <td colspan="1" class="td1_koyu">Yýl</td>
        <td colspan="6">
          <select name ="myyear" style="width:80" class="select1">
<?          $this_year = date("Y");
            for ($i=$this_year-3;$i<=$this_year;$i++){
              echo "<option value=\"$i\"";
              if($i == $this_year){echo "selected";}
              echo ">".$i."</option>";
            }?>
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="1" class="td1_koyu">Ay</td>
        <td colspan="6">
          <select name ="mymonth" style="width:45" class="select1">
<?          $this_month = date("n");
            for ($i=1;$i<13;$i++){
              echo "<option value=\"$i\"";
              if($i == $this_month){echo "selected";}
              echo ">".$i."</option>";
            }?>
          </select>
        </td>
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

  function Fillothercombos(my_val){
    FillSecondCombo('DEPT_ID',      'DEPT_NAME',    '01SITE_ID='+ my_val               , ''                   , 'DEPT_ID' , '');
  }

  function set_action(type){
    if(type == 'dept'){
      document.all('call_search').action = 'dept_calls_prn.php?act=src';
    }else if(type == 'top_rep'){
      document.all('call_search').action = '../top_calls/top_calls.php?act=src';
    }
  }
  
  function submit_form(form_name){
        popup('','report_screen',800,600)
        document.all('SITE_ID').disabled=false;  
        document.all(form_name).target= 'report_screen';
        document.all(form_name).submit();
  }
  
//-->
</script>

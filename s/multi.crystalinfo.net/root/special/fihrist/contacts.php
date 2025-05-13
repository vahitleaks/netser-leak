<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   session_cache_limiter('nocache');
   require_valid_login();
   $conn = $cdb->getConnection();

  //Admin veya Fihrist hakký varsa ve Site Admin hakký yoksa 
  //sadece kendi sitesine ait bilgiyi görebilmeli
 
  if ($act=="upd" && $id!="" && is_numeric($id)){
     $sql_str = "SELECT FIHRIST.* ,FIH_DEPARTMENT.DEP_NAME,FIH_DEPARTMENT.DEP_ID,FIH_TITLE.TITLE_ID,FIH_TITLE.TITLE_NAME
                     FROM FIHRIST 
                     LEFT JOIN FIH_TITLE ON FIHRIST.TITLE_ID = FIH_TITLE.TITLE_ID
                     LEFT JOIN FIH_DEPARTMENT ON FIHRIST.DEP_ID = FIH_DEPARTMENT.DEP_ID
                     WHERE CONTACT_ID = $id ";
                     
     if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }
     
     if (mysql_numrows($result)>0){
        $row = mysql_fetch_object($result);
     }else{
        print_error("Belirtilen Kayýt Bulunamadý");
        exit;
     }
  }else{
    $act="new";
  }

  cc_page_meta();
  echo "<center>";
  page_header();
  fillSecondCombo();
  echo "<center><br>";
  table_header("Fihrist","75%");if (!right_get("FIHRIST"))
?>

<script language="javascript">
  function submit_form() {
    if(check_form(document.contact)){
       document.contact.submit();
    }
  }
</script>

<table border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
<form name="contact" action="contacts_db.php?act=<? echo $act ?>" method="post" onsubmit="return check_form(this);">
<INPUT TYPE="hidden" value="<?=$id?>" name=id> 
     <tr class="form">
       <tr> 
           <td class="td1_koyu" width="25%"><span id=NAME1  STYLE="display:none"> Kontak Kiþi </span><span id=NAME2> Adý</span></td>
           <td width="75%"><input class="input1" type="text" value="<? echo $row->NAME; ?>" name="NAME" size="20" maxlength="20"></td>
       </tr>  
       <tr> 
            <td class="td1_koyu" width="25%"><span id=SURNAME1  STYLE="display:none"> Kontak Kiþi</span> Soyadý</td>
            <td width="75%"><input class="input1" type="text" value="<? echo $row->SURNAME; ?>" name="SURNAME" size="20" maxlength="20"></td>
       </tr>  
       <tr> 
           <td class="td1_koyu" width="25%">Ünvaný</td>
           <td width="75%">
             <select name="TITLE_ID" class="select1" style="width:100"  >
               <?
                 $strSQL = "SELECT TITLE_ID, TITLE_NAME FROM FIH_TITLE";
                 echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $row->TITLE_ID );
               ?>
             </select>
           </td>
       </tr>    
       <tr> 
           <td class="td1_koyu" width="25%">Birimi</td>
           <td width="75%">
             <select name="DEP_ID" class="select1" style="width:150" onchange="Fillothercombos(this.value)">
                <?
                  $strSQL = "SELECT DEP_ID, DEP_NAME FROM FIH_DEPARTMENT";
                   echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $row->DEP_ID );
                ?>
             </select>
           </td>
        </tr>  
        <tr> 
            <td class="td1_koyu" width="25%">Alt Birimi</td>
            <td width="75%">
              <select name="SUB_DEP_ID" class="select1" style="width:150"  >
                 <?
                   $strSQL = "SELECT SUB_DEP_ID, FIH_SUB_DEPT FROM FIH_SUB_DEPT WHERE DEP_ID=".$row->DEP_ID;
                   echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $row->SUB_DEP_ID );
                 ?>
               </select>
            </td>
        </tr>  
        <tr> 
            <td class="td1_koyu" width="25%">Dahili Ýþ Tel</td>
            <td width="75%"><input class="input1" type="text" value="<? echo $row->EXT_COMP_TEL; ?>" name="EXT_COMP_TEL" size="20" ></td>
        </tr>  
        <tr> 
            <td class="td1_koyu" width="25%">Dahili Ev Tel</td>
            <td width="75%"><input class="input1" type="text" value="<? echo $row->EXT_HOME_TEL; ?>" name="EXT_HOME_TEL" size="20" ></td>
        </tr>  
        <tr> 
            <td class="td1_koyu" width="25%">Harici Ýþ Tel</td>
            <td width="75%"><input class="input1" type="text" value="<? echo $row->EXTERNAL_COMP_TEL; ?>" name="EXTERNAL_COMP_TEL" size="20" ></td>
        </tr>  
        <tr> 
            <td class="td1_koyu" width="25%">Harici Ev Tel</td>
            <td width="75%"><input class="input1" type="text" value="<? echo $row->EXTERNAL_HOME_TEL; ?>" name="EXTERNAL_HOME_TEL" size="20"></td>
        </tr>   
        <tr> 
            <td class="td1_koyu" width="25%">Kiþisel E-Mail</td>
            <td width="75%"><input class="input1" type="text" value="<? echo $row->PERSONAL_EMAIL; ?>" name="PERSONAL_EMAIL" size="30" maxlength="30"></td>
        </tr>  
        <tr> 
            <td class="td1_koyu" width="25%">Adres</td>
            <td width="75%"><textarea class ="textarea1" cols="45" rows="3"  name="ADDRESS"><? echo $row->ADDRESS; ?></textarea></td>
        </tr>  
        <tr>
            <td></td>
            <td>
               <img border="0" style="cursor:hand;" src="<?=IMAGE_ROOT?>kaydet.gif" onclick="javascript:submit_form()">
            </td>
        </tr>
    
    </form>
  </table><br>
  <table align="right" width="100%" border="0">
    <tr>
      <td colspan="4" width="70%"></td>
      <td align="right"><a href="contacts_db.php?act=del&id=<?=$id?>&SITE_ID=<?=$SITE_ID?>"><img border="0" src="<?=IMAGE_ROOT?>kayit_sil.gif" style="cursor:hand;"></a></td>
      <td align="right"><a href="contact_src.php"><img border="0" src="<?=IMAGE_ROOT?>arama_yap.gif" style="cursor:hand;"></a></td>
      <td align="right"><a href="contacts.php?act=new"><img border="0" src="<?=IMAGE_ROOT?>yeni_kayit.gif" style="cursor:hand;"></a></td>
    </tr>
  </table>
   <script language="javascript" src="/scripts/form_validate.js"></script>
   <script language="javascript">
        form_fields[0] = Array ("NAME", "Adý alanýný Giriniz.",  TYP_NOT_NULL);
        form_fields[1] = Array ("SURNAME", "Soyadý Alanýný Giriniz.",  TYP_NOT_NULL );
        function Fillothercombos(my_val){
          FillSecondCombo('SUB_DEP_ID',      'FIH_SUB_DEPT',    '11DEP_ID='+ my_val               , ''                   , 'SUB_DEP_ID' , '');
        }        
        function del_record(REC){
         if (confirm("Bu kaydý silmek istediðinizden emin misiniz?")) {
            popup('phone_db.php?act=del&id='+REC,'FIHRIST',500,500);
         }
         else 
         return false;
      }   
   </script> 
<? table_footer();
   page_footer(0);
?>
</body>
</html>


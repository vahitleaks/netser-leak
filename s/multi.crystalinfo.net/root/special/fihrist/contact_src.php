<?
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  require_valid_login();

  $conn = $cdb->getConnection();
  if ($p=="" || $p < 1)
     $p = 1;
  $start = $cUtility->myMicrotime();

  cc_page_meta();
  echo "<center>";
  fillSecondCombo();
  page_header();
  echo "<br><br>";
  table_header_mavi("Fihrist Arama","75%");
?>
 <form name="contact_search" method="post" action="contact_src.php?act=src">
   <input type="hidden" name="p" VALUE="<?echo $p?>">
   <input type="hidden" name="ORDERBY" value="<?=$ORDERBY?>">
   <center>
     <table cellpadding="0" cellspacing="0" ALIGN="center" border="0" width="65%">      
       <tr> 
         <td class="font_beyaz" width="25%"> Adý</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $NAME; ?>" name="NAME" size="20" maxlength="20"></td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%"> Soyadý</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $SURNAME; ?>" name="SURNAME" size="20" maxlength="20"></td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%">Ünvaný</td>
         <td width="75%">
           <select name="TITLE_ID" class="select1" style="width:100" >
              <?
                 $strSQL = "SELECT TITLE_ID, TITLE_NAME FROM FIH_TITLE";
                  echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $TITLE_ID );
               ?>
           </select>
         </td>
       </tr>    
       <tr> 
         <td class="font_beyaz" width="25%">Birimi</td>
         <td width="75%">
           <select name="DEP_ID" class="select1" style="width:150" onchange="Fillothercombos(this.value)">
              <?
                $strSQL = "SELECT DEP_ID, DEP_NAME FROM FIH_DEPARTMENT";
                 echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $DEP_ID );
              ?>
           </select>
         </td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%">Alt Birimi</td>
         <td width="75%">
           <select name="SUB_DEP_ID" class="select1" style="width:150"  >
              <?
                 $strSQL = "SELECT SUB_DEP_ID, FIH_SUB_DEPT FROM FIH_SUB_DEPT ORDER BY FIH_SUB_DEPT";
                  echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $SUB_DEP_ID );
               ?>
           </select>
         </td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%">Dahili Ýþ Tel</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $EXT_COMP_TEL; ?>" name="EXT_COMP_TEL" size="20" ></td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%">Dahili Ev Tel</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $EXT_HOME_TEL; ?>" name="EXT_HOME_TEL" size="20" ></td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%">Harici Ýþ Tel</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $EXTERNAL_COMP_TEL; ?>" name="EXTERNAL_COMP_TEL" size="20" ></td>
       </tr>  
       <tr> 
         <td class="font_beyaz" width="25%">Harici Ev Tel</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $EXTERNAL_HOME_TEL; ?>" name="EXTERNAL_HOME_TEL" size="20"></td>
       </tr>   
       <tr> 
         <td class="font_beyaz" width="25%">Kiþisel E-Mail</td>
         <td width="75%"><input class="input1" type="text" value="<? echo $PERSONAL_EMAIL; ?>" name="PERSONAL_EMAIL" size="30" maxlength="30"></td>
       </tr> 
       <tr>
         <td colspan=2 align=center><br>
           <a href="javascript:submit_form('contact_search')">
           <img name="Image631" border="0" src="<?=IMAGE_ROOT?>ara.gif"></a>
         </td>
       </tr>
    </table>
   </form>
   <table width="100%"> 
     <tr>
       <td width="100%" align="right">
       <a href="contacts.php?act=new"><img border="0" src="<?=IMAGE_ROOT?>yeni_kayit1.gif" style="cursor:hand;"></a>
       </td>
     </tr>  
   </table>
<?
   table_footer_mavi();
   if ($act == "src") {
      $kriter = "";   
     
      $kriter .= $cdb->field_query($kriter, "NAME"                   ,  "LIKE",  "'%$NAME%'"); 
      $kriter .= $cdb->field_query($kriter, "SURNAME"                ,  "LIKE",  "'%$SURNAME%'");
      $kriter .= $cdb->field_query($kriter, "TITLE_ID"               ,  "="   ,  "$TITLE_ID");
      $kriter .= $cdb->field_query($kriter, "POSITION"               ,  "LIKE",  "'%$POSITION%'");
      $kriter .= $cdb->field_query($kriter, "DEP_ID"                 ,  "="   ,  "$DEP_ID");
      $kriter .= $cdb->field_query($kriter, "SUB_DEP_ID"             ,  "="   ,  "$SUB_DEP_ID");
      $kriter .= $cdb->field_query($kriter, "EXT_COMP_TEL"           ,  "LIKE",  "'%$EXT_COMP_TEL%'");
      $kriter .= $cdb->field_query($kriter, "EXT_HOME_TEL"           ,  "LIKE",  "'%$EXT_HOME_TEL%'");
      $kriter .= $cdb->field_query($kriter, "EXTERNAL_COMP_TEL"      ,  "LIKE",  "'%$EXTERNAL_COMP_TEL%'");
      $kriter .= $cdb->field_query($kriter, "EXTERNAL_HOME_TEL"      ,  "LIKE",  "'%$EXTERNAL_HOME_TEL%'"); 
      $kriter .= $cdb->field_query($kriter, "PERSONAL_EMAIL"         ,  "LIKE",  "'%$PERSONAL_EMAIL%'");       
         
      $sql_str  = "SELECT NAME,SURNAME,PERSONAL_EMAIL,CONTACT_ID,EXT_COMP_TEL,EXT_HOME_TEL 
                    FROM FIHRIST ";
       if ($kriter != "")
          $sql_str .= " WHERE ". $kriter;  
       if ($ORDERBY) {
          $sql_str .= " ORDER BY ". $ORDERBY ;      
       }
         
       $rs = $cdb->get_Records($sql_str, $p, $page_size,  $pageCount, $recCount);    
       $stop = $cUtility->myMicrotime();
?>
<br><br>
<?
       table_arama_header("95%");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td class="sonuc" width="50%" height="20"><? echo $cdb->calc_current_page($p, $recCount, $page_size);?></td>
    <td class="sonuc" align="right" width="50%" height="20"><? $cdb->show_time(($stop -$start)); ?></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="header_beyaz">
        <td width="5%"><a href="javascript:submit_form('contact_search',1, 'CONTACT_ID')">ID</a></td>
        <td width="15%"><a href="javascript:submit_form('contact_search',1, 'NAME')">Adý</a></td>
        <td width="15%"><a href="javascript:submit_form('contact_search',1, 'SURNAME')">Soyadý</a></td>
        <td width="15%"><a href="javascript:submit_form('contact_search',1, 'PERSONAL_EMAIL')">EMAIL</a></td>
        <td width="15%"><a href="javascript:submit_form('contact_search',1, 'EXT_COMP_TEL')">Dahili Ýþ Tel.</a></td>
        <td width="15%"><a href="javascript:submit_form('contact_search',1, 'EXT_HOME_TEL')">Dahili Ev Tel.</a></td>    
        <td>Güncelle</td>
    </tr>
<? 
   $i;
   while($row = mysql_fetch_object($rs)){
        $i++;
        echo " <tr class=\"".bgc($i)."\">".CR;
        echo " <td height=\"20\">$row->CONTACT_ID</td> ".CR;
        echo " <td>$row->NAME</td>".CR;
        echo " <td>$row->SURNAME</td>".CR;
        echo " <td>$row->PERSONAL_EMAIL</td>".CR;           
        echo " <td>$row->EXT_COMP_TEL</td>".CR;
        echo " <td>$row->EXT_HOME_TEL</td>".CR;               
        echo " <td><a HREF=\"contacts.php?act=upd&id=$row->CONTACT_ID\">Güncelle</td>".CR;
        echo "</tr>".CR;
        list_line(18);           
   }
?>
</table>
<table width="80%" align="center">
    <tr>
        <td align="center">
          <? echo $cdb->get_paging($pageCount, $p, "contact_search", $ORDERBY); ?>
        </td>
    </tr>
</table>
<?table_arama_footer(); }?>

<script >
<!--
    function Fillothercombos(my_val){
          FillSecondCombo('SUB_DEP_ID', 'FIH_SUB_DEPT', '11DEP_ID='+ my_val , ''  ,'SUB_DEP_ID' , '');
    }        
    function submit_form(form_name, page, sortby){
          document.all("ORDERBY").value = sortby;
          if (!sortby)
                document.all("ORDERBY").value = '';
          document.all("p").value = page;
          document.all(form_name).submit();
    }
//-->
</script>

<?page_footer(0);?>

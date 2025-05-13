<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   require_valid_login();
   $conn = $cdb->getConnection();

//Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Burayý Görme Hakkýnýz Yok");
    exit;
   }
//Site Admin Hakký Yoksa sadece kendisine baðlý kayýtlarý görsün   
     if (right_get("ADMIN") && !right_get("SITE_ADMIN")){
         $SITE_ID = $SESSION['site_id'];
     }

   if ($p=="" || $p < 1)
       $p = 1;

   $start = $cUtility->myMicrotime();

    cc_page_meta();
    echo "<center>";
    fillSecondCombo();  
    page_header();
    echo "<BR><BR>";   
    table_header_mavi("Kullanýcý Arama",'50%');
?>
   <form name="user_arama" method="post" action="user_src.php?act=src">
       <input type="hidden" name="p" VALUE="<?echo $p?>">
       <input type="hidden" name="ORDERBY" value="<?=$ORDERBY?>">
       <table cellpadding=0 cellspacing=0 ALIGN="center">
            <tr class="form">
                <td class="font_beyaz">Site Adý</td>
                <td>
                    <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?> onchange="FillSecondCombo('DEPT_ID', 'DEPT_NAME', '01SITE_ID='+ this.value , '' , 'DEPT_ID' , this.value)">
                    <?
                        $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ";
                        echo $cUtility->FillComboValuesWSQL($conn, $strSQL, true, $SITE_ID);
                    ?>
                    </select>
                   </td>
            </tr>
            <tr> 
                  <td width="30%" class="font_beyaz" ALIGN="right">Adý&nbsp;&nbsp;</td>
                  <td width="70%"><input type="text" class="input1" name="NAME" VALUE="<?echo $NAME?>" size="25" Maxlength="30"></td>
            </tr>
            <tr> 
                  <td width="30%" class="font_beyaz" ALIGN="right">Soyadý&nbsp;&nbsp;</td>
                  <td width="70%"><input type="text" class="input1" name="SURNAME" VALUE="<?echo $SURNAME?>" size="25" Maxlength="15"></td>
            </tr>
            <tr> 
                  <td width="30%" class="font_beyaz" ALIGN="right">Departmaný&nbsp;&nbsp;</td>
                  <td width="70%">
                        <select name="DEPT_ID" class="select1" style="width:165">
                            <?
                             $strSQL = "SELECT DEPT_ID, DEPT_NAME FROM DEPTS ";
                             echo    $cUtility->FillComboValuesWSQL($conn, $strSQL, true,  $DEPT_ID);
                           ?>
                        </select>
                  </td>
            </tr>
            <tr>
                <td colspan=2 align=center><br>
                  <a href="javascript:submit_form('user_arama');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>ara.gif"></a>
                </td>
            </tr>
   </form>
      </table>
       <table width="100%"> 
      <tr>
        <td width="100%" align="right">
        <a href="user.php?act=new"><img border="0" src="<?=IMAGE_ROOT?>yeni_kayit1.gif" style="cursor:hand;"></a>
        </td>
      </tr>  
    </table>
<?
   table_footer_mavi();

   if ($act == "src") {
         $kriter = "";   

         $kriter .= $cdb->field_query($kriter, "USERS.USER_ID", "<>", "1");
         $kriter .= $cdb->field_query($kriter, "USERS.NAME", "LIKE", "'%$NAME%'");
         $kriter .= $cdb->field_query($kriter, "USERS.SURNAME", "LIKE", "'%$SURNAME%'");

         if ($SITE_ID<>'-1'){
             $kriter .= $cdb->field_query($kriter, "SITES.SITE_ID",  "=",    "'$SITE_ID'");
         }
         if ($DEPT_ID !="-1")
               $kriter .= $cdb->field_query($kriter, "DEPTS.DEPT_ID", "=", "'$DEPT_ID'");
      
         $sql_str  = "SELECT USERS.USER_ID, USERS.NAME, USERS.SURNAME, USERS.GSM, SITES.SITE_ID,
                             USERS.EMAIL, USERS.POSITION, DEPTS.DEPT_NAME, SITES.SITE_NAME
                       FROM USERS
                       LEFT JOIN DEPTS ON DEPTS.DEPT_ID = USERS.DEPT_ID
             LEFT JOIN SITES ON USERS.SITE_ID = SITES.SITE_ID 
                      ";
         
         if ($kriter != "")
               $sql_str .= " WHERE ". $kriter;  
       
         if ($ORDERBY) {
               $sql_str .= " ORDER BY ". $ORDERBY ;      
         }
         $rs = $cdb->get_Records($sql_str, $p, $page_size,  $pageCount, $recCount);    
         $stop = $cUtility->myMicrotime();

?>
<BR><BR>
<?
table_arama_header("93%");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bordercolor="60ADD1">
  <tr > 
    <td class="sonuc" HEIGHT="20" width="40%"  ><? echo $cdb->calc_current_page($p, $recCount, $page_size);?></td>
    <td class="sonuc" align="right" width="50%"><? $cdb->show_time(($stop -$start)); ?></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" ALIGN="left">
    <tr class="header_beyaz" >
        <td HEIGHT="20"><a href="javascript:submit_form('user_arama',1, 'USERS.USER_ID')">ID</a></td>
        <td><a href="javascript:submit_form('user_arama',1, 'SITES.SITE_NAME')">Site</a></td>
        <td><a href="javascript:submit_form('user_arama',1, 'USERS.NAME')">Adý</a></td>
        <td><a href="javascript:submit_form('user_arama',1, 'USERS.SURNAME')">Soyadý</a></td>
        <td><a href="javascript:submit_form('user_arama',1, 'USERS.GSM')">GSM</a></td>
        <td><a href="javascript:submit_form('user_arama',1, 'DEPTS.DEPT_NAME')">Departman</a></td>
        <td>Ýþlemler</td>
    </tr>
<? 
   $i;
   while($row = mysql_fetch_object($rs)){
         $i++;
         echo "<tr class=\"".bgc($i)."\">".CR;
         echo "  <td HEIGHT=20>$row->USER_ID</td> ".CR;
         echo "  <td>".substr($row->SITE_NAME,0,25)."</td>".CR;
         echo "  <td>".substr($row->NAME,0,15)."</td>".CR;
         echo "  <td>".substr($row->SURNAME,0,15)." </td>".CR;
     echo "  <td>$row->GSM</td>".CR;
         echo "  <td>".substr($row->DEPT_NAME,0,25)."</td>".CR;
         echo "  <td><a HREF=\"user.php?act=upd&id=$row->USER_ID\">Güncelle</a>";
         if(right_get("SITE_ADMIN") || right_get("ADMIN"))
              echo "<a HREF=\"user_db.php?act=del&id=$row->USER_ID&SITE_ID=$row->SITE_ID\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sil</a>";
         echo  "</td>".CR;
         echo "</tr>".CR;
       list_line(18);
   }
?>
</table>
<?
table_arama_footer();  

 }
?>
<table width="80%">
    <tr>
        <td align="center">
        <?echo $cdb->get_paging($pageCount, $p, "user_arama", $ORDERBY);?></td>
    </tr>
</table>

<?page_footer(0);?>
<script language="javascript" src="/scripts/form_validate.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
    FillSecondCombo('DEPT_ID', 'DEPT_NAME', '01SITE_ID='+ '<?=$SITE_ID?>' , '<?=$DEPT_ID?>' , 'DEPT_ID' , '<?= $DEPT_ID?>')
    function submit_form(form_name, page, sortby){
          document.all("ORDERBY").value = sortby;
          if (!sortby)
                document.all("ORDERBY").value = '';
          document.all("p").value = page;
          document.all(form_name).submit();
    }
//-->
</script>

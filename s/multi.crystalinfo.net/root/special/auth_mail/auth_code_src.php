<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
     require_valid_login();

  //Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
     if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Bu sayfaya eriþim hakkýnýz yok!");
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
     page_header();
     echo "<br><br>";
     table_header_mavi("Auth. Kod Arama","65%");
?>
<center>
       <form name="auth_code_src" method="post" action="auth_code_src.php?act=src">
         <input type="hidden" name="p" VALUE="<?echo $p?>">
         <input type="hidden" name="ORDERBY" value="<?=$ORDERBY?>">
         <table cellpadding="0" cellspacing="0" align="center" width="75%">
            <tr class="form">
                <td class="font_beyaz">Site Adý</td>
                <td>
                    <select name="SITE_ID" class="select1" style="width:200" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?>>>
                    <?
                        $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ";
                        echo $cUtility->FillComboValuesWSQL($conn, $strSQL, true, $SITE_ID);
                    ?>
                    </select>
                </td>
            </tr>
            <tr> 
                <td width="30%" class="font_beyaz">Auth. Kodu</td>
                <td width="70%"><input type="text" class="input1" size="10" name="AUTH_CODE" VALUE="<?echo $AUTH_CODE?>" Maxlength="15"></td>
            </tr>
            <tr> 
                <td width="30%" class="font_beyaz">Açýklama</td>
                <td width="70%"><input type="text" class="input1" name="AUTH_CODE_DESC" VALUE="<?echo $AUTH_CODE_DESC?>" Maxlength="30"></td>
            </tr>
            <tr>
                <td colspan=2 align=center><br>
                    <a href="javascript:submit_form('auth_code_src');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>ara.gif"></a>
                </td>
            </tr>
        </table>
       </form>
    <table width="100%"> 
      <tr>
        <td width="100%" align="right">
        <a href="auth_code.php?act=new"><img border="0" src="<?=IMAGE_ROOT?>yeni_kayit1.gif" style="cursor:hand;"></a>
        </td>
      </tr>  
    </table>
<?
   table_footer_mavi();
   if ($act == "src") {
         $kriter = "";   

         if ($SITE_ID<>'-1'){
             $kriter .= $cdb->field_query($kriter, "SITES.SITE_ID",       "=",    "'$SITE_ID'");
         }
         $kriter .= $cdb->field_query($kriter, "AUTH_CODE",     "=", "'$AUTH_CODE'");
         $kriter .= $cdb->field_query($kriter, "AUTH_CODE_DESC", "LIKE", "'%$AUTH_CODE_DESC%'");
      
         $sql_str  = "SELECT AUTH_CODES.AUTH_CODE_ID,AUTH_CODES.AUTH_CODE,AUTH_CODES.AUTH_CODE_DESC,
                        SITES.SITE_NAME
                      FROM AUTH_CODES
                      INNER JOIN SITES ON AUTH_CODES.SITE_ID = SITES.SITE_ID
                      ";
         
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
table_arama_header("75%");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td class="sonuc" width="50%" height="20"><? echo $cdb->calc_current_page($p, $recCount, $page_size);?></td>
    <td class="sonuc" align="right" width="50%" height="20"><? $cdb->show_time(($stop -$start)); ?></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="header_beyaz">
        <td><a href="javascript:submit_form('auth_code_src',1, 'AUTH_CODES.AUTH_CODE_ID')">ID</a></td>
        <td><a href="javascript:submit_form('auth_code_src',1, 'SITES.SITE_NAME')">Site Adý</a></td>        
        <td><a href="javascript:submit_form('auth_code_src',1, 'AUTH_CODES.AUTH_CODE')">Auth.Kodu</a></td>
        <td><a href="javascript:submit_form('auth_code_src',1, 'AUTH_CODES.AUTH_CODE_DESC')">Açýklama</a></td>
        <td>Güncelle</td>
    </tr>
<? 
   $i;
   while($row = mysql_fetch_object($rs)){
      $i++;
    echo " <tr class=\"".bgc($i)."\">".CR;
        echo " <td height=\"20\">$row->AUTH_CODE_ID</td> ".CR;
         echo " <td>$row->SITE_NAME</td>".CR;
         echo " <td>$row->AUTH_CODE</td>".CR;
         echo " <td>".substr($row->AUTH_CODE_DESC,0,30)."</td>".CR;
         echo " <td><a HREF=\"auth_code.php?act=upd&id=$row->AUTH_CODE_ID\">Güncelle</td>".CR;
        echo " </tr>".CR;
       list_line(18);
   }
?>
</table>
<?table_arama_footer();   }?>
<table width="80%" align="center">
    <tr>
        <td align="center">
        <?
        echo $cdb->get_paging($pageCount, $p, "auth_code_src", $ORDERBY);
        ?></td>
    </tr>
</table>
<?page_footer(0);?>

<script language="javascript" src="/scripts/form_validate.js"></script>
<script language="JavaScript" type="text/javascript">
<!--

    function submit_form(form_name, page, sortby){
          document.all("ORDERBY").value = sortby;
          if (!sortby)
                document.all("ORDERBY").value = '';

          document.all("p").value = page;
          document.all(form_name).submit();
    }
//-->
</script>
</html>


<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   require_valid_login();
   $conn = $cdb->getConnection();

   //Site Admin veya Admin Hakký yoksa bu tanýmý yapamamalý
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Burayý Görme Hakkýnýz Yok");
    exit;
   }

?><body bgcolor="F7FBFF"><?

   // EKLEME >>
   if ($ekle && $id && $eklenen)
   {
      mysql_query("INSERT INTO USERS_RIGHTS (USER_ID, RIGHT_ID) VALUES ('$id', '$eklenen')");
   }
   // CIKARMA <<
   if ($cikar && $id && $cikarilan)
   {
      mysql_query("DELETE FROM USERS_RIGHTS WHERE RIGHT_ID = $cikarilan AND USER_ID = $id");
   }
   
  $cell_header_left = "<font face=Verdana size=1>Potansiyel Haklar</font>";
  $cell_header_right = "<font face=Verdana size=1>Sahip Olunan Haklar</font>";
  cc_page_meta(0);
?>

<script language="javascript">
  function check_transfer(obj){
    if (obj.selectedIndex == -1) { return false; }
    return true;
  }
</script>

<form name="user_transfer" method="post" action="user_rights.php?act=<? echo $HTTP_GET_VARS['act'] ?>">
<input type="hidden" name="id" value="<?=$id?>">
<table width=100% height=100% class="formbgx" bgcolor="F7FBFF">
<tr><td width=180 class=generic><?=$cell_header_left?></td>
<td width=50>&nbsp;</td>
<td width=180><?=$cell_header_right?></td>
</tr>
<tr>
<td width=180 valign=top>
<select name=eklenen size="6" style="width=120" class="select1">
         <?
         $strSQL = "SELECT RIGHT_ID FROM USERS_RIGHTS WHERE USER_ID = $id";
         $cdb->execute_sql($strSQL, $arg_result, $arg_error_msg);
         $query = "";
         while ($row = mysql_fetch_object($arg_result))
         {
          $query .= '\''.$row->RIGHT_ID.'\',';
         }
         if ($query) { $query = "WHERE ID NOT IN (".substr($query,0,-1).")"; }
         else { $query = "WHERE 1=1"; }
     if (right_get("SITE_ADMIN"))
           $strSQL = "SELECT RIGHTS.ID, RIGHTS.RIGHT_DEF FROM RIGHTS $query";
     else 
       $strSQL = "SELECT RIGHTS.ID, RIGHTS.RIGHT_DEF FROM RIGHTS $query AND RIGHT_NAME<>'SITE_ADMIN'";
         echo    $cUtility->FillComboValuesWSQL($conn, $strSQL, false, "");
          ?>
</select>
</td>
<td width=50 valign=top>
<input id=ekle type="submit" name="ekle" value=">>" onclick="javascript:return check_transfer(document.all('eklenen'));"><p>
<input id=cikar type="submit" name="cikar" value="<<" onclick="javascript:return check_transfer(document.all('cikarilan'));">
</td>
<td width=180 valign=top>
<select name=cikarilan size="6" style="width=120" class="select1">
         <?
         $strSQL = " SELECT DISTINCT RIGHTS.ID, RIGHTS.RIGHT_DEF FROM RIGHTS ". 
                   " LEFT JOIN USERS_RIGHTS ". 
                   " ON RIGHTS.ID = USERS_RIGHTS.RIGHT_ID ".
                   " WHERE USERS_RIGHTS.USER_ID = $id";
         echo    $cUtility->FillComboValuesWSQL($conn, $strSQL, false, "");
         ?>
</select>

</td>
</tr>
</table>
</form>



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

   //Kullanýcýnýn site id si bulunmalý
   $sql_str = "SELECT SITE_ID FROM USERS WHERE USER_ID = $id";
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
          print_error($error_msg);
          exit;
  }
  if (mysql_numrows($result)>0){
    $row = mysql_fetch_object($result);
    $site_crt = " AND SITE_ID=".$row->SITE_ID;
    $my_site_id = $row->SITE_ID;
  }else{
     print_error("Lütfen kullanýcýnýn baðlý olduðu Site'yi seçiniz.");
       exit;    
  }
   
?><body bgcolor="F7FBFF"><?   

  $cell_header_left = "<font face=Verdana size=1>Raporunu Alabileceði Departmanlar</font>";
  $cell_header_right = "<font face=Verdana size=1>Raporunu Aldýðý Departmanlar</font>";
  cc_page_meta(0);
?>

<script language="javascript">
  function check_transfer(obj){
    if (obj.selectedIndex == -1) { return false; }
    return true;
  }
</script>

<form name="user_transfer" method="post" action="dept_rep_rights.php?act=<? echo $HTTP_GET_VARS['act'] ?>">
<input type="hidden" name="id" value="<?=$id?>">
<table width=100% height=100% class="formbgx" bgcolor="F7FBFF" border="0">
    <tr><td width=260><?=$cell_header_right?></td></tr>
    <tr>
        <td width=260 valign=top>
            <select name=cikarilan size="6" style="width=270" class="select1">
                 <?
                 $strSQL = " SELECT DISTINCT DEPTS.DEPT_ID, DEPTS.DEPT_NAME FROM DEPTS ". 
                           " LEFT JOIN DEPT_REP_RIGHTS ". 
                           " ON DEPTS.DEPT_ID = DEPT_REP_RIGHTS.DEPT_ID ".
                           " WHERE DEPT_REP_RIGHTS.USER_ID = $id ORDER BY DEPT_ID";
                 echo    $cUtility->FillComboValuesWSQL($conn, $strSQL, false, "");
                 ?>
            </select><br>
            <input type="button" value="Düzenle" onclick="javascript:popup('select_dept.php?USER_ID=<?=$id?>','user',800,600);">
        </td>
    </tr>
</table>
</form>



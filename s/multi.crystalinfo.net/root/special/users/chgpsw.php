<?  //INCLUDES
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  
   require_valid_login();

   $cdb = new db_layer();
   $conn = $cdb->getConnection();

  if (isset($pass1) or isset($pass2)){
    if ( ($pass1 == $pass2) and ($pass2 <> "") and (strlen($pass2) >= $MAX_PASS_LEN) ) {
        $sql_str = "UPDATE USERS SET PASSWORD = PASSWORD('$pass1') WHERE USER_ID ='$id' ;";

        if (!($cdb->execute_sql($sql_str,$haber_result,$error_msg))){ 
          print_error($error_msg);
          exit;
        }
      
       // logout the user for re-login
      //header("Location:/logout.php");
      echo "�ifreniz Ba�ar�yla De�i�tirildi, Tekrar Login Olabilirsiniz";
    
    } // There was a problem with the passwords
    else {
      print_error("�ifre giri� kurallar�na uygun giri� yapmad�n�z.".NL.
                "L�tfen �ifre giri� ekran�nda belirtilen kurallara uyunuz...");
      exit;
    }  
        
  
  }
  else{

   echo "<BR><BR><BR>";
   cc_page_meta(0);  

    if (empty($id) || $id==""){
      print_error("Hatal� Durum Olu�tu!");
      exit;
    }  

  table_header("�ifre De�i�ikli�i","100%");
?>

<form name="login" action="chgpsw.php" method="post">
<div align="center">  
<table class="menu" border="0" width="21%" height="123" cellspacing="0" cellpadding="0">
  <tr>
    <td class="subheader" width="13%" nowrap align="left" height="23">Yeni �ifre: </td>
    <td width="87%" nowrap height="23">
<input class ="input1" type="password" name="pass1" maxlength="15" size="20" value="">
    </td>
  </tr>
  <tr>
    <td class="subheader" width="13%" nowrap align="left" height="23">Yeni �ifre Tekrar: </font></td>
    <td width="87%" nowrap height="23">
<input class ="input1" type="password" name="pass2" maxlength="32" size="20">
<input type="hidden" name="id" value="<?=$id?>">
    </td>
  </tr>
  <tr>
    <td width="100%" nowrap colspan="2" height="59">
      <p align="center">
         <br>
         <input class="button1" type="submit" value="De�i�tir" name="B1">

          
    </td>
  </tr>
</table>
         <HR>
<table CLASS=MENU>
  <tr>
    <td>
    <p align="center">
         <h2>Kurallar</h2>
        * Her iki �ifreniz de <font color="#FF0000">ayn�</font> olmal�d�r<br>
        * Uzunlu�u en az <font color="#FF0000"><? echo $MAX_PASS_LEN; ?>&nbsp;</font>karakter&nbsp; olmal�d�r<br>
        * <font color="#FF0000">Bo�luk</font> i�ermemelidir<br>
        * <font color="#FF0000">*()/&amp;%+^'!</font> gibi karakterler <font color="#FF0000">olmamal�d�r</font></p>      </p>
    </td>
  </tr>
</table>

</div>
</form>

<?
  }
  
  table_footer();

?>
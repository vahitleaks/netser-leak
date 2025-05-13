<?php
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cDB = new db_layer();

   function login1(){
     global $my_s;
     global $user_name;
     global $user_pass;
     global $customer_code;
     global $REMOTE_ADDR;
     global $user_mobil;
     $my_s = new Session;
     $my_s->login_request($user_name,$user_pass,$customer_code,$REMOTE_ADDR,$user_mobil);
     if (!$my_s->logged_in){
       header("Location:/special/login_frm.php?act=retry");
       print_error($my_s->err_message());
       exit;
     }
   }
   
   function logout1($closed){
     global $my_s;
     $my_s = new Session;
     $my_s->test_login();
     if ($my_s->logged_in){
       //// Eðer database iþlemleri yapýlacaksa connect yapabiliriz
       $my_s->destroy();
       if ($closed) {
          put_header("Logout","/body1.css");
          echo "<p align=\"center\" class=\"important\">Sistemi kullandýðýnýz için teþekkürler.Logout oldunuz.</p>";
          echo "<p align=\"center\"><a href=\"javascript:close();\">KAPAT</a></p><br>";
       }else{
         header("Location:login_frm.php");
       }
     }else{  // Not logged in yet
       if ($closed) {
         put_header("Logout","/body1.css");
         echo "<p align=\"center\" class=\"important\">Sistemi kullandýðýnýz için teþekkürler.Logout oldunuz.</p>";
         echo "<p align=\"center\"><a href=\"javascript:close();\">KAPAT</a></p><br>";
       }else
         header("Location:login_frm.php");
     }
   }

   if (isset($SESSION["user_id"]))  // Eðer login durumda ise logout olsun
      logout1(0);  // First logout...
  login1();
  right_set($SESSION["user_id"]);
  header("Location:outbound/report_outb.php");
?>

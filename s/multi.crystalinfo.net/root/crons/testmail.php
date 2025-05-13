<?php
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
  include("mail_send.php");
  mail_send('skaya@vodasoft.com.tr',"CrystalInfo Sistemi Özet Raporu.-Test","Deneme");
?>
 

<? 
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   require_valid_login();
   Header("Content-Type: application/x-octet-stream"); 
   // you can put the header file-length here but I never use it! 
   Header( "Content-Disposition: attachment; filename=$filename"); 
   $strdir  = dirname($DOCUMENT_ROOT)."/root/temp/".$filename;
   $fp = fopen($strdir,"r"); 
   $buff = fread($fp,100000000); 
   echo $buff; 
   die;
?>



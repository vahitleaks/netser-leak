<?
$ftp_server = "10.16.76.19";
$ftp_user_name = "root";
$ftp_user_pass = "123456";
$local_root = "/usr/local/sigma/crystal/tini/data/";
$conn_id = ftp_connect($ftp_server); 

// login with username and password
 $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 

//ftp time out saniye cinsinden
ftp_set_option($conn_id, FTP_TIMEOUT_SEC, 60); 
//tini datalarýnýn olduðu klasör
ftp_chdir($conn_id, "/data");

//data klasörünün içeriðini listeler
$dir=ftp_pwd($conn_id); 

$list=Array(); 
$list=ftp_nlist($conn_id, "$dir");
$i=0;
while($list[$i]) { 
   $file_name =  $list[$i]; $i++;
  // echo $file_name ."\n";
   //dosyalarý tek tek lokal'e kaydeder
   if(ftp_get($conn_id, "$local_root$file_name", "$file_name", FTP_ASCII)){
      if(!ftp_delete($conn_id, "$file_name")){ //ftp den silemez isek local den sil.
         //Dosya Lokale indirildi ancak bufferdan silinemedi. O zaman Local den sil.
         unlink ("$file_name");
      }
   }



if (file_exists($local_root.$file_name)) {
// Connecting, selecting database
   $link = mysql_connect('localhost', 'crinfo', 'SiGmA*19') or die('Could not connect: ' . mysql_error());
   
   mysql_select_db('MCRYSTALINFONE') or die('Could not select database');

$handle = fopen($local_root.$file_name, "a+");
@mkdir("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/");

// $handle = fopen($local_root.$file_name, "a+");
$handle2= fopen("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/20raw_".date("m")."_".date("Y").".dat","a+");


if ($handle) {
    while (!feof($handle)) {
    
        $buffer = fgets($handle, 256);


//$buffer = str_replace (chr(10),"", $buffer);
//$buffer = str_replace (chr(13),"", $buffer);
$buffer = str_replace (chr(0),"", $buffer);
	
//        echo $buffer;
//    if(substr($buffer, 14,1) == chr(32) && substr($buffer, 1,1) != chr(32)){


//    $buffer =  substr_replace($buffer,"",14,1);
//    for ($ii = 0; $ii <= strlen($buffer); $ii++) {
//    if(substr($buffer, $ii,1)==chr(13) || substr($buffer, $ii,1)==chr(10)){
//        $buffer =  substr_replace($buffer,"",$ii,1);
//	echo "asdasd";
//    }
//     }
//         }	    



	fwrite($handle2, $buffer);    
        if(strlen($buffer)>2){
           $sqlQuery =  "\n INSERT INTO RAW_DATA(DATA, DATE, SOURCE, SITE_ID,ERROR_CODE) VALUES ('".$buffer."',CURDATE(),'buffer',20, '0')";
   	   //echo $sqlQuery;
           mysql_query($sqlQuery,$link);
	}
   }
 
 } 
 unlink ($local_root.$file_name);
 fclose($handle);

}

} //while of dir
?>
<?
/*
Dikkat edilecek hususlar;
1- burada geçen root ve directory lerin sistemde olmasý gerekli.
2- DB adý, þifresi ve user name düzeltilmeli
3- DB name düzeltilmeli
4- bu klasörlerin yazma haklarý kontrol edilmeli.
5- RAW_DATA ile atýlan bir dosyanýn içeriðikarþýlaþtýrýlmalý, 
doðru bir aktarým yapýlýyor mu diye.

*/
date_default_timezone_set("Europe/Istanbul");


		$local_root = "/home/cisco/CDR/";  //BUFFER IN DOSYALARI KOYDUÐU YER
		$list=Array(); 
		$list=read_dir($local_root);
		$i=0;
		$site_id= "1";

while($list[$i]) { 
          $file_name =  $list[$i]; $i++;
          echo $file_name ."\n <br>";
          if (file_exists($local_root.$file_name)) {
                    // Connecting, selecting database
                    $link = mysql_connect('localhost', 'crinfo', 'SiGmA*19') or die('Could not connect: ' . mysql_error());
                    //SELECT DB
                    mysql_select_db('MCRYSTALINFONE') or die('Could not select database');
                    //OPEN BUFFER FILE
                //    $handle = fopen($local_root.$file_name, "r");
                $handle = fopen($local_root.$file_name, "a+");
                @mkdir("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/");
                 // BU DOSYAYI NORMAL OKUNAN BIR DOSYA GIBI SISTEME KAYDET
                    $handle2= fopen("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/".$site_id."cisco_raw_".date("m")."_".date("Y").".dat","a+");
             
  

 if ($handle) {
                     $mysayac=1;
        while (!feof($handle)) {
    $buffer = fgets($handle, 4096); //DOSYA OKUNUYOR
                                        
$buffer = str_replace ("\"","", $buffer);

$bufarray= split(",", $buffer);



$buffernew = "";

if (is_numeric ( $bufarray[2])){
$buffernew = $bufarray[2].str_repeat(" ", 10- strlen($bufarray[2]));}                                                //Sýra No - globalCallID_callId
else{
$buffernew ="callId    ";
}

if (is_numeric ( $bufarray[4])){
$buffernew .= date("Y-m-d H:i:s", $bufarray[4]).str_repeat(" ", 21-strlen( gmdate("Y-m-d H:i:s", $bufarray[4]))); //Tarih Saat Linux time - dateTimeOrigination
}else{
$buffernew .= "dateTimeOrigination  ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[8].str_repeat(" ", 18- strlen($bufarray[8])); 						    //Arayan Numara - callingPartyNumber
}else{
$buffernew .= "callingPartyNum   ";
}

if (is_numeric ( $bufarray[55])){
$buffernew .= gmdate('H:i:s', $bufarray[55]).str_repeat(" ", 10-strlen( gmdate('H:i:s', $bufarray[55])));	    //süre saniye -duration
}else{
$buffernew .= "Duration  ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[29].str_repeat(" ", 18- strlen($bufarray[29])); 					 	    //Aranan Numara - originalCalledPartyNumber
}else{
$buffernew .= "origCalledPNum    ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[30].str_repeat(" ", 18- strlen($bufarray[30])); 					 	    //finalCalledPartyNumber
}else{
$buffernew .= "finalCalledPNum   ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[49].str_repeat(" ", 18- strlen($bufarray[49])); 					           // -lastRedirectDn
}else{
$buffernew .= "lastRedirectDn    ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[51].str_repeat(" ", 18- strlen($bufarray[51])); 						    //Arayan parity - originalCalledPartyNumberPartition
}else{
$buffernew .= "origCalledPNumPn  ";
}

if (is_numeric ( $bufarray[94])){
$buffernew .= $bufarray[94].str_repeat(" ", 5- strlen($bufarray[94])); 						    //-IncomingProtocolID
}else{
$buffernew .= "I_ID ";
}

if (is_numeric ( $bufarray[96])){
$buffernew .= $bufarray[96].str_repeat(" ", 5- strlen($bufarray[96])); 						    //-OutgoingProtocolID
}else{
$buffernew .= "O_ID ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[65].str_repeat(" ", 20- strlen($bufarray[65]));						    //site - globalCallId_ClusterID
}else{
$buffernew .= "globCallId_ClustID  ";
}

if (is_numeric ( $bufarray[2])){
$buffernew .= $bufarray[56].str_repeat(" ", 20- strlen($bufarray[56])); 						    // - origDeviceName
}else{
$buffernew .= "origDeviceName      ";
}

if (is_numeric ( $bufarray[2])){	
$buffernew .= $bufarray[57].str_repeat(" ", 20- strlen($bufarray[57])); 						    // - destDeviceName
}else{
$buffernew .= "destDeviceName      ";
}
	

$buffer = $buffernew."\n";


echo gmdate('H:i:s', $your_time_in_seconds);


                      fwrite($handle2, $buffer);     //DOSYA DÝÐER DOSYAYA YAZILIYOR.
 	
			 if(strlen($buffer)>2){
                                                  //DB YE ÝNSET EDÝLÝYOR.
                                                 $sqlQuery =  "\n INSERT INTO SEMI_FINISHED(LINE1_ID, LINE1, DATE_TIME, YEAR, MONTH, DAY, SITE_ID, DONE) VALUES (".$mysayac.", '".$buffer."',CURDATE(), YEAR(CURDATE()),MONTH(CURDATE()), DAYOFMONTH(CURDATE()),'".$site_id."', '0')"; 
                                                 $mysayac=$mysayac+1;
                                                 mysql_query($sqlQuery,$link);


                                        }

                              }

	
                              fclose($handle);
                    }

          }
          unlink ($local_root.$file_name); // ESKÝ DOSYA SÝSTEMDEN SÝLÝNÝYOR.
} //while of dir
fclose($handle2);


function read_dir($dir) {
     if ($handle = opendir($dir)) {
          /* This is the correct way to loop over the directory. */
          while (false !== ($file = readdir($handle))) {
               //if ($file != "." && $file != "..")  $array[] = $file;
              if(substr($file,0, 9)=="cdr_Stand") $array[] = $file; // Halil  - ana dosyayý silmemesi icin yapýldý 
          }
          closedir($handle);
     }
     return $array;
}

?>

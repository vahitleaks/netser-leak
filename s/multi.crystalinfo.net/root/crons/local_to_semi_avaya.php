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

$local_root = "/usr/local/sigma/crystal/avaya/";  //BUFFER IN DOSYALARI KOYDUÐU YER

$list=Array(); 
$list=read_dir($local_root);
$i=0;
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
                    $handle2= fopen("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/raw_".date("m")."_".date("Y").".dat","a+");
                    if ($handle) {
                     $mysayac=1;
                              while (!feof($handle)) {
                                        $buffer = fgets($handle, 256); //DOSYA OKUNUYOR
                                        
                                        //$buffer = str_replace (chr(10),"", $buffer);
//$buffer = str_replace (chr(13),"", $buffer);
$buffer = str_replace ("\"","", $buffer);
$bufarray= split(",", $buffer);
$buffernew = "";
for($say=0;$say<sizeof($bufarray);$say++){
  if($say!=2 && $say!=8 && $say!=10 && $say!=12 && $say<14){
    if($say==0 || $say==3 || $say==5 || $say==6)
      $maxLen = 20;
    else if($say==11 || $say==13)
      $maxLen = 6;
    else if($say==4)
      $maxLen = 2;
    else
      $maxLen = 10;
    $len = strlen($bufarray[$say]);
    if($len<$maxLen){
      $buffernew.=$bufarray[$say].str_repeat(" ", $maxLen-$len);
    }else{
      $buffernew.=$bufarray[$say];
    }
  }
}	
$buffer = $buffernew."\n";
//        echo $buffer;
//    if(substr($buffer, 14,1) == chr(32) && substr($buffer, 1,1) != chr(32)){


//$buffer =  substr_replace($buffer,"	",32,1);
//    for ($ii = 0; $ii <= strlen($buffer); $ii++) {
//    if(substr($buffer, $ii,1)==chr(13) || substr($buffer, $ii,1)==chr(10)){
//        $buffer =  substr_replace($buffer,"",$ii,1);
//	echo "asdasd";
//    }
//     }
//         }	    
                                  
                                        fwrite($handle2, $buffer);     //DOSYA DÝÐER DOSYAYA YAZILIYOR.
                                        if(strlen($buffer)>2){
                                                  //DB YE ÝNSET EDÝLÝYOR.
                                                  $sqlQuery =  "\n INSERT INTO SEMI_FINISHED(LINE1_ID, LINE1, DATE_TIME, YEAR, MONTH, DAY, SITE_ID, DONE) VALUES (".$mysayac.", '".$buffer."',CURDATE(), YEAR(CURDATE()),MONTH(CURDATE()), DAYOFMONTH(CURDATE()), 1, '0')"; 
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
              if(substr($file,0, 9)=="avayadata") $array[] = $file; // Halil  - ana dosyayý silmemesi icin yapýldý 
          }
          closedir($handle);
     }
     return $array;
}

?>

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
$local_root = "/home/soganlik/";  //BUFFER IN DOSYALARI KOYDUÐU YER

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
                    $handle = fopen($local_root.$file_name, "r");
                    // BU DOSYAYI NORMAL OKUNAN BÝR DOSYA GÝBÝ SÝSTEME KALDET
                    $handle2= fopen("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/2raw_".date("m")."_".date("Y").".dat","a+");
                    if ($handle) {
                              while (!feof($handle)) {
                                        $buffer = fgets($handle, 256); //DOSYA OKUNUYOR
                                        fwrite($handle2, $buffer);     //DOSYA DÝÐER DOSYAYA YAZILIYOR.
                                        if(strlen($buffer)>2){
                                                  //DB YE ÝNSET EDÝLÝYOR.
                                                  $sqlQuery =  "\n INSERT INTO RAW_DATA(DATA, DATE, SOURCE, SITE_ID,ERROR_CODE) VALUES ('".$buffer."',CURDATE(),'buffer',2, '0')"; 
                                                  mysql_query($sqlQuery,$link);
                                         }
                              }
                              fclose($handle);
                    } 
          }
          unlink ($local_root.$file_name); // ESK] DOSYA S]STEMDEN S]L]N]YOR.
} //while of dir
fclose($handle2);


function read_dir($dir) {
    global $local_root;
     if ($handle = opendir($dir)) {
          /* This is the correct way to loop over the directory. */ 
          while (false !== ($file = readdir($handle))) {
               
               if ($file != "." && $file != "..")  {
                  if(substr($file,0, 8)=="Record.2"){ // uygun dosya 
                    $array[] = $file;
                  }else{ //yanlýþ dosyalarý sil
                    unlink ($local_root.$file);   
                  }                
               }
          }
          closedir($handle);
     }
     return $array;
}

?> 
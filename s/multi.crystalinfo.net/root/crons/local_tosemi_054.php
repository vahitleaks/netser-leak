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
$local_root = "/home/emeklisandigi/";  //BUFFER IN DOSYALARI KOYDUÐU YER
$SITEID = 54;
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
                    $handle2= fopen("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/".$SITEID."raw_".date("m")."_".date("Y").".dat","a+");
                    if ($handle) {
                               $row_data = "";
                              while (!feof($handle)) {
                                        $buffer = fgets($handle, 256); //DOSYA OKUNUYOR
                                        //fwrite($handle2, $buffer);     //DOSYA DÝÐER DOSYAYA YAZILIYOR.
                                        $buffer =trim($buffer);
                                        if(substr($buffer,0,8)=="--------" && $row_data == ""){
                                          $row_data = $buffer." ";
                                        }else if(substr($buffer,0,8)!="--------" && $row_data != "" && $buffer!=""){
                                          if(substr($buffer,0,13)=="DIGITS DIALED"){
                                            $buffer=$buffer."#";
                                          }
                                          if(substr($buffer,0,14)=="CALLING NUMBER"){
                                            $buffer=$buffer."#";
                                          }
                                          $row_data .= $buffer." ";
                                        }
                                        if(substr($buffer,11,13)=="CALL RELEASED"){
                                          if(strlen($row_data)>2){
                                                  //DB YE ÝNSERT EDÝLÝYOR.
                                                  
                                                  /*$sqlQuery =  "\n INSERT INTO RAW_DATA(DATA, DATE, SOURCE, SITE_ID,ERROR_CODE) VALUES ('".$buffer."',CURDATE(),'buffer',11, '0')"; 
                                                  mysql_query($sqlQuery,$link);*/
                                                  shape_data($row_data,1, $SITEID);
                                          }
                                          $row_data = "";
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







     function shape_data($data="",$ID="", $SITE_ID){
       global $handle2;
  //   echo $data."<br>";
         $data = str_replace(chr(10)," ", $data);
         $data = str_replace(chr(13)," ", $data);
     
         $call_array = split("--------",$data);
         

         
         foreach ($call_array as $key => $value) {
					  if($value!="") {
            
                  $trans = 0;
                  $fr_trans = 0;
                  $call_type = 0;
                  $dot_line = 1;
              
		  $pDATE            = 0;
		  $pTIME            = 0;
		  $pTRUNK           = 0;
                  $pEXTENSION       = 0;
		  $pPHONE_NUMBER    = 0;
		  $pPHONE_NUMBER_END= 0;
		  $pDURATION        = 0;
		  $txtDATE            = "";
		  $txtTIME            = "";
		  $txtTRUNK           = "";
                  $txtEXTENSION       = "";
		  $txtPHONE_NUMBER    = "";
		  $txtPHONE_NUMBER_END= "";
		  $txtDURATION        = "";
									
                  $trans = strpos($value, "TRANSFERRED");
                  $fr_trans = @strpos($value, " FROM TRANSFER ");
                  
                  $pDATE = 3;
                  $pTIME = $dot_line +13;
                  $pTRUNK = strpos($value, " LINE ") + 8;
                  $pEXTENSION = strpos($value, " STN ") + 7;            

                  $pPHONE_NUMBER = strpos($data, " DIGITS DIALED ");
                  if(strpos($call_array[1],"CALLING NUMBER")){        
                    $pPHONE_NUMBER = strpos($data, " CALLING NUMBER ");
                  }
      
                  $pPHONE_NUMBER_END = strpos($data, "#", $pPHONE_NUMBER+15);
      
                  $pDURATION = strpos($value, " CALL RELEASED")-10;
                  
                  if($trans>1){
                        $pDURATION = strpos($value, " TRANSFERRED", $dot_line)-10;
                  }
                  if($pDURATION<1){
                        $pDURATION = 1;
                  }
      
                  $txtDATE = substr($value, $pDATE,8);
                  $txtTIME = substr($value, $pTIME,8);
		
                  $txtTRUNK = substr($value, $pTRUNK ,4);
                  $txtEXTENSION = substr($value, $pEXTENSION,4);
      
                  if($pPHONE_NUMBER_END<$pPHONE_NUMBER) 
                    $pPHONE_NUMBER_END=32;
		          else
                    $pPHONE_NUMBER_END=$pPHONE_NUMBER_END-$pPHONE_NUMBER-18;
                  
                  if($fr_trans <1){
                       $txtPHONE_NUMBER = substr($data, $pPHONE_NUMBER+18,12);
                  }
                  $txtPHONE_NUMBER = substr($data, $pPHONE_NUMBER+18, $pPHONE_NUMBER_END);
                  
                  if(substr($txtPHONE_NUMBER, 0, 1)=="9"){
                    $txtPHONE_NUMBER = substr($txtPHONE_NUMBER, 1, 32);
                  }
                  if(strlen($txtPHONE_NUMBER)<32){
                    $txtPHONE_NUMBER .= str_repeat(" ", 32-strlen($txtPHONE_NUMBER));
                  }  
                  
		  if($pPHONE_NUMBER==""){
		      $txtPHONE_NUMBER = "       ";
		  }
									
                  $txtDURATION = substr($value, $pDURATION,8);
		  

                  if(strpos($call_array[1]," OUTGOING ")){
                      $call_type = 1;            
                  }

                  if($trans >=1  && $fr_trans >=1 ){
                        $txtREC_TYPE = "X";
                  }else if(!is_numeric($trans) && !is_numeric($fr_trans)){
                        $txtREC_TYPE = "N";
                  }else if($trans >= 1 ){
                        $txtREC_TYPE = "S";
                  }else if($fr_trans >= 1 ){
                        $txtREC_TYPE = "E";
                  }
                  $txtSPACE = " ";
                  $txtTOTAL = "";
                  $txtTOTAL = $txtREC_TYPE .$txtSPACE 
                              .$txtDATE . $txtSPACE 
                              .$txtTIME . $txtSPACE 
                              .$txtTRUNK . $txtSPACE 
                              . str_pad($txtEXTENSION, 4 ," ") . $txtSPACE 
                              . str_pad($txtPHONE_NUMBER, 32, " ") . $txtSPACE 
                              .$txtDURATION . $txtSPACE 
                              . $call_type;
                              
//         echo   $txtTOTAL."******$ID<br>";
	 
                  insert_into_semifinished($txtTOTAL,$ID, $SITE_ID)          ;
                  fwrite($handle2, $txtTOTAL."\r\n");
      //            echo $txtPHONE_NUMBER;
             }
         } 
         
     
     } 
   
    
    function insert_into_semifinished($DATA,$ID, $SITE_ID){
         global $link;
         $dt = time();
         $y  = date('Y');
         $m  = date('m');
         $d  = date('d');
         $sql_str = "INSERT INTO SEMI_FINISHED(LINE1,LINE1_ID, DATE_TIME, YEAR,MONTH,DAY, SITE_ID) VALUES('$DATA', '$ID', $dt, '$y', '$m', '$d', '$SITE_ID')" ; 

         //echo $DATA."<br>";
         mysql_query($sql_str,$link);

		 
    }         


?>
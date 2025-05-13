<?php
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Project : Data Collection from LG Ericsson ipecs cm over IP and conversion of it  
// Coder : Seyit Kaya & Halil Mutlu
// Date  : 14.02.2011, a snowy day :) 
// 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Son versiyon : v 2.7
// 
// 16.02.2011  Tarih fark hesaplamasi duzeltildi   
// 28.02.2011  Arka planda php5 ile calisacak guncellemeler yapildi ve time zone bilgisi eklendi 
// 01.03.2011  ipecscmv22.php process durumunu kontrol edecek ipecschkps hazirlandi ve crontaba eklendi
// 01.03.2011  Incoming datalar icin CLID bilgisi ve gerekli tanimlamalari yapildi, format duzeltildi, auth cod bilgisi cikartildi, 9 acc cod replace edildi
// 02.03.2011  process ile santral arasindaki baglanti koptugunda, process calismasini surdurmekte iken tekrar baglanti kuracak cod eklendi
// 23.04.2011  Clid bilgisi ve metring pulse poziyona göre alýnmaya baþladý
// 24.04.2011  Problemli olan Tarih saat süre hesaplama fonksyonu date_diff2 iptal edildi farklý bir hesaplama sistemi uygulandý
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$local_root = "/usr/local/sigma/crystal/tini/data/";
	date_default_timezone_set("Europe/Istanbul");
	ob_start();
	ob_implicit_flush(true);
	set_time_limit(0);
	//general definitions
	$pbx_switch 	= "192.168.26.2";
	$pbx_port 		= "6017";
	$sleepSec 		= "2";
	$debug 			= "1";
	$mysql_host		= "localhost";
	$mysql_user		= "crinfo";
	$mysql_pass		= "SiGmA*19";
	$mysql_db		= "MCRYSTALINFONE";
	$site_id		= "1";
	collect_data($pbx_switch,$pbx_port,12000);

	ob_end_flush();
 
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Santralden data okuma isini gerceklestirir. 
//Her satirdan sonra santrale ACK gonderilir. 
//Eger santralde birikmis data varsa bazen 1 seferde 3-4 CDR kaydi gelmektedir. Bu da bir dongu ile cozuldu.
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function collect_data($pbx_switch,$pbx_port,$secs) {
	global $debug,$sleepSec;
    $secs = (int) $secs;
	//santrale baglanti acar
    $fp = fsockopen($pbx_switch, $pbx_port, $errno, $errstr, 5);
    $buffer = str_repeat(".", 4096);
	
	//belirlenen adet kadar santralden data alir
	for ($i=0; $i<$secs; $i++) {
		if($debug=="1") echo date("H:i:s", time())." (".($i+1).")"."\r\n\r\n".$buffer."<br />\r\n \r\n";
		
		if ($fp > 0) {
			$raw_data = fread($fp, 1024);
	//santral ile prograin baglantisi kesildiginde tekrar baglanti kuruyor process ayakta iken
		if($raw_data==false){
		return;
		}
		$str = (bin2hex($raw_data));
		if($debug=="1") {
			echo "String : ".$str;
			echo "<br> String Uzunlugu : ".strlen($str)."<br>";
			}
	//eger gonderilen data bos degilse isleme alinir
		if(strlen($str)>80){
			$one_record_pointer = 2*hexdec(substr($str,0,2));
			$one_record = substr($str,0,($one_record_pointer));
			format_rawData($one_record);
	//eger biriken data varsa bir satirda 10 kayit gonderecegi varsayilir.
	//eger ki cagri kaybi olusursa bu noktaya bakmak lazim. gerekirse bu sayi arttirilabilir
	for ($lc=0;$lc<10;$lc++){
 		if(strlen($str)>$one_record_pointer+15){
			$two_record_pointer = 2*hexdec(substr($str,$one_record_pointer,2));
			if($debug=="1") echo "<br>".strlen($str) . "????".$two_record_pointer;
				$two_record = substr($str,($one_record_pointer),($two_record_pointer));
				if($debug=="1") echo date("H:i:s", time())." (".($i+1).")"."\r\n\r\n.....................".$two_record .".................<br />\r\n \r\n";
					format_rawData($two_record);
					$one_record_pointer += $two_record_pointer;
					}
				}
		}
        
 	//santrale datayi aldim diye ACK ginderir
			$raw_data[11] = 1;
			fputs($fp, (substr($raw_data,0,11))); 
		}
		ob_flush();
		flush();
		sleep($sleepSec);
		}
		fclose($fp);
  }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Santralden HEX olarak gelen datayi anlasilir bir hale cevirir
//
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function format_rawData($strRawData){
	global $debug, $mysql_host, $mysql_user, $mysql_pass, $mysql_db,$site_id;
	$str = $strRawData;
	## HAM Data donusturme ve parcalama
	/*
	ipecs cm v2.2  ----------------   1 Local   2 Incoming
3f000500100000020111000d11a2000000000000ffff140b020c0d321b00140b020c0d323a006122a6800000000000000000000000000502010008020100ff
4d00d0001000000b010100091aaa0000000000000100140b0301112a0e00140b0301112a2600000000000000000000000000000000000502010008020100160ca2324586a6a0000000000000ff 
	ipecs cm v2.0
3e007600100000010100000d4aa500000000140b020900060200140b020900060e004aa2000000000000000000000000000005020100080201000d0102ff
$str = "3e007600100000010100000d4aa500000000140b020900060200140b020900060e004aa2000000000000000000000000000005020100080201000d0102ff";
/** CDR DATASININ DÜZENLENDÐÝ BÖLÜM*/
echo "<br>PMSType : ";		         echo $PMSType                    = str_replace('00','BillingMessage',
                                                                        str_replace('01','PMSMessage',
                                                                        substr($str,6,2)));

echo "<br>psNumber: ";			     echo $psNumber                   = str_pad(hexdec(substr($str,4,2)),4,"0",STR_PAD_LEFT);
                                                                
echo "<br>REC_TYPE: ";			     echo $REC_TYPE                   = substr($str,8,2); 	                                                                                                          

echo "<br>FeatureCode : ";		            echo $FeatureCode         = str_replace('10','CdrData',
                                                                        str_replace('20','CheckIn ',
                                                                        str_replace('21','CheckOut',
                                                                        str_replace('22','WakeUpRegistration',
										                                str_replace('23','WakeUpResult      ',
										                                str_replace('24','MaidStatus',
										                                str_replace('25','RoomCutOff ', 
                                                                        str_replace('26','MiniBar',
                                                                        str_replace('27','DND',
                                                                        str_replace('28','MessageWait',
                                                                        str_replace('29','RoomSwapping',
                                                                        substr($str,8,2))))))))))));

                                                
															
if($PMSType=="BillingMessage"){
		echo "<br>callType:";			    echo $callType            = str_replace('01','Dahili',
                                                                        str_replace('02','Local',
                                                                        str_replace('03','Local',
                                                                        str_replace('04','Local',
                                                                        str_replace('0b','Incoming',
                                                                        str_replace('0c','Local', 
                                                                        substr($str,14,2)))))));
		echo "<br>callStationCategory:";	echo $callStationCategory = substr($str,16,2);
		echo "<br>calledTrunkNumber:";		echo $calledTrunkNumber1  = (substr($str,18,2)); //little endian
		echo "<br>calledTrunkNumber:";		echo $calledTrunkNumber2  = (substr($str,20,2)); //little endian
		echo "<br>chargeInfo:";			    echo $chargeInfo          = hexdec(substr($str,22,2)); 
		echo "<br>callingDn:";			    echo $callingDn           = str_replace('a','0',str_replace('0', '',str_replace('f', '', 
                                                                        str_replace('0000000000','1aaa',
                                                                        substr($str,24,10))))); ///normalde 16 karakter goruluyor 
		echo "<br>callingTrunk:";		    echo $callingTrunk        = substr($str,40,2); //little endian
		echo "<br>startDateTime:";		    echo $startDateTime       = formatCmDate(substr($str,44,16)); 
		echo "<br>endDateTime:";		    echo $endDateTime         = formatCmDate(substr($str,60,16)); 
		echo "<br>";   
// date_diff2 yerine kullanýlan süre hesaplama sistemi.           
           $d1 = new DateTime($startDateTime);
           $d2 = new DateTime($endDateTime);
           $d  = $d1->diff($d2);
           print_r($d);
           
                                                 $suregun             = str_pad($d->format("%d"),2,"0",STR_PAD_LEFT);
                                                 $suresaat            = str_pad($d->format("%h"),2,"0",STR_PAD_LEFT);
                                                 $suredakika          = str_pad($d->format("%i"),2,"0",STR_PAD_LEFT);
                                                 $suresaniye          = str_pad($d->format("%s"),2,"0",STR_PAD_LEFT);
        echo "<br>Sure: ";                  echo $sure = $suregun." ".$suresaat.":".$suredakika.":".$suresaniye;


	if($callType=='Local'){
	
    if(strpos($str,"001704")==true){
                                            echo $authorizationPos	 = strpos($str,"001704") +6;
        echo "<br>authorizatonCode:";	    echo $authorizationCode	 = str_replace('a','0',str_replace('0','',(substr($str,$authorizationPos ,8))));
               }
	
  //aranan numara ve çýkýþ kodunun alýndýðý yer
        echo "<br>Digits :";	            echo $Digits              = substr($str,75,20);
        echo "<br>DigitsR:";		        echo $DigitsR             = substr_replace($Digits, 'a', 0, 1);
        echo "<br>dialedDigits:";		    echo $dialedDigits        = str_replace('a','0',
                                                                        str_replace('c','1',
                                                                        str_replace('0',' ',
                                                                        substr($DigitsR,0,20))));
 
   if(strpos($str,"001404")==true){     
        $MeteringPulse1pos	 = strpos($str,"001404") +6;
        $MeteringPulse2pos	 = strpos($str,"001404") +8;
        $MeteringPulse3pos	 = strpos($str,"001404") +10;
        $MeteringPulse4pos	 = strpos($str,"001404") +12;
         
        echo "<br>MeteringPulse:";		    echo $MeteringPulse1	 = (substr($str,$MeteringPulse1pos ,2)); //little endian
		echo "<br>MeteringPulse:";		    echo $MeteringPulse2	 = (substr($str,$MeteringPulse2pos ,2)); //little endian
		echo "<br>MeteringPulse:";		    echo $MeteringPulse3	 = (substr($str,$MeteringPulse3pos ,2)); //little endian
		echo "<br>MeteringPulse:";		    echo $MeteringPulse4	 = (substr($str,$MeteringPulse4pos ,2)); //little endian
        }
		  
           }
           
           
		if($callType=='Incoming'){
	    if(strpos($str,"0160c")==true){
		echo "<br>CLIDpos:";			    echo $CLIDpos		     = strpos($str,"0160c") +5;
 	    echo "<br>CLID:";			        echo $CLID		         = str_replace('a','0',
										                               str_replace('0','',
										                               substr($str,$CLIDpos,20))); 
      }elseif(strpos($str,"0160c")==false){
          $CLID = ('GizliNumara');
          } 
            }
	echo "<br>";
	
	//RAW_DATA da olacak alanlarin ve dolgu kogunun ayarlandigi yer
    $psNumber	 		          = str_pad($psNumber			      ,5," ",STR_PAD_RIGHT);
    @$callingDn 			      = str_pad($callingDn		          ,6," ",STR_PAD_RIGHT);
    @$calledTrunkNumber	 	      = hexdec($calledTrunkNumber2.$calledTrunkNumber1);   //little endian donusturmesi yapildi
    @$calledTrunkNumber 		  = str_pad($calledTrunkNumber        ,4,"0",STR_PAD_LEFT);
    @$calledTrunkNumber 		  = str_pad($calledTrunkNumber        ,6," ",STR_PAD_RIGHT);    
    @$sure   			          = str_pad($sure   			      ,9," ",STR_PAD_LEFT);
    @$startDateTime 		      = str_pad($startDateTime            ,25," ",STR_PAD_BOTH);
    @$callType 			          = str_pad($callType		          ,10," ",STR_PAD_RIGHT);
    @$dialedDigits 		          = str_pad($dialedDigits	          ,20," ",STR_PAD_RIGHT);
    @$CLID		     	          = str_pad($CLID.$authorizationCode  ,20," ",STR_PAD_RIGHT);
    @$MeteringPulse	 	          = hexdec($MeteringPulse4.$MeteringPulse3.$MeteringPulse2.$MeteringPulse1);   //little endian donusturmesi yapildi
    @$MeteringPulse 		      = str_pad($MeteringPulse            ,8,"0",STR_PAD_LEFT);
    @$MeteringPulse 		      = str_pad($MeteringPulse            ,10," ",STR_PAD_RIGHT);    


	//RAW_DATA nin olusturuldugu ve iceriginin belirlendigi yer
	$RAW_DATA = $psNumber.$callingDn.$calledTrunkNumber.$sure.$startDateTime.$callType.@$dialedDigits.@$CLID.@$MeteringPulse."\r\n" ;

	if($PMSType=="BillingMessage"){
		echo "<br>".$RAW_DATA  ;
	}
  
    	//Ham Datayi locale yazan kisim
	@mkdir("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/");
	$handle= fopen("/usr/local/sigma/crystal/data/".date("m")."_".date("Y")."/".$site_id."raw_".date("m")."_".date("Y").".dat","a+");
	fwrite($handle, $RAW_DATA);    
	fclose($handle);

	//Ham Datayi localde paylasim klasorune yazan kisim
//    	$handle= fopen("/usr/local/sigma/crystal/hotel/rawData/".$site_id."raw_data.dat","a+");
//	fwrite($handle, $RAW_DATA);    
//	fclose($handle); 

	//binary log  yazan kisim
    	@mkdir("/usr/local/sigma/crystal/logs/".date("m")."_".date("Y")."/");
    	$handle= fopen("/usr/local/sigma/crystal/logs/".date("m")."_".date("Y")."/".$site_id."cm_log_".date("m")."_".date("Y").".log","a+");
	fwrite($handle, $str."\r\n".$RAW_DATA."\r\n");    
	fclose($handle); 

	/* Ham datayi veritabanina yazan kisim*/
	$link = mysql_connect($mysql_host, $mysql_user, $mysql_pass) or die('Could not connect: ' . mysql_error());
	mysql_select_db($mysql_db) or die('Could not select database');
	if(strlen($RAW_DATA)>5){
		$sqlQuery =  "\n INSERT INTO RAW_DATA(DATA, DATE, SOURCE, SITE_ID,ERROR_CODE) VALUES ('".$RAW_DATA."',CURDATE(),'PHP Module','".$site_id."', '0')";
		if($debug=="1") echo "<br>".$sqlQuery;
		mysql_query($sqlQuery,$link);
	}
  }
}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Santralden HEX olarak gelen tarih datasini anlasilir bir hale cevirir
//## Tarih Saat formatinin alindigi yer
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function formatCmDate($strDate){
	$yil    = hexdec(substr($strDate,0,2));
	$yil2   = hexdec(substr($strDate,2,2));
	$ay 	= str_pad(hexdec(substr($strDate,4,2)),2,"0",STR_PAD_LEFT);
	$gun 	= str_pad(hexdec(substr($strDate,6,2)),2,"0",STR_PAD_LEFT);
	$saat 	= str_pad(hexdec(substr($strDate,8,2)),2,"0",STR_PAD_LEFT);
	$dakika = str_pad(hexdec(substr($strDate,10,2)),2,"0",STR_PAD_LEFT);
	$saniye = str_pad(hexdec(substr($strDate,12,2)),2,"0",STR_PAD_LEFT);

	return $yil.$yil2."-".$ay."-".$gun." ".$saat.":".$dakika.":".$saniye;
}


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Converts a hex value to string value
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function hextostr($x) {
  $s='';
  foreach(explode("\n",trim(chunk_split($x,2))) as $h) $s.=chr(hexdec($h));
  return($s);
}
?>

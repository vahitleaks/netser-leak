<?
/*$user = "uid=crystalinfo,ou=connectors,ou=People,c=tr,dc=com,o=disbank,o=gds";
echo $user;
echo "<br>";
echo myEncryption($user);
echo "<br>";
echo strlen(myEncryption($user));
echo "<br>";
echo myDecryption(myEncryption($user));
echo "<br>";
*/
function myEncryption($txtPassword){
    $k = -1;
    $vers = "FLDAP";
    for($i = 0;$i<strlen($txtPassword);$i++){
        $k++;
        if($k > strlen($vers)){
            $k = 0;
        }
        $CharVal1 = ord(substr($txtPassword, $i, 1));
        $CharVal2 = ord(substr($vers, $k, 1));
        $EN = dechex(($CharVal1 ^ $CharVal2));
        $EncryptedVal = $EncryptedVal.$EN;
        
    }
    return $EncryptedVal;
}


function myDecryption($s){

$k = -1;
$vers = "FLDAP";
$Start = 2;
//$s = substr($s, 6, 100);
//$s = Replace(s, " ", "")
for($j = 0;$j<strlen($s);$j=$j+2){
    $k++;
    if($k > strlen($vers)){
        $k = 0;
    }
    $StrVl = substr($s, $j, 2);
    $CharVal = ord(substr($vers, $k, 1));
    $StrVl = hexdec($StrVl);
    $DecryptedVal = ($StrVl ^ $CharVal);
    $DecryptedVal = Chr($DecryptedVal);
    $FinalVal = $FinalVal . $DecryptedVal;
}
return $FinalVal;
}

function utf2turk($data)
{
    $chars_utf = Array("Ã‡","Ã§", "Ã–", "Ã¶", "Ã¼", "Ãœ", "Ä°", "Ä±", "ÄŸ", "Äž", "ÅŸ", "Åž");
    //$chars_utf = Array(chr(195).chr(135),"Ã§", "Ã–", "Ã¶", "Ã¼", "Ãœ", "Ä°", "Ä±", "ÄŸ", "Äž", "ÅŸ", "Åž");
    $chars_trk = Array("Ç","ç", "Ö", "ö", "ü", "Ü", "Ý", "ý", "ð", "Ð", "þ", "Þ");

    $retVal="";
    if (!is_array($data))//parameter must be an array
    {
        for($k=0;$k<strlen($data);$k++)// loop through end of parameter 
        {
         if(ord(substr($data, $k, 1))<125){
           $tmpStr = substr($data, $k, 1);
         }else{
           $tmpStr = substr($data, $k, 2);
           $k++;
         }
          for($i=0;$i<12;$i++) // loop 12 times becauser there are 12 turkish chars to change
          {
            if($tmpStr ==$chars_utf[$i] ){$tmpStr=$chars_trk[$i];}
          }
          $retVal.=$tmpStr;
        }
        return $retVal;
    }
    
}
?>
<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
header("charset:windows-1254");
//cc_page_meta();
if($act=="src"){
// Designate a few variables
$host = "10.200.20.162";
//$host = "ldaptest";
$user = "uid=crystalinfo,ou=connectors,ou=People,c=tr,dc=com,o=disbank,o=gds";

$pswd = "voda1234";

$ad = ldap_connect($host, 3141)
      or die( "Could not connect!" );

// Set version number
ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3)
     or die ("Could not set ldap protocol");
//)//, $user, $pswd)
// Binding to ldap server
$bd = ldap_bind($ad,$user,$pswd)
      or die ("Could not bind");

// Create the DN
$dn = "ou=People,c=tr,dc=com,o=disbank,o=gds";

// Specify only those parameters we're interested in displaying
$attrs = array("displayname","mail","telephonenumber");

// Create the filter from the search parameters
$filtre = $_POST['filter']."=".$_POST['keyword']."*";

$search = ldap_search($ad, $dn, $filtre)
          or die ("ldap search failed");

$entries = ldap_get_entries($ad, $search);

if ($entries["count"] > 0) {
echo ord("z");
for ($i=0; $i<$entries["count"]; $i++) {

/* foreach ($entries[$i] as $key => $value) {
  echo "$key: ".utf2turkish($entries[$i][$key][0])."<br />\n";
 }
*/
   echo "<p>Name: ".utf2turk($entries[$i]["cn"][0])."<br />";
   echo "Extension: ".utf2turk($entries[$i]["extension"][0])."<br />";
   echo "Sicil: ".utf2turk($entries[$i]["disbanksicil"][0])."<br>";
   echo "Email: ".utf2turk($entries[$i]["mail"][0])."<br>";
   echo "fuidu: ".utf2turk($entries[$i]["fuidu"][0])."<br>";
   echo "fuiddesc: ".utf2turk($entries[$i]["fuiddesc"][0])."<br>";
   echo "fuidid: ".utf2turk($entries[$i]["fuidid"][0])."<br>";
   echo "managerfuidp: ".utf2turk($entries[$i]["managerfuidp"][0])."<br>";
   echo "location: ".utf2turk($entries[$i]["l"][0])."<br>";
   echo "<br>";
}

} else {
   echo "<p>No results found!</p>";
}

ldap_unbind($ad);
}
?>



<p>
  <form action="example.php?act=src" method="post">
    Search criteria:<br />
    <input type="text" name="keyword" size="20"
           maxlength="20" value="<?=$keyword?>" /><br />
    Filter:<br />
    <select name="filter">
        <option value="cn" <?if($filter=="cn") {echo " selected";}?>>Full Name</option>
        <option value="extension" <?if($filter=="extension") {echo " selected";}?>>Extension</option>
        <option value="disbanksicil" <?if($filter=="disbanksicil") {echo " selected";}?>>Sicil</option>
        <option value="l" <?if($filter=="l") {echo " selected";}?>>Location</option>
    </select><br />
    <input type="submit" value="Search!" />
  </form>
</p>
<?
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
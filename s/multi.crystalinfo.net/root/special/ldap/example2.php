<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");

cc_page_meta();
if($act=="src"){
// Designate a few variables
$host = "10.200.20.162";
//$host = "ldaptest";
$user = "uid=vodaconnector,ou=connectors,ou=people,c=tr,dc=com,o=disbank,o=gds";

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
$dn = "ou=people,c=tr,dc=com,o=disbank,o=gds";

// Specify only those parameters we're interested in displaying
$attrs = array("displayname","mail","telephonenumber");

// Create the filter from the search parameters
$filtre = $_POST['filter']."=".$_POST['keyword']."*";

$search = ldap_search($ad, $dn, $filtre)
          or die ("ldap search failed");

$entries = ldap_get_entries($ad, $search);

if ($entries["count"] > 0) {

for ($i=0; $i<$entries["count"]; $i++) {

/* foreach ($entries[$i] as $key => $value) {
  echo "$key: ".utf2turkish($entries[$i][$key][0])."<br />\n";
 }
*/
   echo "<p>Name: ".utf8_decode($entries[$i]["displayname"][0])."<br />";
   echo "Phone: ".$entries[$i]["telephonenumber"][0]."<br />";
   echo "Email: ".$entries[$i]["mail"][0]."<br>";
   echo "Fuid: ".$entries[$i]["fuidid"][0]."<br>";
   echo "Uid: ".$entries[$i]["uid"][0]."<br>";}

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
        <option value="displayname" <?if($filter=="displayname") {echo " selected";}?>>Name</option>
        <option value="sn" <?if($filter=="sn") {echo " selected";}?>>Last Name</option>
        <option value="telephonenumber" <?if($filter=="telephonenumber") {echo " selected";}?>>Phone</option>
        <option value="l" <?if($filter=="1") {echo " selected";}?>>City</option>
    </select><br />
    <input type="submit" value="Search!" />
  </form>
</p>
<?
function utf2turkish($data)
{
    $chars_utf = Array("Ã‡","Ã§", "Ã–", "Ã¶", "Ã¼", "Ãœ", "Ä°", "Ä±", "ÄŸ", "Äž", "ÅŸ", "Åž");
    $chars_trk = Array("Ç","ç", "Ö", "ö", "ü", "Ü", "Ý", "ý", "ð", "Ð", "þ", "Þ");
    if (is_array($data))//parameter must be an array
    {
        for($k=0;$k<sizeof($data);$k++)// loop through end of parameter 
        {
            for($i=0;$i<12;$i++) // loop 12 times becauser there are 12 turkish chars to change
            {
                 $data[$k] = str_replace ($chars_trk[$i], $chars_utf[$i], $data[$k]);
            }
        }  
    }
    if (!is_array($data))//parameter must be an array
    {
        for($i=0;$i<12;$i++) // loop 12 times becauser there are 12 turkish chars to change
        {
            $data = str_replace ($chars_trk[$i], $chars_utf[$i], $data);
        }
    }
    return $data;
}

?>
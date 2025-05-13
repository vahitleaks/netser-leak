<?php

$server='10.200.20.200';
$admin='uid=vodaconnector,ou=connectors,ou=people,c=tr,dc=com,o=disbank,o=gds';
$passwd='voda1234';

$ds=ldap_connect($server,3141);  // assuming the LDAP server is on this host

if ($ds) {
    // bind with appropriate dn to give update access
    $r=ldap_bind($ds, $admin, $passwd);
    if(!$r) die("ldap_bind failed<br>");

    echo "ldap_bind success";
if($result_array) 
{ 
for($i=0; $i<count($format_array[$i]) ; $i++){ 
$format_array[$i][0] = strtolower($result_array[$i]["cn"][0]); 
$format_array[$i][1] = $result_array[$i]["dn"]; 
$format_array[$i][2] = strtolower($result_array[$i]["givenname"][0]); 
$format_array[$i][3] = strtolower($result_array[$i]["sn"][0]); 
$format_array[$i][4] = strtolower($result_array[$i]["mail"][0]); 
} 

//Sort array 
sort($format_array, "SORT_STRING"); 

for($i=0; $i<count($format_array); $i++) 
{ 
$cn = $format_array[$i][0]; 
$dn = $format_array[$i][1]; 
$fname = ucwords($format_array[$i][2]); 
$lname = ucwords($format_array[$i][3]); 
$email = $format_array[$i][4]; 

if($dn && $fname && $lname && $email) 
{ 
$result_list .= "<A HREF=\"ldap://$LDAP_SERVER[$SERVER_ID]/$dn\">$fname $lname</A>"; 
$result_list .= " <A HREF=\"mailto:$email\">$email</A><BR>\n"; 
} 
elseif($dn && $cn && $email) 
{ 
$result_list .= "$cn"; 
$result_list .= " <$email>
\n"; 
} 
} 
} 
else 
{ 
echo "Result set empty for query: $ldap_query"; 
} 


 
    
    ldap_close($ds);
} else {
    echo "Unable to connect to LDAP server"; 
}
?> 

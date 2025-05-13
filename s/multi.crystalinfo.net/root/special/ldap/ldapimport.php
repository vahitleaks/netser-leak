<?
    $DOCUMENT_ROOT = "/usr/local/wwwroot/multi.crystalinfo.net/root/";
    require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
    require_once(dirname($DOCUMENT_ROOT)."/root/special/ldap/ldapfnc.php");
    header("charset:windows-1254");
    $cdb = new db_layer();
    $cUtility = new Utility();    
       $sql_str = "SELECT * FROM TbLdapUser Limit 1";
       if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
       if (mysql_numrows($result)>0){
         $row = mysql_fetch_object($result);
         
       }


$host = $row->StLdapServer;
//$host = "ldaptest";
$user = myDecryption($row->StLdapUserName);

$pswd = myDecryption($row->StLdapPassword);

$ad = ldap_connect($host, $row->StPortNo)
      or die( "Could not connect!" );

// Set version number
ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3)
     or die ("Could not set ldap protocol");
//)//, $user, $pswd)
// Binding to ldap server
$bd = ldap_bind($ad,$user,$pswd)
      or die ("Could not bind");

// Create the DN
$dn = myDecryption($row->StLdapBaseDn);

// Specify only those parameters we're interested in displaying
$start=  $cUtility->myMicrotime();
// Create the filter from the search parameters
$filtre = $row->StFilter;

$search = ldap_search($ad, $dn, $filtre)
          or die ("ldap search failed");

$entries = ldap_get_entries($ad, $search);


$stop =  $cUtility->myMicrotime();
$cdb->show_time(($stop -$start));
echo sizeof($entries);
if ($entries["count"] > 0) {
$sql_str="Delete from TbLdapTmp";
if (!($cdb->execute_sql($sql_str,$resDel,$error_msg))){
          print_error($error_msg);
          exit;
       }
for ($i=0; $i<$entries["count"]; $i++) {
   $args = Array();
   if($entries[$i]["cn"][0]!="" && $entries[$i]["extension"][0]!="" && $entries[$i]["fuidu"][0]!="" && $entries[$i]["fuidid"][0]!="" && $entries[$i]["l"][0]!=""){
       $StFuiddesc=str_replace("&", "-", utf2turk($entries[$i]["fuiddesc"][0]));
       $StFuiddesc=str_replace("/", " ", $StFuiddesc);
       $StFuiddesc=str_replace("\\", " ", $StFuiddesc);
               $stExtNo = $entries[$i]["extension"][0];
               
               $sqlCheck = "select InLdapTmpId from TbLdapTmp Where StExtension='".$stExtNo."'";
               
               if (!($cdb->execute_sql($sqlCheck,$res_chk,$error_msg))){
                  print_error($error_msg);
                  exit;
               }
               if(mysql_num_rows($res_chk)>0){
                 
                   $sqlDel = "delete from TbLdapTmp Where StExtension='".$stExtNo."'";
                   
                   if (!($cdb->execute_sql($sqlDel,$res_del,$error_msg))){
                      print_error($error_msg);
                      exit;
                   }
               }
       $args[] = array("StFullName",        utf2turk($entries[$i]["cn"][0]),                cFldWQuote);
       $args[] = array("StExtension",       $entries[$i]["extension"][0],                   cFldWQuote);
       $args[] = array("StSicil",           utf2turk($entries[$i]["disbanksicil"][0]),      cFldWQuote);
       $args[] = array("StEmail",           utf2turk($entries[$i]["mail"][0]),              cFldWQuote);
       $args[] = array("StFuidu",           $entries[$i]["fuidu"][0],                       cFldWQuote);
       $args[] = array("StFuidDesc",        $StFuiddesc,                                    cFldWQuote);
       $args[] = array("StFuidid",          $entries[$i]["fuidid"][0],                      cFldWQuote);
       $args[] = array("StManagerFuididp",  $entries[$i]["managerfuidp"][0],                cFldWQuote);
       $args[] = array("StLocation",        utf2turk($entries[$i]["l"][0]),                 cFldWQuote);
       $sql_str =  $cdb->InsertString("TbLdapTmp", $args);
       if (!($cdb->execute_sql($sql_str,$res_insert,$error_msg))){
          print_error($error_msg);
          exit;
       }
   }
}
    $sql_str  = "call spLdapImport(); ";
    $mysqli = new mysqli(DB_IP, DB_USER, DB_PWD, DB_NAME );
    $ivalue=1;
    $result = $mysqli->query( $sql_str  );
    if ($result->num_rows>0) {
      $row = $result->fetch_row() ;
    }
    echo "\nAktarma iþlemi Baþarýyla Tamamlandý.\n";
} else {
   echo "<p>No results found!</p>";
}

ldap_unbind($ad);
/*
- cn                    :Full Name 
- disbanksicil          :kullanýcýnýn sicil nosu
- extension             :dahili numaralarý
- mail                  :Email address 
- fuidu                 :kiþinin çalýþtýðý departman id si  
- fuiddesc              :kiþinin çalýþtýðý departman ismi 
- fuidid                :kullanýcýnýn id si
- managerfuidp          :kulanýcýnýn managerýnýn id si  managerfuidp
- l                     :Locality (hangi lokasyonda olduðu)
*/
?>
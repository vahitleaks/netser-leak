<?
    require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
    require_once(dirname($DOCUMENT_ROOT)."/root/special/ldap/ldapfnc.php");
    header("charset:windows-1254");
    $cdb = new db_layer();
    $cUtility = new Utility();  
     require_valid_login();    
     cc_page_meta();
     fillsecondcombo();
     echo "<center>";
     page_header();
     echo "<br><br>";
     table_header("LDAP SERVERDAN DATA AKTARIMI","65%");
      
    /////////   1. Basamak
    ///////// Ho� geldiniz.
    if($p ==""){
        ?>
            <table border=1>
            <tr>
            	<td>Dahili Data Aktar�m Mod�l�.</td>
            </tr>
            <tr>
            	<td><input type="button" value="1/3- LDAP Server'a Ba�lan"  onclick="window.location.href='?p=1'"></td>
            </tr>
            </table>
        
        <?
    }
    if($p =="1"){
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
    
    if($ad = ldap_connect($host, $row->StPortNo)){
            ?>
            <table border="1">
            <tr>
            	<td>Ba�lant� Ba�ar�l�</td>
                </tr><tr>
            	<td><input type="button" value=" 2/3 - Kay�tlar� Getir" onclick="this.value='L�tfen Bekleyiniz.';this.disabled=true;window.location.href='?p=2';"></td>
            </tr>
            </table>
        <?
    }else{
            ?>
            <table border=1>
            <tr>
            	<td>Ba�lant� Ba�ar�s�z</td>
                </tr><tr>
            	<td><input type="button" value="Tekrar Dene" onclick="window.location.href='?p=1'"></td>
            </tr>
            </table>
        <?
    
    }
}

    if($p =="2"){
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
                 or die("Ba�lant� Hatas�");
        
        // Set version number
        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3)
             or die ("Could not set ldap protocol");
        //)//, $user, $pswd)
        // Binding to ldap server
        if($bd = ldap_bind($ad,$user,$pswd)){
        
        // Create the DN
        $dn = myDecryption($row->StLdapBaseDn);
        
        // Specify only those parameters we're interested in displaying
        $start=  $cUtility->myMicrotime();
        // Create the filter from the search parameters
        $filtre = $row->StFilter;
        
        $search = ldap_search($ad, $dn, $filtre)
                  or die ("ldap search failed -".$filtre);
        
        $entries = ldap_get_entries($ad, $search);
        
        
        $stop =  $cUtility->myMicrotime();
        //$cdb->show_time(($stop -$start));
        //echo sizeof($entries);
        $successfullEntry=0;
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
               $successfullEntry ++;
           }
        }
        ?>
            <table border=1>
            <tr>
            	<td>Datalar ge�ici tabloya at�lm��t�r. <br>
                    At�lan data adedi :<strong> <?=$successfullEntry?></strong><br>
                    Sorunlu data adedi : <strong><?=$entries["count"]-$successfullEntry;?></strong><br><br>
                    Not : Datalar�n at�labilmesi i�in; <br>
                    Ad� Soyad�(cn), <br>
                    Dahilisi(extension), <br>
                    Departman Id(fuidu),<br>
                    Kullan�c�n�n id si(fuidid),<br>
                    Lokasyon(l)<br>
                    alanlar�n�n dolu olmas� gerekmektedir. <br><br>
                    
                    <strong>Bu kriterleri sa�lamayan kay�tlar sorunlu kay�t olarak kabul edilmektedir. </strong>
                    
                </td>
                </tr><tr>
            	<td><input type="button" value="3/3  Datalar� Ger�ek Ortama Aktar" onclick="this.value='L�tfen Bekleyiniz.';this.disabled=true;window.location.href='?p=3'"></td>
            </tr>
            </table>
        <?
    } else {
            ?>
            <table border=1>
            <tr>
            	<td>Hi� kay�t bulunamad�</td>
                </tr><tr>
            	<td><input type="button" value="Tekrar Dene" onclick="window.location.href='?p=2'"></td>
            </tr>
            </table>
        <?
    }
    
    ldap_unbind($ad);
  }else{
            ?>
            <table border=1>
            <tr>
            	<td>Bind problemi olu�tu!</td>
                </tr><tr>
            	<td><input type="button" value="Tekrar Dene" onclick="window.location.href='?p=2'"></td>
            </tr>
            </table>
        <?
  
  }

}
if($p=="3"){
        $sql_str  = "call spLdapImport(); ";
        $mysqli = new mysqli(DB_IP, DB_USER, DB_PWD, DB_NAME );
        $ivalue=1;
        $result = $mysqli->query( $sql_str  );
        if ($result->num_rows>0) {
          $row = $result->fetch_row() ;
        }
        
        $sql_str = "SELECT count(*) AS adet FROM DEPTS ";
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
            print_error($error_msg);
            exit;
        }
        if (mysql_numrows($result)>0){
            $row = mysql_fetch_object($result);
        }    

        $sql_str = "SELECT count(*) AS adet FROM EXTENTIONS ";
        if (!($cdb->execute_sql($sql_str,$result2,$error_msg))){
            print_error($error_msg);
            exit;
        }
        if (mysql_numrows($result2)>0){
            $row2 = mysql_fetch_object($result2);
        }    
            
        ?>
            <table border=1>
            <tr>
            	<td>Datalar ger�ek ortama at�lm��t�r. <br>
                    At�lan departman adedi :<strong> <?=$row->adet?></strong><br>                    
                    At�lan dahili adedi : <strong><?=$row2->adet?></strong><br><br><br>
                    
                    Not : Normal olarak al�nan kay�tlar�n i�erisinde tekrarl� kay�tlar olabilir. <br>
                    Ger�ek ortama at�laca�� zaman sadece 1 adet(distinct) veri at�lmaktad�r. <br>
                    Yukar�da ki adetler al�nan kay�tlarla fakl�l�k g�sterebilir. <br>
                </td>
                </tr><tr>
            	<td><input type="button" value="Ba�a D�n" onclick="window.location.href='?p='"></td>
            </tr>
            </table>
        <?
}
   table_footer();
/*
- cn                    :Full Name 
- disbanksicil          :kullan�c�n�n sicil nosu
- extension             :dahili numaralar�
- mail                  :Email address 
- fuidu                 :ki�inin �al��t��� departman id si  
- fuiddesc              :ki�inin �al��t��� departman ismi 
- fuidid                :kullan�c�n�n id si
- managerfuidp          :kulan�c�n�n manager�n�n id si  managerfuidp
- l                     :Locality (hangi lokasyonda oldu�u)
*/
?>
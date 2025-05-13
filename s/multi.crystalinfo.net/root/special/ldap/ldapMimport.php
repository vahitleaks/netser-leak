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
    ///////// Hoþ geldiniz.
    if($p ==""){
        ?>
            <table border=1>
            <tr>
            	<td>Dahili Data Aktarým Modülü.</td>
            </tr>
            <tr>
            	<td><input type="button" value="1/3- LDAP Server'a Baðlan"  onclick="window.location.href='?p=1'"></td>
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
            	<td>Baðlantý Baþarýlý</td>
                </tr><tr>
            	<td><input type="button" value=" 2/3 - Kayýtlarý Getir" onclick="this.value='Lütfen Bekleyiniz.';this.disabled=true;window.location.href='?p=2';"></td>
            </tr>
            </table>
        <?
    }else{
            ?>
            <table border=1>
            <tr>
            	<td>Baðlantý Baþarýsýz</td>
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
                 or die("Baðlantý Hatasý");
        
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
            	<td>Datalar geçici tabloya atýlmýþtýr. <br>
                    Atýlan data adedi :<strong> <?=$successfullEntry?></strong><br>
                    Sorunlu data adedi : <strong><?=$entries["count"]-$successfullEntry;?></strong><br><br>
                    Not : Datalarýn atýlabilmesi için; <br>
                    Adý Soyadý(cn), <br>
                    Dahilisi(extension), <br>
                    Departman Id(fuidu),<br>
                    Kullanýcýnýn id si(fuidid),<br>
                    Lokasyon(l)<br>
                    alanlarýnýn dolu olmasý gerekmektedir. <br><br>
                    
                    <strong>Bu kriterleri saðlamayan kayýtlar sorunlu kayýt olarak kabul edilmektedir. </strong>
                    
                </td>
                </tr><tr>
            	<td><input type="button" value="3/3  Datalarý Gerçek Ortama Aktar" onclick="this.value='Lütfen Bekleyiniz.';this.disabled=true;window.location.href='?p=3'"></td>
            </tr>
            </table>
        <?
    } else {
            ?>
            <table border=1>
            <tr>
            	<td>Hiç kayýt bulunamadý</td>
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
            	<td>Bind problemi oluþtu!</td>
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
            	<td>Datalar gerçek ortama atýlmýþtýr. <br>
                    Atýlan departman adedi :<strong> <?=$row->adet?></strong><br>                    
                    Atýlan dahili adedi : <strong><?=$row2->adet?></strong><br><br><br>
                    
                    Not : Normal olarak alýnan kayýtlarýn içerisinde tekrarlý kayýtlar olabilir. <br>
                    Gerçek ortama atýlacaðý zaman sadece 1 adet(distinct) veri atýlmaktadýr. <br>
                    Yukarýda ki adetler alýnan kayýtlarla faklýlýk gösterebilir. <br>
                </td>
                </tr><tr>
            	<td><input type="button" value="Baþa Dön" onclick="window.location.href='?p='"></td>
            </tr>
            </table>
        <?
}
   table_footer();
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
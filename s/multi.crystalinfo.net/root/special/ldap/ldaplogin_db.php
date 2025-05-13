<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    require_once(dirname($DOCUMENT_ROOT)."/root/special/ldap/ldapfnc.php");
    $cDB = new db_layer();
    require_valid_login();

    $args = Array();
    //Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
     if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Bu sayfaya eriþim hakkýnýz yok!");
    exit;
     }



      $args[] = array("StLdapServer",       $StLdapServer,                        cFldWQuote);
      $args[] = array("StLdapUserName",     myEncryption($StLdapUserName),        cFldWQuote);
      if($StLdapPassword!=""){
        $args[] = array("StLdapPassword",     myEncryption($StLdapPassword),        cFldWQuote);
      }
      $args[] = array("StLdapBaseDn",       myEncryption($StLdapBaseDn),          cFldWQuote);
      $args[] = array("StPortNo",      	  $StPortNo,          		     cFldWQuote);
      $args[] = array("StFilter",      	  $StFilter,          		     cFldWQuote);

            
   
      if($InLdapUserId !="" && is_numeric($InLdapUserId)){
            $args[] = array("InLdapUserId",$InLdapUserId, cReqWoQuote);
            $sql_str =  $cDB->UpdateString("TbLdapUser", $args);
            if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
      }else{
            $sql_str =  $cDB->InsertString("TbLdapUser", $args);
            if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
            $id = mysql_insert_id();
      }


//////////////////////////////////////////////////////////////////////////////////////////
    
      header("Location:ldaplogin.php");
?>

<?
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
      $cDB = new db_layer();
      require_valid_login();

      $cDT = new datetime_operations();
      $args = Array();

      //Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
       if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
          print_error("Burayý Görme Hakkýnýz Yok");
     exit;
       }

      $sql_str = "SELECT * FROM USERS WHERE USERNAME = '$USERNAME'" ; 
      $my_state = exist_status($sql_str,$act,'USER_ID',$id);
      if ($my_state==1 || $my_state==2){
            print_error("Bu kullanýcý kaydý daha önce yapýlmýþ. Ýkinci Defa Yapamazsýnýz.");
            exit;
      }

////////////////////////////////////////DELETE USER/////////////////////////////////////
// bir kullanýcý silinince nelerin silinmesi gerektiði belirlenmeli.
      if ($act =="del" && $id!="" && is_numeric($id))  { 
            $sql_str = "DELETE FROM USERS WHERE USER_ID = '$id' AND SITE_ID = ".$SITE_ID;
      if (!($cDB->execute_sql($sql_str, $result, $error_msg))){
                  print_error($error_msg);
                  exit;
            }
      header("Location:user_src.php");
    exit;
    }
//////////////////////////////////////////////////////////////////////////////////////////

      $args[] = array("SITE_ID",      $SITE_ID,       cFldWoQuote);
      $args[] = array("USERNAME",       $USERNAME,       cFldWQuote);
//      $args[] = array("PASSWORD",       $PASSWORD,       cFldWQuote);
      $args[] = array("NAME",           $NAME,           cFldWQuote);
      $args[] = array("SURNAME",        $SURNAME,        cFldWQuote);
      $args[] = array("POSITION",       $POSITION,       cFldWQuote);
      $args[] = array("EMAIL",          $EMAIL,          cFldWQuote);
      $args[] = array("LAST_UPDATER",   $LAST_UPDATER,   cFldWQuote);
      $args[] = array("LAST_UPDATE",    $LAST_UPDATE,    cFldWQuote);
      $args[] = array("DISABLED",       $DISABLED,       cFldWQuote);
      $args[] = array("LAST_TOUCH",     $LAST_TOUCH,     cFldWQuote);
      $args[] = array("EXT_ID1",        $EXT_ID1,       cFldWQuote);
      $args[] = array("EXT_ID2",        $EXT_ID2,       cFldWQuote);
      $args[] = array("EXT_ID3",        $EXT_ID3,       cFldWQuote);
      $args[] = array("DEPT_ID",        $DEPT_ID,        cFldWQuote);
      $args[] = array("AUTH_CODE_ID",   $AUTH_CODE_ID,  cFldWoQuote);
      $args[] = array("GSM",            $GSM,            cFldWQuote);
      $args[] = array("HOME_TEL",       $HOME_TEL,       cFldWQuote);
      $args[] = array("NOTE",           $NOTE,           cFldWQuote);

      
      if ($act =="" || $act =="new" )  { 
            $sql_str =  $cDB->InsertString("USERS", $args);
            if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
            $id = mysql_insert_id();
      }
   
      if($act == "upd" && $id !="" && is_numeric($id)){
            $args[] = array("USER_ID",$id, cReqWoQuote);
            $sql_str =  $cDB->UpdateString("USERS", $args);
            if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }

      }

      header("Location:user.php?act=upd&id=".$id);

?>

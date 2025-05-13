<?
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");

      $cDB = new db_layer();
      require_valid_login();

      $cDT = new datetime_operations();
      $args = Array();
      $args[] = array("NAME",              $NAME,               cFldWQuote);
      $args[] = array("SURNAME",           $SURNAME,            cFldWQuote);
      $args[] = array("POSITION",          $POSITION,           cFldWQuote);
      $args[] = array("TITLE_ID",          $TITLE_ID,           cFldWoQuote);
      $args[] = array("DEP_ID",            $DEP_ID,             cFldWoQuote);
      $args[] = array("SUB_DEP_ID",        $SUB_DEP_ID,         cFldWoQuote);
      $args[] = array("EXT_HOME_TEL",      $EXT_HOME_TEL,       cFldWQuote);
      $args[] = array("EXT_COMP_TEL",      $EXT_COMP_TEL,       cFldWQuote);
      $args[] = array("EXTERNAL_HOME_TEL", $EXTERNAL_HOME_TEL,  cFldWQuote);
      $args[] = array("EXTERNAL_COMP_TEL", $EXTERNAL_COMP_TEL,  cFldWQuote);
      $args[] = array("PERSONAL_EMAIL",    $PERSONAL_EMAIL,     cFldWQuote);
      $args[] = array("ADDRESS",           $ADDRESS,            cFldWQuote);
     
      if ($act =="" || $act =="new" )  { 
         $sql_str =  $cDB->InsertString("FIHRIST", $args);
         if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
             print_error($error_msg);
             exit;
         }
         $id = mysql_insert_id();
      }
      if($act == "upd" && $id !="" && is_numeric($id)){
        $args[] = array("CONTACT_ID",$id, cReqWoQuote);
        $sql_str =  $cDB->UpdateString("FIHRIST", $args);
        if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
        }
      }
    
////////////////////////////////////////DELETE CONTACT/////////////////////////////////////

      if ($act =="del" && $id!="" && is_numeric($id))  { 
         $sql_str = "DELETE FROM FIHRIST WHERE CONTACT_ID = '$id'";
         if (!($cDB->execute_sql($sql_str, $result, $error_msg))){
            print_error($error_msg);
            exit;
         }        
         header("Location:contact_src.php");
         exit;
      }
//////////////////////////////////////////////////////////////////////////////////////////

      header("Location:contacts.php?act=upd&id=".$id);
?>
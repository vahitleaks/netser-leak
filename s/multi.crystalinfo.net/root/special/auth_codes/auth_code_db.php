<?  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    $cDB = new db_layer();
    require_valid_login();

    $args = Array();
    //Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
     if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Bu sayfaya eriþim hakkýnýz yok!");
    exit;
     }

      $sql_str = "SELECT * FROM AUTH_CODES WHERE AUTH_CODE = '$AUTH_CODE' AND SITE_ID = '$SITE_ID'" ; 
      $my_state = exist_status($sql_str,$act,'AUTH_CODE_ID',$id);
      if ($my_state==1 || $my_state==2){
            print_error("Bu Authorization kaydý daha önce bir baþkasý için yapýlmýþ. Ýkinci Defa Yapamazsýnýz.");
            exit;
      }      

      $args[] = array("SITE_ID",        $SITE_ID,          cFldWoQuote);
      $args[] = array("DEP_ID",         $DEP_ID,           cFldWoQuote);
      $args[] = array("AUTH_CODE",      $AUTH_CODE,        cFldWQuote);
      $args[] = array("AUTH_CODE_DESC", $AUTH_CODE_DESC,   cFldWQuote);
      $args[] = array("DETAIL",         $DETAIL,           cFldWoQuote);
      $args[] = array("MER_MAIL",       $MER_MAIL,         cFldWoQuote);
            
      if ($act =="" || $act =="new" )  { 
            $sql_str =  $cDB->InsertString("AUTH_CODES", $args);
            if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
            $id = mysql_insert_id();
      }
   
      if($act == "upd" && $id !="" && is_numeric($id)){
            $args[] = array("AUTH_CODE_ID",$id, cReqWoQuote);
            $sql_str =  $cDB->UpdateString("AUTH_CODES", $args);
            if (!($cDB->execute_sql($sql_str,$result,$error_msg))){
                  print_error($error_msg);
                  exit;
            }
      }

////////////////////////////////////////DELETE AUTH_CODE/////////////////////////////////////
      if ($act =="del" && $id!="" && is_numeric($id))  { 
            $sql_str = "DELETE FROM AUTH_CODES WHERE AUTH_CODE_ID = '$id' AND SITE_ID = ".$SITE_ID;
      if (!($cDB->execute_sql($sql_str, $result, $error_msg))){
                  print_error($error_msg);
                  exit;
            }
          header("Location:auth_code_src.php");
        exit;
    }
//////////////////////////////////////////////////////////////////////////////////////////
    
      header("Location:auth_code.php?act=upd&id=".$id);
?>

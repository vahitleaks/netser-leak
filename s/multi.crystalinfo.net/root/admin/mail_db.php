<?
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
      
      $cUtility = new Utility();
      $cdb = new db_layer();
      $conn = $cdb->getConnection();
      $Mail = new phpmailer();

      $Mail->From       =$FROM;
      $Mail->FromName   =$FROMNAME;
      $Mail->Sender     =$SENDER;
      $Mail->AddCustomHeader($ADDCUSTOMHEADER);
      $Mail->CharSet    = "iso-8859-9";
      $Mail->IsHTML(true);
      $Mail->ClearAllRecipients();
      if ($BODY!=""){
            $Mail->AddAddress($TO);
            $Mail->Subject = $SUBJECT;
            $Mail->Body = $BODY;
            $Mail->IsSMTP();
            if (!$Mail->Send()){
                  $message=$Mail->ErrorInfo;
                    //    $DATA .=  $message. "<br>". $row->ALICI_MAIL ." Adresine Atamadým.";
                  exit;
            }
      }

?>

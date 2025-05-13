<?
  require_once("doc_root.cnf");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");

  function mail_send($mail,$subject,$DATA,$add_img=1){
     $Mail = new phpmailer();
     $Mail->From       = SMTP_FROM;
     $Mail->FromName   = SMTP_FROMNAME;
     $Mail->Sender     = SMTP_PMASTER;
     $Mail->AddCustomHeader("Errors-To: <".SMTP_PMASTER.">");
     $Mail->CharSet    = SMTP_CHARSET;
     $Mail->IsHTML(SMTP_HTML);
     $Mail->ClearAttachments ();
	 if ($add_img==1){
      // $Mail->AddEmbeddedImage("../images/company.gif", "my-attach", "logo.gif");
      // $Mail->AddEmbeddedImage("../images/logo1.gif", "my-crystal", "crystal.gif");
     }
	 $Mail->ClearAllRecipients();
	 if ($DATA!=""){
        $Mail->AddAddress($mail);
        $Mail->Subject = $subject;
        $Mail->Body = $DATA;
        $Mail->IsSMTP();
        if (!$Mail->Send()){
            echo $Mail->ErrorInfo. "<br>". $mail ." Adresine Atamadým. \n";
        }else{
           echo $mail ." Adresine Atýldý. \n<BR>";
        }
        
     }
  }
?>
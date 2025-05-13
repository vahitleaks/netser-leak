<?
      require_once("../crons/doc_root.cnf");
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
      require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/class.phpmailer.php");
      session_cache_limiter('nocache');
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    
      clearstatcache ();
      $Mail = new phpmailer();
      $Mail->From       = SMTP_FROM;
      $Mail->FromName   = SMTP_FROMNAME;
      $Mail->Sender     = SMTP_PMASTER;
      $Mail->AddCustomHeader("Errors-To: <".SMTP_PMASTER.">");
      $Mail->CharSet    = SMTP_CHARSET;
      $Mail->IsHTML(SMTP_HTML);
      $Mail->ClearAttachments ();
      $Mail->AddAttachment($DOCUMENT_ROOT."$page", "report.html");
	  $Mail->ClearAllRecipients();
      $Mail->AddAddress($email);
      $Mail->Subject = "Mail Attachment";
      $Mail->Body = "";
      $Mail->IsSMTP();
      if (!$Mail->Send()){
          print_error("Mail gönderilemedi, Hata:". $Mail->ErrorInfo);
          exit;
      }
      print_error("Mail baþarýyla \" $email \" adresine gönderilmiþtir");
?>

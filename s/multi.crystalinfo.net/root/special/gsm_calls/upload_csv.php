<?php
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/upload_class.php");
   set_time_limit(3600);
   
   $cdb = new db_layer();
   $cUtility = new Utility();
   require_valid_login();
    if (!right_get("SITE_ADMIN")){
        //Site admin hakký varsa herþeyi görebilir.  
      print_error("Bu sayfayý Görme Hakkýnýz Yok!!!");
      exit;
    }
   cc_page_meta();
   page_header();
?>
<script LANGUAGE="javascript" src="/scripts/popup.js"></script>
        <br><br>
        <table border=1 width="550" cellspacing=0>
          <tr>
		  <td class="header">GSM Görüþmeleri Upload
          <?//=strftime("%m", strtotime(convert_date_time("31/12/2007")))?>
          </td>
          </tr>
        </table><br>
<?


     table_header("CSV Dosya Yükleme","50%"); 
$upload = new Upload();
$upload->printFormStart("upload_csv.php");
if ($submit) {
  $upload->setAllowedMimeTypes(
  array(
          "application/x-gzip-compressed"   => ".tar.gz, .tgz",
          "application/x-zip-compressed"     => ".zip",
          "application/x-tar"          => ".tar",
          "text/plain"            => ".php, .txt, .inc, .csv (etc)",
          "text/html"              => ".html, .htm (etc)",
          "image/bmp"             => ".bmp, .ico",
          "image/gif"             => ".gif",
          "image/pjpeg"            => ".jpg, .jpeg",
          "image/jpeg"            => ".jpg, .jpeg",
          "image/x-png"            => ".png",
          "audio/mpeg"            => ".mp3 etc",
          "audio/wav"              => ".wav",
          "application/pdf"          => ".pdf",
          "application/x-shockwave-flash"   => ".swf",
          "application/msword"        => ".doc",
          "application/vnd.ms-excel"      => ".xls",
          "application/octet-stream"      => ".exe, .fla, .psd (etc)"
        )
  );
  $upload->setUploadPath($DOCUMENT_ROOT."/temp/");
  if ($upload->doUpload()) {
    print "Files Uploaded!<br>";
	$filename= $upload->HTTP_POST_FILES[$upload->uploadFieldName]['name'];
      if(!chmod($DOCUMENT_ROOT."/temp/".$filename, 777)){
        echo "Deðiþtiremiyor!";
      }
      $fp = fopen ($DOCUMENT_ROOT."/temp/".$filename, "r"); 
      if(!$fp){  
        echo "Error";
        exit;
      }
      if($MYDATE==""){
          print_error("Tarih Kriteri Girmelisiniz!!!");
          exit;
      }
    $MYDATE = convert_date_time($MYDATE);
    if (!($cdb->execute_sql("Delete from TGSMCALLS Where Year(MYDATE)='".strftime("%Y", strtotime($MYDATE))."' and Month(MYDATE)='".strftime("%m", strtotime($MYDATE))."'", $result, $error_msg))){
        echo $error_msg."<br>";
    }
     while (!feof ($fp)){
          $buffer = fgets($fp, 5000);
          if(strlen($buffer) > 0){
              $sql= $buffer;
              $str_arr = explode(";", $buffer);
			  $ins_fields="";
			  if(trim($str_arr[0])!="Telefon no" && trim($str_arr[0])!=""){
			    for($s=0;$s<=3;$s++){
                  $text = trim($str_arr[$s]);
                  $text = str_replace("'","\'",$text);
                  if($text == "-"){$text = "0";};
                  if($s==3){$text = str_replace(",",".",$text);}
    			  if($ins_fields!=""){$ins_fields=$ins_fields.",";}
				  $ins_fields=$ins_fields."'".$text."'";
                }
                $sql = "INSERT INTO TGSMCALLS (ID, GSM_NO, SICIL_NO, SNAME, DCOST, MYDATE) 
	  		     VALUES (NULL, ".$ins_fields.", '$MYDATE')";
                if (!($cdb->execute_sql($sql, $result, $error_msg))){
                    echo $error_msg."<br>";
                }
			  }
          }
      } 
      fclose ($fp);	
	
	
	
  } else {
    $errors = $upload->getUploadErrors();
    print "<strong>::Errors occured::</strong><br />\n";
    while(list($filename,$values) = each($errors)) {
      "File: " . print $filename . "<br />";
      $count = count($values);
      for($i=0; $i<$count; $i++) {
        print "==>" . $values[$i] . "<br />";
      }
    }
  }
}else{

// put as many of these in as you want, 
// pass a string filename, else a default is used.
$upload->printFormField();
print"<br />";
?>
Fatura Tarihi :<input type="text" size=17 name="MYDATE" VALUE="<?echo $t0?>" readonly><a href="javascript://"><img align="absmiddle" src="<?=IMAGE_ROOT?>takvim_icon.gif" onclick="javascript:show_calendar(document.all('MYDATE').name,null,null,null,window.event.screenX,window.event.screenY,1);" border="0"></a>
<br>
<?
$upload->printFormSubmit();
$upload->printFormEnd();
}
     table_footer(0);
     page_footer("");
?>

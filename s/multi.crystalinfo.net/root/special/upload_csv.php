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
   set_time_limit(3600);
   $cdb = new db_layer();
   $cUtility = new Utility();
   cc_page_meta(0);
   page_header();
?>
        <br><br>
        <table border=1 width="550" cellspacing=0>
          <tr>
		  <td>
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
    if (!($cdb->execute_sql("Delete from TPERSONEL", $result, $error_msg))){
        echo $error_msg."<br>";
    }
     while (!feof ($fp)){
          $buffer = fgets($fp, 5000);
          if(strlen($buffer) > 0){
              $sql= $buffer;
              $str_arr = explode(";", $buffer);
			  $ins_fields="";
			  if(trim($str_arr[0])!="Person Id" && trim($str_arr[0])!="" && (trim($str_arr[6])=="5000")){
			    for($s=0;$s<=10;$s++){
                  $text = trim($str_arr[$s]);
                  $text = str_replace("'","\'",$text);
    			  if($ins_fields!=""){$ins_fields=$ins_fields.",";}
				  $ins_fields=$ins_fields."'".$text."'";
                }
                $sql = "INSERT INTO TPERSONEL (ID, PERSON_ID, SICIL_NO, SNAME, SSURNAME, MAIL1,
		  	     SPLCNAME, IPLCCODE, SMNGCODE, SMNGNAME, SMNGSICIL, MAIL2) 
	  		     VALUES (NULL, ".$ins_fields.")";
                if (!($cdb->execute_sql($sql, $result, $error_msg))){
                    echo $error_msg."<br>";
                }
			  }
          }
      } 
      fclose ($fp);	
        $sql = "SELECT T.* FROM TPERSONEL T
                LEFT JOIN EXTENTIONS E ON lcase(E.DESCRIPTION)=lcase(CONCAT(T.SNAME, ' ', T.SSURNAME))
                WHERE E.EXT_ID IS NULL";
        if (!($cdb->execute_sql($sql, $result, $error_msg))){
          echo $error_msg."<br>";
        }
    echo "<table width=700 border=1>\n";
	while($row=mysql_fetch_array($result)){
      echo "<tr>\n";
      for($g=0;$g<=10;$g++){
        echo "<td>".$row[$g]."</td>\n";
      }
      echo "</tr>\n";
    }
	echo "</table>\n";
    
    if (!($cdb->execute_sql("DELETE FROM DEPTS", $resDpt, $error_msg))){
      echo $error_msg."<br>";
    }
    
    if (!($cdb->execute_sql("ALTER TABLE DEPTS AUTO_INCREMENT=1", $resDpt, $error_msg))){
      echo $error_msg."<br>";
    }
    $sql_Depts="Insert into DEPTS(SITE_ID,DEPT_NAME,DEPT_RSP_EMAIL)  
                VALUES(1, 'TANIMSIZ', '');";
    if (!($cdb->execute_sql($sql_Depts, $resinsDt, $error_msg))){
      echo $error_msg."<br>";
    }
    $sql_Depts="Insert into DEPTS(SITE_ID,DEPT_NAME,DEPT_RSP_EMAIL)  
                Select 1, SMNGNAME, MAIL2 from TPERSONEL 
                WHERE (MAIL2<>'' AND INSTR(MAIL2,'@')) 
                Group by SMNGNAME, MAIL2";
    if (!($cdb->execute_sql($sql_Depts, $resinsDt, $error_msg))){
      echo $error_msg."<br>";
    }
    $sql_Depts="SELECT D.DEPT_ID, T.SICIL_NO FROM DEPTS D 
                LEFT JOIN TPERSONEL T ON D.DEPT_RSP_EMAIL=T.MAIL2 
                ORDER BY D.DEPT_ID";
    if (!($cdb->execute_sql($sql_Depts, $resDt, $error_msg))){
      echo $error_msg."<br>";
    }
    
    while($rwD=mysql_fetch_object($resDt)){
      if($depts_arr[$rwD->DEPT_ID]!="" && isset($depts_arr[$rwD->DEPT_ID])){
        $depts_arr[$rwD->DEPT_ID]=$depts_arr[$rwD->DEPT_ID].", ";
      }
      $depts_arr[$rwD->DEPT_ID]=$depts_arr[$rwD->DEPT_ID]."'".$rwD->SICIL_NO."'";
    }
    foreach($depts_arr as $key=>$value){
      if($value!="" && $key!=""){
        $sql_exts = "UPDATE EXTENTIONS SET DEPT_ID=$key WHERE SICIL_NO IN ($value)";
        //echo  $sql_exts."<br>";
        if (!($cdb->execute_sql($sql_exts, $resEx, $error_msg))){
          echo $error_msg."<br>";
          die;
        }
      }
    }
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

$upload->printFormSubmit();
$upload->printFormEnd();
}
     table_footer(0);
     page_footer("");
?>

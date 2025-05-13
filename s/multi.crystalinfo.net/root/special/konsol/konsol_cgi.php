<? require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cdb = new db_layer();
   require_valid_login();

   header("Content-type:text/xml");
   //header("Man: POST ".$PHP_SELF." HTTP/1.1");
   //sql ler açýktan gönderilmemeli onun için bir select case ile halledilmeli

   $month = date("m");
 //$month=12;
   $year = date("Y");
   if ($month < 10 && substr($month,0,1)<>'0'){
	 $month = "0".$month;
   }

   $DIR_NAME ="/usr/local/sigma/crystal/data/".$month."_".$year."/";
   $siteStart=$SITE_ID;
   if($siteStart=="1")
     $siteStart="";
   $filename = $DIR_NAME.$siteStart."raw_".$month."_".$year.".dat";
    $file = popen("tac $filename",'r');
    $i=0;
    while ($i<10) {
      $line = fgets($file,8096);
      if($line=="")
        break;
      $lineArr[$i]= $line;
      $i++;
    }     
   $sayac=$i-1;
   $i=0;
   if($sayac<0)
     return 0;
   echo"<?xml version=\"1.0\" ?> ";//' Start our XML document.

   echo "<DATABASE> ";     //' Output start of data.
   //' Loop through the data records.
   $sayx=1;
   for($i=$sayac;$i>=0;$i--)
   {
        //Output start of record.
        echo "<SUB> ";
        // Loop through the fields in each record.
        $strValue = $lineArr[$i];
        if (strlen($strValue) > 0 ) $strValue = turkish2utf($strValue);
        $strValue = str_replace ("&", "", $strValue);

               echo "<ID>".$sayx."</ID>";
               echo "<DATA>".$strValue."</DATA>";
        echo "</SUB> ";  //    ' Move to next city in database.
        $sayx++;
   }
   echo "</DATABASE> "; //' Output end of data.
   
?> 
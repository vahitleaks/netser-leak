<?php
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   if(!$month && !$year){
     $month = date("m");
     $year = date("Y");
   }else{
	 if ($type=="next"){
	   $month = $month + 1;
	   if ($month == 13){
	     $month = "1";
		 $year= $year + 1;
	   }
	 }else if($type=="back"){
	   $month = $month - 1;
	   if ($month == 0){
	     $month = "12";
		 $year= $year - 1;
	   }
	 }
   }
   if ($month < 10 && substr($month,0,1)<>'0'){
	 $month = "0".$month;
   }
   if($DIR_NAME==""){
     $DIR_NAME ="/usr/local/sigma/crystal/data/".$month."_".$year."/";
   }
   $cdb = new db_layer();
   $cUtility = new Utility();
   require_valid_login();
   echo $DIR_NAME;
   if($act == "" || $act == "flsel"){
     $d = dir($DIR_NAME);
     if(!$d){
      // $access = "no";
     }
   cc_page_meta(0);
   page_header();
?>
        <br><br>
        <table border=1 width="550" cellspacing=0>
          <tr>
		  <td><b>
            Santral datalarý text dosya olarak sunucunuzda kayýtlý durumdadýr.<br>
            Buradan son datanýn geldiði tarihleri görebilirsiniz.
          </td>
          </tr>
        </table><br>
<?
       table_header("Data dosyalarý...","600"); 
?>
      <center>
      <table border=1 width="600" cellspacing=0>
      <!--<tr><td><a href = "text_to_db.php?month=<?=$month?>&year=<?=$year?>&type=back">Önceki Ay</a></td><td><a href = "text_to_db.php?month=<?=$month?>&year=<?=$year?>&type=next">Sonraki Ay</a></td></tr>-->
      <tr><td colspan="3" class="header">Kontrol Edilen Ay : <?=$month."-".$year?></td></tr>
	  <tr><td><td>Dosya Adý</td><td>Boyutu</td><td>Son Eriþim Tarihi</td></tr>
<?
function cmp ($a, $b) {
    global $month;global $year;
    $baseStr="raw_".$month."_".$year.".dat";
    $x = str_replace ($baseStr, "", $a[0]);
    $y = str_replace ($baseStr, "", $b[0]);
    if($x==""){$x=1;}
    if($y==""){$y=1;}
    if ($x == $y) return 0;
    return ($x < $y) ? -1 : 1;
} 

   if($access!="no"){
    
    while ($entry = $d->read()) {
         $entry = trim($entry);
         if($entry!=".." && $entry!="."){
             $file_size = (number_format((filesize($DIR_NAME.$entry)/1024), 2))." KB";
             $file_index[] = array($entry, $file_size, filemtime($DIR_NAME.$entry));
         }
     }
     $d->close();
     $i=0; 

     usort($file_index, "cmp"); 
    
     for ( $i=0 ; is_Array($file_index[$i]) ; $i++ ) {

             $file_time=strftime ("%d.%m.%Y %H:%M:%S", $file_index[$i][2]);
             echo " <tr><td>".($i+1)."</td><td>\n";
             echo $file_index[$i][0];
             echo "</td>\n";
             echo "<td>".$file_index[$i][1]."</td>\n";
             echo "<td>".$file_time."</td></tr>\n";
             $filename=$DIR_NAME.$file_index[$i][0];
     }
   } ?>
     </tr>
     </table>
      <center>
     <?

/*
        $file = popen("tac $filename",'r');
        echo "<textarea cols='100' rows='30'>";
        $i=0;
        while ($i<16) {
          $line = fgets($file,8096);
          echo $line;
          $i++;
        }     
        echo "</textarea>";
*/

     table_footer(0);
     page_footer("");
    }elseif($act == "upload" && !empty($flname)){
//      $month = "08";
//      $year = "2003";


////////////////////////////////////////7
   if($SITE_ID=="")   $SITE_ID=1; ///// SÝTE NO
////////////////////////////////////////

      $fp = fopen ($DIR_NAME.$flname, "r"); 
      if(!$fp){  
        echo "Error";
        exit;
      }
      $fn = fopen ("/usr/local/sigma/crystal/temp_".$flname, "w+"); 
      if(!$fn){  
        echo "Error";
        exit;
      }

     while (!feof ($fp)){
          $buffer = fgets($fp, 5000);
          if(strlen($buffer) > 0){
              $str_arr = explode("\t", $buffer);
              $text = $str_arr[0];
              $text = str_replace("'","\'",$text);
              
              $sql = "INSERT INTO RAW_DATA(DATA, DATE, SOURCE, DONE, ERROR_CODE, SITE_ID)
                        VALUES('".$text."','".$str_arr[1]."','TEXT',0,0, $SITE_ID)";
              if (!($cdb->execute_sql($sql, $result, $error_msg))){
                    echo $error_msg."<br>";
                    fwrite($fn, implode("\t", $str_arr));
              }
          }  
      } 
      fclose ($fp);
      fclose ($fn);

      $fp = fopen ($DIR_NAME.$flname, "w+"); 
      $fn = fopen ("/usr/local/sigma/crystal/temp_".$flname, "r"); 
      if(!$fp || !$fn){  
        echo "Error";
        exit;
      }
     while (!feof ($fn)){
          $buffer = fgets($fn, 5000);
          if(strlen($buffer) > 0){
                fwrite($fp, $buffer);
          }
     }
      fclose ($fp);
      fclose ($fn);
      unlink("/usr/local/sigma/crystal/temp_".$flname); 
      print_error("Ýþlem Baþarýyla Gerçekleþtirildi..<br>");
      }
?>

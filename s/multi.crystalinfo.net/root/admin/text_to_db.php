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
   if($DIR_NAME==""){
     $DIR_NAME ="/usr/local/sigma/crystal/data/";
   }
   
   $cdb = new db_layer();
   require_valid_login();
   if($act == "" || $act == "flsel"){
     $d = dir($DIR_NAME);
     if(!$d){
       $access = "no";
     }
   cc_page_meta(0);
   page_header();
   

   if($site_id=="") $site_id=1;
?>
        <br><br>
        <table border=1 width="550" cellspacing=0>
          <tr>
		  <td><b>
            Crystalinfo santral verilerini okurken veritabanýna eriþemediði zamanlarda,<br>
            aldýðý verileri "raw_ay_yýl_discnn.dat" gibi bir dosya adý ile kaydeder.<br> 
            Bu verilerin sisteme aktarýlmasý gereklidir. <br><br>
            Yüklenebilecek Dosyalar kýsmýnda buna uygun dosya isimleri varsa lütfen üzerine<br>
            týklayarak veritabanýna aktarýlmasýný saðlayýnýz.
          </td>
          </tr>
        </table>
        <table border=1 width="550" cellspacing=0>
          <tr>
		  <td>
        <form action="" method="post">
         Site ID si : <input type="Text" name="site_id" value="<?=$site_id?>" size=3 maxlength=3>
          <input type="Submit" value="Site Deðiþtir">
        </form>
          </td>
          </tr>
        </table>
        <br>
<?
       table_header("Yüklenebilecek Dosyalar...","50%"); 
?>
      <center>
      <table border=1 width="80%" cellspacing=0>
      <tr><td><a href = "text_to_db.php?month=<?=$month?>&year=<?=$year?>&type=back">Önceki Ay</a></td><td><a href = "text_to_db.php?month=<?=$month?>&year=<?=$year?>&type=next">Sonraki Ay</a></td></tr>
      <tr><td colspan="2" class="header">Kontrol Edilen Ay : <?=$month."-".$year?></td></tr>
	  <tr><td>Dosya Adý</td><td>Boyutu</td></tr>
<?
   if($access<>"no"){
	 while ($entry = $d->read()) {
         $stt = explode(".", $entry);
         if(substr($entry, -3)=="dat" && strchr($stt[0], "raw") && $stt[1]=="dat"){
           if(substr($stt[0], 0, 3) == "raw"){
             $show_file=1;
           }elseif(is_numeric(substr($stt[0], 0, strpos($stt[0],"raw")))){
             $show_file=1;
           }else{
             $show_file=0;
           }
           if($show_file){
             $file_size = (number_format((filesize($DIR_NAME.$entry)/1024), 2))." KB";
             echo " <tr><td>\n<a href=\"text_to_db.php?act=upload&DIR_NAME=".$DIR_NAME."&flname=".$entry."&site_id=$site_id\">";
             echo $entry;
             echo "</a></td>\n";
             echo "<td>".$file_size."</td>\n";
           }
         }
     }
     $d->close();
   }

    ?>
     </tr>
     </table>
      <center>
     <?
     table_footer(0);
     page_footer("");
    }elseif($act == "upload" && !empty($flname)){
      $fp = fopen ($DIR_NAME.$flname, "r"); 
      if(!$fp){  
        echo "Error";
        exit;
      }

      $leftchar = "";
      for($i=0;$i<strlen($flname)-1;$i++){
        $nextchar = substr($flname,$i,1);
        if(is_numeric($nextchar)){
          $leftchar = $leftchar.$nextchar;
        }else{
          break;
        }
      }

      $SITEID  = $site_id;
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
                        VALUES('".$text."','".$str_arr[1]."','TEXT',0,0, ".$SITEID.")";
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
?>

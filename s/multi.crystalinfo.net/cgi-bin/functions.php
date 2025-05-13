<?php
 require_once (dirname($DOCUMENT_ROOT)."/cgi-bin/common.php");
 require_once (dirname($DOCUMENT_ROOT)."/cgi-bin/page_functions.php");
 require_once (dirname($DOCUMENT_ROOT)."/cgi-bin/site.cnf");
 require_once (dirname($DOCUMENT_ROOT)."/cgi-bin/db.php");
 require_once (dirname($DOCUMENT_ROOT)."/cgi-bin/constants.php");
 require_once (dirname($DOCUMENT_ROOT)."/cgi-bin/pagecache.php");
 
 global $my_s; //// Tüm auth require eden  sayfalar bu session
     //// objesine ihtiyaç duyacaklar.
$cdb = new db_layer();     
 ////////////////////////////////////////////////////////////////
 //  1- REQUIRE_VALID_LOGIN: Requires that the user logs in to the system
 ////////////////////////////////////////////////////////////////
 function require_valid_login(){
     global $my_s;
     global $DOCROOT;
     global $SCRIPT_NAME;
     global $HTTP_HOST;

     $my_s = new Session;

     $my_s->test_login();

     if (!$my_s->logged_in){   
       header("Location:/index.php?REDIR=".$HTTP_HOST.$SCRIPT_NAME);
         exit;
     }
     set_active_time();
     //// Eðer database iþlemleri yapýlacaksa connect yapabiliriz
     $my_s->pconnect();
   log_last_touch();
   page_track();
 }

  ////////////////////////////////////////////////////////////////
 //  2- log_last_touch: logs the users last touch
 ////////////////////////////////////////////////////////////////
 function log_last_touch(){
 global $SESSION;
 $cdb = new db_layer();

   $sql_str = "UPDATE USERS SET LAST_TOUCH = ".time()." WHERE USERNAME = '".$SESSION["username"]."'";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
       print_error($error_msg);
       exit;
    }
 }
 
 ////////////////////////////////////////////////////////////////
 //  3- LOGIN: Logs in and creates the session
 ////////////////////////////////////////////////////////////////
 function login(){
        global $my_s;
        global $user_name;
        global $user_pass;
        global $customer_code;
        global $REMOTE_ADDR;
        global $user_mobil;
        //if(checkDemover(1, '2005-07-01')){
        //  print_error("Demo Süreniz dolmuþtur.");
        //}
        $my_s = new Session;
        $my_s->login_request($user_name,$user_pass,$customer_code,$REMOTE_ADDR,$user_mobil);
        
        if (!$my_s->logged_in){
            header("Location:/index.php?act=retry&REDIR=".$HTTP_HOST.$SCRIPT_NAME);
            print_error($my_s->err_message());
            exit;
        }
 }



 ////////////////////////////////////////////////////////////////
 //  4- LOGOUT: Logs out and destroys the session
 //              CLOSED = 1 if the browser is closed and the logout request is involuntary
 ////////////////////////////////////////////////////////////////
 function logout($closed){
   global $my_s;
   $my_s = new Session;

   $my_s->test_login();
 
   if ($my_s->logged_in){

     //// Eðer database iþlemleri yapýlacaksa connect yapabiliriz
     $my_s->destroy();
    
     if ($closed) {
        put_header("Logout","/body1.css");
        echo "<p align=\"center\" class=\"important\">Sistemi kullandýðýnýz için teþekkürler.Logout oldunuz.</p>";
        echo "<p align=\"center\"><a href=\"javascript:close();\">KAPAT</a></p><br>";
     }
     else {   
       header("Location:index.php");
//     header("Location:loginfrm.php");
     }  
   }
   else{  // Not logged in yet
     if ($closed) {
        put_header("Logout","/body1.css");
        echo "<p align=\"center\" class=\"important\">Sistemi kullandýðýnýz için teþekkürler.Logout oldunuz.</p>";
        echo "<p align=\"center\"><a href=\"javascript:close();\">KAPAT</a></p><br>";
     }
     else    
       header("Location:index.php");
//       header("Location:loginfrm.php");
   }
     
 
 }

 ////////////////////////////////////////////////////////////////////////////////
 // 8-  VALID_MAIL: Returms TRUE if the parameter is a valid mail
 ////////////////////////////////////////////////////////////////////////////////
 function valid_mail($prm_mail_addr){
   if (ereg("^.+@.+\\..+$",$prm_mail_addr))
     return TRUE;
   else
     return FALSE;   
 }

 function print_error($err_mess="...",$err_type=0){
    if (TRUE) {
     cc_page_meta(0);
     error_page($err_mess,$err_type);
    } //---end if ----
  }//---end  print_error ----
  
// DATE FUNCTIONS
// MySQL'in anlayacaðý formatta YYYY-MM-DD tarihi bulur 
  function my_date(){ 
    return date("Y-m-d");
  }
 
  // MySQL'in anlayacaðý formatta saati bulur 
  function my_time(){
    return date("H:i:s",time());
  }

 ////////////////////////////////////////////////////////////////////////////////
 // 20--  GET_USER_NAME: Given a user id, the full name + surname is returned 
 ////////////////////////////////////////////////////////////////////////////////
 function get_user_name($user_id){ //????
   $sql_str = "SELECT ADI, SOYADI FROM ACCOUNTS WHERE USER_ID = ${user_id};";
 
   if (!(execute_sql($sql_str,$result,$error_msg))){
     return "Sistem Hatasý";
   }
   
   // No problem occured
   if (mysql_num_rows($result) == 0)
     return "Tanýmsýz Kull.";          // No record found for this user_id
   else if (mysql_num_rows($result) > 1)
     return "Birçok taným";            // Records found for this user_id is multiple
   else{
     $row = mysql_fetch_array($result);
     return $row["ADI"]." ".$row["SOYADI"]; 
   }
 }
 ////////////////////////////////////////////////////////////////////////////////
 //  21-- GET_LOGIN_NAME: Given a user id, the login name is returned
 ////////////////////////////////////////////////////////////////////////////////
 function get_login_name($user_id){
   $sql_str = "SELECT USERNAME FROM ACCOUNTS WHERE USER_ID = ${user_id};";
 
   if (!(execute_sql($sql_str,$result,$error_msg))){
     return "Sistem Hatasý";
   }
   
   // No problem occured
   if (mysql_num_rows($result) == 0)
     return "Tanýmsýz Kull.";          // No record found for this user_id
   else if (mysql_num_rows($result) > 1)
     return "Birçok taným";            // Records found for this user_id is multiple
   else{
     $row = mysql_fetch_array($result);
     return $row["USERNAME"]; 
   }
 }

  ###################################
  # MAILER
  # Date: 09.07.2002
  # Coding: TOLGA D.
  ###################################  
 function sendmail($TYPE, $CONTENT) {
   $cdb = new db_layer();
   $cUtility = new Utility();
   if ($TYPE != "") {
       $sql_str = "
                    SELECT TYPE, SUBJECT, CONTENT, HEADER
                      FROM MAIL_TEXT
                      WHERE TYPE = '$TYPE'
                  ";
       if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
      $row = mysql_fetch_object($result);
      if (mysql_num_rows($result)>0) {
         $MESAJ = $row->CONTENT;
         if ($row->HEADER == "HTML") {
           $HEADER = "Content-type: text/html\r\n";
         }
         if ($CONTENT['SUBJECT'] == "") {     # OVERRIDE DEFAULT SUBJECT IF SPECIFIED
           $CONTENT['SUBJECT'] = $row->SUBJECT;
         }
         $CONTENT['MESAJ'] = preg_match_all("/<T-(\w+)>/m", $MESAJ, $matches);
         foreach ($matches[1] as $val) {
           $MESAJ = preg_replace("/<T-$val>/", $CONTENT["$val"], $MESAJ);
         }
      }
   }
   else {
      $MESAJ = stripslashes($CONTENT['MESAJ']);
   }
         mail($CONTENT['TO'], $CONTENT['SUBJECT'], $MESAJ, "From: ".$CONTENT['FROM']."\r\n"."Reply-To: ".$CONTENT['FROM']."\r\n".$CONTENT['HEADER']);

         ### WRITE TO LOG
         global $SESSION;
         if ($SENDER == "") {
            if ($SESSION['username'] != "") {
              $SENDER = $SESSION['username'];
            }
            else {
              $SENDER = "";
            }
         }
         $args[] = array("SENDER_MAIL",          $CONTENT['FROM'],        cFldWQuote);
         $args[] = array("RECIEVER_MAIL",          $CONTENT['TO'],        cFldWQuote);
         $args[] = array("DATE",          date("Y-m-d"),        cFldWQuote);
         $args[] = array("TIME",          date("H:m:s"),        cFldWQuote);
         $args[] = array("SUBJECT",          $CONTENT['SUBJECT'],        cFldWQuote);
         $args[] = array("CONTENT",          addslashes($MESAJ),        cFldWQuote);
         $args[] = array("TYPE",          $TYPE,        cFldWQuote);
         $args[] = array("USERNAME",      $SENDER,        cFldWQuote);
         $sql_str =  $cdb->InsertString("MAIL_ARCHIVE", $args);
         if (!($cdb->execute_sql($sql_str,$result,$error_msg))) {
            print_error($error_msg);
            exit;
         }
      return true;

 }

 ////////////////////////////////////////////////////////////////////////////////
 // 24--  SEND_MAIL: Send a message to the mail address given in the parameters
 //                  The format is taken from CSS
 ////////////////////////////////////////////////////////////////////////////////
 function send_mail($prm_mail_addr,$prm_user_id,$prm_subject,$prm_body,$prm_header=""){ //????

 $valid_mail = TRUE;
  
 // If the mail addres is not specified explicitly, then get it from the accounts database
 if ($prm_mail_addr == ""){
   $query = "SELECT MAIL FROM ACCOUNTS WHERE USER_ID = ".$prm_user_id.";";
 
   if (!($result = mysql_query($query))){
     print_error(sprintf("Internal Error %d: %s",mysql_errno(),mysql_error()));
     exit();
   }
   $row=mysql_fetch_array($result);
   $mail_addr = $row["MAIL"];
 }
 else
   $mail_addr = $prm_mail_addr;
 
 // Validate the mail address and send the mail
 if (valid_mail($mail_addr)){
   if ($prm_header == "")
     $prm_header = "From:OtoAnaliz";
   else
     $prm_header .= "\r\nFrom:OtoAnaliz";
     
   mail($mail_addr,$prm_subject,$prm_body,$prm_header);
 }
 
 }
  ////////////////////////////////////////////////////////////////////////////////
 // 25--  PAGE_TRACK: tracks the user when clicked any page which ask require_valid_login
 //                 
 ////////////////////////////////////////////////////////////////////////////////
 function page_track() {
            global $SESSION;
            global $REMOTE_ADDR;
            global $HTTP_HOST;
            
            if ($SESSION["mobil"]=="E"){
                    define('IMAGE_ROOT','C:/Mondial/images/');
                    define('IMAGES','C:/Mondial/images/');
            }else{
                    define('IMAGE_ROOT','/images/');
                    define('IMAGES','/images/');
            }
            
            $adi        = $SESSION["adi"];
            $soyadi     = $SESSION["soyadi"];
            $user_name  = $SESSION["username"];
            $date       = my_date();     
            $time       = my_time();
            $page       = GETENV(SCRIPT_NAME) ;    
            $SqlStr     = ("INSERT INTO PAGE_TRACK (SES_ID, USER_NAME, DATE, TIME, DOMAIN, PAGE, IP ) VALUES (\"".$SESSION["last_id"]."\",\"".$user_name."\",\"".$date."\",\"".$time."\",'".$HTTP_HOST."',\"".$page."\",\"".$REMOTE_ADDR."\")");
            @mysql_query($SqlStr);
 }
 
 ////////////////////////////////////////////////////////////////
 //  27-- CHANGE_TURKISH_CHARS($STR):changes Turkish chars in $str string
 // and return normal string
 ////////////////////////////////////////////////////////////////
   function change_turkish_chars($str){
     $str = str_replace("Þ","S",$str);
     $str = str_replace("Ç","C",$str);
     $str = str_replace("Ö","O",$str);      
     $str = str_replace("Ý","I",$str); 
     $str = str_replace("Ð","G",$str);
     $str = str_replace("Ü","U",$str);      

     $str = str_replace("þ","s",$str);
     $str = str_replace("ç","c",$str);
     $str = str_replace("ö","o",$str);      
     $str = str_replace("ý","i",$str); 
     $str = str_replace("ð","g",$str);
     $str = str_replace("ü","u",$str);      
   return $str;
   }


function turkish2utf($data)
{
    $chars_utf = Array("Ã‡","Ã§", "Ã–", "Ã¶", "Ã¼", "Ãœ", "Ä°", "Ä±", "ÄŸ", "Äž", "ÅŸ", "Åž");
    $chars_trk = Array("Ç","ç", "Ö", "ö", "ü", "Ü", "Ý", "ý", "ð", "Ð", "þ", "Þ");

    if (is_array($data))//parameter must be an array
    {
        for($k=0;$k<sizeof($data);$k++)// loop through end of parameter 
        {
            for($i=0;$i<12;$i++) // loop 12 times becauser there are 12 turkish chars to change
            {
                 $data[$k] = str_replace ($chars_trk[$i], $chars_utf[$i], $data[$k]);
            }
        }  
    }
    if (!is_array($data))//parameter must be an array
    {
        for($i=0;$i<12;$i++) // loop 12 times becauser there are 12 turkish chars to change
        {
            $data = str_replace ($chars_trk[$i], $chars_utf[$i], $data);
        }
    }
    return($data);
}

function fillSecondCombo()
{
global $DOCROOT;
?>
<script language="javascript">

function FillSecondCombo(Alan1, Alan2, sql , Secilen , HedefCmbBox , WhereValue) {
  if (sql=='')
    return false;
  if (HedefCmbBox=='')
    return false;
  //if (WhereValue=='' || WhereValue=="-1" || WhereValue=="undefined")
   // return false;
   //to manage the xml document
   var objDOM = new ActiveXObject("Microsoft.XMLDOM");
   //to establish a http connection between the server
   //and the client, thereby sending and receiving
   //message to and from the server
   //without submitting the page
   var xmlh = new ActiveXObject("Microsoft.XMLHTTP");
   
   //to identify whether the server is contacted
   //by clicking the send button OR
   //the routine timer event
   //alert(sql);
   xmlh.open("POST","<?=$DOCROOT?>scripts/cmblist.php?sql_query=" + sql, false);

  //  xmlh.SetRequestHeader("Man", "POST http://www.vodasoft.com.tr HTTP/1.1")
   xmlh.SetRequestHeader("Content-Type", "text/xml")
   xmlh.send();
   
     //alert(xmlh.ResponseText);

   
   objDOM.loadXML(xmlh.ResponseText);
     //alert(xmlh.getAllResponseHeaders());

   //DataIsland.documentElement = objDOM.documentElement;
     //to display the incoming user list
   
      var nodes,strHtml;
      var selList = new Array();
      var newList = new Array();
      var newList_id = new Array();

      nodes = objDOM.getElementsByTagName(Alan2);
      nodes_id = objDOM.getElementsByTagName(Alan1);

      //getting the current user list
    for(i=0;i<nodes.length;i++){
          newList[i] = nodes.item(i).text;
      }
  
    for(i=0;i<nodes_id.length;i++){
          newList_id[i] = nodes_id.item(i).text;
      }
     
    var k, ex_list;
    
    //Before create new option I have t clear ex ones
    // but when I try to remove ex one I coludn't overcome to a problem
    // so I do some workaround to clear all options before create new ones
    f_list = document.all(HedefCmbBox).options.length;
    
    for (var rtnCnt=f_list - 1 ;  rtnCnt >=1 ; rtnCnt--) { 
        document.all(HedefCmbBox).options.remove(rtnCnt);
  }
    
     // Fill combo with names
    for (var i=0; i < newList_id.length; i++) {
        if (newList_id[i] == Secilen)
           document.all(HedefCmbBox).options[i + 1 ] = new Option(newList[i],newList_id[i],true,true);
        else 
           document.all(HedefCmbBox).options[i + 1] = new Option(newList[i],newList_id[i]);  
    }

    // and then select the item which must be selected
    for (var i=0; i < document.all(HedefCmbBox).options.length; i++) {
        if (document.all(HedefCmbBox).options[i].value == Secilen)
           document.all(HedefCmbBox).options[i].selected = true;
        else 
           document.all(HedefCmbBox).options[i].selected = false ;  
    }
}

</script>

<?
}

/*///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
function :db2normal() changes db type date to local type date
     prm1: $strDate ; the date to be converted
     prm2: $sep ;separator to separate
owner : SEYKAY
DATE : 20.04.02

///////////////////////////////////////////////////////////////*/
   
function db2normal($strDate,$sep = "-")
{ 
  $tmpDate = strtotime(trim($strDate));
   switch (strlen($strDate))
   {
      case 8: // YY-MM-DD
      case 10: // YYYY-MM-DD
         $day   = DATE("d",$tmpDate);
         $month = DATE("m",$tmpDate);
         $year  = DATE("Y",$tmpDate);
         return ($day . $sep . $month . $sep . $year);
         break;
      case 17: // YY-MM-DD HH:MM:SS
      case 19: // YYYY-MM-DD HH:MM:SS
         $day   = DATE("d",$tmpDate);
         $month = DATE("m",$tmpDate);
         $year  = DATE("Y",$tmpDate);
         
         $sec   = DATE("s",$tmpDate);
         $min  = DATE("i",$tmpDate);
         $hour  = DATE("H",$tmpDate);                  
         return ($day . $sep . $month . $sep . $year . " ". $hour . ":". $min . ":". $sec);
         break;
     default:
         return(date("d-m-Y"));    
   }
}  
/*///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////
function :normal2db() changes local type date to db type date
     prm1: $strDate ; the date to be converted
     prm2: $sep ;separator to separate
owner : SEYKAY
DATE : 20.04.02

///////////////////////////////////////////////////////////////*/

function normal2db($strDate,$sep = "-")
{ 
   $tmpDate = trim($strDate);
   switch (strlen($strDate))
   {
      case 10: // DD-MM-YYYY
         $day   = substr($tmpDate,0,2);
         $month = substr($tmpDate,3,2);
         $year  = substr($tmpDate,6,4);
         return ($year . $sep . $month . $sep . $day);
         break;
      case 19: // DD-MM-YYYY HH:MM:SS
         $day   = substr($tmpDate,0,2);
         $month = substr($tmpDate,3,2);
         $year  = substr($tmpDate,6,4);
         
         $sec   = substr($tmpDate,11,8);
         return ($year . $sep . $month . $sep . $day ." ". $sec);
         break;
     default:
         return(date("Y-m-d"));    
   }
}  

###################################
# LABEL SAMPLER FOR CHART DISPLAY
# Date: 27.04.2002
###################################
function label_sampler($label,$len) {
      return $sampled_label;
}


###################################
# DOVIZ
# Date: 27.05.2002
# CODE: TOLGAD
###################################
function get_doviz ($t0_day, $t0_mon, $t0_year, $t1_day, $t1_mon, $t1_year, $period, $doviz, &$KUR) {
$cdb = new db_layer();

if ($doviz==0) { $doviz = "USD"; }
if ($doviz==1) { $doviz = "EURO"; }
if ($doviz==2) { $doviz = "STERLIN"; }
if ($doviz==3) { $doviz = "YEN"; }

switch ($period) {
case 1:  // AYLIK
  $SQL =  "SELECT * FROM T_DOVIZ_KURLARI".
  " WHERE IKUR_TIPI = 1 AND TDOVIZ_TIPI = '$doviz' AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',IAY,'-',1)) <= UNIX_TIMESTAMP('".$t1_year."-".$t1_mon."-1') AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',IAY,'-',1)) >= UNIX_TIMESTAMP('".$t0_year."-".$t0_mon."-1')".
  " ORDER BY IYIL, IAY";
  break;

case 2:  // 3 AYLIK
  if ($t1_mon < 4)               { $quarter_1 = 1; }
  if ($t1_mon < 7 && $t1_mon > 3) { $quarter_1 = 2; }
  if ($t1_mon < 10 && $t1_mon > 6){ $quarter_1 = 3; }
  if ($t1_mon > 9)               { $quarter_1 = 4; }
  
  if ($t0_mon < 4)               { $quarter_0 = 1; }
  if ($t0_mon < 7 && $t0_mon > 3) { $quarter_0 = 2; }
  if ($t0_mon < 10 && $t0_mon > 6){ $quarter_0 = 3; }
  if ($t0_mon > 9)               { $quarter_0 = 4; }
  
  $SQL =  "SELECT * FROM T_DOVIZ_KURLARI".
  " WHERE IKUR_TIPI = 2 AND TDOVIZ_TIPI = '$doviz' AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',IAY,'-',1)) <= UNIX_TIMESTAMP('".$t1_year."-".$quarter_1."-1') AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',IAY,'-',1)) >= UNIX_TIMESTAMP('".$t0_year."-".$quarter_0."-1') ".
#  "".((IYIL = $year_1 AND IQUARTER <= $quarter_1) OR (IYIL = $year_0 AND IQUARTER >= $quarter_0) OR (IYIL < $year_1 AND IYIL > $year_0))".
  " ORDER BY IYIL, IQUARTER";
  break;

case 3:  // YILLIK
  $SQL =  "SELECT * FROM T_DOVIZ_KURLARI".
  " WHERE IKUR_TIPI = 3 AND TDOVIZ_TIPI = '$doviz' AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',1,'-',1)) <= UNIX_TIMESTAMP('".$t1_year."-1-1') AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',1,'-',1)) >= UNIX_TIMESTAMP('".$t0_year."-1-1') ".
  " ORDER BY IYIL";
  break;

case 4:
default:  // GUNLUK
  $SQL =  "SELECT * FROM T_DOVIZ_KURLARI".
  " WHERE IKUR_TIPI = 4 AND TDOVIZ_TIPI = '$doviz' AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',IAY,'-',IGUN)) <= UNIX_TIMESTAMP('".$t1_year."-".$t1_mon."-".$t1_day."') AND ".
  " UNIX_TIMESTAMP(CONCAT(IYIL,'-',IAY,'-',IGUN)) >= UNIX_TIMESTAMP('".$t0_year."-".$t0_mon."-".$t0_day."')".
  " ORDER BY IYIL, IAY, IGUN";

}
$cdb->execute_sql($SQL,$result,$mess);
while($row = mysql_fetch_object($result)) {
  $KUR[] = $row->RKUR;
}
return;

}


###################################
# CSV OUT 
# Date: 17.05.2002
# Coding: TOLGAD
###################################
function csv_out($data, $filename) {
  if(is_array($data))
  {
  foreach ($data as $k1 => $v1) {
    foreach($data[$k1] as $k2 => $v2) {
      $file .= $v2.";";
    }
    $file = substr($file, 0, -1);
    $file .= "\n";
  }
  //Header ( "Content-Type: application/octet-stream");
  //Header ( "Content-Length: ".filesize(2000)); 
  //Header( "Content-Disposition: attachment; filename=$filename"); 
  //echo $file;
  $fp = fopen($filename, "w");
  fwrite($fp, $file);
  return true;
  }
}



###################################
# csv_write_line
# Date: 17.05.2002
# Coding: SEYKAY
###################################
function csv_write_line($data, $filename, $fp) {
  if(is_array($data))
  {
  foreach ($data as $k1 => $v1) {
//    foreach($data[$k1] as $k2 => $v2) {
      $file .= $v1.";";
    }
    $file = substr($file, 0, -1);
    $file .= "\n";
//  }
  //Header ( "Content-Type: application/octet-stream");
  //Header ( "Content-Length: ".filesize(2000)); 
  //Header( "Content-Disposition: attachment; filename=$filename"); 
  //echo $file;
//  $fp = fopen($filename, "w");
  fwrite($fp, $file);
  return true;
  }
}
  ###################################
  # SET ACTIVE LOGIN TIME
  # Date: 18.05.2002
  # Coding: TOLGAD
  ###################################
  function set_active_time(){
    global $SESSION;
    $cdb = new db_layer();

    $sql_str = "SELECT LAST_TOUCH FROM USERS WHERE USERNAME = '".$SESSION['username']."'";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
       print_error($error_msg);
       exit;
    }
    $row = mysql_fetch_array($result);
    $sure = time() - $row[0];
    if ($sure > SESSION_TIME) { $sure = 0; } # THE SESSION CAN'T BE OLDER THAN CONSTANT SESSION_TIME
    
   $sql_str = "UPDATE LOGIN_LOGS SET ACTIVE_TIME = ACTIVE_TIME + $sure WHERE ID = '".$SESSION['last_id']."'";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
       print_error($error_msg);
       exit;
    }

  }

   function yuzdebul($a, $b){
     if ($a!=0){
     $c = (($b*100)/($a));
       $sonuc = $c;
   }else{
     $sonuc = 0;
   }
   return number_format($sonuc,2,',','');
   }


  ###################################
  # CHECK RIGHT
  # Date: 24.06.2002
  # Coding: TOLGAD
  ###################################  
  function check_right($right_name) {
	 if ($right_name == "ADMIN"){
	   if (!right_get("ADMIN") && !right_get("SITE_ADMIN")){
         print_error("Bu sayfaya eriþim hakkýnýz yok!");
          exit;
	   }
	 }elseif (!right_get($right_name)) {
          print_error("Bu sayfaya eriþim hakkýnýz yok!");
          exit;
     }
  }

  ###################################
  # WINDOW CLOSE
  # Date: 09.07.2002
  ###################################  
  function window_close(){
    echo "
      <script language=\"javascript\">
        window.close();
      </script>
    ";
  }
  function m_rep($g){
       $g  = str_replace(",", "", $g);
       $g  = str_replace(".", "", $g);
       return $g;
  }
 
  ###################################
  # MOD
  # Date: 07.01.2003
  ###################################
  function modla($a, $b) {
   return ($a % $b) ;
  }

  function bgc($i){
     if (($i % 2) == 0 )
       return "bgc1";
     else
       return "bgc2";
  }

  function bgc2($i){
     if (($i % 2) == 0 )
       return "bg_acik_f_acik";
     else
       return "bg_koyu_f_acik";    
  }
  ###################################
  # FORMAT_DATE 
  # MySql database'indeki tarih saat formatýný normal tarih saate çevirir. Bu sayede 
  # klasik tarih saat formatý ile karþýlaþtýrma yapýlabilir 
  # Date: 10.01.2003
  ###################################
  function format_date($my_date){
    return "DATE_FORMAT($my_date,'%d.%m.%Y %H:%i:%s')";  
  }
  


/***********************GET_DEPT_ID ********************************
 * Returns the the dept id og a given user      *
 ********************************************************************/
function get_dept_id($user_id){
    $query = "SELECT DEPT_ID FROM USERS WHERE USER_ID = $user_id";
  $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    return $row["DEPT_ID"];
}  

/***********************GET_DEPT_MEMBER ********************************
 * Returns true if a given user is in given dept      *
 ********************************************************************/
function get_dept_member($dept_id,$ext_no){
    $query = "SELECT USERS.DEPT_ID FROM USERS 
          LEFT JOIN EXTENTIONS 
          ON USERS.EXT_ID1=EXTENTIONS.EXT_ID 
          OR USERS.EXT_ID2=EXTENTIONS.EXT_ID 
          OR USERS.EXT_ID3=EXTENTIONS.EXT_ID
        WHERE EXTENTIONS.EXT_NO=$ext_no AND USERS.DEPT_ID=$dept_id
        ";
  $result = mysql_query($query);
    if (mysql_num_rows($result)>0)
    return true;
  else 
    return false;
}

function got_dept_right($usr_id){
     global $cdb;
    $sql_str1="SELECT * FROM DEPT_REP_RIGHTS WHERE USER_ID = '$usr_id'"; 
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if(mysql_num_rows($result1) > 0)
        $exist = 1;
    return $exist;
}

function got_this_dept_right($usr_id,$dept_id){
    global $cdb;
    $exist = 0 ;
    $sql_str="SELECT * FROM DEPT_REP_RIGHTS WHERE USER_ID = '$usr_id' AND DEPT_ID ='$dept_id'";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if(mysql_num_rows($result) > 0)
        $exist = 1;
    return $exist;
}

function get_depts_crt($usr_id,$site_id){
    global $cdb;    
  $sql_str="SELECT DEPT_ID FROM DEPT_REP_RIGHTS WHERE USER_ID = '$usr_id' AND SITE_ID = ".$site_id;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if(mysql_num_rows($result)>0){
        $depts_crt = " AND EXTENTIONS.DEPT_ID IN('";
        while ($row = mysql_fetch_object($result)){
            $depts_crt = $depts_crt."$row->DEPT_ID','";
        }
        $depts_crt = rtrim($depts_crt,",'");
        $depts_crt = $depts_crt."')";
    }
    return $depts_crt;
}

function get_unrep_exts_crt($site_id){
    global $cdb;    
  $sql_str="SELECT UNREP_EXT_NO FROM UNREP_EXTS WHERE SITE_ID = ".$site_id;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
    }
    if(mysql_num_rows($result)>0){
        $unrep_exts_crt = " AND CDR_MAIN_DATA.ORIG_DN NOT IN('";
        while ($row = mysql_fetch_object($result)){
            $unrep_exts_crt = $unrep_exts_crt."$row->UNREP_EXT_NO','";
        }
        $unrep_exts_crt = rtrim($unrep_exts_crt,",'");
        $unrep_exts_crt = $unrep_exts_crt."')";
    }
    return $unrep_exts_crt;
}

function get_users_crt($usr_id,$call_type=1,$site_id=1){
  global $cdb;
  if ($call_type == 2)
        $exts_crt = " AND CDR_MAIN_INB.TER_DN IN('";
    else
        $exts_crt = " AND CDR_MAIN_DATA.ORIG_DN IN('";

  $sql_str="SELECT DEPT_ID FROM DEPT_REP_RIGHTS WHERE USER_ID = '$usr_id' AND SITE_ID = '$site_id'";
  if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
    }
  $orig_crt="";
  while($row = mysql_fetch_object($result)){
    if($row->DEPT_ID=="0")
      return "";
        $sql_str1="SELECT EXT_NO FROM EXTENTIONS WHERE DEPT_ID = '$row->DEPT_ID'";
        if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
            print_error($error_msg);
            exit;
        }
        if(mysql_num_rows($result1)>0){
            while ($row1 = mysql_fetch_object($result1)){
                $orig_crt = $orig_crt."$row1->EXT_NO','";
            }
        }
    }
    //Raporunu alabileceði departmanda dahili tanýmlanmamýþsa hata vermemeli.
  if($orig_crt<>""){
    $orig_crt = rtrim($orig_crt,",'");
      $exts_crt = $exts_crt.$orig_crt."')";
    }else{
    $exts_crt = $exts_crt."')";
    }
  return $exts_crt;
}

function get_user_sites($user_id, $not_all_report){
        global $cdb;global $SESSION;
        $retVal = Array();
        $sql_str1="SELECT  SITES.SITE_ID, MIN(DEPT_ID) as DEPT_ID FROM SITES
                        left join DEPT_REP_RIGHTS on DEPT_REP_RIGHTS.SITE_ID=SITES.SITE_ID
                        where USER_ID= ".$user_id." group by SITES.SITE_ID";
        if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
                print_error($error_msg);
                exit;
        }
        if(mysql_num_rows($result1) > 0){
          while($row = mysql_fetch_object($result1)){
            $retVal[] = Array($row->SITE_ID, $row->DEPT_ID);
          }
        }else{
          $retVal[] = Array($SESSION['site_id'], $not_all_report);
        }
        return $retVal;
     }
     

function get_user_id($ext_no,$site_id){
  $query = "SELECT USERS.USER_ID FROM USERS 
          LEFT JOIN EXTENTIONS 
            ON USERS.EXT_ID1=EXTENTIONS.EXT_ID 
            OR USERS.EXT_ID2=EXTENTIONS.EXT_ID 
            OR USERS.EXT_ID3=EXTENTIONS.EXT_ID
        WHERE EXTENTIONS.EXT_NO='$ext_no' AND USERS.SITE_ID = '$site_id'
        ";
  $result = mysql_query($query);
  $row = mysql_fetch_object($result);
  return $row->USER_ID;
}
function get_ext($user_id){
  $query = "SELECT EXTENTIONS.EXT_NO FROM USERS 
          LEFT JOIN EXTENTIONS 
            ON USERS.EXT_ID1=EXTENTIONS.EXT_ID 
            OR USERS.EXT_ID2=EXTENTIONS.EXT_ID 
            OR USERS.EXT_ID3=EXTENTIONS.EXT_ID
        WHERE USERS.USER_ID = '$user_id'
        ";
  $result = mysql_query($query);
   $res = " AND CDR_MAIN_DATA.ORIG_DN IN(";
   $origArr = "";
  while($row = mysql_fetch_object($result)){
     if($origArr == ""){
      $origArr .= "'".$row->EXT_NO."'";
     }else{
      $origArr .= ", '".$row->EXT_NO."'";
     } 
  }           
   $res .= $origArr.")";
  return ($res);
}


function get_auth_crt($user_id){
  $query = "SELECT AUTH_CODES.AUTH_CODE FROM USERS 
          LEFT JOIN AUTH_CODES 
            ON USERS.AUTH_CODE_ID=AUTH_CODES.AUTH_CODE_ID 
        WHERE USERS.USER_ID = '$user_id'
        ";
  $result = mysql_query($query);
   $res = " AND CDR_MAIN_DATA.AUTH_ID IN(";
   $origArr = "";
  while($row = mysql_fetch_object($result)){
     if($origArr == ""){
      $origArr .= "'".$row->AUTH_CODE."'";
     }else{
      $origArr .= ", '".$row->AUTH_CODE."'";
     } 
  }           
  $res .= $origArr.")";
  if($origArr == "")
    return $origArr;
  else  
    return ($res);
}


function get_user_id_for_auth($auth_code){
    $query = "SELECT USERS.USER_ID FROM USERS 
          LEFT JOIN AUTH_CODES 
          ON USERS.AUTH_CODE_ID=AUTH_CODES.AUTH_CODE_ID
        WHERE AUTH_CODES.AUTH_CODE='$auth_code'
        ";
  $result = mysql_query($query);
  $row = mysql_fetch_object($result);
  return $row->USER_ID;
}

function get_ext_name($ext_id,$SITE_ID = 1){
      global $cdb;
      if (!($cdb->execute_sql("SELECT EXT_NO,  SUBSTRING(DESCRIPTION,1,50) AS DESCR FROM EXTENTIONS WHERE EXT_NO = '$ext_id' AND SITE_ID = '$SITE_ID'",$result_2,$error_msg))){
            print_error($error_msg);
            exit;
      }
      $row_2 = mysql_fetch_object($result_2);
      return ($row_2->DESCR);
 }   

function get_ext_name2($ext_no,$SITE_ID){
      global $cdb;
    if (!($cdb->execute_sql("SELECT EXT_NO,  SUBSTRING(DESCRIPTION,1,50) AS DESCR FROM EXTENTIONS WHERE EXT_NO = '$ext_no' AND SITE_ID = '$SITE_ID'",$result_2,$error_msg))){
            print_error($error_msg);
            exit;
      }
      $row_2 = mysql_fetch_object($result_2);
      return ($row_2->DESCR);
 } 
 
function get_site_name($site_id){  
      global $cdb;
      if (!($cdb->execute_sql("SELECT SITE_NAME FROM SITES WHERE SITE_ID = '$site_id'",$result,$error_msg))){
            print_error($error_msg);
            exit;
      }
      $row = mysql_fetch_object($result);
      return ($row->SITE_NAME);
 }   

function get_provider_name($prov_id){  
      global $cdb;
      if (!($cdb->execute_sql("SELECT TEL_PROVIDER FROM TEL_PROVIDERS WHERE TEL_PROVIDER_ID = '$prov_id'",$result,$error_msg))){
            print_error($error_msg);
            exit;
      }
      $row = mysql_fetch_object($result);
      return ($row->TEL_PROVIDER);
 }   

function get_dept_name($dept_id,$SITE_ID="1"){  
      global $cdb;
      if (!($cdb->execute_sql("SELECT DEPT_ID, DEPT_NAME FROM DEPTS WHERE SITE_ID='$SITE_ID' AND DEPT_ID = '$dept_id'",$result_2,$error_msg))){
            print_error($error_msg);
            exit;
      }
      $row_2 = mysql_fetch_object($result_2);
      return ($row_2->DEPT_NAME);
 }   

function get_code_type_name($mtr_code_type_id){  
  global $cdb;
    if (!($cdb->execute_sql("SELECT CODE_TYPE_NAME FROM MTR_CODE_TYPE WHERE MTR_CODE_TYPE_ID = '$mtr_code_type_id'",$result_2,$error_msg))){
        print_error($error_msg);
       exit;
  }
    $row_2 = mysql_fetch_object($result_2);
   return ($row_2->CODE_TYPE_NAME);
 }   

function get_heavy_sql_result($sql_id, $result_arr = Array()){
  $cdb = new db_layer();
    global $SESSION;
  $SITE_ID = $SESSION['site_id'];
    $sql_str = "SELECT ID, NAME, VALUE FROM HEAVY WHERE SQL_ID = '$sql_id'" ;
  if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
     print_error($error_msg);
        exit;
  }
  $str = "";
   $row = mysql_fetch_object($result);
  $rec_array = explode("##", $row->VALUE);
   for($m=0;$m<sizeof($rec_array)-1;$m++){
      $field_array = explode(";;", $rec_array[$m]);
    $localSiteId = explode("||",$field_array[0]);
    if($localSiteId[1]==$SITE_ID){
        for($k=0;$k<sizeof($field_array);$k++){
      $one_rec = explode("||", $field_array[$k]);
      $result_arr[$m][$k][0] = $one_rec[0];
          $result_arr[$m][$k][1] = $one_rec[1];
      }
      }
    }
  return $result_arr;
}

function run_heavy_sqls(){
  $cdb = new db_layer();
   $sql_str = "SELECT ID, SQL FROM HEAVY_SQLS" ;
   if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
        exit;
  }
    while($row1 = mysql_fetch_object($result1)){
     $sql =  $row1->SQL ;
      if (!($cdb->execute_sql($sql,$result,$error_msg))){
           print_error($error_msg);
           exit;
       }
       $str = "";
      while($row = mysql_fetch_row($result)){
          for($k=0;$k<mysql_num_fields($result);$k++){
              $field = mysql_fetch_field($result,$k);
                $str .= $field->name."||";
                $str .= $row[$k].";;";
            }
            $str .= "##";
      }
       $sql_str = "UPDATE HEAVY SET VALUE = '$str' WHERE SQL_ID = '$row1->ID'" ;
          if (!($cdb->execute_sql($sql_str,$result3,$error_msg))){
               print_error($error_msg);
                exit;
          }
      }
}
    
//##########################################################
//  This function is designed for finding the n.
//  day of last week
//  29.01.2003 Fatih Arslan
//##########################################################    
function day_of_last_week($val){
  if (($val > 0  || $val < 8)  && is_numeric($val)){
    $today_var = getdate();
    $last_week_sec = mktime(0,0,0,date("m"),date("d")-7,date("Y"));
    $week_day = $today_var[wday];
    if ($week_day == 0)//Php'de hafta Pazar ile baþlar ve deðeri 0'dýr. Pazartesi'den
      $week_day = 7;// baþlatmak için bu ayar yapýlmalý.
    $offset = ($week_day-1) * 86400 ;//Haftanýn ilk gününü bulmak için (Pazartesi) bu düzenleme gerekti.86400 bir günün sn'yesi.
    $first_day = $last_week_sec - $offset;
    $nth_day_UNC =$first_day + ($val-1)*86400;
    $nth_day = date("Y-m-d", $nth_day_UNC);
    return $nth_day;  
  }else
    echo "Yanlýþ Paramatre Giriþi";
}

//##########################################################
//  This function is designed for finding the n.
//  day of this week
//  29.01.2003 Fatih Arslan
//##########################################################    
function day_of_this_week($val){
  if (($val > 0  || $val < 8)  && is_numeric($val)){
    $today_var = getdate();
    $today_sec = mktime(0,0,0,date("m"),date("d"),date("Y"));
    $week_day = $today_var[wday];
    if ($week_day == 0)//Php'de hafta Pazar ile baþlar ve deðeri 0'dýr. Pazartesi'den
      $week_day = 7;// baþlatmak için bu ayar yapýlmalý.
    $offset = ($week_day-1) * 86400 ;//Haftanýn ilk gününü bulmak için (Pazartesi) bu düzenleme gerekti.86400 bir günün sn'yesi.
    $first_day = $today_sec - $offset;
    $nth_day_UNC =$first_day + ($val-1)*86400;
    $nth_day = date("Y-m-d", $nth_day_UNC);
    return $nth_day;  
  }else
    echo "Yanlýþ Paramatre Giriþi";
}

//##########################################################
//  This function is designed to find the 1. day and
//  the last day of last month
//  29.01.2003 Fatih Arslan
//##########################################################    
function day_of_last_month($val){
  if ($val <>"first" && $val <> "last"){
    echo "Yanlýþ Paramatre Giriþi";
  }else{
     $month=date("m")-1;
    if ($month=='0')
      $month='12';
    $lastday="01";
      $year =date("y");
    /* Figure out how many days are in this month */ 
    while (checkdate($month, $lastday, $year)): 
        $lastday++; 
       endwhile;
      --$lastday;  
  if ($val=='first')
    return strftime("%Y-%m-%d", mktime(0,0,0,date("m")-1,1,date("y")));
  else if ($val=='last')
    return  strftime("%Y-%m-%d", mktime(0,0,0,date("m")-1,$lastday,date("y")));
  }
}

function percent($val1,$val2){
  if ($val2 > '0')
    $perc = number_format((($val1/$val2)*100),2,"","");
  else
    $perc = 0;
  return $perc;
}

//##########################################################
//  This function is designed to convert the date format given
//  (dd/mm/yyyy hh:mm:ss) to mysql date format (yyyy-mm-dd hh:mm:ss)
//  06.02.2003 Fatih Arslan
//##########################################################
function convert_date_time($my_date,$prm ='start'){
  $my_date = str_replace( "/", " ", $my_date );
  list($day, $month, $year,$therest) = explode(" ", $my_date, 4);
  list($hour, $minute, $second) = explode(":", $therest, 3);
  $my_last_date="";
  $my_last_date =  "$year-$month-$day";

  if ($therest<>'')
    $my_last_date = $my_last_date." $hour:$minute:$second";
  else 
        if ($prm=='end')
            $my_last_date = $my_last_date." 23:59:59";
        else
            $my_last_date = $my_last_date." 00:00:00";
//  echo "?$my_last_date?";          

  return $my_last_date;
}

function get_company_name(){
      global $cdb;
    $sql_str1="SELECT VALUE FROM SYSTEM_PRM WHERE NAME= 'COMPANY_NAME'"; 
      if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
                exit;
            }
    $row1=mysql_fetch_object($result1);
      return($row1->VALUE);
}

function get_orig_dept_id($orig_dn,$SITE_ID=1){
    $query = "SELECT DEPT_ID FROM EXTENTIONS WHERE SITE_ID='$SITE_ID' AND EXT_NO = '$orig_dn'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    return $row["DEPT_ID"];
}  

function get_country_code($SITE_ID=1){
    $query = "SELECT LOCAL_COUNTRY_CODE FROM SITES WHERE SITE_ID='$SITE_ID'";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);
    return $row["LOCAL_COUNTRY_CODE"];
}  


  function get_dept_list($user_id){
       global $cdb;
       $sql_str="SELECT DEPT_ID  FROM DEPT_REP_RIGHTS WHERE USER_ID='$user_id'"; 
       if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
          print_error($error_msg);
          exit;
       }
       if(mysql_num_rows($result)<1) return false;

       while($row = mysql_fetch_object($result)){
            $id_string .= $row->DEPT_ID.",";
       }
       $id_string = substr($id_string, 0, strlen($id_string)-1);
       return $id_string;
    }


function get_call_type($LocationTypeid){
      global $cdb;
    $sql_str1="SELECT LocationType FROM TLocationType WHERE LocationTypeid= '$LocationTypeid'"; 
      if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
                exit;
            }
    $row1=mysql_fetch_object($result1);
        return($row1->LocationType);
}

function get_tel_provider($TelProviderid){
      global $cdb;
    $sql_str1="SELECT TelProvider FROM TTelProvider WHERE TelProviderid= '$TelProviderid'"; 
      if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
                exit;
            }
    $row1=mysql_fetch_object($result1);
        return($row1->TelProvider);
}

function get_tel_place($Locationid){
      global $cdb;
    $sql_str1="SELECT LocationName FROM TLocation WHERE Locationid= '$Locationid'"; 
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
             print_error($error_msg);
            exit;
        }
    $row1=mysql_fetch_object($result1);
        return($row1->LocationName);
}

function get_site_prm($MyVal,$site_id){
      global $cdb;
    $sql_str1="SELECT $MyVal FROM SITES WHERE SITE_ID= '$site_id'";
      if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
                exit;
            }
    $row1=mysql_fetch_object($result1);
        return($row1->$MyVal);
}

function get_system_prm($MyVal){
    global $cdb;
    $sql_str1="SELECT VALUE FROM SYSTEM_PRM WHERE NAME= '$MyVal'";
      if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
              print_error($error_msg);
                exit;
            }
    $row1=mysql_fetch_object($result1);
    return($row1->VALUE);
}

function get_ext_mail($ext_No,$site_id){
      global $cdb;
    $sql_str1="SELECT EMAIL FROM EXTENTIONS WHERE SITE_ID= '$site_id' AND EXT_NO ='".$ext_No."'";
    if (!($cdb->execute_sql($sql_str1,$result1,$error_msg))){
             print_error($error_msg);
            exit;
        }
    $row1=mysql_fetch_object($result1);
        if ($row1->EMAIL<>'')
      return($row1->EMAIL);
    else
      return "";
}

function exist_status($sql_str,$act,$id_clmn,$id){
    global $cdb;
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
        exit;
    }
    if (mysql_num_rows($result)>0){
        if ($act =="" || $act =="new" ){ 
            return 1; //Yeni kayýt ama kayýt sistemde mevcut
        }else if($act == "upd" && $id !="" && is_numeric($id)){
            $row = mysql_fetch_object($result);
            $my_id = $row->$id_clmn;
            if ($my_id <> $id)
                return 2; //Update ama yeni girilen kayýt sistemde mevcut.
      }
    }
    return 0;
 }

     function int_format($my_val){
    if ($my_val <= '9')
      return '0'.$my_val;
    else 
      return $my_val;  
  }
  
  function is_hour($my_val){
    if ($my_val<>'-1' && $my_val < 24 && is_numeric($my_val))
      return 1;
    else return 0;  
  }
  
  function is_minute($my_val){
    if ($my_val<>'-1' && $my_val < 60 && is_numeric($my_val))
      return 1;
    else return 0;  
  }

  function format_time($my_time){ //Saat 9'dan küçükse baþýna 0 koysun
       if ($my_time < 10) {
          return "0".$my_time;
       }else{ 
         return $my_time;
       }
  }

  function calculate_time($time,$type){
      if ($type == "hour"){
        $hour = floor($time/3600);
        return $hour;
      }
      if   ($type == "min"){
        $min = round(($time - floor($time/3600)*3600)/60);
        return $min;
      }
            if ($type == "sec"){
                $sec = $time % 60;
                return $sec;
            }
  }
  
  function calculate_all_time($time){
        $hr = floor($time/3600);
        $rest = $time - $hr*3600;
        $min = floor($rest/60);
        $sn = $rest - $min*60;
        $sn = number_format($sn,0,'','');
        $my_time = int_format($hr).":".int_format($min).":".int_format($sn);
        return $my_time;
  }
  
  function write_price($price){
    $price = number_format($price,2, ',', '.');
    return $price;
  }
  
  function add_time_crt(){
    global $cdb;
    global $kriter;
    global $MY_DATE;
    global $t0;
    global $t1;
    global $hh0;
    global $hm0;
    global $hh1;
    global $hm1;
    global $hafta;
    global $last;

  //  echo $MY_DATE;
    
    //Tarih Kontrolü burada baþlýyor.
    switch($MY_DATE){
      case 'b':
         $t0 = date("Y-m-d");
         $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"=",  "'$t0'");
        break;
       case 'c':
        $t0 = date("Y-m-01");
        $t1 = date("Y-m-d");
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
        break;
      case 'f':
         $t0 = strftime("%Y-%m-%d", mktime(0,0,0,date("m"),date("d")-1,date("y")));
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"=",  "'$t0'");
        break;
      case 'g' :
        $t0 = day_of_last_month("first");
        $t1 = day_of_last_month("last");
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
        break;
      case 'h':
         $t0 = day_of_this_week(1);
        $t1 = date("Y-m-d");
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
        break;
      case 'i':
         $t0 = day_of_last_week(1);
        $t1 = day_of_last_week(7);
        $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
          $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
        break;
      case 'e':
        if ($t0){
          $t0 = convert_date_time($t0,'start');
          if(substr($t0,-8)=='00:00:00'){
                      $t0 = substr($t0,0,10);
                      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
                  }else{
                      $kriter .= $cdb->field_query($kriter, "DATE_FORMAT(TIME_STAMP,'%Y-%m-%d %H:%i:%s')"     ,">=",  "'$t0'");
                  }
        }
        if ($t1){
          $t1 = convert_date_time($t1,'end');
          if(substr($t1,-8)=='23:59:59'){
              $t1 = substr($t1,0,10);
                      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
                  }else{
                      $kriter .= $cdb->field_query($kriter, "DATE_FORMAT(TIME_STAMP,'%Y-%m-%d %H:%i:%s')"     ,"<=",  "'$t1'");
              }
        }
             default:
             
    }

     if ($last > 0){
      $t0 = strftime("%Y-%m-%d", mktime(0,0,0,date("m"),date("d")-$last+1,date("y")));
      $t1 = date("Y-m-d");
      $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,">=",  "'$t0'");
         $kriter .= $cdb->field_query($kriter, "MY_DATE"     ,"<=",  "'$t1'");
     }

     //Her ikiside dolu ve anlamlý ise duruma göre sql 'and' veya 'or' olabiliyor.
     if (is_hour($hh0) && is_minute($hm0) && is_hour($hh1) && is_minute($hm1)){ 
       $hh0=int_format($hh0);
      $hm0=int_format($hm0);
       $hh1=int_format($hh1);
      $hm1=int_format($hm1);
        if (date("H:i",mktime($hh0,$hm0)) <= date("H:i",mktime($hh1,$hm1))){
          if ($kriter=="")
            $kriter = " (DATE_FORMAT(TIME_STAMP,'%H:%i') >= '$hh0:$hm0' AND DATE_FORMAT(TIME_STAMP,'%H:%i') <= '$hh1:$hm1')";
          else if($kriter<>"")
            $kriter .= " AND (DATE_FORMAT(TIME_STAMP,'%H:%i') >= '$hh0:$hm0' AND DATE_FORMAT(TIME_STAMP,'%H:%i') <= '$hh1:$hm1')";
         }else { 
          if ($kriter=="")
            $kriter = " (DATE_FORMAT(TIME_STAMP,'%H:%i') >= '$hh0:$hm0' OR DATE_FORMAT(TIME_STAMP,'%H:%i') <= '$hh1:$hm1')";
          else if($kriter<>"")
            $kriter .= " AND (DATE_FORMAT(TIME_STAMP,'%H:%i') >= '$hh0:$hm0' OR DATE_FORMAT(TIME_STAMP,'%H:%i') <= '$hh1:$hm1')";
        }
     //Sadece bir kýsmý anlamlý ise de çalýþmalý. Þu saatten büyük veya þu saatten küçük gibi
     }else if (is_hour($hh0) && is_minute($hm0)){
      $hh0=int_format($hh0);
      $hm0=int_format($hm0);
        $kriter .= $cdb->field_query($kriter, "(DATE_FORMAT(TIME_STAMP,'%H:%i')"      ,">=",    "'$hh0:$hm0')"); 
     }else if (is_hour($hh1) && is_minute($hm1)){
      $hh1=int_format($hh1);
      $hm1=int_format($hm1);
        $kriter .= $cdb->field_query($kriter, "(DATE_FORMAT(TIME_STAMP,'%H:%i')"      ,"<=",    "'$hh1:$hm1')");      
     }

     //Hafta içi
     if ($hafta == "1"){
       if ($kriter == "")
        $kriter = " (WEEKDAY(CDR_MAIN_DATA.MY_DATE)=0  OR WEEKDAY(CDR_MAIN_DATA.MY_DATE)=1 OR
            WEEKDAY(CDR_MAIN_DATA.MY_DATE)=2 OR WEEKDAY(CDR_MAIN_DATA.MY_DATE)=3 OR
            WEEKDAY(CDR_MAIN_DATA.MY_DATE)=4) ";
      else if ($kriter <>"")
        $kriter =$kriter." AND (WEEKDAY(CDR_MAIN_DATA.MY_DATE)=0  OR WEEKDAY(CDR_MAIN_DATA.MY_DATE)=1 OR
            WEEKDAY(CDR_MAIN_DATA.MY_DATE)=2 OR WEEKDAY(CDR_MAIN_DATA.MY_DATE)=3 OR
            WEEKDAY(CDR_MAIN_DATA.MY_DATE)=4) ";
      }

     //Hafta sonu
     if ($hafta == "2"){
       if ($kriter == "")
        $kriter = " (WEEKDAY(CDR_MAIN_DATA.MY_DATE)=5 OR WEEKDAY(CDR_MAIN_DATA.MY_DATE)=6)" ;
      else if ($kriter <> "")
        $kriter = $kriter. " AND (WEEKDAY(CDR_MAIN_DATA.MY_DATE)=5  OR WEEKDAY(CDR_MAIN_DATA.MY_DATE)=6)";
     }
    //Tarih kontrolü burada bitiyor.
    
  }

  function write_links($file_name){
      global $cdb;
      // $file_name adýndaki dosyanýn bilgilerini getir
    $sql_str1="SELECT * FROM PAGE_LINKS WHERE PAGE_URL= '$file_name'";
      if (!($cdb->execute_sql($sql_str1, $result1, $error_msg))){
              echo "Hata:".$error_msg;
                exit;
            }
        $cnt = 0;    
        $row1 = mysql_fetch_object($result1);
        // scriptin adýný ve url'sini getir
        $links_array[$cnt] = " <a class=\"a\" href=\"".$row1->PAGE_URL."\">".$row1->PAGE_NAME."</a>";
        $cnt++;
        // scriptin baðlý olduðu sayfanýn id'sini getir id sýfýr ise ana sayfadýr
        $RELATED_ID = $row1->RELATED_ID;
        if($RELATED_ID>0)
            $LOOP_IT = TRUE;    
    else
            $LOOP_IT = FALSE;    
        while($LOOP_IT){
           // RELATED_ID'ye göre sayfaslarý birbirine baðla
           $qry = "SELECT * FROM PAGE_LINKS WHERE PAGE_ID= '".$RELATED_ID."'";
      if (!($cdb->execute_sql($qry,$result1,$error_msg))){
              echo "Hata : ".$error_msg;
                exit;
            }
            $row1=mysql_fetch_object($result1);
            if($RELATED_ID>0){
                $links_array[$cnt] = " <a class=\"a\" href=\"".$row1->PAGE_URL."\">".$row1->PAGE_NAME."</a>";
                $cnt++;
                $RELATED_ID = $row1->RELATED_ID;
        }else $LOOP_IT = FALSE;    
        }
        $cnt--;
//        tersten çaðrýlan stringleri baþtan hizaya al
        for($i=$cnt;$i>=0;$i--){
           if($RET=="") 
             $RET = $links_array[$i];
           else 
             $RET = $RET."/".$links_array[$i];
        }
        // linkleri geri gönder
        RETURN $RET;
        // bittiiiii....
    }

function checkTable($tableName=""){
    global $cdb;

    if (!($cdb->execute_sql("SHOW TABLES",$result1,$error_msg))){
           print_error($error_msg);
           exit;
    }

    for ($i = 0; $i < mysql_num_rows($result1); $i++){
       if(mysql_tablename($result1, $i)==$tableName)  
         return true;
         
    }
    return false;  

}

function copyTable($mainTable="CDR_MAIN_DATA",$newTable=""){
    global $cdb;

    
     if(checkTable($newTable)) return;


     if (!($cdb->execute_sql("SHOW KEYS FROM `$mainTable`",$result,$error_msg))){
           print_error($error_msg);
           exit;
     }
    
     while($row = mysql_fetch_row($result)){
         $keys[$row[2]][0] = $row[1] ;
         $keys[$row[2]][$row[3]] = $row[4] ;
         //echo $row[2];
     }
     foreach($keys as $key=>$value){
//          echo $key . $value . "<br>";
//          foreach($keys[$key] as $key1=>$value1){
//                echo $key.$value[2] . $value[1]. "<br>";
                
                if($key=="PRIMARY" && $keys[$key][0]=='0'){

                     for ($k=1;$k<sizeof($keys[$key]);$k++){
                         $c.=",".$keys[$key][$k];
                     }

                    if($sqlTxt)
                       $sqlTxt .=",". " PRIMARY KEY (".substr($c,1).")";
                    else   
                       $sqlTxt = " PRIMARY KEY (".substr($c,1).")";
                     
                }elseif($key!="PRIMARY" && $keys[$key][0]=='0'){
                     $c="";
                     for ($k=1;$k<sizeof($keys[$key]);$k++){
                         $c.=",".$keys[$key][$k];
                     }

                    if($sqlTxt)
                       $sqlTxt .=",". "UNIQUE KEY $key(".substr($c,1). ")";
                    else   
                       $sqlTxt = " UNIQUE KEY $key(".substr($c,1). ")";

                     
                }elseif($key!="PRIMARY" && $keys[$key][0]=='1'){
                      
                     $c="";
                     for ($k=1;$k<sizeof($keys[$key]);$k++){
                         $c.=",".$keys[$key][$k];
                     }
                    if($sqlTxt)
                       $sqlTxt .=",". " KEY $key(".substr($c,1). ")";
                    else   
                       $sqlTxt = " KEY $key(".substr($c,1). ")";
                     
                }
                
     
     }      
     
     $sqlTxt =  "CREATE TABLE `$newTable` (  $sqlTxt) SELECT * FROM `$mainTable` WHERE 1 = 0";

     if (!($cdb->execute_sql($sqlTxt,$result,$error_msg))){
           print_error($error_msg);
           exit;
     }


     if (!($cdb->execute_sql("SHOW FIELDS FROM `$mainTable`",$result,$error_msg))){
           print_error($error_msg);
           exit;
     }
    
     while($row = mysql_fetch_row($result)){
         if($row[5]=='auto_increment'){
              $sqlTxt = "ALTER TABLE `$newTable` CHANGE `".$row[0]."` `".$row[0]."` ".$row[1]."  DEFAULT '".$row[4]."'";
              if($row[2]!='YES')
                   $sqlTxt .=" NOT NULL ";  
              $sqlTxt .=" AUTO_INCREMENT ";     
               
              
         } 
     }

     if (!($cdb->execute_sql($sqlTxt,$result,$error_msg))){
           print_error($error_msg);
           exit;
     }
     
     
}

function getTableName($startDate="",$endDate=""){
    global $cdb;
//Her iki tarihte ayný ayda ise ilgili aya ait tablodan yoksa ana tablodan alsýn.
	if($startDate!="" && $endDate!=""){
        $sql_str = "SELECT IF( MONTH('$startDate')=MONTH('$endDate') AND YEAR('$startDate') = YEAR('$endDate'),
                    (CONCAT('CDR_MAIN_',LPAD(MONTH('$startDate'),2,'0'),'_',YEAR('$startDate'))),
                    'CDR_MAIN_DATA') AS TABLE_NAME " ;
    //}elseif($startDate!="" && $endDate==""){
    //    $sql_str = " SELECT  CONCAT('CDR_MAIN_',LPAD(MONTH('$startDate'),2,'0'),'_',YEAR('$startDate')) AS TABLE_NAME " ;
    //}elseif($startDate=="" && $endDate!=""){
    //    return "CDR_MAIN_DATA";
    }else{
        return "CDR_MAIN_DATA";
    }

    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
     }
     $row = mysql_fetch_object($result);
     return $row->TABLE_NAME;

}

function copyTableData($tableName, $date){
    global $cdb;
   
    if($tableName=='CDR_MAIN_DATA') return;
    
    if(checkTable($tableName)){$newTableName= $tableName;}

    $sql_str = "SELECT VALUE FROM SYSTEM_PRM WHERE NAME='LAST_CDR_ID'";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
        
    $row =  mysql_fetch_object($result);
    $CDR_ID = $row->VALUE;
    if(mysql_num_rows($result)<1){
      $sql_str = "INSERT INTO SYSTEM_PRM (ID, NAME, DESCRIPTION, VALUE, DEFAULT_VALUE, SHORT_DESC) VALUES (NULL, 'LAST_CDR_ID', 'Son iþlenen CDR id', '0', '0', 'CDR ID')";
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
      }
      $CDR_ID = 0; 
    }
         
    $sql_str = "SELECT MY_DATE, CONCAT('CDR_MAIN_',DATE_FORMAT(MY_DATE,'%m-%Y')) AS TABLE_NAME FROM CDR_MAIN_DATA WHERE CDR_ID > 150 ORDER BY CDR_ID LIMIT 1";
    if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
      exit;
    }
    $row1 =  mysql_fetch_object($result1);
    $DATE = $row1->MY_DATE;
    $TABLE_NAME = $row1->TABLE_NAME;
    copyTable($mainTable="CDR_MAIN_DATA",$TABLE_NAME); 
    $sql_str = "INSERT INTO $TABLE_NAME SELECT * FROM CDR_MAIN_DATA WHERE MY_DATE = $DATE AND CDR_ID > $CDR_ID";
    //$sql_str = "INSERT INTO $newTableName SELECT * FROM CDR_MAIN_DATA WHERE MY_DATE >= DATE_FORMAT('$date','%Y-%m-1') AND MY_DATE <=DATE_FORMAT('$date','%Y-%m-31')";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }

    $sql_str = "SELECT MAX(CDR_ID) AS MAX_ID FROM $TABLE_NAME";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
    if(mysql_num_rows($result)>1){
      $row = mysql_fetch_object($result2);
      $CDR_ID = $row->MAX_ID; 
    }
        
    $sql_str = "UPDATE SYSTEM_PRM SET VALUE = $CDR_ID WHERE NAME = LAST_CDR_ID";
    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
      exit;
    }
}


function copyTableData2($tableName, $date){
    global $cdb;
    
    if($tableName=='CDR_MAIN_DATA') return;
    
    if(checkTable($tableName)){
        $newTableName= $tableName;
   
        $sql_str = "DELETE FROM  $newTableName ";
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
        }

        $sql_str = "OPTIMIZE TABLE  $newTableName ";
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
        }
        
        $sql_str = "INSERT INTO $newTableName SELECT * FROM CDR_MAIN_DATA WHERE MONTH(MY_DATE) = MONTH('$date') AND YEAR(MY_DATE)=YEAR('$date')";
        $sql_str = "INSERT INTO $newTableName SELECT * FROM CDR_MAIN_DATA WHERE MY_DATE >= DATE_FORMAT('$date','%Y-%m-1') AND MY_DATE <=DATE_FORMAT('$date','%Y-%m-31')";
 
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
        }
    }
}


function copyTableData1($tableName, $date){
    global $cdb;
    
    if(checkTable($tableName)){
        $newTableName= $tableName;

        $sql_str = "SELECT MAX(CDR_ID) AS CDR_ID FROM  $newTableName ";
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
        }
        $row = mysql_fetch_object($result);
        
        $sql_str = "INSERT INTO $newTableName SELECT * FROM CDR_MAIN_DATA WHERE MY_DATE = '$date' AND CDR_ID> '$row->CDR_ID'";
        if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
           print_error($error_msg);
           exit;
        }
    }
}

function checkDemover($dstat, $deadline){
  $datenow = date('Y-m-d');
  if($dstat){
    if($datenow>=$deadline){
      return true;
    }else{
      return false;
    }
  }else{
    return false;
  }

}

  ?>

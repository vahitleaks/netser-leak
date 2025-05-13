<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   require_valid_login();
   session_cache_limiter('nocache');

   $conn = $cdb->getConnection();

   //Kiþisel Bilgiler alýnýyor
   $sql_str = "SELECT USERS.NAME,USERS.SURNAME,USERS.EMAIL,A.EXT_NO EXT1,B.EXT_NO EXT2,
          C.EXT_NO EXT3,USERS.EXT_ID1,USERS.EXT_ID2,USERS.EXT_ID3,AUTH_CODES.AUTH_CODE,
          USERS.AUTH_CODE_ID,USERS.DEPT_ID
        FROM USERS
          LEFT JOIN EXTENTIONS AS A ON USERS.EXT_ID1=A.EXT_ID
          LEFT JOIN EXTENTIONS AS B ON USERS.EXT_ID2=B.EXT_ID
          LEFT JOIN EXTENTIONS AS C ON USERS.EXT_ID3=C.EXT_ID
          LEFT JOIN AUTH_CODES ON USERS.AUTH_CODE_ID = AUTH_CODES.AUTH_CODE_ID
        WHERE USER_ID = ".$SESSION["user_id"];

    if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
      print_error($error_msg);
       exit;
   }
    $row = mysql_fetch_object($result);

 //Kiþinin 1. Dahilisine Yapýlan Kota Atamalarý alýnýyor
   $sql_str = "SELECT QUOTAS.INCITY_LIMIT,QUOTAS.INTERCITY_LIMIT,
          QUOTAS.GSM_LIMIT, QUOTAS.INTERNATIONAL_LIMIT
        FROM QUOTA_ASSIGNS
          LEFT JOIN QUOTAS ON QUOTA_ASSIGNS.QUOTA_ID = QUOTAS.QUOTA_ID
          LEFT JOIN EXTENTIONS ON QUOTA_ASSIGNS.EXT_ID = EXTENTIONS.EXT_ID
          WHERE QUOTA_ASSIGNS.QUOTA_ASSIGN_TYPE_ID = 1 AND QUOTA_ASSIGNS.EXT_ID='".$row->EXT_ID1."'";

  if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
       exit;
   }
  
  if (mysql_num_rows($result1) > 0){
    $QUOTA_EXT1="Yes";
      $row1 = mysql_fetch_object($result1);
     $INCITY_LIMIT[1] = $row1->INCITY_LIMIT;
     $INTERCITY_LIMIT[1] = $row1->INTERCITY_LIMIT;
     $INTERNATIONAL_LIMIT[1] = $row1->INTERNATIONAL_LIMIT;
     $GSM_LIMIT[1] = $row1->GSM_LIMIT;
  }
    
//Kiþinin 2. Dahilisine Yapýlan Kota Atamalarý alýnýyor
   $sql_str = "SELECT QUOTAS.INCITY_LIMIT,QUOTAS.INTERCITY_LIMIT,
          QUOTAS.GSM_LIMIT, QUOTAS.INTERNATIONAL_LIMIT
        FROM QUOTA_ASSIGNS
          LEFT JOIN QUOTAS ON QUOTA_ASSIGNS.QUOTA_ID = QUOTAS.QUOTA_ID
          LEFT JOIN EXTENTIONS ON QUOTA_ASSIGNS.EXT_ID = EXTENTIONS.EXT_ID
          WHERE QUOTA_ASSIGNS.QUOTA_ASSIGN_TYPE_ID = 1 AND QUOTA_ASSIGNS.EXT_ID='".$row->EXT_ID2."'";

  if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
       exit;
   }

  if (mysql_num_rows($result1) > 0){
    $QUOTA_EXT2="Yes";
      $row1 = mysql_fetch_object($result1);
     $INCITY_LIMIT[2] = $row1->INCITY_LIMIT;
     $INTERCITY_LIMIT[2] = $row1->INTERCITY_LIMIT;
     $INTERNATIONAL_LIMIT[2] = $row1->INTERNATIONAL_LIMIT;
     $GSM_LIMIT[2] = $row1->GSM_LIMIT;
  }
    
//Kiþinin 3. Dahilisine Yapýlan Kota Atamalarý alýnýyor
   $sql_str = "SELECT QUOTAS.INCITY_LIMIT,QUOTAS.INTERCITY_LIMIT,
          QUOTAS.GSM_LIMIT, QUOTAS.INTERNATIONAL_LIMIT
        FROM QUOTA_ASSIGNS
          LEFT JOIN QUOTAS ON QUOTA_ASSIGNS.QUOTA_ID = QUOTAS.QUOTA_ID
          LEFT JOIN EXTENTIONS ON QUOTA_ASSIGNS.EXT_ID = EXTENTIONS.EXT_ID
          WHERE QUOTA_ASSIGNS.QUOTA_ASSIGN_TYPE_ID = 1 AND QUOTA_ASSIGNS.EXT_ID='". $row->EXT_ID3."'";

  if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
       exit;
   }
  if (mysql_num_rows($result1) > 0){
    $QUOTA_EXT3="Yes";      
    $row1 = mysql_fetch_object($result1);
     $INCITY_LIMIT[3] = $row1->INCITY_LIMIT;
     $INTERCITY_LIMIT[3] = $row1->INTERCITY_LIMIT;
     $INTERNATIONAL_LIMIT[3] = $row1->INTERNATIONAL_LIMIT;
     $GSM_LIMIT[3] = $row1->GSM_LIMIT;
  }

//Authorisation koduna yapýlan kota atamasý
   $sql_str = "SELECT QUOTAS.INCITY_LIMIT,QUOTAS.INTERCITY_LIMIT,
          QUOTAS.GSM_LIMIT, QUOTAS.INTERNATIONAL_LIMIT
        FROM QUOTA_ASSIGNS
          LEFT JOIN QUOTAS ON QUOTA_ASSIGNS.QUOTA_ID = QUOTAS.QUOTA_ID
          LEFT JOIN AUTH_CODES ON QUOTA_ASSIGNS.AUTH_CODE_ID = AUTH_CODES.AUTH_CODE_ID
          WHERE QUOTA_ASSIGNS.QUOTA_ASSIGN_TYPE_ID = 4 AND QUOTA_ASSIGNS.AUTH_CODE_ID='". $row->AUTH_CODE_ID."'";

  if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
       exit;
   }

  if (mysql_num_rows($result1) > 0){
    $QUOTA_AUTH="Yes";      
    $row1 = mysql_fetch_object($result1);
     $INCITY_LIMIT[4] = $row1->INCITY_LIMIT;
     $INTERCITY_LIMIT[4] = $row1->INTERCITY_LIMIT;
     $INTERNATIONAL_LIMIT[4] = $row1->INTERNATIONAL_LIMIT;
     $GSM_LIMIT[4] = $row1->GSM_LIMIT;
  }

//Departmanýndaki Her bir elemana yapýlan Kota Atamalarý alýnýyor.
   $sql_str = "SELECT QUOTAS.QUOTA_NAME,QUOTAS.INCITY_LIMIT,QUOTAS.INTERCITY_LIMIT,
            QUOTAS.GSM_LIMIT,QUOTAS.INTERNATIONAL_LIMIT
        FROM QUOTA_ASSIGNS
          LEFT JOIN QUOTAS ON QUOTA_ASSIGNS.QUOTA_ID = QUOTAS.QUOTA_ID
          LEFT JOIN USERS ON USERS.DEPT_ID = QUOTA_ASSIGNS.DEPT_ID
          WHERE QUOTA_ASSIGNS.QUOTA_ASSIGN_TYPE_ID = 3 
                  AND QUOTA_ASSIGNS.DEPT_ID = ".$row->DEPT_ID."
                   AND USERS.USER_ID = ".$SESSION["user_id"];

    if (!($cdb->execute_sql($sql_str,$result1,$error_msg))){
      print_error($error_msg);
       exit;
   }

  if (mysql_num_rows($result1) > 0){
    $QUOTA_DEPT_MEMB="Yes";    
    $row1 = mysql_fetch_object($result1);
     $INCITY_LIMIT[5] = $row1->INCITY_LIMIT;
     $INTERCITY_LIMIT[5] = $row1->INTERCITY_LIMIT;
     $INTERNATIONAL_LIMIT[5] = $row1->INTERNATIONAL_LIMIT;
     $GSM_LIMIT[5] = $row1->GSM_LIMIT;
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
    }

     cc_page_meta();
     echo "<center>";
     page_header();
     echo "<center>";
?>
<br>
<table width="90%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="60%" align="center" colspan="5">
    <?table_header("Kiþisel Bilgiler ","90%");?>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
          <td class="td1_koyu" width="10%">Adý</td>
          <td class="td1" width="25%"><?echo $row->NAME?></td>
          <td class="td1_koyu" width="15%">Soyadý</td>
          <td colspan="3" class="td1" width="50%"><?echo $row->SURNAME?></td>
        </tr>
        <tr>
          <td class="td1_koyu" width="10%">E-Mail</td>
          <td class="td1" width="25%"><?echo $row->EMAIL?></td>
          <td class="td1_koyu" width="15%">Auth. Kodu</td>
          <td colspan="3" class="td1" width="50%"><?echo $row->AUTH_CODE?></td>
        </tr>
        <tr>
          <td class="td1_koyu" width="10%">1.Dahili</td>
          <td class="td1" width="25%"><?echo $row->EXT1?></td>
          <td class="td1_koyu" width="15%">2.Dahili</td>
          <td class="td1" width="15%"><?echo $row->EXT2?></td>
          <td class="td1_koyu" width="15%">3.Dahili</td>
          <td class="td1" width="20%"><?echo $row->EXT3?></td>
        </tr>  
      </table>
    <?table_footer();?>
    </td>
  </tr>
  <tr>
    <?if ($QUOTA_EXT1=="Yes"){?>
    <td width="30%" align="center"><br>
    <?table_in_table_header($row->EXT1." Dahilisinin Kotasý","95%");?>
        <table border="0" cellspacing="2" cellpadding="1" align="center" width="100%">
                   <tr>
                       <td class="bg_acik_f_koyu" width="35%">Kota Adý</td>
                        <td class="bg_acik_f_acik"width="65%"><? echo $row->QUOTA_NAME; ?></td>
                    </tr>  
                    <tr> 
                        <td width="35%" class="bg_koyu_f_koyu">Þ.Ý. Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($INCITY_LIMIT[1],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INCITY_LIMIT[1],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">Þ.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERCITY_LIMIT[1],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERCITY_LIMIT[1],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_koyu_f_koyu" width="35%">GSM Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($GSM_LIMIT[1],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($GSM_LIMIT[1],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">U.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERNATIONAL_LIMIT[1],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERNATIONAL_LIMIT[1],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                </table>
        <?table_in_table_footer();?>
    </td>
    <?}?>
    <?if ($QUOTA_EXT2=="Yes"){?>  
    <td width="30%" align="center"><br>
      <?table_in_table_header($row->EXT2." Dahilisinin Kotasý","95%");?>
        <table border="0" cellspacing="2" cellpadding="1" align="center" width="100%">
                   <tr>
                       <td class="bg_acik_f_koyu" width="35%">Kota Adý</td>
                        <td class="bg_acik_f_acik"width="65%"><? echo $row->QUOTA_NAME; ?></td>
                    </tr>  
                    <tr> 
                        <td width="35%" class="bg_koyu_f_koyu">Þ.Ý. Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($INCITY_LIMIT[2],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INCITY_LIMIT[2],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">Þ.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERCITY_LIMIT[2],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERCITY_LIMIT[2],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_koyu_f_koyu" width="35%">GSM Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($GSM_LIMIT[2],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($GSM_LIMIT[2],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">U.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERNATIONAL_LIMIT[2],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERNATIONAL_LIMIT[2],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                </table>
      <?table_in_table_footer();?>
    </td>
    <?}?>
    <?if ($QUOTA_EXT3=="Yes"){?>      
    <td width="30%" align="center"><br>
      <?table_in_table_header($row->EXT3." Dahilisinin Kotasý","95%");?>
        <table border="0" cellspacing="2" cellpadding="1" align="center" width="100%">
                   <tr>
                       <td class="bg_acik_f_koyu" width="35%">Kota Adý</td>
                        <td class="bg_acik_f_acik"width="65%"><? echo $row->QUOTA_NAME; ?></td>
                    </tr>  
                    <tr> 
                        <td width="35%" class="bg_koyu_f_koyu">Þ.Ý. Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($INCITY_LIMIT[3],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INCITY_LIMIT[3],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">Þ.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERCITY_LIMIT[3],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERCITY_LIMIT[3],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_koyu_f_koyu" width="35%">GSM Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($GSM_LIMIT[3],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($GSM_LIMIT[3],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">U.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERNATIONAL_LIMIT[3],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERNATIONAL_LIMIT[3],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                </table>
      <?table_in_table_footer();?>
    </td>
    <?}?>
  </tr>
  <tr>  
    <?if ($QUOTA_AUTH=="Yes"){?>    
    <td width="30%" align="center"><br>
      <?table_in_table_header($row->AUTH_CODE." Kodunun Kotasý","95%");?>
        <table border="0" cellspacing="2" cellpadding="1" align="center" width="100%">
                   <tr>
                       <td class="bg_acik_f_koyu" width="35%">Kota Adý</td>
                        <td class="bg_acik_f_acik"width="65%"><? echo $row->QUOTA_NAME; ?></td>
                    </tr>  
                    <tr> 
                        <td width="35%" class="bg_koyu_f_koyu">Þ.Ý. Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($INCITY_LIMIT[4],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INCITY_LIMIT[4],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">Þ.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERCITY_LIMIT[4],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERCITY_LIMIT[4],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_koyu_f_koyu" width="35%">GSM Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($GSM_LIMIT[4],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($GSM_LIMIT[4],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">U.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERNATIONAL_LIMIT[4],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERNATIONAL_LIMIT[4],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                </table>
      <?table_in_table_footer();?>
    </td>
    <?}?>
    <?if ($QUOTA_DEPT_MEMB=="Yes"){?>          
    <td width="30%" align="center"><br>
      <?table_in_table_header("Departmanýnýn Kotasý","95%");?>
        <table border="0" cellspacing="2" cellpadding="1" align="center" width="100%">
                   <tr>
                       <td class="bg_acik_f_koyu" width="35%">Kota Adý</td>
                        <td class="bg_acik_f_acik"width="65%"><? echo $row->QUOTA_NAME; ?></td>
                    </tr>  
                    <tr> 
                        <td width="35%" class="bg_koyu_f_koyu">Þ.Ý. Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($INCITY_LIMIT[5],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INCITY_LIMIT[5],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">Þ.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERCITY_LIMIT[5],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERCITY_LIMIT[5],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_koyu_f_koyu" width="35%">GSM Limit</td>
                        <td width="65%" class="bg_koyu_f_acik">
              <? echo calculate_time($GSM_LIMIT[5],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($GSM_LIMIT[5],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                    <tr> 
                        <td class="bg_acik_f_koyu" width="35%">U.A. Limit</td>
                        <td width="65%" class="bg_acik_f_acik">
              <? echo calculate_time($INTERNATIONAL_LIMIT[5],"hour"); ?>&nbsp&nbspSaat&nbsp&nbsp
              <? echo calculate_time($INTERNATIONAL_LIMIT[5],"min"); ?>&nbsp&nbspDk
            </td>
                    </tr>  
                </table>
      <?table_in_table_footer();?>
    </td>
    <?}?>
  </tr>  
</table>
<?page_footer(0);?>

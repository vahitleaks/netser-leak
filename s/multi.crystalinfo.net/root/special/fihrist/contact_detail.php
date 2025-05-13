<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   session_cache_limiter('nocache');
   require_valid_login();
   $conn = $cdb->getConnection();

  cc_page_meta();
 
  if ($id!="" && is_numeric($id)){
     $sql_str = "SELECT FIHRIST.* ,FIH_DEPARTMENT.DEP_NAME,FIH_DEPARTMENT.DEP_ID,FIH_TITLE.TITLE_ID,
	                    FIH_TITLE.TITLE_NAME, FIH_SUB_DEPT.FIH_SUB_DEPT
                 FROM FIHRIST 
                   LEFT JOIN FIH_TITLE ON FIH_TITLE.TITLE_ID = FIHRIST.TITLE_ID 
                   LEFT JOIN FIH_DEPARTMENT ON FIHRIST.DEP_ID = FIH_DEPARTMENT.DEP_ID
                   LEFT JOIN FIH_SUB_DEPT ON FIH_SUB_DEPT.SUB_DEP_ID = FIHRIST.SUB_DEP_ID
                 WHERE CONTACT_ID = $id ";
                     
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
   }
     
  if (mysql_numrows($result)>0){
       $row = mysql_fetch_object($result);
     
  $bgc1 = "ECECEC";
  $bgc2 = "F9F9F9";
  
  function bcol($i){
     global $bgc1;
	 global $bgc2;
     if ($i%2 == 0)
	    return $bgc1;
	 return $bgc2;
  }
  $i = 0;
  ?>
  <table border="0" cellspacing="2" cellpadding="0" width="400">
    <tr height="25"> 
      <td class="header_beyaz" width="150">Ünvaný</td>
      <td width="250"><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->TITLE_NAME;?></font></b></td>
    </tr>    
    <tr height="25"> 
      <td class="header_beyaz">Adý</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->NAME;?></font></b></td>
    </tr>  
    <tr height="25"> 
      <td class="header_beyaz">Soyadý</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->SURNAME;?></font></b></td>
    </tr>  
    <tr height="25"> 
      <td class="header_beyaz">Birimi</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->DEP_NAME;?></font></b></td>
    </tr>  
    <tr height="25"> 
      <td class="header_beyaz">Alt Birimi</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->FIH_SUB_DEPT;?></font></b></td>
    </tr>  
    <tr height="25"> 
      <td class="header_beyaz">Dahili Ýþ Tel</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->EXT_COMP_TEL;?></font></b></td>
    </tr>  
    <tr height="25"> 
      <td class="header_beyaz">Dahili Ev Tel</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->EXT_HOME_TEL;?></font></b></td>
    </tr>  
<!--<tr> 
      <td class="header_beyaz">Harici Ýþ Tel</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->EXTERNAL_COMP_TEL;?></font></b></td>
    </tr>  
    <tr>  
      <td class="header_beyaz">Harici Ýþ Tel</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->EXTERNAL_HOME_TEL;?></font></b></td>
    </tr>-->   
    <tr height="25"> 
      <td class="header_beyaz">Kiþisel E-Mail</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->PERSONAL_EMAIL;?></font></b></td>
    </tr>  
    <tr height="25"> 
      <td class="header_beyaz">Adres</td>
      <td><b><font face="Tahoma" size="2" color="#163E72"><?echo $row->ADDRESS;?></font></b></td>
    </tr>  
  </table>
  <?     
  }else{
      print_error("Belirtilen Kayýt Bulunamadý");
      exit;
  }
  
  }
 ?>
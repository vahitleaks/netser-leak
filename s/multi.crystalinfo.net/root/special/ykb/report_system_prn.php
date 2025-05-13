<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
     $cUtility = new Utility();
     $cdb = new db_layer();
     require_valid_login();
  if (right_get("SITE_ADMIN")){
       //Site admin hakk� varsa her�eyi g�rebilir.  
  //Site id gelmemi�se ki�inin bulundu�u site raporu al�n�r.
    if(!$SITE_ID){$SITE_ID = $SESSION['site_id'];}
  }elseif(right_get("ADMIN") || right_get("ALL_REPORT")){
  // Admin vaye ALL_REPORT hakk� varsa kendi sitesindeki her�eyi g�rebilir.
    $SITE_ID = $SESSION['site_id'];
  }else{
        print_error("Bu sayfay� G�rme Hakk�n�z Yok!!!");
    exit;
  } 

   $conn = $cdb->getConnection();
?>
  <form name="sort_me" method="post" action="">
    <input type="hidden" name="type" value="<?=$type?>">
        <input type="hidden" name="SITE_ID" value="<?=$SITE_ID?>">    
        <input type="hidden" name="sort_type" value="<?=($sort_type=="asc")?"desc":"asc"?>">  
  </form>
<?
  $local_country_code = get_country_code($SITE_ID);
  switch ($type){
       case 'ext':
      $sql_str = "SELECT EXTENTIONS.EXT_NO, EMAIL, SICIL_NO, DEPTS.DEPT_NAME, EXTENTIONS.DESCRIPTION 
                    FROM EXTENTIONS LEFT JOIN DEPTS ON EXTENTIONS.DEPT_ID = DEPTS.DEPT_ID 
                  WHERE EXTENTIONS.SITE_ID = ".$SITE_ID; 
      $header="Dahili Listesi";$head_width="95%";
      $field1="EXT_NO";$field1_name="Dahili";$width1="10%";$field1_ord="EXT_NO";
      $field2="SICIL_NO";$field2_name="Sicil No";$width2="10%";$field2_ord="SICIL_NO";
      $field3="DESCRIPTION";$field3_name="A��klama";$width3="40%";$field3_ord="DESCRIPTION";
      $field4="DEPT_NAME";$field4_name="Departman";$width4="20%";$field4_ord="DEPT_NAME";
      $field5="EMAIL";$field5_name="E-Mail";$width5="20%";$field5_ord="EMAIL";
      break;  
      case 'user':
      $sql_str = "SELECT USERS.NAME,USERS.SURNAME,DEPTS.DEPT_NAME 
                  FROM USERS LEFT JOIN DEPTS ON USERS.DEPT_ID = DEPTS.DEPT_ID 
                  WHERE USERS.SITE_ID = ".$SITE_ID; 
      $header="Kullan�c� Listesi";$head_width="60%";
      $field1="NAME";$field1_name="Ad�";$width1="30%";$field1_ord="NAME";
      $field2="SURNAME";$field2_name="Soyad�";$width2="30%";$field2_ord="SURNAME";
      $field3="DEPT_NAME";$field3_name="Departman";$width3="40%";$field3_ord="DEPT_NAME";
      break;
    case 'dept':
      $sql_str = "SELECT DEPT_NAME, DEPT_RSP_EMAIL FROM DEPTS WHERE DEPTS.SITE_ID = ".$SITE_ID; 
      $header="Departman Listesi";$head_width="70%";
      $field1="DEPT_NAME";$field1_name="Departman Ad�";$width1="50%";$field1_ord="DEPT_NAME";
      $field2="DEPT_RSP_EMAIL";$field2_name="Y�netici E-Mail";$width2="50%";$field2_ord="DEPT_RSP_EMAIL";
      break;
    case 'addr':
      $sql_str = "SELECT NAME, SURNAME,COMPANY,POSITION FROM CONTACTS WHERE IS_GLOBAL=1 AND CONTACTS.SITE_ID = ".$SITE_ID;
      $header="Global Fihrist";$head_width="60%";
      $field1="NAME";$field1_name="Ad�";$width1="20%";$field1_ord="NAME";
      $field2="SURNAME";$field2_name="Soyad�";$width2="20%";$field2_ord="SURNAME";
      $field3="COMPANY";$field3_name="Firma";$width3="30%";$field3_ord="COMPANY";
      $field4="POSITION";$field4_name="G�revi";$width4="30%";$field4_ord="POSITION";
      break;
    case 'trunk':
      $sql_str = "SELECT TRUNKS.MEMBER_NO AS TRUNK, TRUNKS.TRUNK_NAME, 
                    TTelProvider.TelProvider, TRUNKS.PHONE_NUMBER 
                  FROM TRUNKS 
                  LEFT JOIN TTelProvider ON TRUNKS.TEL_PROVIDER_ID = TTelProvider.TelProvider
                  WHERE TRUNKS.SITE_ID = ".$SITE_ID; 
      $header="Hat Listesi";$head_width="70%";
      $field1="TRUNK";$field1_name="Hat";$width1="10%";$field1_ord="TRUNK";
      $field2="TRUNK_NAME";$field2_name="Hat Ad�";$width2="25%";$field2_ord="TRUNK_NAME";
      $field3="TelProvider";$field3_name="�ebeke";$width3="20%";$field3_ord="TelProvider";
      $field4="PHONE_NUMBER";$field4_name="Telefon No";$width4="20%";$field4_ord="PHONE_NUMBER";
      break;
    case 'city':
      $sql_str = "SELECT LocalCode, LocationName FROM TLocation WHERE LocationTypeid = 1 AND CountryCode = '$local_country_code'"; 
      $header="�ehir Kodlar�";$head_width="40%";
      $field1="LocalCode";$field1_name="�ehir Kodu";$width1="30%";$field1_ord="LocalCode";
      $field2="LocationName";$field2_name="�ehir";$width2="70%";$field2_ord="LocationName";
      break;
    case 'country':
      $sql_str = "SELECT CountryCode, LocationName FROM TLocation WHERE LocationTypeid = 3"; 
      $header="�lke Kodlar�";$head_width="40%";
      $field1="CountryCode";$field1_name="�lke Kodu";$width1="30%";$field1_ord="CountryCode";
      $field2="LocationName";$field2_name="�lke";$width2="70%";$field2_ord="LocationName";
      break;
    case 'special':
      $sql_str = "SELECT LocalCode, LocationName FROM TLocation WHERE LocationTypeid = 7 AND CountryCode = '$local_country_code'"; 
      $header="�zel Numaralar";$head_width="40%";
      $field1="LocalCode";$field1_name="Kodu";$width1="30%";$field1_ord="LocalCode";
      $field2="LocationName";$field2_name="Ad�";$width2="70%";$field2_ord="LocationName";
      break;
    default:  
     }

    switch ($order){
    case '1':
      $sql_str .= " ORDER BY ".$field1_ord." ".$sort_type; 
      break;
    case '2':
      $sql_str .= " ORDER BY ".$field2_ord." ".$sort_type; 
      break;
    case '3':
      $sql_str .= " ORDER BY ".$field3_ord." ".$sort_type; 
      break;
    case '4':
      $sql_str .= " ORDER BY ".$field4_ord." ".$sort_type; 
      break;
    case '5':
      $sql_str .= " ORDER BY ".$field5_ord." ".$sort_type; 
      break;
    default:
         }
     
   if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
        print_error($error_msg);
        exit;
     }

  

?>
<html>
<body>
<br><br>
<?
   cc_page_meta();
   echo "<center>";
?>
  <table width="<?=$head_width?>%">
    <tr>
    <td align="right">
      <table cellspacing=0 cellpadding=0>
        <tr>
          <td><img src="<?=IMAGE_ROOT?>report/top02.gif" border=0></td>
          <td><a href="javascript:history.back(1);"><img src="<?=IMAGE_ROOT?>report/geri.gif" border=0 title="Geri"></a></td>
          <td><a href="javascript:history.forward(1);"><img src="<?=IMAGE_ROOT?>report/ileri.gif" border=0 title="�leri"></a></td>
          <td><a href="javascript:document.all('sort_me').submit();"><img src="<?=IMAGE_ROOT?>report/yenile.gif" border=0 title="Yenile"></a></td>
          <td><a href="javascript:window.print();"><img src="<?=IMAGE_ROOT?>report/print.gif" border=0 title="Yazd�r"></a></td>
          <td><img src="<?=IMAGE_ROOT?>report/top01.gif" border=0></td>
        </tr>
      </table>
    
    </td>
    </tr></table>
<?
   table_header($header,$head_width);
   echo "</center>";?>
  <table align="center" cellspacing="3" cellpddding="2" border="0" width="100%">
            <?
            if($sort_type=="asc")
                $sort_gif = "report/top.gif";    
            else
                $sort_gif = "report/down.gif";
            ?>
       <tr>
      <td class="td1_koyu" width="<?=$width1;?>"><?echo $field1_name;?><a style="cursor:hand;" onclick="javascript:submit_form('1');"><img src="<?=IMAGE_ROOT?><?=($order=="1")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
      <?if ($field2_name <> ''){?>
        <td class="td1_koyu" width="<?=$width2;?>"><?echo $field2_name;?><a style="cursor:hand;" onclick="javascript:submit_form('2');"><img src="<?=IMAGE_ROOT?><?=($order=="2")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
      <?}?>
      <?if ($field3_name <> ''){?>
        <td class="td1_koyu" width="<?=$width3;?>"><?echo $field3_name;?><a style="cursor:hand;" onclick="javascript:submit_form('3');"><img src="<?=IMAGE_ROOT?><?=($order=="3")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
        <?}?>
      <?if ($field4_name <> ''){?>
        <td class="td1_koyu" width="<?=$width4;?>"><?echo $field4_name;?><a style="cursor:hand;" onclick="javascript:submit_form('4');"><img src="<?=IMAGE_ROOT?><?=($order=="4")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
        <?}?>
      <?if ($field5_name <> ''){?>
        <td class="td1_koyu" width="<?=$width5;?>"><?echo $field5_name;?><a style="cursor:hand;" onclick="javascript:submit_form('5');"><img src="<?=IMAGE_ROOT?><?=($order=="5")?$sort_gif:"sort.gif"?>" align="absmiddle" Title=""></a></td>
        <?}?>
    </tr>
      <?while($row = mysql_fetch_array($result)){
      $i++;
      echo " <tr class=\"\">";
      echo " <td class=\"td1\">".$row["$field1"]."</td>";
      echo " <td class=\"td1\">".$row["$field2"]."</td>";
        if($field3_name <> ''){  
          echo " <td class=\"td1\">".$row["$field3"]."</td>";
        }
        if($field3_name <> ''){  
          echo " <td class=\"td1\">".$row["$field4"]."</td>";  
        }
        if($field3_name <> ''){      
          echo " <td class=\"td1\">".$row["$field5"]."</td>";  
        }
    }?>
   </table>
 <?table_footer();?>
<script language="JavaScript">
  function submit_form(sortby){
    document.all('sort_me').action='report_system_prn.php?type=<?=$type?>&order=' + sortby;    
    document.all('sort_me').submit();
  }
</script>
</script>

</body>
</html>   
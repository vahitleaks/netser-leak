<?
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   $conn = $cdb->getConnection();
   if ($p=="" || $p < 1)
   $p = 1;
   $start = $cUtility->myMicrotime();
   cc_page_meta();
   echo "<center>";
   fillSecondCombo();
   echo "<br><br>";
?>

<style>
A {text-transform: normal; Font-Family:Tahoma; Font-Weight; text-decoration: none; color:#163E72}; Font-Size:8Pt;}
A:active {text-decoration: none}
A:hover {text-decoration; color:#ff6600;}
BODY{FONT-FAMILY:verdana;background-color:#ECECEC;}
.backG {FONT-FAMILY:verdana;background-color:#93c39b; color:#ffffff; border-bottom:#538c4d 0px solid;font-size:%;}	
</style>

</head>
<body topmargin="0" leftmargin="0"  bgcolor="#ECECEC">
 <table border=0 cellspacing=0 with="750">
 <form name="contact_search" method="post" action="rehber.php?act=src">
   <input type="hidden" name="p" VALUE="<?echo $p?>">
   <input type="hidden" name="ORDERBY" value="<?=$ORDERBY?>">
   <tr BGCOLOR="#ECECEC"">
     <td>Adý :</td>
	 <td><input type=text name=NAME value="<? echo $NAME?>" size="15"></td>
     <td>Soyadý :</td>
     <td><input type=text name=SURNAME value="<? echo $SURNAME?>" size="15"></td>
     <td>Birimi :</td>
     <td>
       <select name="DEP_ID" class="select1" style="width:150" onchange="Fillothercombos(this.value)">
         <?
         $strSQL = "SELECT DEP_ID, DEP_NAME FROM FIH_DEPARTMENT";
         echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $DEP_ID );
         ?>
       </select>
     </td>
     <td>Alt Birimi :</td>
     <td>
       <select name="SUB_DEP_ID" class="select1" style="width:150">
         <?
         $strSQL = "SELECT SUB_DEP_ID, FIH_SUB_DEPT FROM FIH_SUB_DEPT WHERE DEP_ID=".$row->DEP_ID;
         echo $cUtility->FillComboValuesWSQL($conn, $strSQL,true,  $row->SUB_DEP_ID );
         ?>
       </select>
     </td>
   </tr>
   <tr height="25">
     <td colspan="8" align="center"><input type=submit value="Ara"></td>
   </tr>
 </table>
 </form>
 <table border="0" width="750" cellspacing="0" cellpadding="0" id="table1" >
   <tr>
     <td style="background-repeat: repeat-x; background-position: left top; background-image:url('icbar.GIF')" valign="middle" height="24" width="34"></td>
   </tr>
   <tr>
     <td style="background-repeat: no-repeat; background-position:    center; ; ; background-image:url('../images/altmenubar.gif')" valign="middle" width="997"   height="43" bgcolor="#ECECEC">
       <p align="center"><b><font face="Tahoma" size="2" color="#163E72">Telefon Rehberi</font></b>
	 </td>
   </tr>
   <tr>
     <td valign="top" width="100%" bgcolor="#ECECEC">
     <div align="center">
     <table border="0" width="750" id="table2" cellspacing="0" cellpadding="0" >
	   <tr height="20">
		 <td width="250" bgcolor="#FFFFFF"><font size="2" color="#163E72">Ýsim</font></td>
         <td width="250" bgcolor="#FFFFFF"><font size="2" color="#163E72">Görev</font></td>
		 <td width="60" bgcolor="#FFFFFF"><font size="2" color="#163E72">Dahili Ýþ</font></td>
		 <td width="60" bgcolor="#FFFFFF"><font size="2" color="#163E72">Dahili Ev</font></td>
		 <td width="100" bgcolor="#FFFFFF"><font size="2" color="#163E72">E-Posta</font></td>
	     <td width="50" bgcolor="#FFFFFF"><font size="2" color="#163E72"></font></td>
       </tr>
   <?
    if ($act == "src") {
       $kriter = "";   
     
       $kriter .= $cdb->field_query($kriter, "NAME"                   ,  "LIKE",  "'%$NAME%'"); 
       $kriter .= $cdb->field_query($kriter, "SURNAME"                ,  "LIKE",  "'%$SURNAME%'");
       $kriter .= $cdb->field_query($kriter, "FIHRIST.DEP_ID"         ,  "="   ,  "$DEP_ID");
       $kriter .= $cdb->field_query($kriter, "FIHRIST.SUB_DEP_ID"     ,  "="   ,  "$SUB_DEP_ID");
       
         
       $sql_str  = "SELECT CONTACT_ID,NAME,SURNAME,PERSONAL_EMAIL,EXT_COMP_TEL,EXT_HOME_TEL,FIH_TITLE.TITLE_NAME,
                      CONCAT(FIH_DEPARTMENT.DEP_NAME,IFNULL(CONCAT('/',FIH_SUB_DEPT.FIH_SUB_DEPT),'')) AS BOLUM
                    FROM FIHRIST 
                    LEFT JOIN FIH_TITLE ON FIH_TITLE.TITLE_ID = FIHRIST.TITLE_ID 
                    LEFT JOIN FIH_DEPARTMENT ON FIHRIST.DEP_ID = FIH_DEPARTMENT.DEP_ID
                    LEFT JOIN FIH_SUB_DEPT ON FIH_SUB_DEPT.SUB_DEP_ID = FIHRIST.SUB_DEP_ID";
       if ($kriter != "")
           $sql_str .= " WHERE ". $kriter;  
       if ($ORDERBY) {
           $sql_str .= " ORDER BY ". $ORDERBY ;
       }
       else $sql_str .= " ORDER BY NAME " ;

       $rs = $cdb->get_Records($sql_str, $p, $page_size,  $pageCount, $recCount);
       $stop = $cUtility->myMicrotime();
         
       $i;
       while ($row = mysql_fetch_object($rs)){
            $i++;
            echo " <tr bgcolor=#ECECEC >".CR;
            echo " <td height=\"20\">".$row->TITLE_NAME." ".$row->NAME." ".$row->SURNAME."</td> ".CR;
            echo " <td height=\"20\">".substr($row->BOLUM,0,40)."</td> ".CR;
            echo " <td>".$row->EXT_COMP_TEL."</td>".CR;
            echo " <td>".$row->EXT_HOME_TEL."</td>".CR;
            echo " <td>".$row->PERSONAL_EMAIL."</td>".CR;
            echo " <td><a HREF=\"#\"  onclick=\"javascript:popup('contact_detail.php?id=$row->CONTACT_ID','system',450,250)\">DETAY</td>".CR;
            echo "</tr>".CR;
            list_line(18);
       }
?>   
  </table>
  
<?
  }
?>
<table width="80%" align="center">
    <tr>
        <td align="center">
        <?  echo $cdb->get_paging($pageCount, $p, "contact_search", $ORDERBY);?>
        </td>
    </tr>
</table> 
</div>
</td>
</tr>
	<tr>
		<td style="background-repeat: no-repeat; background-position:  center; ; background-image:url('../images/altmenubar.gif')" width="997" bgcolor="#ECECEC" height="52">
		<p align="center">&nbsp;</td>
		</tr>
</table>
<script>
         function Fillothercombos(my_val){
           FillSecondCombo('SUB_DEP_ID',      'FIH_SUB_DEPT',    '11DEP_ID='+ my_val               , '<?=$SUB_DEP_ID?>'                   , 'SUB_DEP_ID' , '');
         }
         Fillothercombos(document.all('DEP_ID').value);
     
         function submit_form(form_name, page, sortby){
           document.all("ORDERBY").value = sortby;
           if (!sortby)
             document.all("ORDERBY").value = '';
           document.all("p").value = page;
            document.all(form_name).submit();
          }        
</script>
</body>

</html>
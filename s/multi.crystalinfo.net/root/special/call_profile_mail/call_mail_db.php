<?
     require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   require_valid_login();

//Site Admin veya Admin Hakký yokda bu tanýmý yapamamalý
   if (!right_get("SITE_ADMIN") && !right_get("ADMIN")){
        print_error("Bu sayfaya eriþim hakkýnýz yok!");
    exit;
   }
//Site Admin Hakký Yoksa sadece kendisine baðlý kayýtlarý görsün   
     if (right_get("ADMIN") && !right_get("SITE_ADMIN")){
         $SITE_ID = $SESSION['site_id'];
     }
   
   if ($p=="" || $p < 1)
       $p = 1;

   $start = $cUtility->myMicrotime();

   function exist_mail_status($str_sql){
     global $cdb;
      if (!($cdb->execute_sql($str_sql,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      if (mysql_numrows($result)>0){
        return TRUE;
      }else{
        return FALSE;
      }
   }
   if($act=="save" &&  is_numeric($id)){
      $kriter = "";
      $str_sql = "SELECT * FROM CALL_PROF_MAILING";
      $kriter .= $cdb->field_query($kriter,"SITE_ID",      "=",      "'$SITE_ID'");
      $kriter .= $cdb->field_query($kriter,"TO_EMAIL",      "=",      "'$TO_EMAIL'");
      if($REP_TYPE==2){
        $kriter .= $cdb->field_query($kriter,"DEPT_ID",      "=",      "'$DEPT_ID'");
      }elseif($REP_TYPE==3){
        $kriter .= $cdb->field_query($kriter,"EXT_NO",      "=",      "'$EXT_NO'");
      }
      $kriter .= $cdb->field_query($kriter,"ID",      "<>",      "'$id'");
      $str_sql = $str_sql." WHERE ".$kriter;
      if(exist_mail_status($str_sql)){
         print_error("Buna benzer bir kayýt daha önce girilmiþ. Ýkinci kez giremezsiniz...");
      }
      $args[] = array("SITE_ID",        $SITE_ID,          cFldWQuote);
      $args[] = array("REP_TYPE",       $REP_TYPE,         cFldWQuote);
      $args[] = array("TO_EMAIL",       $TO_EMAIL,         cFldWQuote);
      if($REP_TYPE==3){
        $DEPT_ID = "";
      }elseif($REP_TYPE==2){
        $EXT_NO = "";
      }else{
        $DEPT_ID = "";
        $EXT_NO = "";
      }
      $args[] = array("EXT_NO",         $EXT_NO,           cFldWQuote);
      $args[] = array("DEPT_ID",        $DEPT_ID,          cFldWQuote);
      $args[] = array("ID",$id, cReqWoQuote);
      $sql_str =  $cdb->UpdateString("CALL_PROF_MAILING", $args);
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      $actstr = "act=save&id=".$id."&p=".$p;
   }elseif($act=="saveas"){
      $kriter = "";
      $str_sql = "SELECT * FROM CALL_PROF_MAILING";
      $kriter .= $cdb->field_query($kriter,"SITE_ID",      "=",      "'$SITE_ID'");
      $kriter .= $cdb->field_query($kriter,"TO_EMAIL",      "=",      "'$TO_EMAIL'");
      if($REP_TYPE==2){
        $kriter .= $cdb->field_query($kriter,"DEPT_ID",      "=",      "'$DEPT_ID'");
      }elseif($REP_TYPE==3){
        $kriter .= $cdb->field_query($kriter,"EXT_NO",      "=",      "'$EXT_NO'");
      }
      $str_sql .= " WHERE ".$kriter;
      if(exist_mail_status($str_sql)){
         print_error("Buna benzer bir kayýt daha önce girilmiþ. Ýkinci kez giremezsiniz...");
      }
      $args[] = array("SITE_ID",        $SITE_ID,          cFldWQuote);
      $args[] = array("REP_TYPE",       $REP_TYPE,         cFldWQuote);
      $args[] = array("TO_EMAIL",       $TO_EMAIL,         cFldWQuote);
      if($REP_TYPE==3){
        $args[] = array("EXT_NO",         $EXT_NO,           cFldWQuote);
      }elseif($REP_TYPE==2){
        $args[] = array("DEPT_ID",        $DEPT_ID,          cFldWQuote);
      }
      $sql_str =  $cdb->InsertString("CALL_PROF_MAILING", $args);
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      $id = mysql_insert_id();
      $actstr = "act=save&id=".$id."&p=".$p;
   }elseif($act=="del" &&  is_numeric($id)){
      $sql_str =  "DELETE FROM CALL_PROF_MAILING WHERE ID=".$id;
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      $act = "new";
      $id = "";
      $actstr = "act=saveas";
   }elseif($act=="upd" &&  is_numeric($id)){
      $actstr = "act=save&id=".$id."&p=".$p;
   }elseif($act=="new" || $act == ""){
      $actstr = "act=saveas"."&p=".$p;
   }

   if($act<>"new" &&  is_numeric($id)){
      $sql_str = "SELECT * FROM CALL_PROF_MAILING WHERE ID=".$id;
      if (!($cdb->execute_sql($sql_str,$result,$error_msg))){
         print_error($error_msg);
         exit;
      }
      if (mysql_numrows($result)>0){
        $row = mysql_fetch_object($result);
      }else{
       print_error("Belirtilen Kayýt Bulunamadý");
         exit;    
      }
   }
    if($act=='upd')
        $SITE_ID = $row->SITE_ID;
    else
        $SITE_ID = $SESSION['site_id'];
   
   cc_page_meta();
    fillSecondCombo();
   echo "<center>";
   page_header();
   echo "<br><br>";
   table_header("Analiz mailleri","60%");
?>
     <form name="save_mail" method="post" action="call_mail_db.php?<?=$actstr?>">
    <input type="hidden" name="p1" VALUE="<?echo $p?>">
   <table cellpadding="0" cellspacing="0" border="0" width="80%" align="center">
        <tr>
           <td width="40%" class="td1_koyu">Site Adý</td>
            <td>
             <select name="SITE_ID" class="select1" style="width:250" <?if (!right_get("SITE_ADMIN")) {echo "disabled";}?> onchange="FillSecondCombo('DEPT_ID', 'DEPT_NAME', '01SITE_ID='+ this.value , '' , 'DEPT_ID' , this.value)">
                 <?
                        $strSQL = "SELECT SITE_ID, SITE_NAME FROM SITES ";
                        echo $cUtility->FillComboValuesWSQL($conn, $strSQL, true, $SITE_ID);
                    ?>
              </select>
      </td>
       </tr>
       <tr> 
          <td class="td1_koyu" width="40%">Rapor Türü</td>
           <td>
        <select class="select1" name="REP_TYPE" onchange="javascript:show_hide_fields(this.value)">
                        <OPTION value="-1" <?=($row->REP_TYPE==-1)?"selected":""?>>--Seçiniz--</OPTION>
                        <OPTION value="1" <?=($row->REP_TYPE==1)?"selected":""?>>Site Raporu</OPTION>
                        <OPTION value="2" <?=($row->REP_TYPE==2)?"selected":""?>>Departman Raporu</OPTION>
                        <OPTION value="3" <?=($row->REP_TYPE==3)?"selected":""?>>Dahili Raporu</OPTION>
               </select>
      </td>    
    </tr>
        <tr id="dept_gir" style="display:'<?=($row->REP_TYPE==2)?"":"none"?>';"> 
           <td width="30%" class="td1_koyu">Departman</td>
            <td width="70%">
                    <select name="DEPT_ID" class="select1" style="width:250">
                        <OPTION value="-1">--Seçiniz--</OPTION>
                    </select>
            </td>
       </tr>
       <tr id="dahili_gir" style="display:'<?=($row->REP_TYPE==3)?"":"none"?>';"> 
           <td width="30%" class="td1_koyu">Dahili No</td>
           <td width="70%"><input class="input1" type="text" name="EXT_NO" VALUE="<?=$row->EXT_NO?>" ></td>
       </tr>
       <tr> 
           <td width="30%" class="td1_koyu">Email Adresi</td>
           <td width="70%"><input class="input1" type="text" name="TO_EMAIL" VALUE="<?=$row->TO_EMAIL?>" size=25></td>
       </tr>
       <tr> 
            <td width="30%">&nbsp;</td>
           <td width="70%" class="td1_koyu">
       <a href="javascript:submit_form2('save_mail');"><img name="Image631" border="0" src="<?=IMAGE_ROOT?>kaydet.gif"></a>
      </td>
        </tr>
    </table>
     <table width="100%"> 
    <tr>
      <td width="100%" align="right">
      <a href="call_mail_db.php?act=new&p=<?=$p?>"><img border="0" src="<?=IMAGE_ROOT?>yeni_kayit.gif" style="cursor:hand;"></a>
      <?if($act<>"new"){?>
      <a href="call_mail_db.php?act=del&id=<?=$id?>&SITE_ID=<?=$SITE_ID?>&p=<?=$p?>"><img border="0" src="<?=IMAGE_ROOT?>kayit_sil.gif" style="cursor:hand;"></a>
      <?}?>
      </td>
    </tr>  
  </table>
   </form>
<form name="search_mail" method="post" action="call_mail_db.php">
    <input type="hidden" name="p" VALUE="<?echo $p?>">
    <input type="hidden" name="ORDERBY" value="<?=$ORDERBY?>">
    <input type="hidden" name="act" value="<?=(is_numeric($id))?"upd":"new"?>">
    <input type="hidden" name="id" value="<?=$id?>">
</form>
<?
   table_footer();
         $kriter = "";
         $rep_type_str[1] = "Site Raporu";
         $rep_type_str[2] = "Departman Raporu";
         $rep_type_str[3] = "Dahili Raporu";
/*         if ($SITE_ID<>'-1'){
             $kriter .= $cdb->field_query($kriter, "SITES.SITE_ID",       "=",    "'$SITE_ID'");
         }
         if ($OptTypeid<>'-1'){
             $kriter .= $cdb->field_query($kriter, "TRUNKS.OPT_TYPE_ID",       "=",    "'$OptTypeid'");
         }
         $kriter .= $cdb->field_query($kriter, "TRUNKS.MEMBER_NO", "=", "'$MEMBER_NO'");
         $kriter .= $cdb->field_query($kriter, "TRUNKS.TRUNK_NAME", "LIKE", "'%$TRUNK_NAME%'");
         $kriter .= $cdb->field_query($kriter, "TRUNKS.PHONE_NUMBER", "LIKE", "'%$PHONE_NUMBER%'");*/
      
         $sql_str  = "SELECT *
                      FROM CALL_PROF_MAILING 
                      ORDER BY TO_EMAIL, REP_TYPE";
         
/*         if ($kriter != "")
               $sql_str .= " WHERE ". $kriter;  
       
         if ($ORDERBY) {
               $sql_str .= " ORDER BY ". $ORDERBY ;      
         }*/
         
         $rs = $cdb->get_Records($sql_str, $p, $page_size,  $pageCount, $recCount);    
         $stop = $cUtility->myMicrotime();
         if (!($cdb->execute_sql($sql_str,$rsltTest,$error_msg))){
             print_error($error_msg);
             exit;
         }
         if(mysql_numrows($rsltTest)>0){
?>
<br><br>
<?
table_arama_header("90%");
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
    <td class="sonuc" width="50%" height="20"><? echo $cdb->calc_current_page($p, $recCount, $page_size);?></td>
    <td class="sonuc" align="right" width="50%" height="20"><? $cdb->show_time(($stop -$start)); ?></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr class="header_beyaz">
        <td width="5%" align="center">ID</td>
        <td>Site Adý</td>
        <td>Rapor Tipi</td>
        <td>Alýnacak Rapor</td>        
        <td>Email Adresi</td>
        <td>Güncelle</td>
    </tr>
<? 
      $i;
      while($row1 = mysql_fetch_object($rs)){
         $i++;
      echo " <tr class=\"".bgc($i)."\">".CR;
            echo " <td height=\"20\" align=\"center\">$row1->ID</td> ".CR;
            echo " <td>".substr(get_site_name($row1->SITE_ID),0,25)."</td>".CR;
            echo " <td>".$rep_type_str[$row1->REP_TYPE]."</td>".CR;
            if($row1->REP_TYPE==1){
              $req_rept = get_site_name($row1->SITE_ID);
            }elseif($row1->REP_TYPE==2){
              $req_rept = get_dept_name($row1->DEPT_ID, $row1->SITE_ID);
            }elseif($row1->REP_TYPE==3){
              $req_rept = $row1->EXT_NO." - ".get_ext_name2($row1->EXT_NO, $row1->SITE_ID);
            }
            echo " <td>".$req_rept."</td>".CR;
            echo " <td>".$row1->TO_EMAIL."</td>".CR;
      echo " <td><a HREF=\"call_mail_db.php?act=upd&id=".$row1->ID."&p=$p\">Güncelle</td>".CR;
            echo " </tr>".CR;
      list_line(18);
      }
?>
</table>
<?table_arama_footer(); ?>
<table width="80%" align="center">
    <tr>
        <td align="center">
        <?
        echo $cdb->get_paging($pageCount, $p, "search_mail", $ORDERBY);
        ?></td>
    </tr>
</table>
<?}page_footer(0);?>
<script language="javascript" src="/scripts/form_validate.js"></script>
<script language="JavaScript" type="text/javascript">
<!--

         <?if($act=='upd'){?>   
            FillSecondCombo('DEPT_ID', 'DEPT_NAME', '01SITE_ID='+ '<?=$row->SITE_ID?>' , '<?=$row->DEPT_ID?>' , 'DEPT_ID' , '<?=$row->DEPT_ID?>')
         <?}else{?>
            FillSecondCombo('DEPT_ID', 'DEPT_NAME', '01SITE_ID='+ document.all('SITE_ID').value , '<?=$DEPT_ID?>' ,'DEPT_ID' , document.all('DEPT_ID').value)         
         <?}?>
    function submit_form(form_name, page, sortby){
          document.all("ORDERBY").value = sortby;
          if (!sortby)
                document.all("ORDERBY").value = '';

          document.all("p").value = page;
          document.all(form_name).submit();
    }
    function submit_form2(form_name){
          if(document.all('SITE_ID').value == -1){
            alert("Lütfen Site Adýný Seçin!");
          }else if(document.all('TO_EMAIL').value == ''){
            alert("Lütfen email adresini girin!");
          }else{  
              if(document.all('REP_TYPE').value == -1){
                alert("Rapor Tipini Belirtmeniz Gerekli");
              }else if(document.all('REP_TYPE').value == 2){
                if(document.all('DEPT_ID').value == -1){
                  alert("Lütfen Bir Departman Seçin");
                }else{
                  document.all(form_name).submit();
                }
              }else if(document.all('REP_TYPE').value == 3){
                if(document.all('EXT_NO').value == ''){
                  alert("Lütfen Bir Dahili Numarasý Girin");
                }else{
                  document.all(form_name).submit();
                }
              }else if(document.all('REP_TYPE').value == 1){
                document.all(form_name).submit();
              }
          }
    }
    function show_hide_fields(myVal){
      if(myVal==1){
        document.all('dept_gir').style.display = 'none';
        document.all('dahili_gir').style.display = 'none';
      }else if(myVal==2){
        document.all('dept_gir').style.display = '';
        document.all('dahili_gir').style.display = 'none';
      }else if(myVal==3){
        document.all('dept_gir').style.display = 'none';
        document.all('dahili_gir').style.display = '';
      }else{
        document.all('dept_gir').style.display = '';
        document.all('dahili_gir').style.display = '';
      }
    }
//-->
</script>

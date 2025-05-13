<?php
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once($CHART_ROOT."phpchartdir.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  $capp = new  application();
  $conn = $cdb->getConnection();
  $my_s = new Session;

  $t0 = strftime("%Y-%m", mktime(0,0,0,date("m")-1,date("d"),date("y")));

  $strsql = " SELECT TIME_STAMP_MONTH, SUM(LOC_PRICE) AS LOC_PRICE, SUM(NAT_PRICE) AS NAT_PRICE,
                SUM(GSM_PRICE) AS GSM_PRICE, SUM(INT_PRICE) AS INT_PRICE, SUM(OTH_PRICE) AS OTH_PRICE , TIME_STAMP_YEAR
              FROM MONTHLY_ANALYSE
              WHERE TYPE = 'general' AND CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t0."' 
		      GROUP BY TIME_STAMP_MONTH";
  //echo $strsql;exit;
  $cdb->execute_sql($strsql, $result, $errmsg);
  $row = mysql_fetch_array($result);    
  setlocale(LC_TIME, 'tr_TR');    
  $data = array($row['LOC_PRICE'], $row['NAT_PRICE'], $row['GSM_PRICE'], $row['INT_PRICE'], $row['OTH_PRICE']);
  $c = new PieChart(440,300);
  $title = turkish2utf(strftime("%B %Y", mktime(0, 0, 0, $row['TIME_STAMP_MONTH']-1,32, $row['TIME_STAMP_YEAR']))." Tutar Daðýlýmý ");
  $labels[] = turkish2utf("Þ.Ýçi");
  $labels[] = turkish2utf("Þ.Arasý");
  $labels[] = "GSM";
  $labels[] = turkish2utf("U.Arasý");
  $labels[] = turkish2utf("Diðer");
  $c->setBackground($c->gradientColor($goldGradient), -1, 2);
  $c->setPieSize(220, 130, 90);
  $c->set3D();
  $c->setData($data, $labels);
  $c->setLabelLayout(SideLayout, 5);
  $legendObj = $c->addLegend(290, 230, false, "verdana.ttf", 8);
  $labelStyleObj = $c->setLabelStyle("verdana.ttf", 8);
  $labelStyleObj->setBackground(SameAsMainColor, Transparent, 2);
  $titleobj = $c->addTitle($title, "verdana.ttf",14);
  $titleobj->setBackground($c->gradientColor($redMetalGradient), -1, 1);
  $total = $data[0]+$data[1]+$data[2]+$data[3]+$data[4];
  $note = turkish2utf(strftime("%B %Y", mktime(0, 0, 0, $row['TIME_STAMP_MONTH']-1,32, 04))." Görüþme Tutarýnýz\n".write_price($total)." TL'dir");
  $x = $c->addText(20, 240, $note, "verdana.ttf", 10, 0x333366, TopLeft);
  $sectorObj = $c->sector(0);
  $sectorObj->setExplode();
  
  header("Content-type: image/png");
  print($c->makeChart2(PNG));
?>

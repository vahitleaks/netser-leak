<?php
  require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
  require_once($CHART_ROOT."phpchartdir.php");
  $cUtility = new Utility();
  $cdb = new db_layer();
  $capp = new  application();
  $conn = $cdb->getConnection();
  $my_s = new Session;

  $t0 = strftime("%Y-%m", mktime(0,0,0,date("m")-1,date("d"),date("y")));
  $t1 = strftime("%Y-%m", mktime(0,0,0,date("m")-2,date("d"),date("y")));

  $strsql1 = " SELECT SITE_ID, SITE_NAME FROM SITES";
  $cdb->execute_sql($strsql1, $result1, $errmsg);
  $sites = array();
  $i = 0;
  while($row1 = mysql_fetch_object($result1)){
    $sites[$row1->SITE_ID] = substr($row1->SITE_NAME,0,13);
    $i++;
  }
  $cnt = $i;
  $strsql = " SELECT SITE_ID, TIME_STAMP_MONTH, 
                     (LOC_PRICE + NAT_PRICE + GSM_PRICE + INT_PRICE + OTH_PRICE) AS TOTAL
              FROM MONTHLY_ANALYSE 
              WHERE TYPE = 'general' 
				     AND (CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t0."' OR
					      CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t1."')
		      ORDER BY TIME_STAMP_MONTH, SITE_ID ASC";
  //echo $strsql;exit;

  $a = 1000000000;
  $cdb->execute_sql($strsql, $result, $errmsg);
  while($row = mysql_fetch_object($result)){
	$month[$row->TIME_STAMP_MONTH][$row->SITE_ID] = $row->TOTAL;
    $i++;
  }

  // site key içinde dön
  foreach ($sites as $value => $key){
    $label[] = turkish2utf($key);
    foreach ($month as $value2 => $key2){
	    $data[$value2][] = $key2[$value]/$a;
	}
  }
  
  ///Aylarýn tesbiti için
  $tlmonth = strftime("%m", mktime(0,0,0,date("m")-1,date("d"),date("y")));
  $tpmonth = strftime("%m", mktime(0,0,0,date("m")-2,date("d"),date("y")));
  $tlast = (int)$tlmonth;
  $tprev = (int)$tpmonth;

  setlocale(LC_TIME, 'tr_TR');
  $gecen = strftime("%B", mktime(0, 0, 0, $tlmonth-1,32, 04));
  $onceki = strftime("%B", mktime(0, 0, 0, $tlmonth-2,32, 04));

  $c = new XYChart(940, 365, 0xf0e090, 0x0, 1);

  $titleObj = $c->addTitle(turkish2utf(" Bölgelere Göre Tutar Daðýlýmý\n"),"verdana.ttf" ,13,0xFFFFFF);
  $titleObj->setBackground(0x807040, -1, 1);
  $plotAreaObj = $c->setPlotArea(55, 45, 820, 240, 0xfff0c0, -1, 0xa08040, 0xa08040, 0xa08040);
  $plotAreaObj->setBackground(0xfff0c0, 0xffffff, 1);
  
  $legendObj = $c->addLegend(55, 25, false, "verdana.ttf", 8);
  //$legendObj = $c->addLegend(55, 25, false, "", 8);
  $legendObj->setBackground(Transparent);

  $c->yAxis->setTitle(" Tutar ( Milyar )","verdana.ttf",10,0x000000);

  $c->yAxis->setTopMargin(20);

  $myxAxis = $c->xAxis->setLabels($label);
  $c->xAxis->setTitle(turkish2utf(" Çaðrý Türü "),"verdana.ttf", 14);
  $myxAxis->setFontStyle("verdana.ttf");  $c->xAxis->setTitle(turkish2utf(" Bölgeler "),"verdana.ttf", 9);
  $myxAxis->setFontAngle(45);

  $c->xAxis->setWidth(2);
  $c->yAxis->setWidth(2);

  $layer = $c->addLineLayer();

  $layer->setLineWidth(3);   
  $dataSetObj = $layer->addDataSet($data[$tlast], 0xc0, turkish2utf($onceki));
  $dataSetObj->setDataSymbol(DiamondSymbol, 9, 0xffff00);
  $dataSetObj = $layer->addDataSet($data[$tprev], 0x982810, turkish2utf($gecen));
  $dataSetObj->setDataSymbol(CircleSymbol, 9, 0xf040f0);

  header("Content-type: image/png");
  print($c->makeChart2(PNG));
?>

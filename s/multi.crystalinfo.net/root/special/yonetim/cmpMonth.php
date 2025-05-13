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

      $strsql = " SELECT TIME_STAMP_MONTH,CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) AS MONTH, SUM(LOC_PRICE) AS LOC_PRICE, SUM(NAT_PRICE) AS NAT_PRICE, 
                    SUM(GSM_PRICE) AS GSM_PRICE, SUM(INT_PRICE) AS INT_PRICE, SUM(OTH_PRICE) AS OTH_PRICE 
                  FROM MONTHLY_ANALYSE
		          WHERE TYPE = 'general' 
				     AND (CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t0."' OR
					      CONCAT(TIME_STAMP_YEAR,'-',LPAD(TIME_STAMP_MONTH,2,'0')) = '".$t1."')
			      GROUP BY MONTH ORDER BY MONTH";
     //echo $strsql;exit;
     $cdb->execute_sql($strsql, $result, $errmsg);

     ///Ýlk fetch. Önceki ay alýnýyor
     $row = mysql_fetch_array($result);
     setlocale(LC_TIME, 'tr_TR');
     $a = 1000000000;
	 $total1 = $row['LOC_PRICE']+$row['NAT_PRICE']+$row['GSM_PRICE']+$row['INT_PRICE']+$row['OTH_PRICE'];
	 ///Rakam yüksek ise rakamlar belli oranda azaltýlmalý
     $total1 = $total1 / $a;
	 $data1 = array($row['OTH_DUR']/$a, $row['INT_PRICE']/$a, $row['GSM_PRICE']/$a, $row['NAT_PRICE']/$a, $row['LOC_PRICE']/$a,);
	 $onceki = strftime("%B", mktime(0, 0, 0, $row['TIME_STAMP_MONTH']-1 ,32, 04));

     ///Ýkinci fetch. Sonraki ay alýnýyor
     $row = mysql_fetch_array($result);
     $total0 = $row['LOC_PRICE']+$row['NAT_PRICE']+$row['GSM_PRICE']+$row['INT_PRICE']+$row['OTH_PRICE'];
     $total0 = $total0 / $a;
     $gecen = strftime("%B", mktime(0, 0, 0, $row['TIME_STAMP_MONTH']-1,32, 04));
     $data0 = array($row['OTH_PRICE']/$a, $row['INT_PRICE']/$a, $row['GSM_PRICE']/$a, $row['NAT_PRICE']/$a, $row['LOC_PRICE']/$a);


     $c = new XYChart(480, 300, 0xe0e0ff, 0xccccff, 1);
     $leg =  $c->addLegend(8, 250, 200, "verdana.ttf",7);
     $labels[] = turkish2utf("Diðer");
     $labels[] = turkish2utf("U.Arasý");
     $labels[] = "GSM";
     $labels[] = turkish2utf("Þ.Arasý");
     $labels[] = turkish2utf("Þ.Ýçi");

	 $title = turkish2utf($onceki."-".$gecen." Tutar Daðýlýmý");
     $titleObj = $c->addTitle($title, "verdana.ttf", 14);
     $titleObj->setBackground(0x9999ff);
     $c->setPlotArea(80, 35, 370, 200, 0xffffff);
	 $c->swapXY();
     $c->yAxis->setTitle("Tutar (Milyar)", "verdana.ttf", 11);
     $c->xAxis->setTickLength(7);
	 $c->yAxis->setTopMargin(20);
     $myxAxis = $c->xAxis->setLabels($labels);
     $c->xAxis->setTitle(turkish2utf(" Çaðrý Türü "),"verdana.ttf", 14);
     $myxAxis->setFontStyle("verdana.ttf");
     $layer = $c->addBarLayer2(Side, 5);
     $layer->addDataSet($data0, 0xff6666, turkish2utf($gecen));
     $layer->addDataSet($data1, 0x6666ff, turkish2utf($onceki));

     $c->layout();

     header("Content-type: image/png");
     print($c->makeChart2(PNG));
?>

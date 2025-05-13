<?php
    require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
    require_once($CHART_ROOT."phpchartdir.php");
    $cUtility = new Utility();
    $cdb = new db_layer();
    $capp = new  application();
    $conn = $cdb->getConnection();
    $my_s = new Session;
    $my_s->test_login();

    if($my_s->logged_in){
      require_valid_login();
      $SITE_ID = $SESSION['site_id'];
    }else{
      $SITE_ID = "1";//Kayýt getirmesin.
    }
  
    $res =  get_heavy_sql_result(11);

    $p = 0;$x=0;
    while($x<sizeof($res)){
    if($res[$x][0][1]==$SITE_ID){
            $data[] = $res[$x][1][1]/1000000;
            $labels[] = turkish2utf($res[$x][2][1]);
            $x++;
    }
    }
    $c = new XYChart(419, 275, 0xF0F0E8, 0x0, 1);
    //$c = new XYChart(430, 250, "", Transparent);
    #Set background color to pale yellow 0xffff80, with a black edge and a 1
    #pixel 3D border
    //$c->setBackground(0xF0F8FF);
    #Set the plotarea at (55, 45) and of size 420 x 210 pixels, with white
    #background. Turn on both horizontal and vertical grid lines with light
    #grey color (0xc0c0c0)
    $c->setPlotArea(50, 40, 350, 190, 0xffffff, -1, 0xa08040, 0xa08040, 0xa08040);
    $titleObj = $c->addTitle(turkish2utf(" Son 10 Günün Çaðrý Özeti "), "verdana.ttf", 10,0xffffff);
    $titleObj->setBackground(0x807040, -1, 1);	
	//$c->setPlotArea(50, 40, 350, 165, 0xffffff, -1, -1, 0xc0c0c0, -1);
    #Add a legend box at (55, 25) (top of the chart) with horizontal layout.
    #Use 8 pts Arial font. Set the background and border color to Transparent.
    $legendObj = $c->addLegend(40, 15, false, "verdana.ttf", 8);

    $legendObj->setBackground(Transparent, -1, 0x000000);
//  $legendObj->setBackground(0x9cbade, -1, 0xffffff);
    #Add a title box to the chart using 11 pts Arial Bold Italic font. The text
    #is white (0xffffff) on a dark red (0x800000) background, with a 1 pixel 3D
    #border.
    //$titleObj = $c->addTitle(turkish2utf("Son Bir Hafta Konuþma Ücretleri"), "verdana.ttf", 10, 0x2C5783 );
    //$titleObj->setBackground(0x9cbade, -1, 1);
    #Add a title to the y axis
    $c->yAxis->setTitle("Mio TL");
    #Set the labels on the x axis
    $c->xAxis->setLabels($labels);
    #Add a title to the x axis
    $c->xAxis->setTitle(turkish2utf("Günler"));
    $c->xAxis->setWidth(2);
    $c->yAxis->setWidth(2);

    $layer = $c->addAreaLayer();
    $dataSetObj = $layer->addDataSet($data,$c->gradientColor(0, 30, 0, 255, 0xffffc8, 0x40ffffff));
    $layer1 = $c->addLineLayer();
    $layer1->setLineWidth(2);
    $dataSetObj1 = $layer1->addDataSet($data, 0x807040, "");
    $dataSetObj1->setDataSymbol(SquareSymbol, 7, 0xffff00);
    //$layer->addDataSet($data, -1, turkish2utf("$label0"));
	#output the chart in PNG format
    header("Content-type: image/png");
    print($c->makeChart2(PNG));
    ?>


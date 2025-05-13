<?php
   require_once(dirname($DOCUMENT_ROOT)."/cgi-bin/functions.php");
   include($CHART_ROOT."phpchartdir.php");
   $cUtility = new Utility();
   $cdb = new db_layer();
   $capp = new  application();
   require_valid_login();
   $SITE_ID = $SESSION['site_id'];
   if($p==""){$p='price';}//p gelmemiþse ücret olsun
   
   switch ($p){
     case 'price':
       $fld = 3;
       $header = "Tutar";
     break;
     case 'sure':
       $fld = 4;
       $header = "Süre";
     break;
     case 'adet':
       $fld = 5;
       $header = "Adet";
     break;
   }
   
   if ($fld==""){exit;}//fld hala atanmamýþsa devam etmemeli
   $res_arr = get_heavy_sql_result(16);
   if (is_array($res_arr)){
     for($i=0;$i<=sizeof($res_arr);$i++){//Gelen dizi içinde benim sitem bulunmalý
     if ($res_arr[$i][0][1]==$SITE_ID){ //Ýlgili kaydýn ilk alanýnýn deðeri
       if ($res_arr[$i][1][1] < 4) {
         $data[] = $res_arr[$i][$fld][1]; // Þehir içi Süre/Tutar/Adet
             $labels[]  = turkish2utf($res_arr[$i][2][1]);
           }
       }
     }
   }

      $colors = array(0x678acb, 0xffab00, 0x00bbb4, 0x9dc9f0, 0xd8005f,
      0x594330, 0xa0bdc4);
      
      #The data for the pie chart
      //$data = array(35, 30, 25, 7, 6, 5, 4, 3, 2, 1);
      #The labels for the pie chart
      //$labels = array("Labor", "Production", "Facilities", "Taxes", "Misc",
      //"Legal", "Insurance", "Licenses", "Transport", "Interest");
      #Create a PieChart object of size 500 x 230 pixels
      $c = new PieChart(165, 150, "" );
      #Set background color to light blue (0xccccff) with a 1 pixel 3D border
      $c->setBackground(0xBED1E7, 0xBED1E7, 0);
      #Add a title box using 1Times Bold Italic/14 points/blue (0x9999ff) as font
//      $titleObj = $c->addTitle(turkish2utf("Outbound Daðýlýmlar"), "verdana.ttf", 10);

//      $titleObj->setBackground(0x9cbade, -1, 1);
      #Set the center of the pie at (250, 120) and the radius to 100 pixels
      $c->setPieSize(80, 40, 30);
      #Draw the pie in 3D
      $c->set3D(8, 60);
      #Use the side label layout method  SideLayout
      $c->setLabelLayout(SideLayout, 2);
      $leg =  $c->addLegend(40, 80,"", "verdana.ttf",7);
      $leg->setBackground(0xBED1E7,0xBED1E7);
      $titleObj = $c->addTitle(turkish2utf("[ $header ]"), "verdana.ttf", 7);

      #Set the label box the same color as the sector with a 1 pixel 3D border
      $labelStyleObj = $c->setLabelStyle("verdana.ttf", 7);
      $c->setLabelFormat("&percent&%");
      
      $labelStyleObj->setBackground(SameAsMainColor, Transparent, 1);
      #Set the border color of the sector the same color as the fill color. Set
      #the line color of the join line to black (0x0)
      $c->setLineColor(SameAsMainColor, 0x0);
      #Set the start angle to 135 degrees may improve layout when there are many
      #small sectors at the end of the data array (that is, data sorted in
      #descending order). It is because this makes the small sectors position
      #near the horizontal axis, where the text label has the least tendency to
      #overlap. For data sorted in ascending order, a start angle of 45 degrees
      #can be used instead.
      $c->setStartAngle(135);
      #Set the pie data and the pie labels
      $c->setData($data, $labels);
      $c->setColors2(DataColor, $colors);
      #output the chart in PNG format
      header("Content-type: image/png");
      print($c->makeChart2(PNG));
?>


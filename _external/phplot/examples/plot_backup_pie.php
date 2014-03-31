<?php

$r = $_GET['r'];
$o = $_GET['o'];
$g = $_GET['g'];
$gr = $_GET['gr'];

$data = array(0 => array("Backup statuses", $r,$o,$g,$gr));


////////////////////////////////////////////////

//Required Settings
    include("../phplot.php");
    $graph = new PHPlot(400, 400);
    $graph->SetDataType('text-data');  // Must be first thing

    //print_r($data);
    
    $graph->SetDataValues($data);

//Optional Settings (Don't need them)

//  $graph->SetTitle("This is a\n\rmultiple line title\n\rspanning three lines.");
    $graph->SetTitle("Overall backup statuses");
    //$graph->SetXTitle($xlbl, $which_xtitle_pos);
    //$graph->SetYTitle($ylbl, $which_ytitle_pos);
    $graph->SetLegend(array("Backup Error","Tape Error","Success","Not reporting"));

    $graph->SetFileFormat("jpg");
    $graph->SetPlotType("pie");

    //$graph->SetUseTTF($which_use_ttf);

    $graph->SetYTickIncrement(1);
    $graph->SetXTickIncrement(1);
    $graph->SetXTickLength(1);
    $graph->SetYTickLength(1);
    $graph->SetXTickCrossing(1);
    $graph->SetYTickCrossing(1);
    //$graph->SetXTickPos("plotright");
    //$graph->SetYTickPos("plotright");


    $graph->SetShading(5);
    $graph->SetLineWidth(1);
    $graph->SetErrorBarLineWidth(1);

    //$graph->SetDrawDashedGrid($which_dashed_grid);
 /*   switch($which_draw_grid) {
    case 'x':
        $graph->SetDrawXGrid(TRUE);
        $graph->SetDrawYGrid(FALSE);
        break;
    case 'y':
        $graph->SetDrawXGrid(FALSE);
        $graph->SetDrawYGrid(TRUE);
        break;
    case 'both':
        $graph->SetDrawXGrid(TRUE);
        $graph->SetDrawYGrid(TRUE);
        break;
    case 'none':
        $graph->SetDrawXGrid(FALSE);
        $graph->SetDrawYGrid(FALSE);
    }

    $graph->SetXTickLabelPos($which_xtick_label_pos);
    $graph->SetYTickLabelPos($which_ytick_label_pos);
    $graph->SetXDataLabelPos($which_xdata_label_pos);
    $graph->SetYDataLabelPos($which_ydata_label_pos);

    // Please remember that angles other than 90 are taken as 0 when working fith fixed fonts.
    $graph->SetXLabelAngle($which_xlabel_angle);
    $graph->SetYLabelAngle($which_ylabel_angle);

    // Tests...
    //$graph->SetLineStyles(array("dashed","dashed","solid","solid"));
    //$graph->SetPointShapes(array("plus", "circle", "trianglemid", "diamond"));
    //$graph->SetPointSizes(array(15,10));

    $graph->SetPointShapes($which_point);
    $graph->SetPointSizes($which_point_size);
    $graph->SetDrawBrokenLines($which_broken);

    // Some forms in format_chart.php don't set this variable, suppress errors.
    @ $graph->SetErrorBarShape($which_error_type);

    $graph->SetXAxisPosition($which_xap);
    $graph->SetYAxisPosition($which_yap);
    $graph->SetPlotBorderType($which_btype);

    if ($maxy_in) {
    if ($which_data_type = "text-data") {
        $graph->SetPlotAreaWorld(0,$miny_in,count($data),$maxy_in);
        }
    }
*/
/*
//Even more settings

    $graph->SetPlotAreaWorld(0,100,5.5,1000);
    $graph->SetPlotAreaWorld(0,-10,6,35);
    $graph->SetPlotAreaPixels(150,50,600,400);
*/
    $graph->SetDataColors(
            array("red","orange","green","gray"),   //Data Colors
            array("black")                          //Border Colors
    );
/*
    $graph->SetPlotBgColor(array(222,222,222));
    $graph->SetBackgroundColor(array(200,222,222)); //can use rgb values or "name" values
    $graph->SetTextColor("black");
    $graph->SetGridColor("black");
    $graph->SetLightGridColor(array(175,175,175));
    $graph->SetTickColor("black");
    $graph->SetTitleColor(array(0,0,0)); // Can be array or name
*/

//      $graph->SetPrintImage(false);
      $graph->DrawGraph();
//      xdebug_dump_function_profile(XDEBUG_PROFILER_FS_SUM);
?>

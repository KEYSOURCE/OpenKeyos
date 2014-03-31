<?php

class WarrantyContractModel {
    public $name = "";
    public $description = "";
    public $provider = "";
    public $start_date = "";
    public $end_date = "";
    public $days_left = "";
    public $status = "";

    public static $dellURL = "http://support.dell.com/support/topics/global.aspx/support/my_systems_info/details?c=us&l=en&s=gen&~tab=1&ServiceTag=";
    public static $hpURL = "http://h20000.www2.hp.com/bizsupport/TechSupport/WarrantyResults.jsp?country=US&sn=";

    public static $dell_date_format = 'n#j#Y';
    public static $hp_date_format = 'd M Y';

    function  __construct($name, $provider, $start_date, $end_date, $days_left, $status) {
        $this->name = $name;
        $this->provider = $provider;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->days_left = $days_left;
        $this->status = $status;

        
    }

    public static function normalize_date($date, $format){
        //$pdate = date_parse_from_format($format, $date);
        //return mktime(0,0,0,$pdate['month'], $pdate['day'], $pdate['year']);
        return strtotime($date);
    }

    public static function get_dell_contracts_table($service_tag){
        $link = self::$dellURL.$service_tag;
        $oldSetting = libxml_use_internal_errors( true );
        libxml_clear_errors();

        $html = new DOMDocument();
        $html->loadHtmlFile($link);
        $xpath = new DOMXPath( $html );
        $elements = $xpath->query( '//table[@class="contract_table"]/tr' );

        $ws = array();
        $count_rows = $elements->length;
        $max_expire_date = 0;
        for($i=1; $i<$elements->length;$i++)
        {
          $cells = $xpath->query("./td", $elements->item($i));
          $end_date = self::normalize_date($cells->item(3)->nodeValue, self::$dell_date_format);
          $start_date = self::normalize_date($cells->item(2)->nodeValue, self::$dell_date_format);
          if($end_date >= $max_expire_date){
              $max_expire_date = $end_date;
              $warranty = array(
                 "ServiceTag" => $service_tag,
                 "Description" => $cells->item(0)->nodeValue,
                 "Provider" => $cells->item(1)->nodeValue,
                 "StartDate" => $start_date,
                 "EndDate" => $end_date,
                 "StartDateFormat" => date('d-M-Y', $start_date),
                 "EndDateFormat" => date('d-M-Y', $end_date),
                 "DaysLeft" => $cells->item(4)->nodeValue
              );
              $ws = $warranty;
          }
        }

        libxml_clear_errors();
        libxml_use_internal_errors( $oldSetting );

        return $ws;
    }

    public static function get_hp_contracts_table($serial_number){
        $link = self::$hpURL.$serial_number;
        $oldSetting = libxml_use_internal_errors( true );
        libxml_clear_errors();

        $html = new DOMDocument();
        $html->loadHtmlFile($link);
        $xpath = new DOMXPath( $html );
        $elements = $xpath->query( '//table[@summary="Add summary of the data table"]/tr' );

        $ws = array();
        $count_rows = $elements->length;
        $max_expire_date = 0;
        for($i=1; $i<$elements->length;$i++)
        {
          $cells = $xpath->query("./td", $elements->item($i));
          $end_date = self::normalize_date($cells->item(3)->nodeValue, self::$hp_date_format);
          $start_date = self::normalize_date($cells->item(2)->nodeValue, self::$hp_date_format);
          if($end_date >= $max_expire_date){
              $max_expire_date = $end_date;
              $warranty = array(
                 "Service Tag" => $serial_number,
                 "Description" => trim($cells->item(6)->nodeValue),
                 "Type" => trim($cells->item(0)->nodeValue),
                 "ServiceType" => trim($cells->item(1)->nodeValue),
                 "ServiceLevel" => trim($cells->item(5)->nodeValue),
                 "Provider" => 'Hewlett Packard',
                 "StartDate" => $start_date,//date('d-M-Y', $start_date),
                 "EndDate" => $end_date,//date('d-M-Y', $end_date),
                 "StartDateFormat" => date('d-M-Y', $start_date),
                 "EndDateFormat" => date('d-M-Y', $end_date),
                 "DaysLeft" => time() < $end_date ? ($end_date - time()) / (24 * 60 * 60) : 0
              );
              $ws = $warranty;
          }
        }

        libxml_clear_errors();
        libxml_use_internal_errors( $oldSetting );

        return $ws;
    }
}

?>

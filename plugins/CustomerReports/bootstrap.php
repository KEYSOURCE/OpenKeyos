<?php
   define('REPORT_TYPE_PDF', 1);
   define('REPORT_TYPE_XLS', 2);
   define('REPORT_TYPE_WORD', 3);
   define('REPORT_TYPE_XML', 4);
   
   $GLOBALS['CUSTOMER_REPORTS_TYPES'] = array(
       REPORT_TYPE_PDF => 'PDF',
       REPORT_TYPE_XLS => 'XSL / XSLX',
       REPORT_TYPE_WORD => 'DOC / DOCX',
       REPORT_TYPE_XML => 'XML'
   );
           
?>

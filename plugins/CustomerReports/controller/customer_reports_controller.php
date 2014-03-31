<?php

class_load('Customer');

class CustomerReportsController extends PluginController{
    protected $plugin_name = "CustomerReports";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    function generate_report(){
        check_auth();
        $tpl = "generate_report.tpl";
        
        $filter = array('favorites_first' => $this->current_user->id, 'show_ids' => true);
        if ($this->current_user->restrict_customers) $filter['assigned_user_id'] = $this->current_user->id;
        $customers_list = Customer::get_customers_list ($filter);
        
        $report=array(); //hold the settings for the report here
        if(isset($_SESSION['customer_reports_generate_report']['filter'])){
            $filter = $_SESSION['customer_reports_generate_report']['filter'];
            //unset($_SESSION['customer_reports_generate_report']['filter']);
        }
        
        if(isset($_SESSION['customer_reports_generate_report']['report'])){
            $report = $_SESSION['customer_reports_generate_report']['report'];
            unset($_SESSION['customer_reports_generate_report']['report']);
        }
        
                
        if(isset($filter['customer_id']) and is_numeric($filter['customer_id']) and $filter['customer_id'] > 0){
            $customer = new Customer($filter['customer_id']);
            if(!$customer->id) {
                error_msg($this->get_string('NEED_VALID_CUSTOMER'));
                return $this->mk_redir('generate_report');
            }
            
            if(!isset($report['start_date']) or $report['start_date'] == '') $report['start_date'] = date('d/m/Y');
            if(!isset($report['end_date']) or $report['end_date'] == '') $report['end_date'] = date('d/m/Y');
            
            
            $this->assign('customer', $customer);            
            $this->assign('REPORTS_TYPES', $GLOBALS['CUSTOMER_REPORTS_TYPES']);
        }
        
        $page_messages = array(
            'select_customer' => $this->get_string('SELECT_CUSTOMER'),
            'settings' => $this->get_string('SETTINGS'),
            'technical_information' => $this->get_string('TECHNICAL_INFORMATION'),
            'report_type' => $this->get_string('REPORT_TYPE'),
            'report_title' => $this->get_string('REPORT_TITLE'),
            'report_customer' => $this->get_string('REPORT_CUSTOMER'),
            'report_interval' => $this->get_string('REPORT_INTERVAL'),
            'report_cover_page' => $this->get_string('REPORT_COVER_PAGE'),
            'report_table_of_contents' => $this->get_string('REPORT_TABLE_OF_CONTENTS'),
            'report_section_cover' => $this->get_string('REPORT_SECTION_COVER'),
            'report_yes' => $this->get_string('REPORT_YES'),
            'report_no' => $this->get_string('REPORT_NO'),
            'report_computers' => $this->get_string('REPORT_COMPUTERS'),
            'report_servers' => $this->get_string('REPORT_SERVERS'),
            'report_workstations' => $this->get_string('REPORT_WORKSTATIONS'),
            'report_warranties' => $this->get_string('REPORT_WARRANTIES'),
            'generate' => $this->get_string('GENERATE'),
            'report_peripherals' => $this->get_string('REPORT_PERIPHERALS'),
            'report_software' => $this->get_string('REPORT_SOFTWARE'),
            'report_all_software' => $this->get_string('REPORT_ALL_SOFTWARE'),
            'report_licences' => $this->get_string('REPORT_LICENCES'),
            'report_users' => $this->get_string('REPORT_USERS')
        );
        
        
        
        $this->assign('report', $report);
        $this->assign('page_messages', $page_messages);
        $this->assign('page_title', $this->get_string('GENERATE_REPORT'));
        $this->assign('customers_list', $customers_list);
        $this->assign('error_msg', error_msg());
        $this->set_form_redir('generate_report_submit');
        $this->display($tpl);
        
    }
    
    function generate_report_submit(){
        ini_set('display_errors', 0);
        $ret = $this->mk_redir('generate_report');
        $_SESSION['customer_reports_generate_report']['filter'] = $this->vars['filter'];       
        $_SESSION['customer_reports_generate_report']['report'] = $this->vars['report'];
        $report = $this->vars['report'];
        if(isset($report['report_type'])){
            if($report['report_type'] == REPORT_TYPE_XLS){
                $this->generate_xls_report($report);
            }
            else if($report['report_type'] == REPORT_TYPE_WORD){
                $this->generate_doc_report($report);
            }
        }
        
        return $ret;
    }
    
    function startNewTable ( $header_data, $section, $styleCell, $styleTableFontHeader) {
            $table = $section->addTable('dataTableStyle');
            $table->addRow(20);
            foreach($header_data as $hd){
                $table->addCell(20, $styleCell)->addText($hd, $styleTableFontHeader);                
            }
            return $table;
        }
    
    function generate_doc_report($report){
            class_load('PHPWord');
            ini_set('display_errors', 1);
            $objPHPWord = new PHPWord();
            $styleTableFontHeader = array(
                'bold' => true,
                'size' => 9,
                'align' => 'right',
                'valign' => 'center'
            );
            $styleTableFontBody = array(
                'bold' => false,
                'size' => 9,
                'align' => 'center',
                'valign' => 'center'
            );
            $tdfontstyle = array('align' => 'center', 'size' => 9);
            $styleHeaderFont = array('bold'=>true, size=>9, 'align' => 'left');
            $styleTable = array('borderSize' => 1, 'cellMargin' => 10);
            $objPHPWord->addTableStyle('dataTableStyle', $styleTable);
            $styleCell = array('valign' => 'center');
            $profiles = MonitorProfile::get_profiles_list();
            if($report['servers']){
                    $section = $objPHPWord->createSection();                    
                    $section->addText($this->get_string('REPORT_SERVERS'), array('name'=>'Arial', 'size'=>20, 'color'=>'709D19'));
                    $section->addPageBreak();                   
                    
                    $section = $objPHPWord->createSection();
                    $sectionStyle = $section->getSettings();
                    
                    $server_header = array(
                        'id' => 'Id',
                        'netbios_name' => 'Name',
                        'profile' => 'Monitor profile',
                        'user' => 'User',
                        'warranty_ends' => 'Warranty',
                        'os' => "OS",
                        'last_contact' => "Last contact"
                    );
                    class_load('Computer');
                    class_load('Warranty');
                    $servers = Computer::get_computers(array('customer_id' => $report['customer_id'], 'type'=>COMP_TYPE_SERVER), $cnt);
                    $table = $section->addTable('dataTableStyle');
                    //add the header
                    $row = 1;
                    foreach($servers as $server){
                        if($row == 1){
                            $table = $this->startNewTable($server_header, $section, $styleCell, $styleTableFontHeader);
                        }
                        $table->addRow(20);
                        $table->addCell(20, $styleCell) ->addText($server->id, $styleTableFontBody);
                        $table->addCell(20, $styleCell) ->addText($server->netbios_name, $styleTableFontBody);
                        $table->addCell(20, $styleCell) ->addText($profiles[$server->profile_id], $styleTableFontBody);
                        $table->addCell(20, $styleCell) ->addText($server->get_item('current_user'), $styleTableFontBody);
                        $war = new Warranty(WAR_OBJ_COMPUTER, $server->id);
                        if($war->id and $war->warranty_ends > 0){                                
                            $table->addCell(200, $styleCell) ->addText(date('d/m/Y', $war->warranty_ends), $styleTableFontBody);                            
                        } else {
                            $table->addCell(200, $styleCell) ->addText("Not set", $styleTableFontBody);
                        }
                        $table->addCell(200, $styleCell) ->addText($server->get_item("os_name"), $styleTableFontBody);   
                        $table->addCell(200, $styleCell) ->addText(date('d/m/Y', $server->last_contact), $styleTableFontBody);   
                    }
                    
                    
            }
            
            $objWriter = PHPWord_IOFactory::createWriter($objPHPWord, 'Word2007');
            $doc_file = tempnam(KEYOS_TEMP_FILE, 'KEYOS_DOC_');
            @unlink($doc_file);
	    $doc_file.=".docx";
            $objWriter->save($doc_file);      
            downloadFile($doc_file);       
            @unlink($doc_file);
    }
    
    function generate_xls_report($report){
        class_load('PHPExcel');
            
            $normal_cell_style = array(
                'fill' => array(
                    'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                    'color' => array('argb' => 'FFFFFF')
                ),
                'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
            );
            $normal_cell_font = array(
                'name' => 'Arial',
                'bold' => false,
                'italic' => false,
                'color' => array('rgb'=>'000000')                            
            );
             $error_cell_font = array(
                'name' => 'Arial',
                'bold' => false,
                'italic' => false,
                'color' => array('rgb'=>'ff0000')                            
            );
            $cells = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V');
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("KeyOS System");
            $objPHPExcel->getProperties()->setLastModifiedBy("KeyOS System");
            $objPHPExcel->getProperties()->setTitle($report['title']);
            $objPHPExcel->getProperties()->setSubject($this->get_string('REPORT_SUBJECT'));
            
            $current_sheet_index = 0;
            class_load('MonitorProfile');
            $profiles = MonitorProfile::get_profiles_list();
            if($report['servers']){
                //get servers list
                $current_row = 1;                               
                $server_header = array(
                    'id' => 'Id',
                    'netbios_name' => 'Name',
                    'profile' => 'Monitor profile',
                    'user' => 'User',
                    'warranty_ends' => 'Warranty',
                    'os' => "OS",
                    'last_contact' => "Last contact"
                );
                
                $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                $current_sheet_index+=1;
                $cell_index = 0;
                
                $merge_range = $cells[0].$current_row.":".$cells[count($server_header)-1].$current_row;
                $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_SERVERS'));
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'size' => 20,
                            'color' => array('rgb'=>'709D19')                            
                        )
                );
                $current_row+=1;
                
                //SET THE TABLE HEADER
                foreach($server_header as $rs_header){
                    $cell_name = $cells[$cell_index].$current_row;
                    $cell_index+=1;
                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $rs_header);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                }
                $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'color' => array('rgb'=>'000000')                            
                        )
                );
                $current_row+=1;
                
                
                ///PUT THE SERVERS DATA
                class_load('Computer');
                class_load('Warranty');
                $servers = Computer::get_computers(array('customer_id' => $report['customer_id'], 'type'=>COMP_TYPE_SERVER), $cnt);
                foreach($servers as $server){
                    $current_row += 1;
                    $cell_index = 0;
                    foreach($server_header as $key=>$rs_header){
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray($normal_cell_style);
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($normal_cell_font);
                        $cell_index+=1;
                        if($key == 'id'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $server->id);                            
                        }
                        if($key == 'netbios_name'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $server->netbios_name);
                        }
                        if($key == 'profile'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $profiles[$server->profile_id]);
                        }
                        if($key == 'user'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $server->get_item('current_user'));
                        }
                        if($key == 'warranty_ends'){
                            $war = new Warranty(WAR_OBJ_COMPUTER, $server->id);
                            if($war->id and $war->warranty_ends > 0){                                
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, date('d/m/Y', $war->warranty_ends));
                                if($war->warranty_ends <= time()){
                                    $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($error_cell_font);
                                }
                            } else {
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, 'Not set');
                                 $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($error_cell_font);
                            }
                        }
                        if($key == 'os'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $server->get_item('os_name'));
                        }
                        if($key == 'last_contact'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, date('d/m/Y H:i:s', $server->last_contact));
                        }
                    }
                }
                
                $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_SERVERS'));
                
            }
            
            if($report['workstations']){
                //get servers list
                $current_row = 1;                        
                $server_header = array(
                    'id' => 'Id',
                    'netbios_name' => 'Name',
                    'profile' => 'Monitor profile',
                    'user' => 'User',
                    'warranty_ends' => 'Warranty',
                    'os' => "OS",
                    'last_contact' => "Last contact"
                );
                $objPHPExcel->createSheet($current_sheet_index);
                $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                $current_sheet_index+=1;
                $cell_index = 0;
                
                $merge_range = $cells[0].$current_row.":".$cells[count($server_header)-1].$current_row;
                $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_WORKSTATIONS'));
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'size' => 20,
                            'color' => array('rgb'=>'709D19')                            
                        )
                );
                $current_row+=1;
                
                //SET THE TABLE HEADER
                foreach($server_header as $rs_header){
                    $cell_name = $cells[$cell_index].$current_row;
                    $cell_index+=1;
                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $rs_header);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                }
                $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'color' => array('rgb'=>'000000')                            
                        )
                );
                $current_row+=1;
                
                
                ///PUT THE WORKSTATIONS DATA
                class_load('Computer');
                class_load('Warranty');
                $workstations = Computer::get_computers(array('customer_id' => $report['customer_id'], 'type'=>COMP_TYPE_WORKSTATION), $cnt);
                foreach($workstations as $wks){
                    $current_row += 1;
                    $cell_index = 0;
                    foreach($server_header as $key=>$rs_header){
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray($normal_cell_style);
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($normal_cell_font);
                        $cell_index+=1;
                        if($key == 'id'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $wks->id);                            
                        }
                        if($key == 'netbios_name'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $wks->netbios_name);
                        }
                        if($key == 'profile'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $profiles[$wks->profile_id]);
                        }
                        if($key == 'user'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $wks->get_item('current_user'));
                        }
                        if($key == 'warranty_ends'){
                            $war = new Warranty(WAR_OBJ_COMPUTER, $wks->id);
                            if($war->id and $war->warranty_ends > 0){
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, date('d/m/Y', $war->warranty_ends));
                                if($war->warranty_ends <= time()){
                                    $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($error_cell_font);
                                }
                            } else {
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, 'Not set');
                                $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($error_cell_font);
                            }
                        }
                        if($key == 'os'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $wks->get_item('os_name'));
                        }
                        if($key == 'last_contact'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, date('d/m/Y H:i:s', $wks->last_contact));
                        }
                    }
                }
                
                $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_WORKSTATIONS'));
                
            }
            
            if($report['warranties']){
                class_load('Warranty');
                $current_row = 1;
                $warranties_header = array(
                  'id' => 'Id',
                  'computer_name' => 'Computer',
                  'computer_brand' => 'Computer Brand',
                  'serial_number' => 'Serial Number',
                  'warranty_starts' => 'Warranty Start',
                  'warranty_ends' => 'Warranty End',
                  'service_level' => 'Service level',
                  'service_package' => 'Service package'
                );
                $objPHPExcel->createSheet($current_sheet_index);
                $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                $current_sheet_index+=1;
                $cell_index = 0;
                
                $merge_range = $cells[0].$current_row.":".$cells[count($warranties_header)-1].$current_row;
                $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_WARRANTIES'));
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'size' => 20,
                            'color' => array('rgb'=>'709D19')                            
                        )
                );
                $current_row+=1;
                
                //SET THE TABLE HEADER
                foreach($warranties_header as $rs_header){
                    $cell_name = $cells[$cell_index].$current_row;
                    $cell_index+=1;
                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $rs_header);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                }
                $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'color' => array('rgb'=>'000000')                            
                        )
                );
                $current_row+=1;
                
                //HERE WE PUT THE warranties information
                $computers_list = Computer::get_computers_list (array ('customer_id' => $report['customer_id']));
                $comp_warranties_active = array ();
                $comp_warranties_eow = array ();
                $comp_warranties_unknown = array ();
                class_load('Warranty');
                foreach($computers_list as $comp_id=>$comp_name){
                    $war = new Warranty(WAR_OBJ_COMPUTER, $comp_id);
                    if($war->id){
                        if($war->warranty_ends >= time()){
                            $comp_warranties_active[] = array(
                              'computer_id' => $comp_id,
                              'computer_name' => $comp_name,
                              'warranty' => $war
                            );                            
                        }
                        if($war->warranty_ends < time()){
                            $comp_warranties_eow[] = array(
                              'computer_id' => $comp_id,
                              'computer_name' => $comp_name,
                              'warranty' => $war
                            );
                        }
                            
                    } else {
                        $comp_warranties_unknown[] = array(
                            'computer_id' => $comp_id,
                            'computer_name' => $comp_name, 
                            'warranty' => null
                        );
                    }
                }
                
                $warranties = array_merge($comp_warranties_active, $comp_warranties_unknown, $comp_warranties_eow);
                
                class_load('ServiceLevel');
                class_load('SupplierServicePackage');
                $service_levels = ServiceLevel::get_service_levels_list();
                $service_packages = SupplierServicePackage::get_service_packages_list(array('prefix_supplier'=>true));
                
                foreach($warranties as $key=>$war){
                    $current_row += 1;
                    $cell_index = 0;
                    foreach($warranties_header as $key=>$rs_header){
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->applyFromArray($normal_cell_style);
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($normal_cell_font);
                        $cell_index+=1;
                        if($key == 'id'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $war['computer_id']);  
                        }
                        if($key == 'computer_name'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $war['computer_name']);  
                        }
                        
                        if($war['warranty']){
                            $warranty = $war['warranty'];
                            if($key == 'computer_brand'){
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $warranty->product);  
                            }
                            if($key == 'serial_number'){
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $warranty->sn);
                            }
                            if($key == 'warranty_starts'){
                                if($warranty->warranty_starts)
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, date('d/m/Y', $warranty->warranty_starts));
                                else {
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, 'Not set');
                                    $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($error_cell_font);
                                }
                            }
                            if($key == 'warranty_ends'){
                                if($warranty->warranty_ends)
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, date('d/m/Y', $warranty->warranty_ends));
                                else {
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, 'Not set');
                                    $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray($error_cell_font);
                                }
                            }
                            if($key == 'service_level'){
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $service_levels[$warranty->service_level_id]);
                            }
                            if($key == 'service_package'){
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $service_packages[$warranty->service_package_id]);
                            }
                        }
                    }
                }  
                
                $current_row += 10;
                $cell_index = 0;
                //generate the graph with all the warranties
                class_load('Graph');
                class_load('GanttGraph');
                class_load('GanttBar');
                $graph = new GanttGraph();
                $graph->SetShadow();
                $graph->title->Set('Computers warranties life-cycle');
                $graph->title->SetFont(FF_DEFAULT, FS_BOLD, 12);
                $graph->ShowHeaders(GANTT_HMONTH | GANTT_HYEAR);
                //$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR4);
                $graph->scale->month->SetStyle(MONTHSTYLE_FIRSTLETTER);
                $graph->scale->month->SetFontColor("black");
                $graph->scale->month->grid->Show(true);
                $graph->scale->month->grid->SetColor('gray');
                $graph->scale->month->grid->Show(true);
                $graph->scale->year->SetFontColor("white");
                $graph->scale->year->SetBackgroundColor("blue");
                $graph->scale->year->grid->Show(true);
                $graph->scale->year->grid->SetColor('gray');
                $graph->scale->year->grid->Show(true);
                
                $i=0;
                foreach($warranties as  $k=>$w){
                    if($w['warranty']){
                        $warranty = $w['warranty'];
                        $identifier = "(#".$w['computer_id'].") ".$w['computer_name']." S.N.: ".trim($warranty->sn);
                        if(is_numeric($warranty->warranty_starts) and $warranty->warranty_starts > 0 and is_numeric($warranty->warranty_ends) and $warranty->warranty_ends > 0){
                            $warranty_starts = date('Y-m-d', $warranty->warranty_starts);
                            $warranty_ends = date('Y-m-d', $warranty->warranty_ends);
                            //debug($warranty_starts." - ".$warranty_ends);
                            $warranty_duration = new GanttBar($i, $identifier, $warranty_starts, $warranty_ends, $warranty_ends, 10); 
                            if($warranty->warranty_ends < time()){
                                $warranty_duration->SetPattern(BAND_RDIAG,"red");
                            } else {
                                $warranty_duration->SetPattern(BAND_RDIAG,"green");
                            }
                            $graph->Add($warranty_duration);
                            $i+=1;
                        }                        
                    }
                    
                }
                $wlc_file = tempnam(KEYOS_TEMP_FILE, 'KEYOS_WARLC_');
                @unlink($wlc_file);
                $wlc_file.=".png";
                $graph->Stroke($wlc_file);
                $merge_range = $cells[0].$current_row.":".$cells[count($cells)-1].$current_row;
                $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                
                $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                $objDrawing->setName('Computer Warranties life-cycle');
                $imgr = imagecreatefrompng($wlc_file);
                $objDrawing->setImageResource($imgr);
                $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
                $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                $objDrawing->setCoordinates($cells[$cell_index].$current_row);
                @unlink($wlc_file);
                        
                $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_WARRANTIES'));
            }
            
            if($report['peripherals']){
                class_load('AD_Printer');
                class_load('Peripheral');
                class_load('PeripheralClass');
                class_load('MonitorItem');
                class_load('Computer');
                $current_row = 1;
                
                $objPHPExcel->createSheet($current_sheet_index);
                $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                $current_sheet_index+=1;
                $cell_index = 0;
                
                $ad_printers_header = array(
                    'asset_no' => 'Asset No',
                    'name' => 'Name',
                    'location' => 'Location'                        
                );
                
                //add the AD printers
                $merge_range = $cells[0].$current_row.":".$cells[count($ad_printers_header)-1].$current_row;
                $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('AD_PRINTERS'));
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'size' => 20,
                            'color' => array('rgb'=>'709D19')                            
                        )
                );
                $current_row+=1;
                foreach($ad_printers_header as $ap_head){
                    $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $ap_head);
                    $cell_index += 1;
                    $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                }
                $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                );
                $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                        array(
                            'name' => 'Arial',
                            'bold' => true,
                            'italic' => false,
                            'color' => array('rgb'=>'000000')                            
                        )
                );
                $cell_index=0;
                $current_row+=1;
                //put the header
                $ad_printers = AD_Printer::get_ad_printers (array('customer_id' => $report['customer_id']));
                
                foreach($ad_printers as $ad_printer){
                    $cell_index = 0;
                    $current_row += 1;
                    foreach($ad_printers_header as $key=>$adph){
                        $cell_name = $cells[$cell_index].$current_row;
                        $cell_index += 1;
                        if($key == 'asset_no'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $ad_printer->asset_no);
                        }
                        if($key == 'name'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $ad_printer->name);
                        }
                        if($key == 'location'){
                            $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $ad_printer->get_formatted_value('location'));
                        }
                    }
                }
                $current_row += 1;
                
                $all_peripherals = Peripheral::get_peripherals (array('customer_id' => $report['customer_id']));
                $classes_list = PeripheralClass::get_classes_list ();
                
                foreach($all_peripherals as $class_id => $peripherals){                       
                    $current_row+=1;
                    $cell_index = 0;
                    $merge_range = $cells[0].$current_row.":".$cells[count($ad_printers_header)-1].$current_row;
                    $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                    $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $classes_list[$class_id]);
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                    'color' => array('argb' => 'EFEFEFEF')
                                ),
                                'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                            )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'size' => 20,
                                'color' => array('rgb'=>'709D19')                            
                            )
                    );
                    $current_row+=1;
                    $periph_fields = array('anumber' => 'Asset No', 'periph_name' => 'Name');
                    foreach($peripherals[0]->class_def->field_defs as $k=>$pcfd){
                        if($pcfd->in_reports){
                            $periph_fields[$k] = $pcfd->name;
                        }
                    }
                    
                    foreach($periph_fields as $pf){
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $pf);
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                        $cell_index += 1;
                    }
                    $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                    'color' => array('argb' => 'EFEFEFEF')
                                ),
                                'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                            )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'color' => array('rgb'=>'000000')                            
                            )
                    );
                    $cell_index = 0;
                    $current_row += 1;
                    
                    foreach($peripherals as $peripheral){
                        
                        foreach(array_keys($periph_fields) as $k){
                            $cell_name = $cells[$cell_index].$current_row;
                            $cell_index += 1;
                            if($k === 'anumber'){                                
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $peripheral->asset_no);
                            }
                            else if($k === 'periph_name'){                                
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $peripheral->name);
                            }
                            else if(is_numeric($k)) {                                
                                $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $peripheral->get_formatted_value($k));                            
                            }
                        }       
                        $cell_index = 0;
                        $current_row += 1;
                    }                     
                } 
                
                $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_PERIPHERALS'));
            }
            if($report['software'] or $report['licences']){
                class_load('Software');
                class_load('SoftwareLicense');
                $softwares = SoftwareLicense::get_customer_licenses ($report['customer_id'], true);
                if($report['licences']){
                    $current_row = 1;
                
                    $objPHPExcel->createSheet($current_sheet_index);
                    $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                    $current_sheet_index+=1;
                    $cell_index = 0;
                    $licenses_header = array(
                        'name' => $this->get_string('SOFTWARE_NAME'),
                        'manufacturer' => $this->get_string('MANUFACTURER'),
                        'licenses' => $this->get_string('LICENSES'),
                        'used' => $this->get_string('USED_LICENSES'),
                        'available' => $this->get_string('AVAILABLE')
                    );
                    
                    $merge_range = $cells[0].$current_row.":".$cells[count($licenses_header)-1].$current_row;
                    $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                    $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_LICENCES'));
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'size' => 20,
                                'color' => array('rgb'=>'709D19')                            
                            )
                    );
                    $current_row+=1;
                    foreach($licenses_header as $lic_head){
                        $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $lic_head);
                        $cell_index += 1;
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                    }
                    $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                    'color' => array('argb' => 'EFEFEFEF')
                                ),
                                'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                            )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'color' => array('rgb'=>'000000')                            
                            )
                    );
                    $cell_index=0;
                    $current_row+=1;
                     foreach($softwares as $sw){
                        if($sw->software->in_reports && $sw->license_type!=LIC_TYPE_CLIENT){
                            $cell_index = 0;
                            $current_row += 1;
                            foreach($licenses_header as $key=>$swh){
                                $cell_name = $cells[$cell_index].$current_row;
                                $cell_index += 1;
                                if($key == 'name'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->software->name);
                                }
                                if($key == 'manufacturer'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->software->manufacturer);
                                }
                                if($key == 'licenses'){                                    
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->licenses);
                                }
                                if($key == 'used'){
                                    if($sw->license_type == LIC_TYPE_CLIENT){
                                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->used);
                                    } else {
                                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->used_licenses);
                                    }
                                }
                                if($key == 'available'){                                    
                                    $used = $sw->license_type == LIC_TYPE_CLIENT ? $sw->used : $sw->used_licenses;
                                    $available = $sw->licenses - $used;
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $available);
                                }                                        
                            }
                        }
                    }
                    $current_row += 1;
                    $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_LICENCES'));
                    
                }
                if($report['software']){
                    // Load the list of computers using this software
                    for ($i=0; $i<count($softwares); $i++)
                    {
                            if ($softwares[$i]->software->in_reports)
                            {
                                    $softwares[$i]->computers_list = $softwares[$i]->get_computers_list (true); // Fetch the computers with asset numbers, not IDs
                            }
                    }
                     $current_row = 1;
                
                    $objPHPExcel->createSheet($current_sheet_index);
                    $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                    $current_sheet_index+=1;
                    $cell_index = 0;

                    $softwares_header = array(                        
                        'name' => $this->get_string('SOFTWARE_NAME'),
                        'manufacturer' => $this->get_string('MANUFACTURER'),
                        'used' => $this->get_string('USED_LICENSES'),
                        'computers' => $this->get_string('REPORT_COMPUTERS')
                    );
                    
                    $merge_range = $cells[0].$current_row.":".$cells[count($softwares_header)-1].$current_row;
                    $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                    $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_SOFTWARE'));
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'size' => 20,
                                'color' => array('rgb'=>'709D19')                            
                            )
                    );
                    $current_row+=1;
                    foreach($softwares_header as $sw_head){
                        $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $sw_head);
                        $cell_index += 1;
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                    }
                    $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                    'color' => array('argb' => 'EFEFEFEF')
                                ),
                                'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                            )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'color' => array('rgb'=>'000000')                            
                            )
                    );
                    $cell_index=0;
                    $current_row+=1;
                    foreach($softwares as $sw){
                        if($sw->software->in_reports && $sw->license_type!=LIC_TYPE_CLIENT){
                            $cell_index = 0;
                            $current_row += 1;
                            foreach($softwares_header as $key=>$swh){
                                $cell_name = $cells[$cell_index].$current_row;
                                $cell_index += 1;
                                if($key == 'name'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->software->name);
                                }
                                if($key == 'manufacturer'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->software->manufacturer);
                                }
                                if($key == 'used'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $sw->used_licenses);
                                }
                                if($key == 'computers'){
                                    $comps_str = "";
                                    if(!is_array($sw->computers_list)) $sw->computers_list = array();
                                    foreach($sw->computers_list as $asset_no=>$computer_name){
                                        $comps_str .= $asset_no.": ".$computer_name."\r\n";
                                    }
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $comps_str);
                                }                                        
                            }
                        }
                    }
                    $current_row += 1;
                    $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_SOFTWARE'));
                }
               
            }
            if($report['all_software']){                    
                    $clist = Computer::get_computers_list (array('customer_id' => $report['customer_id']));
                    $installed_sft = array();
                    class_load('Software');
                    foreach($clist as $id=>$name)
                    {
                            $sft = Software::get_permachine_sofware(array('computer_id'=>$id));
                            $installed_sft[$name] = $sft;
                    }
                    //debug($installed_sft);
                    //die;
                    $current_row = 1;
                
                    $objPHPExcel->createSheet($current_sheet_index);
                    $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                    $current_sheet_index+=1;
                    $cell_index = 0;

                    $all_software_header = array(                        
                        'computer' => $this->get_string('COMPUTER'),
                        'software' => $this->get_string('SOFTWARE_NAME'),
                        'installation_date' => $this->get_string('INSTALLATION_DATE')                        
                    );
                    
                    $merge_range = $cells[0].$current_row.":".$cells[count($softwares_header)-1].$current_row;
                    $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                    $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_ALL_SOFTWARE'));
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'size' => 20,
                                'color' => array('rgb'=>'709D19')                            
                            )
                    );
                    $current_row+=1;
                    foreach($all_software_header as $sw_head){
                        $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $sw_head);
                        $cell_index += 1;
                        $objPHPExcel->getActiveSheet()->getColumnDimension($cells[$cell_index])->setAutoSize(true);
                    }
                    $fr = $cells[0].$current_row.":".$cells[$cell_index-1].$current_row;
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->applyFromArray(
                            array(
                                'fill' => array(
                                    'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                    'color' => array('argb' => 'EFEFEFEF')
                                ),
                                'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                            )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($fr)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'color' => array('rgb'=>'000000')                            
                            )
                    );
                    $cell_index=0;
                    $current_row+=1;
                    foreach($installed_sft as $cname=>$sft){                
                        $sv_start_row = $current_row+1;
                        $cn_included = false;
                        foreach($sft as $soft_name){
                            $cell_index = 0;
                            $current_row += 1;
                            foreach($all_software_header as $key=>$swh){                            
                                $cell_name = $cells[$cell_index].$current_row;
                                $cell_index += 1;
                                if($key == 'computer' && !$cn_included){
                                    //$objPHPExcel->getActiveSheet()->setCellValue($cell_name, $cname);
                                    $cn_included = true;
                                }
                                if($key == 'software'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $soft_name['name']);
                                }
                                if($key == 'installation_date'){
                                    $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $soft_name['install_date']);
                                }
                            }                            
                        }
                        if($current_row > $sv_start_row){
                            $merge_range = $cells[0].$sv_start_row.":".$cells[0].$current_row;                            
                            $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                            $objPHPExcel->getActiveSheet()->setCellValue($cells[0].$sv_start_row, $cname);
                            $objPHPExcel->getActiveSheet()->getStyle($cells[0].$sv_start_row)->applyFromArray(
                                    array(
                                        'alignment' => array(
                                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP
                                        )
                                    )
                            );
                        }
                    }
                    $current_row += 1;
                    $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_ALL_SOFTWARE'));
            }
            if($report['users']){
                    class_load('AD_User');
                    $ad_users = AD_User::get_ad_users (array('customer_id' => $report['customer_id']));
                    
                     $current_row = 1;
                
                    $objPHPExcel->createSheet($current_sheet_index);
                    $objPHPExcel->setActiveSheetIndex($current_sheet_index);
                    $current_sheet_index+=1;
                    $cell_index = 0;

                    $softwares_header = array(                        
                        'prop' => "",
                        'value' => "",                        
                    );
                    
                    $merge_range = $cells[0].$current_row.":".$cells[count($softwares_header)-1].$current_row;
                    $objPHPExcel->getActiveSheet()->mergeCells($merge_range);
                    $objPHPExcel->getActiveSheet()->setCellValue($cells[$cell_index].$current_row, $this->get_string('REPORT_USERS'));
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type'=>  PHPExcel_Style_Fill::FILL_SOLID, 
                                'color' => array('argb' => 'EFEFEFEF')
                            ),
                            'borders' => array('bottom' => array('style'=>PHPExcel_Style_Border::BORDER_THIN))                           
                        )
                    );
                    $objPHPExcel->getActiveSheet()->getStyle($cells[$cell_index].$current_row)->getFont()->applyFromArray(
                            array(
                                'name' => 'Arial',
                                'bold' => true,
                                'italic' => false,
                                'size' => 20,
                                'color' => array('rgb'=>'709D19')                            
                            )
                    );
                   
                    $cell_index=0;
                    $current_row+=1;
                    foreach($ad_users as $user){         
                        $cell_index=0;
                        $current_row+=1;
                        $cell_name = $cells[$cell_index].$current_row;
                        //merge the first to and put the name of the user there
                        $merge_cells = $cells[$cell_index].$current_row.':'.$cells[$cell_index+1].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->display_name);
                        $objPHPExcel->getActiveSheet()->mergeCells($merge_cells);
                        $objPHPExcel->getActiveSheet()->getStyle($cell_name)->getFont()->applyFromArray(
                                array(
                                    'name' => 'Arial',
                                    'bold' => true,
                                    'italic' => false,
                                    'size' => 16                                    
                                )
                        );
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "SAM account name");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->sam_account_name);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Display name");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->display_name);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Home directory");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->home_dir);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Home drive");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->home_drive);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Email");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->email);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Email nickname");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->email_nickname);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Profile path");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->profile_path);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Profile size");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->profile_size);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "My Documents size");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->my_documents_size);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Mailbox size");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->exchange_mailbox_size);
                        
                        $cell_index = 0;
                        $current_row += 1;
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, "Total size");
                        $cell_index += 1;                        
                        $cell_name = $cells[$cell_index].$current_row;
                        $objPHPExcel->getActiveSheet()->setCellValue($cell_name, $user->total_size);
                        
                    }
                    $current_row += 1;
                    $objPHPExcel->getActiveSheet()->setTitle($this->get_string('REPORT_USERS'));
                    
            }
            $objPHPExcel->setActiveSheetIndex(0); //set the first sheet as the active sheet so the excel opens directly here
            //save the excel 2007 file
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $xls_file = tempnam(KEYOS_TEMP_FILE, 'KEYOS_XLS_');
            @unlink($xls_file);
	    $xls_file.=".xlsx";
            $objWriter->save($xls_file);      
            downloadFile($xls_file);       
            @unlink($xls_file);
    }
}

?>

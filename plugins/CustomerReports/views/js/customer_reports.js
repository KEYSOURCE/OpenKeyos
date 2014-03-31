$(document).ready(function(){
   $('#select_customer_filter').change(function(){
       $('#customer_report_frm').submit();
   }); 
   
   $('#start_date').DatePicker({
	format:'d/m/Y',
	date: $('#start_date').val(),
	current: $('#start_date').val(),
	starts: 1,
	position: 'r',
	onBeforeShow: function(){
            $('#start_date').DatePickerSetDate($('#start_date').val(), true);
	},
	onChange: function(formated, dates){
            $('#start_date').val(formated);
            $('#start_date').DatePickerHide();		
	}
   });
   
   $('#end_date').DatePicker({
	format:'d/m/Y',
	date: $('#end_date').val(),
	current: $('#end_date').val(),
	starts: 1,
	position: 'r',
	onBeforeShow: function(){
            $('#end_date').DatePickerSetDate($('#end_date').val(), true);
	},
	onChange: function(formated, dates){
            $('#end_date').val(formated);
            $('#end_date').DatePickerHide();		
	}
   });
});
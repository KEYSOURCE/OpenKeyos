<?php
    class_load('User');
    class_load('TicketDetail');
    class_load('Activity');
    /**
     * Class for handling the work_markers - automatic time completion on the tickets details
     * */
    class WorkMarker extends Base
    {
        /**
         * user_id of the marker creator
         *
         * @var user_id int
         * */
        var $user_id = null;
        
        /**
         * ticket_detail_id  - the object of the making
         *
         * @var ticket_detail_id int
         * */        
        var $ticket_detail_id = null;
        
        /**
         * start_time - the start timestamp
         *
         * @var start_time int
         * */
        var $start_time = null;
        
        /**
         * stop_time - the end timestamp
         *
         * @var stop_time int
         * */
        var $stop_time = null;
        
        /**
         * duration - the total time worked
         *
         * @var duration int
         * */
        var $duration = null;
        
        var $table = TBL_WORK_MARKERS;
        var $fields = array('user_id', 'ticket_detail_id', 'start_time', 'stop_time');
        
        /**
         *[Class Method] Start the work on a ticket detail
         *
         *@param $user_id int
         *@param $ticket_detail_id int
         *@return TRUE or FALSE
         **/
        public static function mark_working($user_id, $ticket_detail_id)
        {
            //first check for valid data
            //1. must be a valid user
            $user = new User($user_id);
            if(!$user->id) return FALSE; //must be a valid user
            $detail = new TicketDetail($ticket_detail_id);
            if(!$detail->id) return FALSE; //must have a ticket id (the object of this mark)
            
            $current_start_time = time();
            
            //first check if this user has another marking opened
            $query = "select user_id, ticket_detail_id, start_time from ".TBL_WORK_MARKERS." where user_id = ".$user_id;
            $markers = db::db_fetch_array($query);
            foreach($markers as $marker)
            {
                WorkMarker::complete_marker($marker->user_id, $marker->ticket_detail_id ,$marker->start_time, $current_start_time);
            }
            $query = "insert into ".TBL_WORK_MARKERS."(user_id, ticket_detail_id, start_time) values (".$user_id.", ".$ticket_detail_id.", ".$current_start_time.")";
            DB::db_query($query);
            return TRUE; //finished return true
        }
        
        function complete_marker($user_id, $ticket_detail_id, $start_time, $stop_time)
        {
            $detail = new TicketDetail($ticket_detail_id);            
            if(!$detail->id) return FALSE; //the object of the mark is missing            
            
            //set the defaults for the activity, location, billable
            $detail->location_id = DEFAULT_LOCATION_ID;
            $detail->activity_id = DEFAULT_ACTIVITY_ID;
            $detail->billable = 1;
            
            $detail->time_in = $start_time;
            $detail->time_out = $stop_time;
            $wt = intval(($stop_time - $start_time)/60);
            if($wt == 0) $wt = 1;
            $detail->work_time = $wt;
            //save the new detail
            if($detail->is_valid_data())
            {            
                $detail->save_data();
                $query = "delete from ".TBL_WORK_MARKERS." where user_id=".$user_id." and ticket_detail_id=".$ticket_detail_id;
                DB::db_query($query);
                return TRUE;
            }            
            return FALSE;                    
        }
        
        public static function close_marker($user_id, $ticket_detail_id)
        {
            //get the start time
            $query = "select start_time from ".TBL_WORK_MARKERS." where user_id=".$user_id." and ticket_detail_id=".$ticket_detail_id;
            $start_time = DB::db_fetch_field($query, 'start_time');
            WorkMarker::complete_marker($user_id, $ticket_detail_id, $start_time, time());
        }
        
        /**
         *[Class method] Gets the ticket detail the user has marked as working on
         *
         *@param $user_id int
         *@param $ticket_id int
         *@return $ticket_detail_id
         */
        public static function get_working_detail($user_id, $ticket_id)
        {
            $ret = array();
            //first get a list of all the details of this ticket
            $query = "select id from ".TBL_TICKETS_DETAILS." where ticket_id=".$ticket_id;
            $details = db::db_fetch_vector($query);
            
            //get the detail_id this user has marked
            $query="select ticket_detail_id from ".TBL_WORK_MARKERS." where user_id=".$user_id;
            $tids = db::db_fetch_vector($query);
            
            foreach($tids as $tid)
            {
                if(in_array($tid, $details))
                {
                    $ret[] = $tid;
                }
            }
            return $ret;
        }
    }
    
?>
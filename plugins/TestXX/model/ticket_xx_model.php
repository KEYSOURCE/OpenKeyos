<?php

class TicketXXModel extends PluginModel{

	public $id;
	public $customer_id;
	public $subject;
	public $owner_id;
	public $assigned_id;
	public $type;
	public $source;
	public $priority;
	public $deadline;
	public $project_id;
	public $status;
	public $created;
	public $last_modified;
	public $user_id;
	public $private;
	public $deadline_notified;
	public $escalated;
	public $billable;
	public $customer_order_id;
	public $for_subscription;
	public $seen_manager_id;
	public $seen_manager_date;
	public $seen_manager_comments;
	public $po;

	public $fields=array('id', 'customer_id', 'subject', 'owner_id', 'assigned_id', 'type', 'source', 'priority', 'deadline', 'project_id', 'status', 'created', 'last_modified', 'user_id', 'private', 'deadline_notified', 'escalated', 'billable', 'customer_order_id', 'for_subscription', 'seen_manager_id', 'seen_manager_date', 'seen_manager_comments', 'po');

	public $table = TBL_TICKETS;


    /**
	* Constructor. Also loads a activity information if an ID is provided
	* @param	int	$id		The id of the object to load
	*/
    public function __construct($id = null){
        if($id){
            $this->id = $id;
            $this->load_data();
        }
    }

    public function load_data(){
        if($this->id){
            parent::load_data();
            if($this->id){
                //add your object initialization code here
            }
        }
    }

    /** Checks if the object data is valid */
	function is_valid_data ()
	{
		$ret = true;
		//your validation code goes here
		//if (!$this->name) {
		//    error_msg ('Please specify the object name.');
		//    $ret = false;
		//}
		return $ret;
	}

	/** Checks if the object can be deleted */
	function can_delete ()
	{
		$ret = false;
		if ($this->id)
		{
			$ret = true;
			// Check if this object can be deleted - or it has other dependencies that have to be treated first
		}
		return $ret;
	}

	/** Save the object in the database */
	function save_data ()
	{
		// prepare data to be saved - if there's the case
		parent::save_data ();
		if ($this->id){
		    //now we have the object saved in the database
		    //if there's a need to do some additional operations for objects depending on this object's id
		    //you can do it here
		}
	}

    public static function get_all_items($filter=array(), $count=0){
        $ret = array();
        $query_norm = "SELECT id ";
        $query_cnt = "SELECT count(id) as cnt ";
        $query = " FROM " . TBL_TICKETS;

        if(isset($filter['order_by'])){
            $query .= ' ORDER BY '.$filter['order_by'].' ';
            $query .= isset($filter['order_dir']) ? $filter['order_dir'] : 'desc';
        }
        if(isset($filter['max_records'])){
            if(isset($filter['start_record'])){
                $query .= ' LIMIT ' . $filter['start_record'] . ", " . $filter['max_records'];
            } else {
                $query .= ' LIMIT ' . $filter['max_records'];
            }
        }

        $count = Db::db_fetch_field($query_cnt . $query);

        $ids = Db::db_fetch_vector($query_norm . $query);
        foreach($ids as $id){
            $ret[] = new TicketXXModel($id);
        }
        return $ret;
    }
}

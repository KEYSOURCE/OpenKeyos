<?php
    class_load('Computer');
    class CustomerComputerGroup extends Base
    {
        var $id = null;
        var $customer_id = null;
        var $title = "";
        var $description = "";
        var $country = null;
        var $address = "";
        var $email = "";
        var $language = "";
        var $phone1 = "";
        var $phone2 = "";
        var $fax = "";
        var $yim = "";
        var $skype_im = "";
        
        var $computers_list = array();
        
        var $table = TBL_COMPUTER_GROUPS;
        var $fields = array('id', 'customer_id', 'title', 'description', 'country', 'address', 'email', 'language', 'phone1', 'phone2', 'fax', 'yim', 'skype_im');
        
        function CustomerComputerGroup($id = null)
        {
            if($id != null)
            {
                $this->id = $id;
                $this->load_data();
                //$this->verify_access();
            }
        }
        function load_data()
        {
            $ret = false;
            if($this->id)
            {
                parent::load_data();
                if($this->id)
                {
                    $ret = true;
                    //load different stuff here
                    //the computers assigned to this group
                    $query = "select computer_id from ".TBL_COMPUTER_GROUPS_COMPUTERS." where group_id=".$this->id;
                    $this->computers_list = db::db_fetch_vector($query);
                }
            }
            return $ret;
        }
        
        function save_data()
        {
            $this->title = preg_replace ('/\r\n|\n/', ' ', $this->title);
	        parent::save_data ();
            if ($this->id and is_array ($this->computers_list))
            {
                    db::db_query ('DELETE FROM '.TBL_COMPUTER_GROUPS_COMPUTERS.' WHERE group_id='.$this->id);
                    $q = 'INSERT INTO '.TBL_COMPUTER_GROUPS_COMPUTERS.' (group_id, computer_id) VALUES ';
                    
                    $q_ins = '';
                    foreach ($this->computers_list as $computer_id)
                    {
                            $q_ins.= '('.$this->id.', '.$computer_id.'), ';
                    }
                    
                    if ($q_ins != '')
                    {
                            $q.= preg_replace ('/\,\s*$/', '', $q_ins);
                            db::db_query ($q);
                    }
            }
		
        }
        
        /**
         * [class method] gets all groups filtered by the filter array
         * @param array(mixed) => possible values are "customer_id"
         *
         * @return array(CustomerComputerGroup)
         * */
        public static function get_groups($filter = array())
        {
            $ret = array();
            $query = "select id from ".TBL_COMPUTER_GROUPS." ";
            if(isset($filter['customer_id']))
            {
                $query.=" where customer_id=".$filter['customer_id'];
            }
            $ids = db::db_fetch_vector($query);
			//debug($query);
            foreach($ids as $id)
            {
                $ret[] = new CustomerComputerGroup($id);
            }
            return $ret;
        }
        
        /**
         *[class method] same as get_groups but the return is an array where the key is the group_id and the value is  the title of the group
         **/
        public static function get_groups_list($filter = array())
        {
            $ret = array();
            $query = "select id, title from ".TBL_COMPUTER_GROUPS." ";
            if(isset($filter['customer_id']) and is_int($filter['customer_id']))
            {
                $query.=" where customer_id=".$filter['customer_id'];
            }
            $ret = db::db_fetch_list($query);
            return $ret;
        }
        
        /**
         *  gets a list with all the computers in this group
         *  @return array(Computer)
         * */
        function get_group_computers()
        {
            $ret = array();
            if($this->id)
            {
                $query = "select computer_id from ".TBL_COMPUTER_GROUP_COMPUTERS." where group_id=".$this->id;
                $ids = db::db_fetch_vector($query);
                foreach($ids as $id)
                {
                    $ret[] = new Computer($id);
                }
            }
            return $ret;
        }
        
        /**
         * same as the above only the return is a list where the key is the computer id and the value is the computer netbios name
         * */
        function get_group_computers_list()
        {
            $ret = array();
            if($this->id)
            {
                $query = "select c.id, c.netbios_name from ".TBL_COMPUTERS."c inner join ".TBL_COMPUTER_GROUPS_COMPUTERS." gc on c.id=gc.computer_id where group_id=".$this->id;
                $ret = db::db_fetch_list($query);
            }
            return $ret;            
        }
        
        function get_countries($lang = "UK")
        {
            $ret = array();
            if($lang == "") $lang="UK";
            $query = "select COCLEUNIK, Country_Name_".$lang." from country";
            $ret = db::db_fetch_list($query);
            return $ret;
        }
        
        /**
         *[Class Method] searches the text against the computer groups for one customer
         *
         * @param array - Filter possible values would be
         *                  'customer_id' - to filter by a customer
         *                  'search_text' - if this is missing or void the search returns empty
         *
         * @return array(CustomerComputerGroup)
         **/
        public static function search_computer_group($filter = array())
        {
            $ret = array();
            if(!isset($filter['search_text']) or $filter['search_text'] == "")
                return array();
            $query = "select id from ".TBL_COMPUTER_GROUPS." cg ";
            
            $int_components = array();
            $search_components = explode(" ", $filter['search_text']);
            
            $j=0;
            foreach($search_components as $search_item)
            {
                $sics = explode("#", $search_item);                
                foreach($sics as $sic)
                {
                    if(is_numeric($sic))
                    {                        
                        $int_components[$j] = $sic;
                        $j++;
                    }
                }                                
            }
            
            if(!empty($int_components))
            {
                $query.=" inner join ".TBL_COMPUTER_GROUPS_COMPUTERS." cgc on cg.id=cgc.group_id ";
            }
            $query .= "where MATCH (cg.title, cg.description, cg.address, cg.email, cg.yim, cg.skype_im) against ('".db::db_escape($filter['search_text'])."' in boolean mode) ";
            if($filter['customer_id'])
                $query .= "AND cg.customer_id = ".$filter['customer_id']." ";
            if(!empty($int_components))
            {
                $query.=" OR cgc.computer_id in (";
                $i =0;
                foreach($int_components as $c)
                {                    
                    if($i<count($int_components)-1) $query.=$c.",";
                    else $query.=$c.")";
                    $i++;
                }
            }
            
            $ret = db::db_fetch_vector($query);
            return $ret;
            
        }

        function verify_access() {
            $uid = get_uid();
            class_load('User');
            $user = new User($uid);
            if($user->type == USER_TYPE_CUSTOMER) {
                if($this->customer_id != $user->customer_id) {
                    $url = BaseDisplay::mk_redir('permission_denied', array('goto' => $_SERVER['REQUEST_URI']), 'user');
                    header("Location: $url\n\n");
                    exit;
                }
            }
        }
        
    }
?>
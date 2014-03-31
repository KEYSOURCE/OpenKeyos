<?php
class_load('Customer');
class_load('User');
class_load('UserPhone');
class AsteriskModel{
    public function __construct(){
        //do any initializations here
    }
    
    public static function search_user_phone_number($number){
        $query = "select id, phone from ".TBL_USERS_PHONES;
        $phone_list = db::db_fetch_list($query);
       
        //prepare the Phone list
        foreach ($phone_list as $key=>$phone)
        {
                //1.remove all the dots, slashes, brackets, dashes
                $phone = preg_replace('/[\-\.\(\)\/]/','',$phone);
                //2. replace the + sign by 00
                $phone = preg_replace('/\+/','00',$phone);
                //3. remove the whitespaces
                $phone = preg_replace('/[\s\t]/','',$phone);
                //4. if we have leading 0 not followed by 0 replace by 0032 -- assume belgium localphone
                $sub = substr($phone, 0, 2);
                if($sub!="00")
                {
                	if(substr($sub, 0, 1) == "0")
                	{
                		$phone = "0032".substr($phone, 1);
                	}
                }
                $phone_list[$key] = $phone;
        }
        
        //search with approximate match     in the    
        $phone_list = array_unique($phone_list);           
        
        $shortest = -1;
        $closest = "";
        $uid = -1;
        foreach($phone_list as $key=>$phone)
        {
                //calculate the distance between the input number and the current phone
                $lev = levenshtein($number, $phone);
                //check for an exact match
                if($lev == 0)
                {
                        //closest number is (exact match)
                        $closest = $phone;
                        $uid = $key;
                        $shortest = 0;

                        //break out, there is an exact match
                        break;
                }
                if($lev <= $shortest || $shortest < 0)
                {
                        //set the closest match and the closest distance
                        $closest = $phone;
                        $uid = $key;
                        $shortest = $lev;
                }
        }
        if($shortest == 0)
        {

                $q = "select id, customer_id from ".TBL_USERS." where id=(select user_id from users_phones where id=".$uid.")";
                $cid = db::db_fetch_list($q);
                foreach($cid as $user_id=>$customer_id){
                    if($customer_id==0) $customer_id=6;
                    $ret = array(
                        'customer' => new Customer($customer_id),
                        'user' => new User($user_id),
                        'phone' => new UserPhone($uid)
                    );
                }
                
                return $ret;
        }
        else
        {                
                return null;
        }        
    }
    
    public static function search_contact_phone_number($number){
        //now get the contacts phones
        class_load('CustomerContact');
        class_load('CustomerContactPhone');
        
        $query = "select id, phone from ".TBL_CUSTOMERS_CONTACTS_PHONES;
        $phone_list_contacts = db::db_fetch_list($query);
        //prepare the Phone list
        foreach ($phone_list_contacts as $key=>$phone)
        {
                //1.remove all the dots, slashes, brackets, dashes
                $phone = preg_replace('/[\-\.\(\)\/]/','',$phone);
                //2. replace the + sign by 00
                $phone = preg_replace('/\+/','00',$phone);
                //3. remove the whitespaces
                $phone = preg_replace('/[\s\t]/','',$phone);
                //4. if we have leading 0 not followed by 0 replace by 0032 -- assume belgium localphone
                $sub = substr($phone, 0, 2);
                if($sub!="00")
                {
                	if(substr($sub, 0, 1) == "0")
                	{
                		$phone = "0032".substr($phone, 1);
                	}
                }
                $phone_list_contacts[$key] = $phone;
        }


        //search with approximate match     in the             
        $phone_list_contacts = array_unique($phone_list_contacts);
        
        $shortest = -1;
        $closest = "";
        $uid = -1;
        foreach($phone_list_contacts as $key=>$phone)
        {
                //calculate the distance between the input number and the current phone
                $lev = levenshtein($number, $phone);
                //check for an exact match
                if($lev == 0)
                {
                        //closest number is (exact match)
                        $closest = $phone;
                        $uid = $key;
                        $shortest = 0;

                        //break out, there is an exact match
                        break;
                }
                if($lev <= $shortest || $shortest < 0)
                {
                        //set the closest match and the closest distance
                        $closest = $phone;
                        $uid = $key;
                        $shortest = $lev;
                }
        }
        if($shortest == 0)
        {

                $q = "select id, customer_id from ".TBL_CUSTOMERS_CONTACTS." where id=(select contact_id from ".TBL_CUSTOMERS_CONTACTS_PHONES." where id=".$uid.")";
                $cid = db::db_fetch_list($q);
                foreach($cid as $user_id=>$customer_id){
                    if($customer_id==0) $customer_id=6;
                    $ret = array(
                        'customer' => new Customer($customer_id),
                        'user' => new CustomerContact($user_id),
                        'phone' => new CustomerContactPhone($uid)
                    );
                }
                
                return $ret;
        }
        else
        {                
                return null;
        }
    }

    public static function check_unique_username($username){
        if(!$username or trim($username) == '') return FALSE;
        $query = "select count(id) as cnt from ".TBL_USERS." where login='".$username."'";
        $cnt = DB::db_fetch_field($query, 'cnt');
        if($cnt > 0) return FALSE;
        return TRUE;
    }
    
    public static function check_unique_email($email){
        if(!$email or trim($email) == '') return FALSE;
        $query = "select count(id) as cnt from ".TBL_USERS." where email='".$email."'";
        $cnt = DB::db_fetch_field($query, 'cnt');
        if($cnt > 0) return FALSE;
        return TRUE;
    }
    
    public static function json_escape($message = ''){
        return base64_encode(utf8_encode($message));
    }
}
?>

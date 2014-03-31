<?php
class_load('Mib');
class SnmpController extends PluginController{
    protected $plugin_name = "SNMP";
    function __construct() {
        $this->base_plugin_dir = dirname(__FILE__).'/../';
        parent::__construct();
    }
    
    /********************************************************/
    /* MIBs management					*/
    /********************************************************/

    /** Displays the page for managing the MIBs from the system */
    function manage_mibs ()
    {
            check_auth ();
            $tpl = 'manage_mibs.tpl';

            $mibs = Mib::get_mibs ();

            $this->assign ('mibs', $mibs);
            $this->assign ('error_msg', error_msg());

            $this->display ($tpl);
    }

    /** Displays the page for uploading and creating a new MIB */
    function mib_add ()
    {
            check_auth ();
            $tpl = 'mib_add.tpl';

            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('mib_add_submit');

            $this->display ($tpl);
    }

    /** Processes an uploaded MIB */
    function mib_add_submit ()
    {
            check_auth ();
            $ret = $this->mk_redir ('manage_mibs');

            if ($this->vars['save'])
            {
                    // Create a Mib object, which will be deleted if the operation fails
                    $mib = new Mib ();
                    $mib->save_data ();

                    if (($cnt_files = $mib->set_uploaded_file ($_FILES['mib_file']['tmp_name'], $_FILES['mib_file']['name'])))
                    {
                            $mib->save_data ();
                            if ($cnt_files > 1) $ret = $this->mk_redir ('mib_main_file', array('id' => $mib->id));
                            else
                            {
                                    $mib->load_data ();
                                    if (@$mib->process_uploaded_file ())
                                    {
                                            $mib->save_data ();
                                            $ret = $this->mk_redir ('mib_edit', array('id' => $mib->id));
                                    }
                                    else
                                    {
                                            $mib->delete ();
                                            $ret = $this->mk_redir ('mib_add');
                                    }
                            }
                    }
                    else
                    {
                            $mib->delete ();
                            $ret = $this->mk_redir ('mib_add');
                    }
            }

            return $ret;
    }


    /** Displays the page for selecting the main file for MIBs comprised of multiple files */
    function mib_main_file ()
    {
            check_auth ();
            $tpl = 'mib_main_file.tpl';

            $mib = new Mib ($this->vars['id']);
            if (!$mib->id) return $this->mk_redir ('manage_mibs');

            $params = $this->set_carry_fields (array('id'));
            $this->assign ('mib', $mib);
            $this->assign ('error_msg', error_msg());
            $this->set_form_redir ('mib_main_file_submit', $params);

            $this->display ($tpl);
    }

    /** Sets the main file for an MIB */
    function mib_main_file_submit ()
    {
            check_auth ();
            $mib = new Mib ($this->vars['id']);
            $params = $this->set_carry_fields (array('id'));
            $ret = $this->mk_redir ('manage_mibs');

            if ($this->vars['save'] and $mib->id)
            {
                    $mib->main_file_id = $this->vars['main_file_id'];
                    if ($mib->main_file_id and isset($mib->files_list[$mib->main_file_id])) 
                    {
                            $mib->save_data ();
                            $mib->load_data ();
                            if ($mib->process_uploaded_file())
                            {
                                    $mib->save_data ();
                                    $ret = $this->mk_redir ('mib_edit', $params);
                            }
                            else $ret = $this->mk_redir ('mib_main_file', $params);
                    }
                    else
                    {
                            error_msg ('Please specify a valid file');
                            $ret = $this->mk_redir ('mib_main_file', $params);
                    }
            }

            return $ret;
    }


    /** Displays the page for editing an MIB */
    function mib_edit ()
    {
            check_auth ();
            $tpl = 'mib_edit.tpl';

            $mib = new Mib ($this->vars['id']);
            if (!$mib->id) return $this->mk_redir ('manage_mibs');
            elseif (!$mib->fname) return $this->mk_redir ('mib_main_file', array('id' => $mib->id));

            $oids = $mib->get_oids ();

            $params = $this->set_carry_fields (array('id'));
            $this->assign ('mib', $mib);
            $this->assign ('oids', $oids);
            $this->assign ('SNMP_TYPES', $GLOBALS['SNMP_TYPES']);
            $this->assign ('SNMP_ACCESSES', $GLOBALS['SNMP_ACCESSES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('mib_edit_submit', $params);

            $this->display ($tpl);
    }

    /** Saves the details of the MIB */
    function mib_edit_submit ()
    {
            check_auth ();
            $mib = new Mib ($this->vars['id']);
            $ret = $this->mk_redir ('manage_mibs');

            if ($this->vars['save'] and $mib->id)
            {
                    $mib->load_from_array ($this->vars['mib']);
                    $mib->save_data ();
                    $ret = $this->mk_redir ('mib_edit', array('id' => $mib->id));
            }

            return $ret;
    }

    /** Displays the page for uploading a new file to an existing MIB */
    function mib_upload_file ()
    {
            check_auth ();
            $tpl = 'mib_upload_file.tpl';
            $mib = new Mib ($this->vars['id']);
            if (!$mib->id) return $this->mk_redir ('manage_mibs');

            $params = $this->set_carry_fields (array('id'));
            $this->assign ('mib', $mib);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('mib_upload_file_submit', $params);

            $this->display ($tpl);
    }

    /** Processes the uploading of the new file to an existing MIB */
    function mib_upload_file_submit ()
    {
            check_auth ();
            $mib = new Mib ($this->vars['id']);
            $params = $this->set_carry_fields (array('id'));
            $ret = $this->mk_redir ('mib_edit', $params);

            if ($this->vars['save'] and $mib->id)
            {
                    if (($cnt_files = $mib->set_uploaded_file ($_FILES['mib_file']['tmp_name'], $_FILES['mib_file']['name'])))
                    {
                            $mib->save_data ();
                            if ($cnt_files > 1) $ret = $this->mk_redir ('mib_main_file', $params);
                            else
                            {
                                    $mib->load_data ();
                                    if (@$mib->process_uploaded_file ()) $mib->save_data ();
                                    else $ret = $this->mk_redir ('mib_upload_file', $params);
                            }
                    }
                    else $ret = $this->mk_redir ('mib_upload_file', $params);
            }

            return $ret;
    }

    /** Download an MIB file */
    function mib_download ()
    {
            check_auth ();
            $mib = new Mib ($this->vars['id']);
            if (!$mib->id) return $this->mk_redir ('manage_mibs');

            $file = $mib->work_dir.'/'.$mib->orig_fname;
            header ("Pragma: private");
            header ("Expires: 0");
            header ("Content-type: application/mib");
            header ("Content-Transfer-Encoding: none");
            header ("Cache-Control: private");
            header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header ("Content-length: ".filesize($file));
            header ("Content-Disposition: inline; filename=\"".$mib->orig_fname."\"");
            header ("Connection: close");
            readfile ($file);
            die;
    }

    /** Deletes an MIB */
    function mib_delete ()
    {
            check_auth ();
            $mib = new Mib ($this->vars['id']);
            if ($mib->can_delete()) $mib->delete ();
            return $this->mk_redir ('manage_mibs');
    }

    /** Displays the pop-up window for selecting an OID */
    function popup_oids ()
    {
            check_auth ();
            $tpl = 'popup_oids.tpl';

            $mibs_list = Mib::get_mibs_list ();

            if ($this->vars['oid_id'])
            {
                    $selected_oid = new MibOid ($this->vars['oid_id']);
                    if ($selected_oid->id) $selected_mib = new Mib ($selected_oid->mib_id);
                    else $selected_oids = null;
            }
            elseif ($this->vars['mib_id'])
            {
                    $selected_mib = new Mib ($this->vars['mib_id']);
            }

            if ($selected_mib->id) $oids = $selected_mib->get_oids ();
            else $oids = array ();

            $this->assign ('mibs_list', $mibs_list);
            $this->assign ('selected_oid', $selected_oid);
            $this->assign ('selected_mib', $selected_mib);
            $this->assign ('oids', $oids);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('popup_oids');

            $this->display_template_limited ($tpl);
    }


    /** Displays the page for viewing SNMP devices */
    function snmp_devices ()
    {
            $tpl = 'snmp_devices.tpl';

            if (isset($this->vars['customer_id'])) $_SESSION['snmp_devices']['customer_id'] = $this->vars['customer_id'];
            elseif ($this->locked_customer->id and !$this->vars['do_filter']) $_SESSION['snmp_devices']['customer_id'] = $this->locked_customer->id;

            $filter = $_SESSION['snmp_devices'];
            if ($filter['customer_id']) check_auth (array('customer_id' => $filter['customer_id']));
            else check_auth ();

            // Extract the list of Kawacs customers, eventually restricting only to the customers assigned to 
            // the current user, if he has restricted customer access.
            $customers_filter = array ('has_kawacs' => 1, 'favorites_first' => $this->current_user->id);
            if ($this->current_user->restrict_customers) $customers_filter['assigned_user_id'] = $this->current_user->id;
            $customers_list = Customer::get_customers_list ($customers_filter);

            // Mark the potential customer for locking
            if ($filter['customer_id']>0) $_SESSION['potential_lock_customer_id'] = $filter['customer_id'];

            // Fetch the SNMP devices 
            $snmp_devices = Mib::get_snmp_devices ($filter);

            $params = $this->set_carry_fields (array('do_filter'));
            $this->assign ('snmp_devices', $snmp_devices);
            $this->assign ('filter', $filter);
            $this->assign ('customers_list', $customers_list);
            $this->assign ('SNMP_OBJ_CLASSES', $GLOBALS['SNMP_OBJ_CLASSES']);
            $this->assign ('error_msg', error_msg ());
            $this->set_form_redir ('snmp_devices_submit', $params);

            $this->display ($tpl);
    }

    /** Saves the filtering criteria for the SNMP devices page */
    function snmp_devices_submit ()
    {
            check_auth ();
            $_SESSION['snmp_devices'] = $this->vars['filter'];
            return $this->mk_redir ('snmp_devices', array('do_filter' => 1));
    }
}
?>

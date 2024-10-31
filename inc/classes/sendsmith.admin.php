<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/*
 * Sendsmith Admin Class Declration. This class will handle all the Front end functions like shortcode, 
 * redering form, recording emails.
 */

class sendsmith_admin {

    /**
     * Decleration for Sendsmith api credentials
     */
    public $api_key = '';
    public $api_secret = '';

    /**
     *
     * @var null|string hold the success message
     */
    public $success;

    /**
     *
     * @var null|string This variable hold the error message
     */
    public $error;

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'sendsmith_add_admin_menu')); //Admin menus
        add_action('admin_enqueue_scripts', array($this, 'sendsmith_admin_scripts')); // Add javascripts
        add_action('init', array($this, 'sendsmith_process_post')); //processing post
	add_action('plugins_loaded', array($this, 'sendsmith_load_plugin_textdomain'));
        add_action('media_buttons', array($this, 'sendsmith_add_rte_button'), 15);

        /**
         * Getting API details
         */
        $api_details = get_option('sendsmith-api-details');
        if ($api_details && $api_details != '') {
            $api_details = unserialize($api_details);

            if (isset($api_details['api_key']) && isset($api_details['api_secret'])) {
                $this->api_key = $api_details['api_key'];
                $this->api_secret = $api_details['api_secret'];
            }
            
            //Activate SendSmith API credential verification only on SendSmith pages
            if(strpos($_GET['page'], 'sendsmith') !== false){
                $api_status = $this->sendsmith_api_verify();
                if($api_status !== 200){
                    if($api_status == 'curl_failed'){
                        $this->error = __("CURL is not enabled on your Server. We require CURL to connect to SendSmith. Please contact your hosting provider and try again.", 'sendsmith');
                    }else{
                        $this->error = __("SendSmith API credential are invalid. Please login to your SendSmith account and provide your API credentials in the <a href='admin.php?page=sendsmith_settings'>settings.</a>", 'sendsmith');
                    }
                }  
            }
        }
    }

    public function sendsmith_add_rte_button(){
        echo '<a href="#" id="sendsmith-add-shortcode" class="button">SendSmith Form</a>';
    }
    
    /**
     * This function will load the text domain
     */
    public function sendsmith_load_plugin_textdomain(){
        load_plugin_textdomain( 'sendsmith', FALSE, SENDSMITH_PLUGIN_DIR. '/languages/' );		
    }

    /**
     * This function will add Admin menus
     */
    public function sendsmith_add_admin_menu() {
        add_menu_page(__('SendSmith', 'sendsmith'), __('SendSmith', 'sendsmith'), 'publish_posts', 'sendsmith', array($this, 'sendsmith_admin_main'), SENDSMITH_ASSETS_URL . '/images/icon.png');
        
	//Not to display menu of Form if the api details are not set
        if ($this->api_key != '' && $this->api_secret != '') {
            add_submenu_page('sendsmith', __('Forms', 'sendsmith'), __('Forms', 'sendsmith'), 'publish_posts', 'sendsmith_forms', array($this, 'sendsmith_admin_forms'));
            // add_submenu_page('sendsmith', __('Roles', 'sendsmith'), __('Roles', 'sendsmith'), 'publish_posts', 'sendsmith_roles', array($this, 'sendsmith_admin_roles'));
            // add_submenu_page('sendsmith', __('Tracking', 'sendsmith'), __('Tracking', 'sendsmith'), 'publish_posts', 'sendsmith_tags', array($this, 'sendsmith_admin_tags'));
        }
        add_submenu_page('sendsmith', __('Settings', 'sendsmith'), __('Settings', 'sendsmith'), 'manage_options', 'sendsmith_settings', array($this, 'sendsmith_admin_settings'));
    }

    /**
     * This function will display form for updating front form fields.
     */
    public function sendsmith_admin_forms() {
        $fields = get_option('sendsmith-registration-form-fields'); //getting stored fields if any
        $double_opt = get_option("sendsmith-doubleopt-form"); //double opt value
        $form_messages = get_option("sendsmith-form-message"); //getting for messages if any
        
        if ($fields && $fields != '') {
            $fields = unserialize($fields);
        } else {
            $fields = array();
        }

        if ($form_messages && $form_messages != '') {
            $form_messages = unserialize($form_messages);
        } else {
            $form_messages = array();
        }
        
	// Get the form field from SendSmith
	$form_info = $this->sendsmith_get_available_form_info();	

        $available_fields = '';
        if(!is_null($form_info)){
            $form_info = json_decode($form_info, true);
            if(isset($form_info['fields'])){
                $available_fields = $form_info['fields'];
            }
        }

        require_once SENDSMITH_TPL . '/admin/forms.tpl.php';
    }

    /**
     * This function will display form for updating tracking
     */
    public function sendsmith_admin_tags() {
        $pages = get_pages();
        $page_tags = get_option('sendsmith-tags-page-mapping'); //getting stored page-tags connections
        if ($page_tags && $page_tags != '') {
            $page_tags = unserialize($page_tags);
            foreach ($page_tags as $pageID => $page_tag) {
                foreach ($page_tag as $tag) {
                    $tagsArray[$tag][] = $pageID;
                }
            }
        } else {
            $tagsArray = array();
        }

        require_once SENDSMITH_TPL . '/admin/tags.tpl.php';
    }

    /**
     * This function will display form for updating Roles - user connections
     */
    public function sendsmith_admin_roles() {
        $role_tags = get_option('sendsmith-role-tags-mapping'); /// getting roles-user connection
        $role_status = get_option('sendsmith-role-tags-status'); // getting role status. If a role is set to disable it wont update then
        $double_opt = get_option("sendsmith-doubleopt-roles"); // double opt for storing role-tag connection

        if ($role_tags && $role_tags != '') {
            $role_tags = unserialize($role_tags);
        } else {
            $role_tags = array();
        }

        if ($role_status && $role_status != '') {
            $role_status = unserialize($role_status);
        } else {
            $role_status = array();
        }

        $available_roles = $this->sendsmith_available_roles(); // getting all roles created by wordpress or other plugin

        require_once SENDSMITH_TPL . '/admin/roles.tpl.php';
    }
    
    /**
     * Setting Page rendering
     */
    public function sendsmith_admin_settings() {
        require_once SENDSMITH_TPL . '/admin/settings.tpl.php';
    }

    /**
     * Cover Page
     */
    public function sendsmith_admin_main() {
        if ($this->api_key == '' || $this->api_secret == '') {
            $this->error = __("SendSmith API credential are not set. Please login to your sendsmith account and add your API credentials in the <a href='admin.php?page=sendsmith_settings'>settings.</a>", 'sendsmith');
        }
        require_once SENDSMITH_TPL . '/admin/main.tpl.php';
    }

    /**
     * Enqueueing public scripts
     */
    public function sendsmith_admin_scripts() {
        wp_enqueue_style('sendsmith-admin', SENDSMITH_ASSETS_URL . 'css/style.admin.css');
	$version = get_bloginfo("version");
        if($version <= "3.7"){
            wp_enqueue_style('sendsmith-admin-legacy', SENDSMITH_ASSETS_URL . 'css/style.admin.legacy.css');
        }
        wp_enqueue_style('sendsmith-select2-css', SENDSMITH_ASSETS_URL . 'thirdparty/select2/select2.min.css');
        wp_enqueue_script('sendsmith-select2-js', SENDSMITH_ASSETS_URL . 'thirdparty/select2/select2.min.js', array('jquery'));
        wp_enqueue_script('sendsmith-ajax-script', SENDSMITH_ASSETS_URL . 'js/scripts.js', array('jquery'));
    }
    
    /**
     * Format post data
     */
    protected function formPostRequest($data){
        $post = '';
        if($data && is_array($data)){
            if(function_exists('get_magic_quotes_gpc')) {
                $get_magic_quotes_exists = true;
            }
            foreach ($data as $key => $value) {
                if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                    $value = urlencode(stripslashes($value));
                } else {
                    $value = urlencode($value);
                }
                $post .= "&$key=$value";
            }
        }
        return $post;
    }
   
    function sendsmith_api($path = '', $data = array(), $domain = '') {
        if (function_exists('curl_init') && $path != '') {
            
            if($domain === ''){
                $domain = SENDSMITH_URL;
            }else{
                if(!$domain){
                    return false;
                }
            }

            $url = $domain."/api/$path/";

            $req = $this->formPostRequest($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

            $res = curl_exec($ch);

            if (curl_errno($ch) != 0){
                curl_close($ch);
                return "curl_error";
            }
            curl_close($ch);
            return $res;
        }
    }

    function sendsmith_get_server(){
        $res = $this->sendsmith_api('server',array('k'=>$this->api_key,'s'=>$this->api_secret));

        if($res){
            $data_array = json_decode($res, true);
            if(is_array($data_array)){
                if(isset($data_array['status'])){
                    if($data_array['status'] === 200){
                        return $data_array['server'];
                    }
                }else{
                    return false;
                }
            }
        }
        return false;
    }
    
    /**
     * This function contact sendsmith and verify if the api credentials are valid.
     */
    function sendsmith_api_verify() {
        $server = $this->sendsmith_get_server();
        
        $resp = $this->sendsmith_api('authenticate',array('k'=>$this->api_key,'s'=>$this->api_secret), $server);

        if($resp){
            $data_array = json_decode($resp, true);
            if(is_array($data_array)){
                if(isset($data_array['status'])){
                    return $data_array['status'];
                }else{
                    return false;
                }
            }
        }
        return false;
    }
    
    function sendsmith_get_available_form_info(){
        $server = $this->sendsmith_get_server();
        $resp = $this->sendsmith_api('forminfo',array('k'=>$this->api_key,'s'=>$this->api_secret), $server);

        if($resp === 'curl_error'){
            return null;
	}else{
            return $resp;
	}
    }

    /**
     * All post request by this plugin will be controlled by this plugin
     * It will return incase of invalid form action. Function will called based on the action provided in the POST. Only allowed actions will be processed
     */
    public function sendsmith_process_post() {
        $allowed_action = array("update_field_form", "update_tag_form", "update_sendsmith_settings", "update_role_tags");
        if (current_user_can("publish_posts")) {
            if (isset($_POST) && isset($_POST['sendsmith_form_action']) && in_array($_POST['sendsmith_form_action'], $allowed_action)) {
                // Only the administrator can update the setting
                if($_POST['sendsmith_form_action'] === 'update_sendsmith_settings' && !current_user_can('manage_options')){
                    return FALSE;
                }
                $action_name = "sendsmith_" . $_POST['sendsmith_form_action'];
                $this->$action_name($_POST);
            }
        } else {
            return FALSE;
        }
    }

    /**
     * Process from Forms submit and save it in Wordpress options
     * @param array $post POST ARRAY
     */
    public function sendsmith_update_field_form($post) {
        $done = 0;
        if(isset($post['sendsmith-form-message']) && !empty($post['sendsmith-form-message'])){
            update_option("sendsmith-form-message", serialize($post['sendsmith-form-message']));
            $done = 1;
        }
            
        if(isset($post['sendsmith-doubleopt-form']) && $post['sendsmith-doubleopt-form'] == 1){
            update_option('sendsmith-doubleopt-form', 1);
            $done = 1;
        }else{
            update_option('sendsmith-doubleopt-form', 0);
            $done = 1;
        }
        
        if (isset($post['field']['name']) && !empty($post['field']['name']) && isset($post['field']['key']) && !empty($post['field']['key']) && isset($post['field']['type']) && !empty($post['field']['type'])) {
            foreach ($post['field']['name'] as $nkey => $name) {
                $fields[$nkey]['name'] = $name;
	    }

		$key_set= array();
            foreach ($post['field']['key'] as $kkey => $key) {
		if(array_key_exists($key, $key_set)){
            		$this->error = __("Form Fields > Attribute cannot be used more than one time", 'sendsmith');
			return;
		}else{
			$key_set[$key] = 1;
		}
                $fields[$kkey]['key'] = $key;
            }

            foreach ($post['field']['type'] as $tkey => $type) {
                $fields[$tkey]['type'] = $type;
            }
            
           if (isset($fields)) {
                update_option('sendsmith-registration-form-fields', serialize($fields));
            }else{
                update_option('sendsmith-registration-form-fields', '');
            }
            $done = 1;
        }else{
            update_option('sendsmith-registration-form-fields', '');
            $done = 1;
        }
        
        if($done){
            $this->success = __("Registration form fields updated successfully", 'sendsmith');
        }
    }

    /**
     * Process Tracking menu submission and save as wordpress option
     * @param array $post POST ARRAY
     */
    public function sendsmith_update_tag_form($post) {
        if ($post['sendsmith-tags'] && !empty($post['sendsmith-tags'])) {
            foreach ($post['sendsmith-tags'] as $mt) {
                foreach ($mt['pages'] as $mtpages) {
                    $tags_by_page[$mtpages][] = $mt['tag'];
                }
            }
            if (isset($tags_by_page) && !empty($tags_by_page)) {
                update_option('sendsmith-tags-page-mapping', serialize($tags_by_page));
                $this->success = __("Tags connected to pages successfully", 'sendsmith');
            }
        } else {
            update_option('sendsmith-tags-page-mapping', serialize(array()));
            $this->success = __("All tag mapping to pages removed  successfully", 'sendsmith');
        }
    }

    /**
     * Process form Settings and save as wordpress option
     * @param array $post POST ARRAY
     */
    public function sendsmith_update_sendsmith_settings($post) {
        if (isset($post['sendsmith-api-key']) && $post['sendsmith-api-key'] != '' && isset($post['sendsmith-api-secret']) && $post['sendsmith-api-secret'] != '') {
            $nonce = $post['sendsmith-nonce'];
            if (wp_verify_nonce($nonce,'sendsmith-nonce')){
                update_option('sendsmith-api-details', serialize(array("api_key" => $post['sendsmith-api-key'], "api_secret" => $post['sendsmith-api-secret'])));
                $this->api_key = $post['sendsmith-api-key'];
                $this->api_secret = $post['sendsmith-api-secret'];
                $this->success = __("API credentials saved successfully", 'sendsmith');
            }
        }
    }

    /**
     * Save Role as wordpress option
     * @param array $post POST ARRAY
     */
    public function sendsmith_update_role_tags($post) {
        if ($post['sendsmith-role-tags'] && !empty($post['sendsmith-role-tags'])) {
            update_option('sendsmith-role-tags-mapping', serialize($post['sendsmith-role-tags']));
            update_option('sendsmith-role-tags-status', serialize($post['sendsmith-role-status']));
            $this->success = __("Tags connected to roles successfully", 'sendsmith');
        } else {
            update_option('sendsmith-role-tags-mapping', serialize(array()));
            update_option('sendsmith-role-tags-status', serialize(array()));

            $this->success = __("All tag mapping to roles removed  successfully", 'sendsmith');
        }
        
        if(isset($post['sendsmith-doubleopt-roles']) && $post['sendsmith-doubleopt-roles'] == 1){
            update_option('sendsmith-doubleopt-roles', 1);
        }else{
            update_option('sendsmith-doubleopt-roles', 0);
        }
    }
    
    /**
     * Get available roles
     * @param array $post POST ARRAY
     */
    public function sendsmith_available_roles() {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        return $wp_roles->get_names();
    }

}

$sendsmith_admin = new sendsmith_admin();


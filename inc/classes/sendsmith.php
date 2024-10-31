<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

require_once(ABSPATH . 'wp-includes/pluggable.php');


/*
 * SendSmith Class Declration. This class will handle all the Front end functions like shortcode, 
 * redering form, recording emails and matching tags.
 */

class sendsmith {

    /**
     * Decleration for SendSmith api credentials
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

    public function __construct() {
        /* Public script enqueue */
        add_action('wp_enqueue_scripts', array($this, 'sendsmith_publicscripts'));

        /* Hook to save tags at the time of registration */
        add_action('user_register', array($this, 'sendsmith_process_registration'));
        
       // Shortcode
        add_shortcode('sendsmith-form', array($this, 'sendsmith_form_shortcode'));
        
       //Process Post
        add_action('init', array($this, 'sendsmith_process_post')); //processing post

        /* wp-ajax implementation */
        add_action('wp_ajax_sendsmith_tag_page_visit', array($this, 'sendsmith_tag_page_visit_callback'));

        /* setting api-details to class objects */
        $api_details = get_option('sendsmith-api-details');
        if ($api_details && $api_details != '') {
            $api_details = unserialize($api_details);

            if (isset($api_details['api_key']) && isset($api_details['api_secret'])) {
                $this->api_key = $api_details['api_key'];
                $this->api_secret = $api_details['api_secret'];
            }
        }
    }

    /**
     * Public Scripts
     */
    public function sendsmith_publicscripts() {
        wp_enqueue_script('sendsmith-front-js', SENDSMITH_ASSETS_URL . 'js/front.js', array('jquery')); //javascript for frontend
        wp_localize_script('sendsmith-front-js', 'sendsmithAjax', array('ajaxurl' => admin_url('admin-ajax.php'), 'security' => wp_create_nonce('save-tag-based-on-url')));
    }

    /**
     * Call to wp-ajax action sendsmith_tag_page_visit. Function will save tags based on the page the logged in user will visit
     */
    public function sendsmith_tag_page_visit_callback() {
        $server = $this->sendsmith_get_server();
        
        $current_user = wp_get_current_user(); //current user
        if (isset($current_user->data) && isset($current_user->data->user_email) && $current_user->data->user_email != '') {
            check_ajax_referer('save-tag-based-on-url', 'security'); //checking valid wp nonce
            $pageid = url_to_postid(sanitize_text_field($_POST['path'])); //guessing the ID of the url the user is visiting
            if ($pageid > 0) {
                $page_tags = get_option('sendsmith-tags-page-mapping');
                if ($page_tags && $page_tags != '') {
                    $page_tags = unserialize($page_tags);
                    if (isset($page_tags) && isset($page_tags[$pageid])) {
                        foreach ($page_tags[$pageid] as $pt) {
                            $page_tag_array[] = array("name" => $pt);
                            if ($page_tag_array && !empty($page_tag_array) && is_array($page_tag_array)) {
                                $tagInfo = json_decode($this->sendsmith_api("tags", array("email" => $current_user->data->user_email, "tags" => $page_tag_array), $server));
                            }
                        }
                    }
                }
            }
        }
        wp_die();
    }
    
    public function sendsmith_form_shortcode(){
         if ($this->api_key != '' && $this->api_secret != '') {
            $fields = get_option('sendsmith-registration-form-fields');
            $form_messages = get_option("sendsmith-form-message");
            
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
            
            // Get the token
            $server = $this->sendsmith_get_server();
            $token_result = json_decode($this->sendsmith_api("token", array(), $server));                
            
            $sendsmith_token = '';
            if(trim($token_result->data) !== ''){
                $sendsmith_token = $token_result->data;
            }
            
            /**
             * Allow to show the form even no additional field defined
             * Email field is added by default 
             */
            if (is_array($fields)){
                ob_start();
                require_once SENDSMITH_TPL . 'front/form.php';
                $sendsmith_form = ob_get_contents();
                ob_end_clean();
                
                return $sendsmith_form;
            }
        }       
    }

    /**
     * Post-registration hook, called to save attributes and tags to sendsmith. This hook will keep on looking for all the registration form for new registration event.
     * @param int $user_id
     */
    function sendsmith_process_registration($user_id) {
        $server = $this->sendsmith_get_server();
        
        if (is_numeric($user_id) && $user_id > 0) {
            $userInfo = get_user_by('id', $user_id);
            $role_tags = get_option('sendsmith-role-tags-mapping');
            $role_status = get_option('sendsmith-role-tags-status');
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
            
            $roles_tags_array = array(); //initializing an array
            if (!empty($role_tags) && !empty($userInfo) && isset($userInfo->roles) && is_array($userInfo->roles) && !empty($userInfo->roles)) {
                foreach ($userInfo->roles as $ur) {
                    if (isset($role_status[$ur]) && $role_status[$ur] == 1) { //check if current user's role status is active
                        foreach ($role_tags[$ur] as $rt) {
                            $roles_tags_array[] = array("name" => $rt); //storing tags in main tag array to update in batch on sendsmith
                        }
                    }
                }
            }
            
            foreach($userInfo->roles as $user_roles){
                if (isset($role_status[$user_roles]) && $role_status[$user_roles] == 1) {
                    $add_contact = 1;
                }
            }


            //if any of user role's update to sendsmith status is active
            if(isset($add_contact) && $add_contact == 1){
                $double_opt = get_option("sendsmith-doubleopt-roles");
                if($double_opt && $double_opt == 1){
                    $confirmed = true;
                }else{
                    $confirmed = false;
                }
                //Create Contact
                $contactInfo = json_decode($this->sendsmith_api("contacts", array("email" => str_replace("+", "%2B", $userInfo->data->user_email), "confirmed" => $confirmed), $server));                
            }

            //If contact created successfully then save the tags
            if (isset($contactInfo) && isset($contactInfo->id) && isset($roles_tags_array) && !empty($roles_tags_array)) {
                $tagInfo = json_decode($this->sendsmith_api("tags", array("email" =>  str_replace("+", "%2B", $userInfo->data->user_email), "tags" => $roles_tags_array), $server));
            }
        }
    }

    /**
     * Format post data
     */
    protected function formPostRequest($data){
        $post = '';
        if(is_array($data)){
            $data['k'] = $this->api_key;
            $data['s'] = $this->api_secret;
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
  
    function sendsmith_get_server(){
        $res = $this->sendsmith_api('server');

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
     * This function contact sendsmith through sendsmith API. It will use CURL to communicate.
     * @param string $path Contain path to make api request for example /contacts
     * @param string $requestType Contain request type like GET, POST, DELETE
     * @param array  $data Contain data to be posted to sendsmith
     */
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

    /**
     * Function will detect post action and will perform post action accordingly
     */
    public function sendsmith_process_post(){
        $allowed_action = array("front_form_post");
        if (isset($_POST) && isset($_POST['sendsmith_form_action']) && in_array($_POST['sendsmith_form_action'], $allowed_action)) {
        	$action_name = "sendsmith_" . $_POST['sendsmith_form_action'];
                $this->$action_name($_POST);
       	}
    }
    
    /**
     * Function will save attributes to sendsmith through sendsmith rendered form
     * @param array $post POST Data
     */
    public function sendsmith_front_form_post($post) {
        $server = $this->sendsmith_get_server();
        
        $post['sendsmith-fields']['contact'] = str_replace("+", "%2B", $post['sendsmith-fields']['contact']);
        $form_messages = get_option("sendsmith-form-message");
        if ($form_messages && $form_messages != '') {
            $form_messages = unserialize($form_messages);
        } else {
            $form_messages = array();
        }
       
        $fields_json = '';
	if (isset($post['sendsmith-fields']) && !empty($post['sendsmith-fields'])) {
		$fields_array = array();
		foreach ($post['sendsmith-fields'] as $mkey => $mfields) {
			if($mkey != 'contact'){
				$fields_array[$mkey] = trim($mfields);
			}
		}
		if(is_array($fields_array) && count($fields_array)){
			$fields_json = json_encode($fields_array);
		}
	}
	
 
        if (filter_var($post['sendsmith-fields']['contact'], FILTER_VALIDATE_EMAIL)) {
            $contactInfo = json_decode($this->sendsmith_api("postdata", array("email" => str_replace("+", "%2B", $post['sendsmith-fields']['contact']), 't' => $post['sendsmith_form_t'], 'xfields'=>$fields_json), $server));

            if(isset($contactInfo) && isset($contactInfo->status)){
                if($contactInfo->status === 'success'){
                    $this->success = (isset($form_messages['success-message'])) ? $form_messages['success-message'] : $contactInfo->message;
                }else{
                    if($contactInfo->message === 'Member_exist'){
                        $this->error = (isset($form_messages['email-exist'])) ? $form_messages['email-exist'] : $contactInfo->message;
                    }else{
                        $this->error = (isset($form_messages['incorrect-email-message'])) ? $form_messages['incorrect-email-message'] : $contactInfo->message;
                    }
                }
            }else{
                $this->error = (isset($form_messages['incorrect-email-message'])) ? $form_messages['incorrect-email-message'] : "Error";
            }

        } else {
            $this->error = (isset($form_messages['incorrect-email-message'])) ? $form_messages['incorrect-email-message'] : "you have entered an invalid email";
        }
    }

}

$sendsmith = new sendsmith(); //sendsmith start


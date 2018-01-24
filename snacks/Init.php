<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Init
 *
 * @author user
 */
class Init {
    //put your code here
    private $developer_access_token;
    private $isVerificationMode;
    function __construct($fb_developer_access_token) {
      $this->developer_access_token=$fb_developer_access_token;
      $this->isVerificationMode=false;
    }
    
    private function curl($url,$postfields,$header,$returntransfer){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $msg=curl_exec($ch);
    curl_close($ch);
    return $msg; 
    }
    
     /**
 * (PHP 5 &gt;= 5.2.0, PECL json &gt;= 1.2.0)<br/>
 * Verification Method for the Bot which is required from facebook
 * @link http://4handheld.com/github/botapi
 * @param string $verification_token <p>
 * The verification token known only by the developer. 
 * The verification is a one time event
 * </p>
 * @return ChatBot Chatbot api instance.
 * @example $bot=new FBResponseBot();<br>
 * $bot->setupVerify("verification token here"); 
 */
    function setupVerify($verification_token){
        if(isset($_REQUEST['hub_challenge'])){
            $facebook_challenge=$_REQUEST['hub_challenge'];
            if($_REQUEST['hub_verify_token']===$verification_token){
                echo $facebook_challenge;
                $this->isVerificationMode=true;
            }
        }
        return $this;
    }
    
    function setWelcomeButton($returnparameter="http://return.parameter.com"){
        $collect=array(
            "get_started"=>array("payload"=>"$returnparameter")//Note that $returnparameter is returned as postback
        );
        $to_json=json_encode($collect);
        $this->curl("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=".$this->developer_access_token, $to_json, array('Content-Type: application/json'), true);
  return $this;
    }
    
    function setWelcomeText($text="Welcome {{user_first_name}}"){  //{{user_first_name}}{{user_last_name}}{{user_full_name}}
        $text=$this->insertUserData($text);
        $collect=array(//text <=160
            "greeting"=>array(array("locale"=>"default","text"=>"$text"))
        );
        $to_json=json_encode($collect);
        $this->curl("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=".$this->developer_access_token, $to_json, array('Content-Type: application/json'), true);
       return $this;
    }
    
    function whiteListDomains($array_of_domains){
        //White List Domains      
         $collect=array(
            "whitelisted_domains"=>$array_of_domains
        );
        $to_json=json_encode($collect);
        $this->curl("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=".$this->developer_access_token, $to_json, array('Content-Type: application/json'), true);
       return $this;
    }
    
    function setBOTCountry($array_of_countries){
        
    }
    
    function setUpMenu($menu,$locale="default"){
        $collect=array(
                "persistent_menu"=>array(
                    array(
                    "locale"=>$locale,
                    "composer_input_disabled"=>false,
                    "call_to_actions"=>$menu
                    )
                )
        );
        $to_json=json_encode($collect);
        $this->curl("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=".$this->developer_access_token, $to_json, array('Content-Type: application/json'), true);
       return $this;
    }
    
    private function insertUserData($inputString){
        $update=str_replace(array("#lastname","#firstname"),
       array("{{user_last_name}}","{{user_first_name}}"), $inputString);
//        $update=str_replace($search, $replace, $update);
        return $update;
    }
    
    private function curlGETDELETE($url,$postfields,$header,$returntransfer){
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_PUT, true);
//    curl_setopt($ch, CURLOPT_,$postfields);
  //  curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $msg=curl_exec($ch);
    curl_close($ch);
    return $msg; 
    }
    
    function clearAll(){
        $collect=array(
            "fields"=>array("get_started","persistent_menu","greeting","whitelisted_domains")
        );
       $to_json=json_encode($collect);
        $this->curlGETDELETE("https://graph.facebook.com/v2.6/me/messenger_profile?access_token=".$this->developer_access_token, $to_json, array('Content-Type: application/json'), true);
       return $this; 
    }
    
    function isVerificationMode(){
        return $this->isVerificationMode;
    }
    
}

?>

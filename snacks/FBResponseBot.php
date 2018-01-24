<?php
/**
 * Description of ResponseBot
 *
 * @author user
 */

//include_once 'Approval.php';
include_once 'Init.php';

class FBResponseBot {
    //put your code here
    private $incoming;
    private $approval;
    private $fb_developer_token;
    private $init;
    private $userid;
    private $userText;
    private $quickReply;
    private $postback;
    
    private $isEchoFromBot;
    private $isDeliveryReport;
    private $isReadReport;
    private $isQuickReply;
    
    private $firstName;
    private $lastName;
    private $profilePic;
    private $locale;
    private $timezone;
    private $gender;
    
    private $paystackpng;
    
    private $debugmode;
    
    function __construct($fb_developer_access_token) {
        $this->paystackpng="https://scontent.flos5-1.fna.fbcdn.net/v/t1.0-1/p200x200/22007571_1977984625770081_905302124256559301_n.png?oh=61fb6975927873d10eaf0c6d2cedd6b8&oe=5ABD4683";
        $this->fb_developer_token=$fb_developer_access_token;
        $this->init=new Init($this->fb_developer_token);
        $this->incoming=stripslashes(file_get_contents('php://input'));
        $this->approval=false;
        $decode=json_decode($this->incoming,true);
        $this->userid=$decode['entry'][0]['messaging'][0]['sender']['id'];
        
        $this->userText="";
        if(isset($decode['entry'][0]['messaging'][0]['message']['text']))
        $this->userText=$decode['entry'][0]['messaging'][0]['message']['text'];//text sent by user
        
        $this->quickReply="";
        if(isset($decode['entry'][0]['messaging'][0]['message']['quick_reply']['payload']))
        $this->quickReply=$decode['entry'][0]['messaging'][0]['message']['quick_reply']['payload'];//quickreply sent by user
        
        $this->postback="";
        if(isset($decode['entry'][0]['messaging'][0]['postback']['payload']))
        $this->postback=$decode['entry'][0]['messaging'][0]['postback']['payload'];//postback sent by user
        
        $is_echo=isset($decode['entry'][0]['messaging'][0]['message']['is_echo'])?true:false;// is echo of message sent by bot , true if from bot, false if from sender
        $delivery=isset($decode['entry'][0]['messaging'][0]['delivery']['watermark'])?true:false;
        $isReadReport=isset($decode['entry'][0]['messaging'][0]['read']['watermark'])?true:false;
        
        $isfromUser=$is_echo;
        $isfromBot=!$is_echo;
        
        $this->isEchoFromBot=$is_echo;
        $this->isDeliveryReport=$delivery;
        $this->isReadReport=$isReadReport;
        
        $this->debugmode=false;
        
        if((!($is_echo || $delivery || $isReadReport))&&$this->userid)
        $this->getUserInfo($this->userid);
        
    }
    
    private function getUserInfo($user_id){
       $data= $this->curlGET("https://graph.facebook.com/v2.6/$this->userid?fields=first_name,last_name,profile_pic,locale,timezone,gender&access_token=".$this->fb_developer_token,
                "", array('Content-Type: application/json'), true);
       $info=json_decode($data, true);
       if(isset($info["first_name"]))
       $this->firstName=$info["first_name"];
       if(isset($info["last_name"]))
       $this->lastName=$info["last_name"];
       if(isset($info["profile_pic"]))
       $this->profilePic=$info["profile_pic"];
       if(isset($info["locale"]))
       $this->locale=$info["locale"];
       if(isset($info["timezone"]))
       $this->timezone=$info["timezone"];
       if(isset($info["gender"]))
       $this->gender=$info["gender"];
    }
    
    function getBuild(){
        return new Init($this->fb_developer_token) ;
    }
    
    private function curl($url,$postfields,$header,$returntransfer){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $fp = fopen(dirname(__FILE__).'/errorlog.txt', 'a+');
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_STDERR, $fp);
    $msg=curl_exec($ch);
    curl_close($ch);
//    file_put_contents("debug",$msg);
    return $msg; 
    }
   
    private function curlGET($url,$postfields,$header,$returntransfer){
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_PUT, true);
//    curl_setopt($ch, CURLOPT_,$postfields);
  //  curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returntransfer);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $msg=curl_exec($ch);
    curl_close($ch);
    return $msg; 
    }
   
    private function insertUserData($inputString){
        $update=str_replace(array("#lastname","#firstname","#profilepic","#locale","#timezone","#gender"),
       array($this->lastName,$this->firstName,$this->profilePic,$this->locale,$this->timezone,$this->gender), $inputString);
//        $update=str_replace($search, $replace, $update);
        return $update;
    }
   
    function sendTypingResponse(){
        if($this->debugmode){
            if($this->approval){
                echo "<br>Incoming text = ".$this->userText;
            }else{
            }
        }
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
        $collect=array(
            "messaging_type"=>"RESPONSE",
            "recipient"=>array("id"=>$this->userid),
            "sender_action"=>"typing_on"
        );
        if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
        $to_json=json_encode($collect);
        $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
    function sendTextResponse($text="Dummy Text"){
        
        if($this->debugmode){
            if($this->approval){
                echo "<br>Incoming text = ".$this->userText;
            }else{
            }
        }
        
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
        
        if(is_array($text)){
            $r=rand(0, count($text)-1);
            $text=$text[$r];
        }
        
        $text=$this->insertUserData($text);
           $collect=array(
               "messaging_type"=>"RESPONSE",
               "recipient"=>array("id"=>$this->userid),
               "message"=>array("text"=>"$text"),
           );
        $to_json=json_encode($collect);
        if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
        //echo '"messaging_type": "RESPONSE"'.$to_json;
        $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
//    postback, web, share, call, login, logout, quickbutton, webview
    function getNewButtonTemplate($type="postback",$title="Sample Postback",$payload="Post back was clicked"){
        if($type=="quickbutton"){  //Quick Response Button
        return array(
                     "content_type"=>"text", //
                     "title"=>$title,
                     "payload"=>$payload
                     );
        }else if($type=="nested"){
         return array(
                "type"=>$type, //
                "call_to_actions"=>$payload,
                "title"=>$title
                 ); 
        }else if($type=="postback"){
        return array(
                     "type"=>$type, //
                     "title"=>$title,
                     "payload"=>$payload
                     );
        }else if($type=="web"){
         return array(
                "type"=>"web_url", //
                "url"=>$payload,
                "title"=>$title
                 ); 
        }else if($type=="webview_full"){
         return array(
                "type"=>"web_url", //
                "url"=>$payload,
                "title"=>$title,
                "fallback_url"=>$payload,
                "messenger_extensions"=>true,
                "webview_height_ratio"=>"full"
                 ); 
        }else if($type=="webview_tall"){
         return array(
                "type"=>"web_url", //
                "url"=>$payload,
                "title"=>$title,
                "fallback_url"=>$payload,
                "messenger_extensions"=>true,
                "webview_height_ratio"=>"tall"
                 ); 
        }else if($type=="webview_compact"){
         return array(
                "type"=>"web_url", //
                "url"=>$payload,
                "title"=>$title,
                "fallback_url"=>$payload,
                "messenger_extensions"=>true,
                "webview_height_ratio"=>"compact"
                 ); 
        }else if($type=="share"){
         return array(
                "type"=>"element_".$type, //
                "share_contents"=>array(
                        "attachment"=>array(
                            "type"=>"template",
                            "payload"=>$payload  //list template,  generic(carousal) template, media template
                        )
                )
           ); 
        }else if($type=="call"){
         return array(
                "type"=>"phone_number", //
                "url"=>$payload,
                "title"=>$title
                 ); 
        }else if($type=="login"){
         return array(
                "type"=>"account_link", //
                "url"=>$payload
                 ); 
        }else if($type=="logout"){
         return array(
                "type"=>"account_unlink"
                 ); 
        }else{
         return array();   
        }
        
    }
    
    function sendQuickReplyResponse($text="Dummy Text",$quickreplyButtons=array()){
        if($this->debugmode){
            if($this->approval){
                echo "<br>Incoming text = ".$this->userText;
            }else{
            }
        }
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
        
        $collect=array(
            "messaging_type"=>"RESPONSE",
            "recipient"=>array("id"=>$this->userid),
            "message"=>array("text"=>$text,"quick_replies"=>$quickreplyButtons)
        );
        $to_json=json_encode($collect);
        if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
        $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
    function getNewCarouselTemplateItem($title="Title",$imageurl="https://4handheld.com/favico",$subtitle="subtitle",$defaultaction="",$buttontemplatearrayof3=array()){
        $data=array();
        $data["title"]=$title;
        if($imageurl)
        $data["image_url"]=$imageurl;
        if($subtitle)
        $data["subtitle"]=$subtitle;  //optional
        if($defaultaction)
        $data["default_action"]=$defaultaction;
        $data["buttons"]=$buttontemplatearrayof3;
        return $data;
    }
    
    function sendCarousel($carouselItemArray){
        //generic Item
        //type=generic,
        if($this->debugmode){
            if($this->approval){
                echo "<br>Incoming text = ".$this->userText;
            }else{
            }
        }
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
        $collect=array(
            "messaging_type"=>"RESPONSE",
            "recipient"=>array("id"=>$this->userid),
            "message"=>array(
                "attachment"=>array("type"=>"template",
                "payload"=>array(
                   "template_type"=>"generic",
                    "image_aspect_ratio"=>"square", //horizontal or square . Default horizontal
                    "sharable"=>true,
                    "elements"=>$carouselItemArray //up to 10 permitted
                )
                      )
            )
        );
         $to_json=json_encode($collect);
         if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
      echo  $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
    function sendList($carouselItemArray,$showMastHead,$moreButton){
        if($this->debugmode){
            if($this->approval){
                echo "<br>Incoming text = ".$this->userText;
            }else{
            }
        }
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
        $collect=array(
            "messaging_type"=>"RESPONSE",
            "recipient"=>array("id"=>$this->userid),
            "message"=>array(
                "attachment"=>array("type"=>"template",
                "payload"=>array(
                   "template_type"=>"list",
                    "top_element_style"=>$showMastHead?"large":"compact", //compact or large  ///optional
                    "elements"=>$carouselItemArray, //Min of 2, Max of 4
                    "buttons"=>array($moreButton)// 
                )
                      )
            )
        );
        $to_json=json_encode($collect);
        if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
        echo  $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
    function sendMedia($filetype,$url,$attachment_id){
        if($this->debugmode){
            if($this->approval){
                echo "<br>Incoming text = ".$this->userText;
            }else{
            }
        }
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
//      $filetype=  audio, video, image, file
        
//        $collect=array(
//            "recipient"=>array("id"=>$this->userid),
//            "message"=>array(
//                "attachment"=>array("type"=>"template",
//                "payload"=>array(
//                   "template_type"=>"media",
//                   "elements"=>array(
//                       array(
//                           "media_type"=>"$filetype", //image or video
//                           (true)?"attachment_id":"url"=>(true)?"":""
//                           )
//                       ) 
//                )
//                      )
//            )
//        );
//        if(true)
//            $collect['message']['attachment']['payload']['elements']['buttons']=array($button);
        
        if(!$attachment_id){
            $collect=array(
                "messaging_type"=>"RESPONSE",   //RESPONSE, UPDATE, MESSAGE_TAG, NON_PROMOTIONAL_SUBSCRIPTION
                "recipient"=>array("id"=>$this->userid),
                "message"=>array(
                    "attachment"=>array(
                    "type"=>$filetype,
                    "payload"=>array(
                       "url"=>$url,
                        "is_reusable"=>"true"
                    )
                          )
                )
            );
        }else{
            $collect=array(
                "messaging_type"=>"RESPONSE",   //RESPONSE, UPDATE, MESSAGE_TAG, NON_PROMOTIONAL_SUBSCRIPTION
                "recipient"=>array("id"=>$this->userid),
                "message"=>array(
                    "attachment"=>array(
                    "type"=>$filetype,
                    "payload"=>array(
                       "attachment_id"=>$attachment_id
                    )
                          )
                )
            );
        }
        
      $to_json=json_encode($collect);
      if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
      $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
    function sendPaymentRequest($token,$description,$amount){
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
        $buyer=$this->userid;
        $this->sendCarousel(array(
                    $this->getNewCarouselTemplateItem($description."",$this->paystackpng,"Pay via Paystack",false,array($this->getNewButtonTemplate(ButtonType::WEBVIEW_TALL,"Pay", "https://4handheld.com/quizza/paystack.php?buyer=$buyer&token=$token")))
        ));
        return $this;
    }
    
    function sendReciept($customer_name,$order_id,
                        $currency,$pay_method,$order_url,
                        $total_cost,$timestamp,
                        $merchant_name,$extra_data=false,$sharable=false){
        if(!$this->approval){
            return $this;
        }
        if($this->isEchoFromBot){
            return $this;
        }
        if($this->isDeliveryReport){
            return $this;
        }
        if($this->isReadReport){
            return $this;
        }
      $collect=array(
            "messaging_type"=>"RESPONSE",
            "recipient"=>array("id"=>$this->userid),
            "message"=>array(
                "attachment"=>array(
                    "type"=>"template",
                    "payload"=>array(
                       "template_type"=>"reciept",
                        "sharable"=>$sharable,  //optional
                        "recipient_name"=>"$customer_name",
                        "merchant_name"=>$merchant_name,    //optional, replaces logo text
                        "order_number"=>"$order_id",
                        "currency"=>"$currency",
                        "payment_method"=>"$pay_method",    //optional , helps user to identify which card was used for transaction
                        "order_url"=>"$order_url",
                        "timestamp"=>"$timestamp",     //optional
                        "summary"=>array(
                            "total_cost"=>"$total_cost"
                        )
                )
                      )
            )
        );
      
      if($extra_data){
          $extra_data[0]['total_cost']=$total_cost;
          $summary=$extra_data[0];
          $address=$extra_data[1];
          $adjustments=$extra_data[2];
          $elements=$extra_data[3];
          $collect['message']['attachment']['payload']['summary']=$summary;
          $collect['message']['attachment']['payload']['address']=$address;
          $collect['message']['attachment']['payload']['adjustments']=$adjustments;
          $collect['message']['attachment']['payload']['elements']=$elements;
      }
      
         $to_json=json_encode($collect);
         if($this->debugmode){
            if($this->approval){
                echo "<br>Outgoing text = ".$to_json;
            }else{
            }
        }
      echo  $this->curl("https://graph.facebook.com/v2.6/me/messages?access_token=".$this->fb_developer_token, $to_json, array('Content-Type: application/json'), true);
        return $this;
    }
    
   function getRecieptExtra($summary,$address,$adjustments,$elements){
       return array($summary,$address,$adjustments,$elements);
   }
   
   function getRecieptSummary($subtotal,$shipping_cost,$total_tax){
       return array(
                            "subtotal"=>"$subtotal",   //optional
                            "shipping_cost"=>"$shipping_cost",  //optional
                            "total_tax"=>"$total_tax",   //optional
                        );
   }
   
   function getRecieptAddress($sreet_1,$street_2,$city,$postal_code,$state,$country){
       return array(     //optional
                            "street_1"=>"$sreet_1",
                            "street_2"=>"$street_2",    //optional
                            "city"=>"$city",
                            "postal_code"=>"$postal_code",
                            "state"=>"$state",
                            "country"=>"$country",
                        );
   }
   
   function getRecieptCartItem($title,$subtitle,$quantity,$price,$currency,$image_url){
       $tack=array("title"=>"$title");
       if($subtitle)
       $tack["subtitle"]="$subtitle";  //optional
       if($quantity)
       $tack["quantity"]="$quantity";  //optional
       if($price)
       $tack["price"]="$price";  //set to 0 for free items
       if($currency)
       $tack["currency"]="$currency";  //optional
       if($image_url)
       $tack["image_url"]="$image_url";  //optional
       return $tack;
   }
   
   function getRecieptAdjustmentsItem($name,$adjustment_amount){
       return array(
                    "name"=>"$name",
                    "amount"=>"$adjustment_amount"
       );
   }
   
//   function getRecieptCartItem(){
//       return array(    //optional, max=100
//                           array(
//                               "title"=>"",
//                               "subtitle"=>"",
//                               "quantity"=>"",
//                               "price"=>"",
//                               "currency"=>"",
//                               "image_url"=>""
//                           ),
//                           array(
//                               "title"=>"",
//                               "subtitle"=>"",  //optional
//                               "quantity"=>"",  //optional
//                               "price"=>"",  //set to 0 for free items
//                               "currency"=>"",  //optional
//                               "image_url"=>""  //optional
//                           )
//                        );
//   }
    
    private function isArray($var){
        return is_array($var);
    }
    
    function setDebugMode($boolean){
        $this->debugmode=$boolean;
        if($boolean){
            echo "<form method='post'>
                <input type='text' name='inctext' placeholder='Input incoming text..' style='width:300px;' />
                <input type='submit' name='send' value='SEND' />
                </form>";
            
            if(isset ($_POST['send'])){
                $this->userText=$_POST['inctext'];
            }
        }
        return $this;
    }
    
    function setUserText($text){
        $this->userText=$text;
        return $this;
    }
    
     private  function contains($input,$data){
   $contains=false;
    foreach($data as $index=>$fsm){
        $allWordMatch=true;
        if(is_array($fsm)){
            foreach ($fsm as $key => $word) {
                if(strlen($word)<=0){
                continue;
                }
                
                if(strstr(strtolower($input),  strtolower($word))){
                }else{
                    $allWordMatch=false;
                }
            }
        }else{
//            is word
            if(strlen($fsm)<=0){
            continue;
            }
            
            if(strstr(strtolower($input),  strtolower($fsm))){
            }else{
               $allWordMatch=false;
            }
        }
        $contains=$contains||$allWordMatch;
    }
    return $contains;
}
    
    function isIncomingContains($pattern,$caseSensitive=false){
        //word search, checks for words
//        $pattern can be item or array
       $this->approval=($this->contains($this->userText, $pattern));
       return  $this;
    }
    
    function isIncoming($pattern){
        // $pattern can be item or array
        $foundMatch=false;
        if($this->userText==$pattern){
         $foundMatch=true;   
        }
        $this->approval=($foundMatch);
        return  $this;
    }
    
    function isQuickReply($qr){
        $foundMatch=false;
        if($this->quickReply==$qr){
         $foundMatch=true;   
        }
        $this->approval=($foundMatch);
        return $this;
    }
    
    function isPostBack($postback){
        $foundMatch=false;
        if($this->postback==$postback){
         $foundMatch=true;   
        }
        $this->approval=($foundMatch);
        return $this;
    }
    
    function close(){
        $this->approval=false;
//        $this->userText="";
        return $this;
    }

    function processNLP(){
        
    }
    
}

class ButtonType{
     const  POSTBACK="postback";
     const  WEB_URL="web";
     const  WEBVIEW_TALL="webview_tall";
     const  WEBVIEW_FULL="webview_full";
     const  WEBVIEW_COMPACT="webview_compact";
     const  SHARE="share";
     const  CALL="call";
     const  LOGIN="login";
     const  LOGOUT="logout";
     const  QUICKBUTTON="quickbutton";
     const  NESTED="nested";
}

class MediaType{
    const VIDEO="video";
    const AUDIO="audio";
    const FILE="file";
    const IMAGE="image";
}

class Locale{
 const ALL="default";   
 
 static function Africa($exception_array){
     
 }
 
 static function USA($exception_array){
     
 }
 
 static function Europe($exception_array){
     
 }
 
 static function Asia($exception_array){
     
 }
 
 static function Australia($exception_array){
     
 }
 
}

?>

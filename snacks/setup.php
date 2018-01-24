<?php
$build=$bot
        ->getBuild();
        $build->setupVerify("YOUR VERIFICATION TEXT HERE")
        ->setWelcomeButton("http://fb.com/?firsttime")
        ->setWelcomeText("Hi #firstname :). I'm a Bot. Try me. ") 
        ->whiteListDomains(array("http://fb.com")) //Needed for Webviews. Include urls that would be in direct connection with messenger within the array() 
//        menu requires 3 parameters Title, type and payload. payload type depends on the type. Visit https://github.com/botapi#menu for more
        ->setUpMenu(array(   //
            $bot->getNewButtonTemplate(ButtonType::POSTBACK,"Menu","https://fb.com/?menu"), // Opens a full Webview within messenger
            $bot->getNewButtonTemplate(ButtonType::NESTED,"More...",array(                               // Opens a submenu (Nested menu) within messenger
                    $bot->getNewButtonTemplate(ButtonType::POSTBACK,"About","https://fb.com/about"), //Returns that a button was clicked
                    $bot->getNewButtonTemplate(ButtonType::WEBVIEW_FULL,"I want a Chat Bot","https://4handheld.com/index.php")
                    ))
            ))
        ;
        //terminate if incoming is just verification. This enables quick verification on systems with slow connection and  processing speed
        if($build->isVerificationMode()){
            return;
        }
?>
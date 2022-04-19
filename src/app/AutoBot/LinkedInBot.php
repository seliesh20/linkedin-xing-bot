<?php

namespace App\AutoBot;

use App\AutoBot\Bot;
use Laravel\Dusk\Browser;
use App\Models\TaskCookies;
use App\Models\TaskActions;
use App\Models\TaskSkipActions;
use App\Models\Settings;
use DateTime;
use Illuminate\Support\Facades\Crypt;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use GenderApi\Client as GenderApiClient;

use Illuminate\Support\Facades\Log;

class LinkedInBot {
    
    private $login_URL = "https://www.linkedin.com/login?fromSignIn=true&trk=guest_homepage-basic_nav-header-signin";
    private $home_URL = "https://www.linkedin.com/feed/?trk=guest_homepage-basic_nav-header-signin";
    private $search_URL = "https://www.linkedin.com/search/results/people/?keywords={keyword}&origin=SWITCH_SEARCH_VERTICAL";
    private $withdraw_URL = "https://www.linkedin.com/mynetwork/invitation-manager/sent/";

    private $can_proceed = true;
    private $max_page = 1;
    private $current_page = 1;
    private $invitation_send = false;    

    public function run($task)
    {
        ini_set('max_execution_time', 0);
        //Login to LinkedIn
        Bot::browse(function(Browser $browser) use($task){
            Log::info("LinkedIn Bot-Started");
            $this->loginControl($browser, $task);
            Log::info("LinkedIn Bot-Login Completed");

            if($this->can_proceed){                
                $this->searchPeople($browser, $task);
            }

        });
    }
    /**
     * Verify Send
     */
    public function verifySend($browser, $task, $name, $url, $image)
    {
        $browser->visit($this->withdraw_URL);        
        $browser->waitFor("main#main");
        $mainElement = $browser->elements("main#main")[0];

        //remove Overlay
        $browser->whenAvailable("#msg-overlay", function ($modal) use ($mainElement) {
            $overlayClass = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]'))->getAttribute("class");
            if (strpos($overlayClass, "msg-overlay-list-bubble--is-minimized") == false) {
                $overlaybutton = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]/header/section[2]/button[2]'));
                $modal->click('button#' . $overlaybutton->getAttribute('id'));
            }
        });
        
        $found_invitation = false;
        if (count($browser->elements("main#main > section > div:nth-child(3) > div:nth-child(3) > ul > li"))) {
            $elements = $browser->elements("main#main > section > div:nth-child(3) > div:nth-child(3) > ul > li");
            foreach ($elements as $key => $element) {  
                if($this->can_proceed && !$found_invitation){  
                    
                    $c_name = $element->findElement(WebDriverBy::xpath("div/div[1]/div[1]/a/span[2]"))->getText();
                    $c_url = $element->findElement(WebDriverBy::xpath("div/div[1]/div[1]/a"))->getAttribute("href");
                    $c_url = substr($url, 0, strlen($url)-2);                            

                    if($name == $c_name && $url == $c_url){
                        $found_invitation = true;
                        return true;
                    }                    
                }
            }   
        }
        //Add into skip
        TaskSkipActions::create([
            'task_id' => $task->id,
            'task_user' => $name,
            'task_user_url' => $url,
            'task_user_img' => $image,
        ]);
        return false;
    }

    public function withdraw($task)
    {
        Bot::browse(function(Browser $browser) use($task){
            $this->loginControl($browser, $task);
            
            if($this->can_proceed){

                $browser->visit($this->withdraw_URL);
                $browser->waitFor("main#main");
                $mainElement = $browser->elements("main#main")[0];

                //remove Overlay
                $browser->whenAvailable("#msg-overlay", function($modal) use($mainElement){
                    $overlayClass = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]'))->getAttribute("class");
                    if(strpos($overlayClass, "msg-overlay-list-bubble--is-minimized") == false){
                        $overlaybutton = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]/header/section[2]/button[2]'));
                        $modal->click('button#'.$overlaybutton->getAttribute('id'));
                    }
                });
                                    
                if (count($browser->elements("main#main > section > div:nth-child(3) > div:nth-child(3) > ul > li"))) {
                    $elements = $browser->elements("main#main > section > div:nth-child(3) > div:nth-child(3) > ul > li");
                    foreach ($elements as $key => $element) {  
                        if($this->can_proceed){  
                            
                            $name = $element->findElement(WebDriverBy::xpath("div/div[1]/div[1]/a/span[2]"))->getText();
                            $url = $element->findElement(WebDriverBy::xpath("div/div[1]/div[1]/a"))->getAttribute("href");
                            $url = substr($url, 0, strlen($url)-2);                            

                            //Fetching from TaskActions
                            $action = TaskActions::where("task_id", $task->id)                                
                                ->where("task_user", $name)
                                ->where("task_user_url", "like", "%".$url."%")
                                ->first();                            
                            if(is_object($action)){
                                $setting = Settings::where("var_name", "max_withdraw_days")->first();
                                $today = new DateTime(date("Y-m-d"));
                                $sendday = new DateTime($action->created_at);
                                $interval = $sendday->diff($today);
                                $days = $interval->format("%a");                                
                                if($days > $setting->value){
                                    Log::info("LinkedIn Bot-Withdrawal days".$days);
                                    $browser->click("main#main > section > div:nth-child(3) > div:nth-child(3) > ul > li:nth-child(".($key+1).") > div > div:nth-child(2) > button");                        
                                    $browser->elsewhereWhenAvailable("div#artdeco-modal-outlet", function($modal) use($task, $action) {
                                        $modal->click("div > div.artdeco-modal__actionbar > button:nth-child(2)");
                                        $modal->pause(1000);
                                        $modal->screenshot("LinkedIn-Task-".$task->id."-Withdraw-".time());    
                                        //Update TaskAction
                                        $action->task_run_status = "withdrawn";
                                        $action->save();

                                        //stopping at one completed
                                        $this->can_proceed = false;
                                    });
                                }                            
                            }
                        }
                    }
                }
            }
        });        
    }

    public function loginControl($browser, $task)
    {   
        try{
            $browser->visit($this->login_URL);
            $task_cookie = TaskCookies::where('task_type', $task->task_type)
                    ->where('login_email', $task->login_email)
                    ->where('login_password', $task->login_password)
                    ->first();
            if(is_object($task_cookie)){
                //use cookie
                $cookies = unserialize(Crypt::decryptString($task_cookie->cookie_string));                
                foreach($cookies as $cookie){
                    $browser->driver->manage()->addCookie($cookie);
                }
                $browser->visit($this->home_URL);
            } 
            try{                
                $browser->waitFor("#ember20", 10);                
            } catch (\Exception $e){
                $browser->waitForText("Sign in")                    
                    ->type("session_key", $task->login_email)
                    ->type("session_password", Crypt::decryptString($task->login_password))
                    ->click(".login__form_action_container > button[type=submit]")
                    ->waitFor("#ember20", 10);
                if(strpos($browser->driver->getCurrentURL(), '/feed/') > -1){
                    $cookies = serialize($browser->driver->manage()->getCookies());
                    if(is_object($task_cookie)){
                        $task_cookie->cookie_string = Crypt::encryptString($cookies);
                        $task_cookie->save();
                    } else {
                        TaskCookies::create([
                            'task_type' => $task->task_type,
                            'login_email' => $task->login_email,
                            'login_password' => $task->login_password,
                            'cookie_string' => Crypt::encryptString($cookies)
                        ]);    
                    }                    
                }
                return true;
            }
        } catch(\Exception $e){
            Log::error($e->getMessage());
            $browser->screenshot("LinkedIn-Task-".$task->id.'-Error-'.time());
            $this->can_proceed = false;
        }
    } 

    public function searchPeople($browser, $task){
        try{
            $this->search_URL = str_replace('{keyword}', $task->search_query, $this->search_URL);
            while($this->current_page <= $this->max_page && !$this->invitation_send)
            {
                if($this->current_page > 1 && !strpos($this->search_URL, '{page}')){
                    $this->search_URL.="&page={page}";
                }
                
                $browser->visit(str_replace('{page}', $task->current_page, $this->search_URL));
                $browser->waitFor("main#main", 10);
                $browser->script("window.scrollTo(300, 0)");
                $browser->screenshot("linkedinsearch");
                $mainElement = $browser->elements("main#main")[0];                
                Log::info("LinkedIn Bot-Search-Page".$this->current_page);

                //remove Overlay
                $browser->whenAvailable("#msg-overlay", function($modal) use($mainElement){
                    $overlayClass = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]'))->getAttribute("class");
                    if(strpos($overlayClass, "msg-overlay-list-bubble--is-minimized") == false){
                        $overlaybutton = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]/header/section[2]/button[2]'));
                        $modal->click('button#'.$overlaybutton->getAttribute('id'));
                    }
                });

                if($this->current_page == 1){
                    //find the max pages
                    if(count($browser->elements("div.search-results-container > div:last-child.artdeco-card"))){                        
                        $browser->scrollIntoView("div.search-results-container > div:last-child.artdeco-card");

                        $browser->whenAvailable("div.search-results-container > div:last-child.artdeco-card > div > div > ul:nth-child(2)", function($pagination){
                            $this->max_page = $pagination->element("li:last-child > button > span")->getText("innerHTML");
                        });    

                        $browser->screenshot("scrollTo");
                        $browser->script('window.scrollTo(300, 0);');
                    } else { 
                        $this->max_page = 1;
                    }
                }                
                $browser->with("div.search-results-container", function($container) use ($task){
                    $divelements = $container->elements("div:nth-child(2) > ul > li");
                    foreach($divelements as $key=>$element){ 
                                               
                        $image = "";
                        try{
                            $image = $element->findElement(WebDriverBy::xpath('div/div/div[1]/div/a/div/div/img'))->getAttribute('src');
                        } catch(\Exception $e){

                        }                        
                        
                        $url = $element->findElement(WebDriverBy::xpath('div/div/div[2]/div[1]/div/div[1]/span/div/span[1]/span/a'))->getAttribute('href');
                        $name = $element->findElement(WebDriverBy::xpath('div/div/div[2]/div[1]/div/div[1]/span/div/span[1]/span/a/span/span'))->getText();                                            

                        $container->screenshot("BeforeProfilePage");
                        $taskactioncount = TaskActions::where("task_user", $name)->where('task_user_url', $url)->count();
                        $taskskipactioncount = TaskSkipActions::where("task_user", $name)->where('task_user_url', $url)->count();
                        if(!$taskactioncount && !$taskskipactioncount){
                            $this->connectPeople($container, $key, $url, $name, $image, $task);
                            //$this->profilePage($sbrowser, $url, $name, $image, $task);
                        }
                        if($this->invitation_send){
                            break 1;
                        }                        
                        $container->scrollTo("div:nth-child(2) > ul > li:nth-child(".($key+1).")");
                        $container->screenshot("ScrollToElement");                                               
                    }
                    $divelements = $container->elements("div:nth-child(4) > ul > li");
                    if(count($divelements)){
                        foreach($divelements as $key=>$element){                        
                            $image = "";
                            try{
                                $image = $element->findElement(WebDriverBy::xpath('div/div/div[1]/div/a/div/div/img'))->getAttribute('src');
                            } catch(\Exception $e){
    
                            }                        
                            
                            $url = $element->findElement(WebDriverBy::xpath('div/div/div[2]/div[1]/div/div[1]/span/div/span[1]/span/a'))->getAttribute('href');
                            $name = $element->findElement(WebDriverBy::xpath('div/div/div[2]/div[1]/div/div[1]/span/div/span[1]/span/a/span/span'))->getText();                                            
    
                            $container->screenshot("BeforeProfilePage");
                            $taskactioncount = TaskActions::where("task_user", $name)->where('task_user_url', $url)->count();
                            $taskskipactioncount = TaskSkipActions::where("task_user", $name)->where('task_user_url', $url)->count();
                            if(!$taskactioncount && !$taskskipactioncount){
                                $this->connectPeople($container, $key, $url, $name, $image, $task);
                                //$this->profilePage($sbrowser, $url, $name, $image, $task);
                            }
                            if($this->invitation_send){
                                break 1;
                            }
                            $container->scrollTo("div:nth-child(4) > ul > li:nth-child(".($key+1).")");
                            $container->screenshot("ScrollToElement");                                               
                        }
                    }
                    $this->current_page++;                 
                });                
            }
        } catch(\Exception $e) {            
            Log::error($e->getMessage());
            $browser->screenshot("LinkedIn-Task-".$task->id.'-Error-'.time());
            $this->can_proceed = false;
        }        
    }

    /**
     * Connect Button Click
     */
    public function connectPeople($container, $key, $url, $name, $image, $task)
    {        
        $container->with("div:nth-child(2) > ul > li:nth-child(" . ($key + 1) . ") > div > div > div:nth-child(3)", function ($connectdiv)
        use ($url, $name, $image, $task) {
            if (count($connectdiv->elements("button"))) {
                $connectButton = $connectdiv->elements("button")[0];
                if ($connectButton->isEnabled() && $connectButton->getText() == "Connect") {
                    $connectdiv->click("button#" . $connectButton->getAttribute("id"));                    
                    $connectdiv->elsewhereWhenAvailable('#artdeco-modal-outlet', function ($modal) use ($connectButton, $task, $name, $url, $image) {                        
                        $addNoteButton = $modal->elements('div > div.artdeco-modal__actionbar > button')[0];                        
                        $modal->click("div > div.artdeco-modal__actionbar > button:first-child");

                        //Gender API
                        Log::info("LinkedIn Bot-Gender-API");
                        $full_name = $name;
                        $name = explode(" ", $name);
                        $first_name = $name[0];
                        $last_name = count($name) > 1 ? $name[count($name) - 1] : "";
                        $gender = $this->checkGender($first_name, $last_name);
                        $message = $task->message_undetect;
                        switch ($gender) {
                            case 'male':
                                $message = $task->message_male;
                                break;
                            case 'female':
                                $message = $task->message_female;
                                break;
                        }
                        $message = preg_replace(
                            ['/{first_name}/', '/{last_name}/', '/{full_name}/'],
                            [$first_name, $last_name, $full_name],
                            $message
                        );

                        $modal->value("div > div.artdeco-modal__content > div > textarea[name=message]", substr($message, 0, 300));                        
                        $modal->script("document.getElementById('custom-message').blur();");
                        Log::info("LinkedIn Bot-Modal-send-button");
                        //Send Button
                        $modal->mouseover("div > div.artdeco-modal__actionbar > button:nth-child(2)");                        
                        $modal->click("div > div.artdeco-modal__actionbar > button:nth-child(2)");
                        $modal->pause("2000");
                    });
                    $connectButton = $connectdiv->elements("button")[0];
                    $connectdiv->screenshot("LinkedIn-Task-".$task->id."-Send-".time());
                    if ($connectButton->getText()  == "Pending") {
                        Log::info("LinkedIn Bot-Request-send");
                        $this->invitation_send = true;
                        TaskActions::create([
                            'task_id' => $task->id,
                            'task_user' => $name,
                            'task_user_url' => $url,
                            'task_user_img' => $image,
                            'task_run_status' => 'send'
                        ]);
                    }
                }
            }
        });
    }

    public function profilePage($sbrowser, $url, $name, $image, $task)
    {
        if (!TaskActions::where("task_user", $name)
            ->where('task_user_url', $url)->count()) {
            $sbrowser->visit($url);
            $sbrowser->waitFor("main#main");
            $mainElement = $sbrowser->elements("main#main")[0];
            
            //remove Overlay
            $sbrowser->whenAvailable("#msg-overlay", function ($modal) use ($mainElement) {
                $overlayClass = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]'))->getAttribute("class");
                if (strpos($overlayClass, "msg-overlay-list-bubble--is-minimized") == false) {
                    $overlaybutton = $mainElement->findElement(WebDriverBy::xpath('//*[@id="msg-overlay"]/div[1]/header/section[2]/button[2]'));
                    $modal->click('button#' . $overlaybutton->getAttribute('id'));
                }
            });

            try{
                $connectButton = $mainElement->findElement(WebDriverBy::xpath('//*[@id="main"]/div/div[1]/section/div[2]/div[1]/div[2]/div/div/div[1]/div/button'));
                if ($connectButton->getText() == "Connect") {
                    //$sbrowser->script("window.scrollTo(300, 0)");                                   
                    $sbrowser->click("button#" . $connectButton->getAttribute("id"));
                    $sbrowser->whenAvailable('#artdeco-modal-outlet', function ($modal) use ($connectButton, $task, $name, $url, $image) {
                        $addNoteButton = $modal->elements('div > div.artdeco-modal__actionbar > button')[0];                        
                        $modal->click("div > div.artdeco-modal__actionbar > button:first-child");

                        //Gender API
                        $full_name = $name;
                        $name = explode(" ", $name);
                        $first_name = $name[0];
                        $last_name = count($name) > 1 ? $name[count($name)]:"";
                        $gender = $this->checkGender($first_name, $last_name);
                        $message = $task->message_undetect;
                        switch($gender){
                            case 'male':
                                $message = $task->message_male;
                                break;
                            case 'female':
                                $message = $task->message_female;
                                break;
                        }
                        $message = preg_replace(
                            ['{first_name}', '{last_name}', '{full_name}'],
                            [$first_name, $last_name, $full_name],
                            $message
                        );

                        $modal->value("div > div.artdeco-modal__content > div > textarea[name=message]", substr($message, 0, 300));                        
                        $modal->script("document.getElementById('custom-message').blur();");

                        $sendButton = $modal->elements('div > div.artdeco-modal__actionbar > button:nth-child(2)')[0];
                        $modal->mouseover("div > div.artdeco-modal__actionbar > button:nth-child(2)");                        
                        $modal->click("div > div.artdeco-modal__actionbar > button:nth-child(2)");                
                        $modal->pause("2000");                    
                        if ($connectButton->getText()  == "Pending") {
                            $this->invitation_send = true;
                            TaskActions::create([
                                'task_id' => $task->id,
                                'task_user' => $full_name,
                                'task_user_url' => $url,
                                'task_user_img' => $image,
                                'task_run_status' => 'send'
                            ]);
                        }
                        $modal->screenshot("ClickSendBtn");
                    });
                    $sbrowser->scrollTo("button#" . $connectButton->getAttribute("id"));
                }
            } catch(\Exception $e){
                //No connect button
            }    

            $sbrowser->visit($url);
            $sbrowser->waitFor("main#main");            
        }
        //$this->invitation_send = true;
    }
    /**
     * Gender Checking
     * @param $name String First Name and Last Name
     * @return String Gender
     */
    public function checkGender($first_name, $last_name)
    {        
        try{
            $setting = Settings::where("var_name", 'gender_api_token')->first();
            $apiClient = new GenderApiClient($setting->value);
            // Get gender by email address name and country
            $lookup = $apiClient->getByFirstNameAndLastName($first_name.' '.$last_name);
            return $lookup->getGender();
        } catch(\Exception $e){
            return 'undetect';
        }        
    }
}
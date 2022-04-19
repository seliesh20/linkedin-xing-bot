<?php

namespace App\AutoBot;

use App\Models\TaskCookies;
use App\Models\TaskActions;
use App\Models\Settings;
use App\AutoBot\Bot;
use DateTime;
use Facebook\WebDriver\WebDriverBy;
use Laravel\Dusk\Browser;

use Illuminate\Support\Facades\Crypt;
use GenderApi\Client as GenderApiClient;

use Illuminate\Support\Facades\Log;

class XingBot {
    
    private $login_URL = "https://www.xing.com/";
    private $home_URL = "https://www.xing.com/home";
    private $search_URL = "https://www.xing.com/search/members?keywords={keyword}";
    private $search_page_key = "MembersSearchConnection---{page}";
    private $search_page_key_append = "OQ%3D%3D";
    private $withdraw_URL = "https://www.xing.com/network/requests/sent";

    private $can_proceed = true;
    private $max_page = 2;
    private $current_page = 0;
    private $invitation_send = false; 

    public function run($task)
    {
        //Login to LinkedIn
        Bot::browse(function(Browser $browser, Browser $sbrowser) use($task){
            Log::info("Xing Bot-Started");
            $this->loginControl($browser, $task);
            $this->loginControl($sbrowser, $task);
            Log::info("Xing Bot-Login Completed");

            if($this->can_proceed){
                $this->searchPeople($browser, $sbrowser, $task);
            }

        });
    }

    public function searchPeople($browser, $sbrowser, $task){
        try{
            $this->search_URL = str_replace('{keyword}', $task->search_query, $this->search_URL);
            while($this->current_page < $this->max_page && !$this->invitation_send){                
                if($this->current_page > 0 && !strpos($this->search_URL, 'after=')){                    
                    $this->search_URL.="&first=20&after={search_key}&last=&before=";
                } 

                $search_key = base64_encode(str_replace("{page}", $this->current_page, $this->search_page_key)).$this->search_page_key_append;                
                $browser->visit(str_replace("{search_key}", $search_key, $this->search_URL));
                $browser->pause(2000);

                Log::info("Xing Bot-Search-Page".$this->current_page);

                if($this->current_page == 0){
                    $maxpagecontainer = $browser->elements("main#content > div > div.Frame-style-container-c4dc0351 > main > div:nth-child(6) > div > div > div > div > div.MembersResults-MembersResults-paginationContainer-15ce18dc > nav > ol > li.malt-pagination-Pagination-display-e06601fc > span.malt-pagination-Pagination-label-89b32c49.malt-pagination-Pagination-firstItem-c55ae25c");
                    if(is_array($maxpagecontainer) && count($maxpagecontainer)){
                        $this->max_page = $maxpagecontainer[0]->getText();                                                
                    } else {
                        $this->max_page = 0;
                    }
                }    
                
                $listcontainer = $browser->elements("#content > div > div.Frame-style-container-c4dc0351 > main > div:nth-child(6) > div > div > div > div > div.MembersResults-MembersResults-container-8bced388");
                if(is_array($listcontainer) && count($listcontainer)){
                    $listcontainer = $listcontainer[0]; 
                    $listelements = $listcontainer->findElements(WebDriverBy::xpath('a'));
                    foreach($listelements as $listelement){
                        $image = "";
                        try{    
                            $image = $listelement->findElement(WebDriverBy::xpath('div/div[1]/div/div/div[1]/img'))->getAttribute("src");
                        } catch(\Exception $e){

                        }                        
                        $name = "";
                        try{    
                            $name = $listelement->findElement(WebDriverBy::xpath('div/div[2]/div[1]/div[1]/span'))->getText();
                        } catch(\Exception $e){

                        }
                        $url = $listelement->getAttribute("href");
                        $this->connectPeople($sbrowser, $url, $name, $image, $task);

                        if($this->invitation_send){
                            break 1;
                        }
                    }
                }                
                $this->current_page++;
            }
        } catch(\Exception $e){
            Log::error($e->getMessage());
            $browser->screenshot("Xing-Task-".$task->id.'-Error-'.time());
        }
    }
    /**
     * Send Invitation
     */
    public function connectPeople($browser, $url, $name, $image, $task)
    {
        if (!TaskActions::where("task_user", $name)
        ->where('task_user_url', $url)->count()) {

            //Gender API
            Log::info("Xing Bot-Gender-API");
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

            $browser->visit($url);
            $browser->waitFor("main#content");
            $browser->click("main#content button[data-qa=profile-primary-action]");            
            $browser->pause(1000);            
            $browser->whenAvailable("div.src-Canvas-canvas-47d3ef0c > div > div", function ($modal) use ($task, $full_name, $url, $image, $message) {                
                $modal->type("div:nth-child(3) > div > textarea", $message);                
                Log::info("Xing Bot-Modal-send-button");
                $modal->click("div:nth-child(4) > button:nth-child(2)");                
                Log::info("Xing Bot-Request-send");
                $this->invitation_send = true;
                TaskActions::create([
                    'task_id' => $task->id,
                    'task_user' => $full_name,
                    'task_user_url' => $url,
                    'task_user_img' => $image,
                    'task_run_status' => 'send'
                ]);
            });            
        }
    }

    public function withdraw($task)
    {
        Bot::browse(function(Browser $browser) use($task){                     
            $this->loginControl($browser, $task);                        
            if($this->can_proceed){
                $browser->visit($this->withdraw_URL);
                $browser->waitFor("main#content", 10);                
                if(count($browser->elements("main#content > div > div:nth-child(2) > div > div > div > ul > li"))){
                    foreach($browser->elements("main#content > div > div:nth-child(2) > div > div > div > ul > li") as $key => $element){
                        if($this->can_proceed){
                            $browser->with("main#content > div > div:nth-child(2) > div > div > div > ul > li:nth-child(".($key+1).")", function($container) 
                                use($key,$task)
                            {                                   
                                $name = $container->element("div > div:nth-child(1) > div:nth-child(1) > div:nth-child(1) > a > div:nth-child(2) > div:nth-child(1) > h2")->getText(); 
                                $url = $container->element("div > div:nth-child(1) > div:nth-child(1) > div:nth-child(1) > a")->getAttribute("href"); 
                                $image = $container->element("div > div:nth-child(1) > div:nth-child(1) > div:nth-child(1) > a > div:nth-child(1) > div > div > img")->getAttribute("src");
                                //url remove params
                                $url = explode("?", $url)[0];
                                
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
                                        Log::info("Xing Bot-Withdrawal days".$days);
                                        $container->click("div > div > span:nth-child(2) > div > button:nth-child(2)");
                                        $container->elsewhereWhenAvailable("div:last-child", function($dropdown){
                                            $dropdown->click('ul > li:nth-child(1) > button');                                            

                                            $dropdown->elsewhereWhenAvailable("div:last-child", function($modal){
                                                $modal->click("div > div > div:nth-child(2) > button:nth-child(2)");                                        
                                            });                                    
                                        });
                                        $container->pause(2000);
                                        if(!count($container->elements("div > div > span:nth-child(2)"))){  
                                            //Update TaskAction
                                            $action->task_run_status = "withdrawn";
                                            $action->save();

                                            //stopping at one completed
                                            $this->can_proceed = false;
                                        }
                                    }
                                }
                            });
                        }
                    }
                }
            }
        });        
    }

    public function loginControl($browser, $task)
    {   
        try {
            $browser->visit($this->login_URL);
            $browser->screenshot("Login");            
            if (count($browser->elements(".jkt0t3-0.cookie-consent-CookieConsent-bottomSheet-f2212f99"))) {
                $browser->whenAvailable(".jkt0t3-0.cookie-consent-CookieConsent-bottomSheet-f2212f99", function ($modal) {                                        
                    $modal->click("#consent-accept-button");
                });
            }            
                        
            $task_cookie = TaskCookies::where('task_type', $task->task_type)
                ->where('login_email', $task->login_email)
                ->where('login_password', $task->login_password)
                ->first();                
            if (is_object($task_cookie)) {                
                //use cookie
                $cookies = unserialize(Crypt::decryptString($task_cookie->cookie_string));
                foreach ($cookies as $cookie) {
                    $browser->driver->manage()->addCookie($cookie);
                }
                $browser->visit($this->home_URL);
            }
            try{                
                $browser->screenshot("XingCookieLogin");
                $browser->waitFor("div#people", 10);                
                return true;
            } catch (\Exception $e){                
                $browser->waitFor("div#javascript-content")                    
                    ->type("username", $task->login_email)
                    ->type("password", Crypt::decryptString($task->login_password))
                    ->click("#javascript-content > div.Layouts-IllustrationGrid-wrapperSpace-96fb276a > div > div > div:nth-child(1) > div.Start-Start-formContainer-9e72b704 > div > form > button")
                    ->waitFor("main#content", 10);

                    if(strpos($browser->driver->getCurrentURL(), '/home') > -1){
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
            $browser->screenshot("Xing-Task-".$task->id.'-Error-'.time());
            $this->can_proceed = false;
        }

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
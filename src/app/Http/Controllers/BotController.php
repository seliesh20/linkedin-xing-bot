<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskCrons;
use App\Models\TaskActions;
use App\AutoBot\LinkedInBot;
use App\AutoBot\XingBot;

class BotController extends Controller
{
    //
    public function runTask()
    {        
        try{
            $start_cron = false;
            $crons = TaskCrons::where("type", "request")
                ->where("status", 0)->get();              
            foreach($crons as $cron){  
                $cron_end_time = new \DateTime($cron->end_time);
                $today = new \DateTime(date("Y-m-d"));
                $interval = $cron_end_time->getTimestamp() - $today->getTimestamp();
                $send_request_count = TaskActions::where('task_id', $cron->task->id)->count();

                if(!$start_cron 
                    && $cron->status == 0 //Cron status
                    && $cron->request_delay < $interval //Delay Seconds
                    && $cron->task->task_status_id == 1 //task staus 
                    && $send_request_count < $cron->task->max_request){

                        //Start Cron 
                        $start_cron = true;

                        //update task cron
                        $cron->status = 1;
                        $cron->start_time = date("Y-m-d H:i:s");
                        $cron->save();


                        switch($cron->task->task_type){
                            case 'L' :
                                $bot = new LinkedInBot();
                                $bot->run($cron->task);
                                break;
                            case 'X' :
                                $bot = new XingBot();
                                $bot->run($cron->task);
                                break;
                            default:
                                //Invalid Task
                        }

                        //update task cron
                        $cron->status = 0;
                        $cron->end_time = date("Y-m-d H:i:s");
                        $cron->save();
                    
                } else if($send_request_count >= $cron->task->max_request){
                    $cron->task->task_status_id = 2;
                    $cron->task->save();
                }
            }
        } catch(\Exception $e){

        }   
    }

    public function withdrawTask(){
        try{
            $start_cron = false;
            $crons = TaskCrons::where("type", "withdraw")
                ->where("status", 0)
                ->orderBy("start_time", 'asc')
                ->get();                
            foreach($crons as $cron){                                        
                if(!$start_cron                 
                    && $cron->task->task_status_id == 1 //task staus 
                    && TaskActions::where('task_id', $cron->task->id)
                        ->where('task_run_status', 'send')->count()){                                              
                        //Start Cron                         
                        $start_cron = true;
                        
                        //update task cron
                        $cron->status = 1;
                        $cron->start_time = date("Y-m-d H:i:s");
                        $cron->save();                        
                        
                        switch($cron->task->task_type){
                            case 'L' :
                                $bot = new LinkedInBot();
                                $bot->withdraw($cron->task);
                                break;
                            case 'X' :                                
                                $bot = new XingBot();
                                $bot->withdraw($cron->task);
                                break;
                            default:
                                //Invalid Task
                        }

                        //update task cron
                        $cron->status = 0;
                        $cron->end_time = date("Y-m-d H:i:s");
                        $cron->save();
                    
                } 
            }
        } catch(\Exception $e){
            
        }  
    }
}

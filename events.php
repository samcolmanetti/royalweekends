<?php
    class Event{
        
        public $link;
        public $title;
        public $description;
        public $start_date; 
        public $end_date;
        public $time_start;
        public $time_end; 
        public $location; 
        public $pic_url;
        
        public function __construct($e){
            $header_date_format = "m/d/Y";
            $date_format = "l, M jS Y";
            $time_format = "g:iA";
            date_default_timezone_set('America/New_York');
    
            $start_dt = strtotime($e->startdate);
            $end_dt = strtotime($e->enddate);
    
            $this->link = $e->link;
            $this->title = trim($e->title);
            $this->description = trim($e->description);
            $this->start_date = date($date_format,$start_dt);
            $this->end_date = date($date_format,$end_dt);
            $this->time_start = date($time_format,$start_dt);
            $this->time_end =  date($time_format,$end_dt);
            $this->location = trim($e->location);
            $this->pic_url = $e->content[0]["url"];
        }
    }
    
    class EventManager {
        public $events = array(); 
        public $deans_event; 
        
        public function __construct($start_date, $end_date){
            $data = $this->getEventsXml();
            
            $start = strtotime($start_date);
            $end = strtotime($end_date);

            foreach ($data->channel->item as $i){
                $event = new Event ($i[0]);
                
                $eDate = strtotime($event->start_date);
                if ($eDate >= $start && $eDate <= $end){
                    array_push($this->events, $event);    
                }
            }
        }
        
        public function isDeansEvent($e){
            return strpos($e->title, 'Dean') !== false && strpos($e->title, 'Meet') !== false; 
        }
        
        function getEventsXml(){
            $api_key = "gxgzGfg5OcYVfd84-HBQFwttYn4klWvaV34TRXjZMeQ";
            $xml = file_get_contents("https://api.orgsync.com/api/v3/communities/656/events.rss?key=${api_key}&per_page=100&upcoming=true");
            $opening_pattern = "#<\w+:#";
            $closing_pattern = "#</\w+:#";
            
            $xml = preg_replace($opening_pattern, "<", $xml);
            $xml = preg_replace($closing_pattern, "</", $xml);
            
            //echo htmlspecialchars($xml);
            
            $data = simplexml_load_string($xml) or die("Error: Cannot create object");
            
            //echo ($data->channel->item[10]->content[0]["url"]);
            //echo json_encode($data->channel->item[30]->content["url"]);
            
            return $data;
        }
        
        function cmp($a, $b){
            $e1 = strtotime($a->start_date);
            $e2 = strtotime($b->start_date);
            
            return $e1 - $e2; 
        }
        
        function dumpEvents(){
            usort($this->events, array($this, "cmp"));
            $html = ''; 
            
            $html .= '<style>table, th, td {border: 1px solid black; padding: 5px; text-align: center;}</style>';
            $html .= '<table style="width:100%">';
            
            foreach ($this->events as $event){
                $html .= '<tr>';
                $html .= "<th>{$event->title}</th>";
                $html .= "<th>{$event->start_date}</th>";
                $html .= "<th>{$event->time_start}</th>";
                $html .= "<th>{$event->time_end}</th>";
                $html .= "<th>{$event->location}</th>";
                $html .= "<th>{$event->link}</th>";
                $html .= "<th>{$event->pic_url}</th>";
                $html .= '</tr>';
            }
            $html .= '</table>';
            
            return $html; 
        }
        
        function getHtml(){
            usort($this->events, array($this, "cmp"));
            
            if (sizeof($this->events) <= 0) return;
            
            $html = '<p>#RoyalWeekends is your weekly list of events going on on campus! Make sure to watch for emails every Thursday!</p><p>Use <strong>#RoyalWeekends</strong> to tell us what you are doing this weekend! @UofSClubs</p><h2><span style="color:#4B0082;">EVENTS</span></h2>';
            
            $currentDate = ''; 
            
            foreach ($this->events as $event){
                if (strcasecmp($currentDate, $event->start_date)  != 0) {
                    $html .= '<h2><span style="color:#4B0082;">' .strtoupper($event->start_date) . '</span></h2>';
                    $html .= '<hr />';
                    
                    $currentDate = $event->start_date; 
                }
                
                if ($this->isDeansEvent($event)){
                    $this->deans_event = $event; 
                    continue; 
                }
                
                $html .= $this->getEventHtml($event); 
            }
            
            if (isset($this->deans_event)){
                $html .= '<h2><span style="color:#4B0082;">NEED ASSISTANCE? WANT TO TALK?</span></h2>';
                $html .= '<hr />';
                
                $html .= $this->getEventHtml($this->deans_event); 
            }
            
            
            return $html; 
        }
        
        public function getEventHtml($event){
            $html = '';
            
            $html .= '<div class="event" style="padding-bottom:10px;">';
                
                if (sizeof($event->pic_url) == 0){
                    
                }
                $html .= '<div class="image" style="display:inline-block;"><img alt="No Image Available" src="'. 
                    $event->pic_url . '"style="width: 100px; height: 100px; border-radius: 50%;\" /></div>';
                
                $html .= '<div class="text" style="display:inline-block;vertical-align: top; margin-left: 12px; width: 75%;">';
                
                $html .= '<h3>' . $event->title . '</h3>';
                
                if (sizeof($event->time_start) > 0 && sizeof($event->location) > 0){
                    $html .= '<h4>' . $event->time_start .' - ' . $event->time_end . '. '. $event->location . '</h4>';
                } else if (sizeof($event->time_start) > 0){
                    $html .= '<h4>' . $event->time_start .' - ' . $event->time_end . '</h4>';
                } else if(sizeof($event->location)){
                    $html .= '<h4>' . $event->location . '</h4>';
                }
                
                $html .= '<h6><span style="font-size:11px;">' . $event->description . '</span></h6>';
                $html .= '<div class="button-group "><a class="button green" href="' . $event->link . '" rel="nofollow">Learn More</a></div>'; 
                
                $html .= '</div></div>';
                
                return $html; 
        }
        
    }
    

?>
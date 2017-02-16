<?php
    require 'events.php';
    
    if (isset($_GET['start_date'])) {
      $start_dt = date("l, M dS Y",strtotime($_GET['start_date']));
    } else {
        
    }
    
    if (isset($_GET['end_date'])) {
      $end_dt = date("l, M dS Y",strtotime($_GET['end_date'] . '+1 day'));
    } else {
    }
    
    $em = new EventManager($start_dt,$end_dt); 
    echo $em->getHtml();
?>
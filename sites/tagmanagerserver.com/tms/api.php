<?php

require_once('TMS.php');

$oTMS = new TMS();
$oEventData = file_get_contents('php://input');
$oTMS->TMS_Event($oEventData);


?>
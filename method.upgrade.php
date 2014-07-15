<?php
if (!isset($gCms)) exit;

$current_version = $oldversion;
switch($current_version)
{
    case "0.1.0":
    case "0.2.0":
        $this->createDatabaseIfNeeded();
        $this->AddEventHandler('Cron', 'Cron15min', false);
}

// put mention into the admin log
$this->Audit( 0, $this->Lang('friendlyname'), $this->Lang('upgraded',$this->GetVersion()));

?>
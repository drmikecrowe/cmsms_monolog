<?php
if (!isset($gCms)) exit;

$this->createDatabaseIfNeeded();
$this->Audit( 0, $this->Lang('friendlyname'), $this->Lang('installed',$this->GetVersion()));

?>
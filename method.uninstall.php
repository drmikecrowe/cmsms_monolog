<?php
if (!isset($gCms)) exit;

$prefix = cms_db_prefix();
$db = cmsms()->GetDb();
$db->Execute("DROP TABLE IF EXISTS {$prefix}monolog");
$this->Audit( 0, $this->Lang('friendlyname'), $this->Lang('uninstalled'));

?>
<?php
/* @var $this AppsMarket */

/** @var $db ADOConnection */
$db = cmsms()->GetDb();

if (!isset($gCms)) exit;

$prefix = cms_db_prefix();

$db->Execute("TRUNCATE {$prefix}monolog");
if ( $db->ErrorNo() ) {
    echo "ERROR: ".$db->ErrorMsg()." (".$db->ErrorNo().")\n";
    die;
}

$this->Redirect($id, 'defaultadmin', '', array("module_message"=>"No settings changed","tab"=>"settings"));
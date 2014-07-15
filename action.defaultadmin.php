<?php

use \Monolog\Logger;

if (!isset($gCms)) exit;

if (!$this->CheckAccess()) {
    return $this->DisplayErrorPage($id, $params, $returnid, $this->Lang('accessdenied'));
}

$return_tab="";
if (isset($params["tab"])) $return_tab=$params["tab"];

$tabs = array();

$tabs[] = 'settings';
$tabs[] = 'logbrowser';

if ( array_key_exists("tab_error", $params) && $params['tab_error']) {
    echo $this->ShowErrors($params['tab_error']);
}
if ( array_key_exists("tab_info", $params) && $params['tab_info']) {
    $this->ShowMessage($params['tab_info']);
}

echo $this->StartTabHeaders();
foreach ( $tabs as $tab )
    echo $this->SetTabHeader($tab,$this->Lang("title_$tab"),($tab==$return_tab));
echo $this->EndTabHeaders();

// Thecontent of the tabs
echo $this->StartTabContent();

foreach ( $tabs as $tab ) {
    echo $this->StartTab($tab,$params);
    include(dirname(__FILE__)."/function.{$tab}.php");
    echo $this->EndTab();
}

echo $this->EndTabContent();
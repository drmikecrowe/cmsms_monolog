<?php
/* @var $this CMSMonolog */

if (!isset($gCms))
	exit;
if (!$this->VisibleToAdminUser())
	exit;

$smarty = $this->smarty;

$smarty->assign('id','lb');

$headers = LogBrowserDataTable::get_headers();
$searchable = LogBrowserDataTable::get_searchable();
$show_search = LogBrowserDataTable::get_show_search();

$ajax_params = array('disable_theme'=>'true','showtemplate' => 'false');
$ajurl=$this->CreateLink($id, 'ajax_get_adminlogbrowser', $returnid, '', $ajax_params, '', true);
$smarty->assign('headers',$headers);
$smarty->assign('searching',$searchable);
$smarty->assign('show_search',$show_search);
$smarty->assign('urlajax', $ajurl);
$smarty->assign('add',"");
$smarty->assign('init',1);
$smarty->assign('sort',0);
$smarty->assign('sortdir',"desc");
$smarty->assign('lower',0);
$smarty->assign('upper',5);
echo $this->ProcessTemplate('admindatatable.tpl');


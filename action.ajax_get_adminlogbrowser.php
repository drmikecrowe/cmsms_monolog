<?php
/* @var $this CMSMonolog */

use \Monolog\Logger;

if (!isset($gCms))
    exit;
if (!$this->VisibleToAdminUser())
    exit;

$headers = LogBrowserDataTable::get_headers();
$aColumns = array_keys($headers);

$order_by = "ORDER BY id DESC";
$where = array();

/*
    "id" => array(
    "channel" => array(
    "level" => array(
    "message" => array(
    "time" => array(
 */

/* ordering */
if (isset($_GET['iSortCol_0'])) {
	for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
		if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
			$order_by = "ORDER BY ".$aColumns[intval($_GET['iSortCol_' . $i])] ." ". $_GET['sSortDir_' . $i];
			break;
		}
	}
}

$levels = Logger::getLevels();

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
	if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
		$field = $_GET['sSearch_' . $i];
		switch ( $aColumns[$i] ) {
            case "channel":
                $where[] = "channel LIKE '%".$field."%'";
                break;
			case "level":
                $field = $levels[$field];
				$where[] = "level = ".$field;
				break;
            case "message":
                $where[] = "message REGEXP '".$field."'";
                break;
            case "time":
                $where[] = "`time` LIKE '%".$field."%'";
                break;
			default:
				$query->filterBy($aColumns[$i], "%$field%",Criteria::LIKE);
				break;
		}
	}
}
if ( count($where) > 0 )
    $swhere = "WHERE ".implode(" AND ",$where);
else
    $swhere = "";

/* Paging */
$sLimit = 50;
$sPage = 1;
$sOffset = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = $_GET['iDisplayLength'];
    $sPage = round(($_GET['iDisplayStart']-1)/$sLimit,0);
    if ( $sPage > 0 ) {
        $sOffset = "OFFSET $sPage";
    }
}
$limit = "LIMIT $sLimit $sOffset";


$prefix = cms_db_prefix();
$sql = <<<EOS
SELECT *
FROM `{$prefix}monolog`
$swhere
$order_by
$limit
EOS;

$levels = array_flip(Logger::getLevels());

$data = $db->GetArray($sql);
$aaData = array();
if ( data ) {
    foreach ($data as $items) {
        $one = array();
        foreach ($headers as $k => $v) {
            switch ($k) {
                case "level":
                    $one[] = $levels[$items[$k]];
                    break;
                case "message":
                    $items[$k] = "<pre>".$items[$k]."</pre>";
                default:
                    $one[] = $items[$k];
                    break;
            }
        }
        $aaData[] = $one;
    }
    $row = $db->GetRow("SELECT COUNT(id) FROM `{$prefix}monolog`");
    $iTotal = $row['COUNT(id)'];
    $row = $db->GetRow("SELECT COUNT(id) FROM `{$prefix}monolog` $swhere");
    $iFilteredTotal = $row['COUNT(id)'];
} else {
    $iTotal = 0;
    $iFilteredTotal = 0;
}

$jsonArr = array("iTotalRecords" => $iFilteredTotal, "iTotalDisplayRecords" => $iTotal, "aaData" => $aaData);

echo json_encode($jsonArr);
ob_end_flush();
exit;

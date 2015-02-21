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
if (isset($_GET['order'])) {
    $order = $_GET['order'];
	$order_by = "ORDER BY ".$aColumns[intval($order[0]['column'])] ." ". $order[0]['dir'];
}

$levels = Logger::getLevels();

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
    $column = $_GET['columns'][$i];
	if ($column['searchable']) {
		$field = $column['search']['value'];
        if ( empty($field) ) continue;
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
                //$where[] = "`".$aColumns[$i]."` LIKE \"%$field%\"";
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
if (isset($_GET['length']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = $_GET['length'];
    $sPage = round(($_GET['start'])/$sLimit,0);
    if ( $sPage > 0 ) {
        $sOffset = "OFFSET ".$_GET['start'];
    }
}
$limit = "LIMIT $sLimit $sOffset";

$prefix = cms_db_prefix();
$sql = <<<EOS
SELECT SQL_CALC_FOUND_ROWS *
FROM `{$prefix}monolog`
$swhere
$order_by
$limit
EOS;

$levels = array_flip(Logger::getLevels());

$data = $db->GetArray($sql);
$aaData = array();
if ( $data ) {
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
    $row = $db->GetRow("SELECT FOUND_ROWS()");
    $iFilteredTotal = $row['FOUND_ROWS()'];
    $row = $db->GetRow("SELECT COUNT(id) FROM `{$prefix}monolog`");
    $iTotal = $row['COUNT(id)'];
} else {
    $iTotal = 0;
    $iFilteredTotal = 0;
}

$jsonArr = array("recordsFiltered" => $iFilteredTotal, "recordsTotal" => $iTotal, "data" => $aaData);

echo json_encode($jsonArr);
ob_end_flush();
exit;

<?php

/**
 * Description of LogBrowserDataTable
 *
 * @author mcrowe
 */
class LogBrowserDataTable {

	static $fields = array(
        "id" => array(
            "header" => "ID",
            "searchable" => false,
        ),
        "channel" => array(
            "header" => "Channel",
            "searchable" => true
        ),
        "level" => array(
            "header" => "Level",
            "searchable" => true
        ),
        "message" => array(
            "header" => "Message",
            "searchable" => true
        ),
        "time" => array(
            "header" => "Time",
            "searchable" => true,
        )
	);
	
	public static function get_searchable() {
		$parts = array();
		foreach ( self::$fields as $key => $value )
			if ( $value['searchable'] )
				$parts[] = '{ "bSearchable": true }';
			else
				$parts[] = 'null';
		$searchable = sprintf('"aoColumns": [%s],',implode(",",$parts));
		return $searchable;
	}

	public static function get_show_search() {
		$parts = array();
		foreach ( self::$fields as $key => $value )
			$parts[] = $value['searchable'];
		return $parts;
	}
	
	public static function get_headers() {
		$parts = array();
		foreach ( self::$fields as $key => $value )
			$parts[$key] = $value['header'];
		return $parts;
	}

}

?>

<?php
/* @var $this AppsMarket */

if (!isset($gCms)) exit;

$dirty = false;

$fields = array();

if (isset($params["email_on_warning"])) {
    $this->SetPreference("email_on_warning",$params["email_on_warning"]);
    $dirty = true;
} else {
    $this->SetPreference("email_on_warning",false);
    $dirty = true;
}

if (isset($params["email_on_error"])) {
    $this->SetPreference("email_on_error",$params["email_on_error"]);
    $dirty = true;
} else {
    $this->SetPreference("email_on_error",false);
    $dirty = true;
}

if (isset($params["admin_emails"])) {
    $this->SetPreference("admin_emails",$params["admin_emails"]);
    $dirty = true;
}

if (isset($params["default_level"])) {
    $this->SetPreference("default_level",$params["default_level"]);
    $dirty = true;
}

if (isset($params["include_newlines"])) {
    $this->SetPreference("include_newlines",$params["include_newlines"]);
    $dirty = true;
}

if (isset($params["date_format"])) {
    $format = $params["date_format"];
    if ( strlen($format) < 10 ) {
        $format = "Y-m-d H:i:s";
    }
    $this->SetPreference("date_format",$format);
    $dirty = true;
}

if (isset($params["log_format"])) {
    $format = $params["log_format"];
    if ( strlen($format) < 10 ) {
        $format = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    }
    $this->SetPreference("log_format",$format);
    $dirty = true;
}

if ( $dirty ) {
    $this->Redirect($id, 'defaultadmin', '', array("module_message"=>"Settings Saved","tab"=>"settings"));
    return;
}

$this->Redirect($id, 'defaultadmin', '', array("module_message"=>"No settings changed","tab"=>"settings"));
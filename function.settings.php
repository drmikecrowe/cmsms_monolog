<?php
/* @var $this CMSMonolog */

use \Monolog\Logger;

function field($name, $input)
{
    $onerow = new stdClass();
    $onerow->name = $name;
    $onerow->input = $input;
    return $onerow;
}

if (!isset($gCms))
	exit;
if (!$this->VisibleToAdminUser())
	exit;

$smarty = $this->smarty;

$this->smarty->assign('title_section',"CMS-Monolog Settings");

$fields = array();
$levels = Logger::getLevels();
$default = $this->GetPreference("default_level",400);
$index = array_flip(array_keys(array_flip($levels)))[$default];
$fields[] = field("Default Log Level",$this->CreateInputDropdown($id,"default_level",$levels,$index));
$fields[] = field("Email on Warning",$this->CreateInputCheckbox($id,"email_on_warning",1,$this->GetPreference("email_on_warning",1)));
$fields[] = field("Email on Error",$this->CreateInputCheckbox($id,"email_on_error",1,$this->GetPreference("email_on_error",1)));
$fields[] = field("Use Database Driver by default",$this->CreateInputCheckbox($id,"use_database_default",1,$this->GetPreference("use_database_default",1)));
$fields[] = field("Error Email(s)",$this->CreateInputText($id,"admin_emails",$this->GetPreference("admin_emails",null),80,255));
$fields[] = field("Keep newlines in logs",$this->CreateInputCheckbox($id,"include_newlines",1,$this->GetPreference("include_newlines",0)));
$fields[] = field("Log format",$this->CreateInputText($id,"log_format",$this->GetPreference("log_format","[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"),80,255));
$fields[] = field("Date format",$this->CreateInputText($id,"date_format",$this->GetPreference("date_format","Y-m-d H:i:s"),20,20));

$clear = <<<EOS
<span class="cms_submit ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary">
<span class="ui-button-icon-primary ui-icon ui-icon-circle-check"></span><span class="ui-button-text">Clear Log</span>
</span>
EOS;


$this->smarty->assign('fields',$fields);
$this->smarty->assign('settingssubmit', $this->CreateInputSubmit($id,"settingssubmit","Save Settings"));
$this->smarty->assign('clearbutton', $this->CreateLink($id,"do_clear",$returnid,$clear,array(),"Clear All Messages -- Are You Sure?"));
$this->smarty->assign('formstart',$this->CreateFormStart($id,"do_settings",$returnid,"post","",false,""));
$this->smarty->assign('formend',$this->CreateFormEnd());

echo $this->ProcessTemplate("adminsettings.tpl");

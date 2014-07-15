<?php

#-------------------------------------------------------------------------
# Module: Monolog - Integrates https://github.com/Seldaek/monolog into CMS Made Simple
# Version: 0.1.0, Mike Crowe
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2014 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
# This file originally created by ModuleMaker module, version 0.3.2
# Copyright (c) 2014 by Samuel Goldstein (sjg@cmsmadesimple.org)
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#-------------------------------------------------------------------------
# For Help building modules:
# - Read the Documentation as it becomes available at
#   http://dev.cmsmadesimple.org/
# - Check out the Skeleton Module for a commented example
# - Look at other modules, and learn from the source
# - Check out the forums at http://forums.cmsmadesimple.org
# - Chat with developers on the #cms IRC channel
#-------------------------------------------------------------------------

use \Monolog\Logger;
use \Monolog\Formatter\LineFormatter;

require_once __DIR__."/vendor/autoload.php";
require_once __DIR__."/PDOHandler.class.php";
require_once __DIR__."/MLog.class.php";


class CMSMonolog extends CMSModule {
    var $channels = array();

    function GetName()                              { return 'CMSMonolog';                       }
    function GetFriendlyName()                      { return $this->Lang('friendlyname');     }
    function GetVersion()                           { return '0.2.0';                         }
    function GetHelp()                              { return $this->Lang('help');             }
    function GetAuthor()                            { return 'Mike Crowe';                    }
    function GetAuthorEmail()                       { return 'drmikecrowe@gmail.com';         }
    function GetChangeLog()                         { return $this->Lang('changelog');        }
    function IsPluginModule()                       { return false;                           }
    function HasAdmin()                             { return true;                            }
    function GetAdminSection()                      { return 'siteadmin';                     }
    function GetAdminDescription()                  { return $this->Lang('admindescription'); }
    function VisibleToAdminUser()                   { return true;                            }
    function CheckAccess($perm = 'Clear Admin Log') { return $this->CheckPermission($perm);   }
    function GetDependencies()                      { return array('Cron'=>'0.0.8');          }
    function MinimumCMSVersion()                    { return "1.11.7";                        }
    function MaximumCMSVersion()                    { return "3.0.0";                         }
    function InstallPostMessage()                   { return $this->Lang('postinstall');      }
    function UninstallPostMessage()                 { return $this->Lang('postuninstall');    }
    function UninstallPreMessage()                  { return $this->Lang('really_uninstall'); }

    public function InitializeAdmin() {
        $this->RestrictUnknownParams(false);
    }

    function DisplayErrorPage($id, &$params, $return_id, $message = '') {
        $this->smarty->assign('title_error', $this->Lang('error'));
        $this->smarty->assign_by_ref('message', $message);

        // Display the populated template
        echo $this->ProcessTemplate('error.tpl');
    }

    public function DoEvent($originator, $eventname, &$params) {
        switch ($eventname) {
            case "Cron15min":
                $days = $this->GetPreference("days_to_save",15);
                $prefix = cms_db_prefix();
                $db = cmsms()->GetDb();
                $db->Execute("DELETE FROM {$prefix}monolog WHERE `time` < DATE_SUB(NOW(),INTERVAL $days DAY)");
                break;
        }
    }

    function __construct() {
        $this->pdo = null;
        ;
    }

    function createDatabaseIfNeeded() {
        $prefix = cms_db_prefix();
        $db = cmsms()->GetDb();

        $db->Execute(<<<EOS
CREATE TABLE IF NOT EXISTS `{$prefix}monolog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `message` longtext,
  `time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel` (`channel`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
EOS
        );
    }

    function _SetupLogger(&$handler,&$logger) {
        $newlines = $this->GetPreference("include_newlines",false);
        $format = $this->GetPreference("log_format","[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n");
        $dformat = $this->GetPreference("date_format","Y-m-d H:i:s");
        $formatter = new LineFormatter($format,$dformat,$newlines);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        $logger->pushProcessor(function ($record) {
            $record['extra']['IP'] = $_SERVER['REMOTE_ADDR'];
            if (isset($_SESSION['login_user_id'])) {
                $userid = $_SESSION['login_user_id'];
                $username = $_SESSION['login_user_username'];
                $record['extra']['feu'] = "$username($userid)";
            }
            return $record;
        });
    }

    var $rot_handler = null; // Rotating log handler
    function GetRotatingLogger($channel,$level) {
        global $config;

        if ( !array_key_exists($channel,$this->channels) ) {
            if ( !$this->rot_handler ) {
                define('LOG_PATH', $config['root_path'].'/tmp');
                $days = $this->GetPreference("days_to_save",15);
                $this->rot_handler = new \Monolog\Handler\RotatingFileHandler(LOG_PATH."/cmsms-monolog.log", $days, $level);
            }
            $logger = new Logger($channel);
            $this->_SetupLogger($this->rot_handler,$logger);
            $this->channels[$channel] = $logger;
        }
        return $this->channels[$channel];
    }

    var $pdo_handler = null; // PDO handler
    function GetDatabaseLogger($channel,$level) {
        global $config;

        if ( !array_key_exists($channel,$this->channels) ) {
            if ( !$this->pdo_handler ) {
                $host = $config->offsetGet('db_hostname');
                $dbname = $config->offsetGet('db_name');
                $user = $config->offsetGet('db_username');
                $password = $config->offsetGet('db_password');
                $pdo = new PDO("mysql:host=$host;dbname=$dbname",$user,$password);
                $this->pdo_handler = new PDOHandler($pdo,$level);
            }
            $logger = new Logger($channel);
            $this->_SetupLogger($this->pdo_handler,$logger);
            $this->channels[$channel] = $logger;
        }
        return $this->channels[$channel];
    }

}

?>

<?php
/*
 +-------------------------------------------------------------------------+
 | PHP Web gui for mssql database based on cacti source                    |
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004-2010 The Cacti Group                                 |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | Cacti: The Complete RRDTool-based Graphing Solution                     |
 +-------------------------------------------------------------------------+
 | This code is designed, written, and maintained by the Cacti Group. See  |
 | about.php and/or the AUTHORS file for specific developer information.   |
 +-------------------------------------------------------------------------+
 | http://www.cacti.net/                                                   |
 +-------------------------------------------------------------------------+
*/

/*
   !!! IMPORTANT !!!

   The following defaults are not to be altered.  Please refer to
   include/config.php for user configurable database settings.

*/

/* Default database settings*/
$database_type = "mssql";
$database_default = "medialog";
$database_hostname = "localhost";
$database_username = "webmedialog";
$database_password = "1234";
$database_port = "";
$WEB_MEDECINS_ID=1;

/* Default session name - Session name must contain alpha characters */
$session_name = "webmedialog";

/* Include configuration */
include(dirname(__FILE__) . "/config.php");

if (isset($config["version"])) {
	die("Invalid include/config.php file detected.");
	exit;
}


/* Files that do not need http header information - Command line scripts */
$no_http_header_files = array();	

define("VERBOSITY_NONE", 1);
define("VERBOSITY_LOW", 2);
define("VERBOSITY_MEDIUM", 3);
define("VERBOSITY_HIGH", 4);
define("VERBOSITY_DEBUG", 5);
define("VERBOSITY_DEVDBG", 6);

define("GT_CUSTOM", 0);
define("GT_LAST_HALF_HOUR", 1);
define("GT_LAST_HOUR", 2);
define("GT_LAST_2_HOURS", 3);
define("GT_LAST_4_HOURS", 4);
define("GT_LAST_6_HOURS", 5);
define("GT_LAST_12_HOURS", 6);
define("GT_LAST_DAY", 7);
define("GT_LAST_2_DAYS", 8);
define("GT_LAST_3_DAYS", 9);
define("GT_LAST_4_DAYS", 10);
define("GT_LAST_WEEK", 11);
define("GT_LAST_2_WEEKS", 12);
define("GT_LAST_MONTH", 13);
define("GT_LAST_2_MONTHS", 14);
define("GT_LAST_3_MONTHS", 15);
define("GT_LAST_4_MONTHS", 16);
define("GT_LAST_6_MONTHS", 17);
define("GT_LAST_YEAR", 18);
define("GT_LAST_2_YEARS", 19);
define("GT_DAY_SHIFT", 20);
define("GT_THIS_DAY", 21);
define("GT_THIS_WEEK", 22);
define("GT_THIS_MONTH", 23);
define("GT_THIS_YEAR", 24);
define("GT_PREV_DAY", 25);
define("GT_PREV_WEEK", 26);
define("GT_PREV_MONTH", 27);
define("GT_PREV_YEAR", 28);

define("DEFAULT_TIMESPAN", 86400);

# graph timeshifts
define("GTS_CUSTOM", 0);
define("GTS_HALF_HOUR", 1);
define("GTS_1_HOUR", 2);
define("GTS_2_HOURS", 3);
define("GTS_4_HOURS", 4);
define("GTS_6_HOURS", 5);
define("GTS_12_HOURS", 6);
define("GTS_1_DAY", 7);
define("GTS_2_DAYS", 8);
define("GTS_3_DAYS", 9);
define("GTS_4_DAYS", 10);
define("GTS_1_WEEK", 11);
define("GTS_2_WEEKS", 12);
define("GTS_1_MONTH", 13);
define("GTS_2_MONTHS", 14);
define("GTS_3_MONTHS", 15);
define("GTS_4_MONTHS", 16);
define("GTS_6_MONTHS", 17);
define("GTS_1_YEAR", 18);
define("GTS_2_YEARS", 19);

define("DEFAULT_TIMESHIFT", 86400);

# weekdays according to date("w") builtin function
define("WD_SUNDAY", 	date("w",strtotime("sunday")));
define("WD_MONDAY", 	date("w",strtotime("monday")));
define("WD_TUESDAY", 	date("w",strtotime("tuesday")));
define("WD_WEDNESDAY", 	date("w",strtotime("wednesday")));
define("WD_THURSDAY", 	date("w",strtotime("thursday")));
define("WD_FRIDAY", 	date("w",strtotime("friday")));
define("WD_SATURDAY", 	date("w",strtotime("saturday")));

define("GD_MO_D_Y", 0);
define("GD_MN_D_Y", 1);
define("GD_D_MO_Y", 2);
define("GD_D_MN_Y", 3);
define("GD_Y_MO_D", 4);
define("GD_Y_MN_D", 5);

define("GDC_HYPHEN", 0);
define("GDC_SLASH", 1);


$config = array();
$colors = array();

/* this should be auto-detected, set it manually if needed */
$config["server_os"] = (strstr(PHP_OS, "WIN")) ? "win32" : "unix";

/* used for includes */
$config["base_path"] = strtr(ereg_replace("(.*)[\\\/]include", "\\1", dirname(__FILE__)), "\\", "/");
$config["library_path"] = ereg_replace("(.*[\\\/])include", "\\1lib", dirname(__FILE__));
$config["include_path"] = dirname(__FILE__);

/* current version */
$config["version"] = $VERSION;
/*check web_access column in database */
$config["check_web_access"]=$CHECK_WEB_ACCESS;

/* colors */
$colors["dark_outline"] = "454E53";
$colors["dark_bar"] = "AEB4B7";
$colors["panel"] = "E5E5E5";
$colors["panel_text"] = "000000";
$colors["panel_link"] = "000000";
$colors["light"] = "F5F5F5";
$colors["alternate"] = "E7E9F2";
$colors["panel_dark"] = "C5C5C5";

$colors["header"] = "00438C";
$colors["header_panel"] = "6d88ad";
$colors["header_text"] = "ffffff";
$colors["form_background_dark"] = "E1E1E1";

$colors["form_alternate1"] = "F5F5F5";
$colors["form_alternate2"] = "E5E5E5";

if ((!in_array(basename($_SERVER["PHP_SELF"]), $no_http_header_files, true)) && ($_SERVER["PHP_SELF"] != "")) 
{
	/* Sanity Check on "Corrupt" PHP_SELF */
	if ($_SERVER["SCRIPT_NAME"] != $_SERVER["PHP_SELF"]) {
		echo "\nInvalid PHP_SELF Path \n";
		exit;
	}

	/* we don't want these pages cached */
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	/* prevent IE from silently rejects cookies sent from third party sites. */
	header('P3P: CP="CAO PSA OUR"');

	/* initilize php session */
	session_name($session_name);
	session_start();

	/* detect and handle get_magic_quotes */
	if (!get_magic_quotes_gpc()) {
		function addslashes_deep($value) {
			$value = is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
			return $value;
		}

		$_POST   = array_map('addslashes_deep', $_POST);
		$_GET    = array_map('addslashes_deep', $_GET);
		$_COOKIE = array_map('addslashes_deep', $_COOKIE);
	}

	/* make sure to start only one session at a time */
	if (!isset($_SESSION["cwd"])) {
		$_SESSION["cwd"] = $config["base_path"];
	}else
	{
		if ($_SESSION["cwd"] != $config["base_path"]) {
			session_unset();
			session_destroy();
		}
	}
}

/* emulate 'register_globals' = 'off' if turned on */
if ((bool)ini_get("register_globals")) {
	$not_unset = array("_GET", "_POST", "_COOKIE", "_SERVER", "_SESSION", "_ENV", "_FILES", "database_type", "database_default", "database_hostname", "database_username", "database_password", "config", "colors");

	/* Not only will array_merge give a warning if a parameter is not an array, it will
	* actually fail. So we check if HTTP_SESSION_VARS has been initialised. */
	if (!isset($_SESSION)) {
		$_SESSION = array();
	}

	/* Merge all into one extremely huge array; unset this later */
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_SESSION, $_ENV, $_FILES);

	unset($input["input"]);
	unset($input["not_unset"]);

	while (list($var,) = @each($input)) {
		if (!in_array($var, $not_unset)) {
			unset($$var);
		}
	}

	unset($input);
}

/* display ALL errors */
error_reporting(E_ALL);

/* include base modules */
include($config["library_path"] . "/adodb/adodb.inc.php");
include($config["library_path"] . "/database.php");
include_once($config["library_path"] . "/functions.php");
include_once($config["include_path"] . "/global_arrays.php");

/* connect to the database server */
db_connect_real($database_hostname, $database_username, $database_password, $database_default, $database_type, $database_port);

/* include additional modules */
//include_once($config["include_path"] . "/global_form.php");
include_once($config["library_path"] . "/html.php");
include_once($config["library_path"] . "/html_form.php");
include_once($config["library_path"] . "/html_utility.php");
include_once($config["library_path"] . "/html_validate.php");
//include_once($config["library_path"] . "/variables.php");
//include_once($config["library_path"] . "/auth.php");


?>

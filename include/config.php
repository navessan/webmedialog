<?php
/*
 PHP web gui for mssql database
 */

/*
mssql connection type 
http://www.php.net/manual/en/mssql.requirements.php
This extension is not available anymore on Windows with PHP 5.3 or later. 
Requirements for Win32 platforms.
The extension requires the MS SQL Client Tools to be installed on the system where PHP is installed. 
The Client Tools can be installed from the MS SQL Server CD or by copying ntwdblib.dll 
from \winnt\system32 on the server to \winnt\system32 on the PHP box. 
Copying ntwdblib.dll will only provide access through named pipes. 
Configuration of the client will require installation of all the tools.

Requirements for Unix/Linux platforms.
To use the MSSQL extension on Unix/Linux, you first need to build and install the FreeTDS library.

mssqlnative
Native mssql driver. Requires mssql client. Works on Windows via SQLSRV,
It's a Windows-only library developed and maintained by Microsoft
   an alternative driver for MS SQL is available from Microsoft: 
    http://msdn.microsoft.com/en-us/sqlserver/ff657782.aspx. 
*/

//$database_type = "mssql";
$database_type = "mssqlnative";

/* make sure these values refect your actual database/host/user/password */
$database_default = "medialog";
$database_hostname = "medialog";
$database_username = "webmedialog";
$database_password = "CHANGEME";
$database_port = "";

//constants
$WEB_MEDECINS_ID=CHANGEME;			//MEDECINS_ID in medialog database for web user
$CHECK_WEB_ACCESS=0;			//check web_access column in database
$ALLOW_WRITE_TO_DB=0;			//allow writing and creating records in db

$VERSION='0.0.1';

/* Default session name - Session name must contain alpha characters */
#$web_session_name = "web";


?>


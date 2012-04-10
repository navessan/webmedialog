<?php
/*
 +-------------------------------------------------------------------------+
 | PHP web gui for medialog planning mssql database                        |
 +-------------------------------------------------------------------------+
 | Copyright (C) 2011 cprzd.ru                                             |
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
 */
?>

	<div align="right"><small>&copy; 2012 cprzd.ru</small></div>
</body>
</html>

<?php
/* we use this session var to store field values for when a save fails,
this way we can restore the field's previous values. we reset it here, because
they only need to be stored for a single page */
kill_session_var("sess_field_values");

/* make sure the debug log doesn't get too big */
debug_log_clear();
?>

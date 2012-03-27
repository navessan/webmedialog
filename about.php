<?php
/*
 PHP web gui for nitgen mssql database
 */

include("./include/auth.php");
include("./include/header.php");

?>

<p>Здесь могла бы быть ваша реклама.</p>
<a href="index.php" target="_blank"> <b>Company</b></a>
<br>
<a href="?phpinfo=1">phpinfo</a>
<?php
	if (!isset($_REQUEST["phpinfo"])) { $_REQUEST["phpinfo"] = ""; }
	if ($_REQUEST['phpinfo'])
	{
		ob_start();
		phpinfo();
		//$sqldr=ob_get_clean();
	}
?>

<?php
include("./include/footer.php");
?>

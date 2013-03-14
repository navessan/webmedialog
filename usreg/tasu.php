<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Просмотр полисов ОМС Московской области</title>

<meta content="text/html; charset=windows-1251" name="Content">
<meta http-equiv="Content-Type"
	content="text/html; charset=windows-1251">
<meta name="keywords"
	content="web, database, gui, oms, mofoms, tasu">
<meta name="description"
	content="web database gui oms mofoms tasu">

</head>
<body>

<?php

/* Default database settings*/
$database_type = "mssql";
$database_default = "usreg";
$database_hostname = "localhost";
$database_username = "usreg";
$database_password = "1234";
$database_port = "";


$debug=0;
/* display ALL errors */
error_reporting(E_ALL);

/* Include configuration */
include("config.php");

//override config file
$database_hostname = "oms-srv";
$database_default = "PDPRegStorage";


/* Display errors. */
function FormatErrors( $errors )
{
	echo "Error information: <br/>";
	foreach ( $errors as $error )
	{
		echo "SQLSTATE: ".$error['SQLSTATE']."<br/>";
		echo "Code: ".$error['code']."<br/>";
		echo "Message: ".$error['message']."<br/>";
	}
}

/* sanitize_search_string - cleans up a search string submitted by the user to be passed
     to the database. NOTE: some of the code for this function came from the phpBB project.
   @arg $string - the original raw search string
   @returns - the sanitized search string */
function sanitize_search_string($string) {
	static $drop_char_match =   array('^', '$', '<', '>', '`', '\'', '"', '|', ',', '?', '~', '+', '[', ']', '{', '}', '#', ';', '!', '=');
	static $drop_char_replace = array(' ', ' ', ' ', ' ',  '',   '', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ');

	/* Replace line endings by a space */
	$string = preg_replace('/[\n\r]/is', ' ', $string);
	/* HTML entities like &nbsp; */
	$string = preg_replace('/\b&[a-z]+;\b/', ' ', $string);
	/* Remove URL's */
	$string = preg_replace('/\b[a-z0-9]+:\/\/[a-z0-9\.\-]+(\/[a-z0-9\?\.%_\-\+=&\/]+)?/', ' ', $string);

	/* Filter out strange characters like ^, $, &, change "it's" to "its" */
	for($i = 0; $i < count($drop_char_match); $i++) {
		$string =  str_replace($drop_char_match[$i], $drop_char_replace[$i], $string);
	}

	$string = str_replace('*', ' ', $string);

	return $string;
}

/* get_request_var_post - returns the current value of a PHP $_POST variable, optionally
     returning a default value if the request variable does not exist
   @arg $name - the name of the request variable. this should be a valid key in the
     $_POST array
   @arg $default - the value to return if the specified name does not exist in the
     $_POST array
   @returns - the value of the request variable */
function get_request_var_post($name, $default = "") {
	if (isset($_POST[$name])) {
		if (isset($_GET[$name])) {
			unset($_GET[$name]);
			$_REQUEST[$name] = $_POST[$name];
		}

		return $_POST[$name];
	}else{
		return $default;
	}
}

function display_search_form()
{
	print '<p>';
	print 'POST:';
	print_r($_POST);
	print '<br>GET:';
	print_r($_GET);
	print '<br>REQUEST:';
	print_r($_REQUEST);
	print '</p>';
	
}

$connectionInfo = array( "UID"=>$database_username,
                         "PWD"=>$database_password,
                         "Database"=>$database_default/*,
						 "ReturnDatesAsStrings" => true*/);

/* Connect using SQL Server Authentication. */
$conn = sqlsrv_connect( $database_hostname, $connectionInfo);
if( $conn === false )
{
     echo "Unable to connect.</br>";
     die( FormatErrors( sqlsrv_errors() ) );
}


//-------------------
if(isset($debug))
	if($debug==1) display_search_form();
//-------------------


$fields_array_old=array('DOMC_TYPE'=>'Тип документа',
'SERIES'=>'Серия полиса',
'NUMBER'=>'Номер полиса',
'CODE_MSK'=>'СМО',
'FAM'=>'Фамилия',
'IM'=>'Имя',
'OT'=>'Отчество',
'BIRTHDAY'=>'Дата рождения',
'SEX'=>'Пол',
'REGION'=>'Регион проживания',
'DATE_N'=>'Дата выдачи документа',
'DATE_E'=>'Дата факт.прекр. действия документа',
'DSTOP'=>'Дата окончания действия документа',
'SS'=>'СНИЛС',
'Q_NAME'=>'Наименование СМО');

$fields_array=array(
		'DOMC_TYPE'=>array(
				"name"=>'Тип документа'
				,"visible"=>0),
		'SERIES'=>array(
				"name"=>'Серия полиса'
				,"type"=>"text"
				,"visible"=>1),
		'NUMBER'=>array(
				"name"=>'Номер полиса'
				,"visible"=>1),
		'CODE_MSK'=>array(
				"name"=>'СМО'
				,"visible"=>1),
		'FAM'=>array(
				"name"=>'Фамилия'
				,"type"=>"text"
				,"visible"=>1),
		'IM'=>array(
				"name"=>'Имя'
				,"type"=>"text"
				,"visible"=>1),
		'OT'=>array(
				"name"=>'Отчество'
				,"type"=>"text"
				,"visible"=>1),
		'BIRTHDAY'=>array(
				"name"=>'Дата рождения'
				,"type"=>"datetime"
				,"visible"=>1),
		'SEX'=>array(
				"name"=>'Пол'
				,"visible"=>1),
		'REGION'=>array(
				"name"=>'Регион проживания'
				,"visible"=>1),
		'ADDRESS'=>array(
				"name"=>'Адрес проживания'
				,"visible"=>1),
		'DATE_N'=>array(
				"name"=>'Дата выдачи документа'
				,"type"=>"datetime"
				,"visible"=>1),
		'DATE_E'=>array(
				"name"=>'Дата факт.прекр. действия документа'
				,"type"=>"datetime"
				,"visible"=>1),
		'DSTOP'=>array(
				"name"=>'Дата окончания действия документа'
				,"type"=>"datetime"
				,"visible"=>1),
		'SS'=>array(
				"name"=>'СНИЛС'
				,"type"=>"text"
				,"visible"=>1),
		'Q_NAME'=>array(
				"name"=>'Наименование СМО'
				,"type"=>"text"
				,"visible"=>1),
		'POLIS_S'=>array(
				"name"=>'Статус полиса'
				,"type"=>"int"
				,"visible"=>1),
		'POLIS_S_BIT'=>array(
				"name"=>''
				,"type"=>"int"
				,"visible"=>0)
);

$fields_search_array=array(
array("sql"=>"fam","name"=>"Фамилия","value"=>"","html"=>"<br>","type"=>"text"),
array("sql"=>"im","name"=>"Имя","value"=>"","type"=>"text"),
array("sql"=>"ot","name"=>"Отчество","value"=>"","type"=>"text"),
array("sql"=>"birthday","name"=>"Дата или год рождения","value"=>"","type"=>"datetime"),
array("sql"=>"series","name"=>"Серия полиса","value"=>"","html"=>"<br>","type"=>"text"),
array("sql"=>"number","name"=>"Номер полиса","value"=>"","type"=>"text")
);

echo 'Версия регистра: '.get_usreg_version($conn).'<br>';
//echo 'Всего записей в регистре: '.get_month_records_count($conn).'<br>';

echo '<form method=post>';

//вывод полей для поиска с заполнением значений
for($i=0;$i<count($fields_search_array);$i++)
{
	$field=$fields_search_array[$i];
	$field['value']=get_request_var_post($field['sql']);
	$field['value']=sanitize_search_string($field['value']);
	$field['value']=trim($field['value']);
	if($field['type']=="datetime")
		$field['value']=str_replace(array('\'', '-', '.', ',', ' ', '/'), '-', $field['value']);
	if(isset($field['html']))
		echo $field['html'];		
	echo $field['name'].': <input type=text name="'.$field['sql'].'" value="'.$field['value'].'">';
	$fields_search_array[$i]=$field;
}

echo '		<input type=submit name=send value=search>
	</form>';


$sql_where="";

foreach ($fields_search_array as $field)
{
	if(strlen($field['value'])>0)
	{			
		if($field['type']=="datetime")
		{
			//datetime query
			$day = "";
			$month = "";
			$year = "";
			$sqldate="";
			$sqldate_query="";
			
			$a_date = explode('-', $field['value']); 
			if(count($a_date)==1)
			{
				//only year
				$year=$a_date[0];
				$month = 1;
				$day = 1;
				if(checkdate($month, $day, $year))
				{
					$sqldate=date("Ymd",mktime(0,0,0,$month,$day,$year));
					$sqldate_query=	"(".$field['sql'].">='".$sqldate." 00:00:00.000'";
					
					$sqldate=date("Ymd",mktime(0,0,0,$month,$day,$year+1));
					$sqldate_query.=" and ".$field['sql']."<'".$sqldate." 00:00:00.000')";
				}					
			}
			else if(count($a_date)==2)
			{
				//year and month
				$year = $a_date[0];
				$month = $a_date[1];
				$day = 1;
				if(checkdate($month, $day, $year))
				{
					$sqldate=date("Ymd",mktime(0,0,0,$month,$day,$year));
					$sqldate_query=	"(".$field['sql'].">='".$sqldate." 00:00:00.000'";
					
					$sqldate=date("Ymd",mktime(0,0,0,$month+1,$day,$year));
					$sqldate_query.=" and ".$field['sql']."<'".$sqldate." 00:00:00.000')";
				}
			}				
			else if(count($a_date)==3)
			{
				//full date with year, month, day
				$year = $a_date[0];
				$month = $a_date[1];
				$day = $a_date[2];
				if(checkdate($month, $day, $year))
				{
					$sqldate=date("Ymd",mktime(0,0,0,$month,$day,$year));
					$sqldate_query=$field['sql']."='".$sqldate." 00:00:00.000'";
				}	
			}

			if(strlen($sqldate_query)>0)
			{
				if(strlen($sql_where)>0)
					$sql_where.=" and ";
				$sql_where.=$sqldate_query;
			}
		}
		else 
		{
			if(strlen($sql_where)>0)
				$sql_where.=" and ";
			$sql_where.=$field['sql']." like '".$field['value']."%'";
		}	
	}
}

if(isset($debug))
	if($debug==1) echo $sql_where;


$top_count=60;

/* Set up and execute the query. */
//--------------------------------------
$tsql = "SELECT top $top_count
[ENP]
      ,[SERIES]
      ,[NUMBER]
      ,[CODE_MSK]
      ,[FAM]
      ,[IM]
      ,[OT]
      ,[BIRTHDAY]
      ,[SEX]
      ,[REGION]
      ,[DATE_N]
      ,[DATE_E]
      ,[DSTOP]
      ,[SS]
   /*   ,[K1]
      ,[K2]
      ,[K3]
      ,[K4]
      ,[K5]
   */   
  ,atd.name ADDRESS
   ,smo.ShortName Q_NAME
      ,case
when date_e<getdate() then 'истек с '+convert(varchar, date_e, 104)
when dstop<getdate() then 'погашен с '+convert(varchar, dstop, 104)
else 'действующий'
end POLIS_S
,case
when date_e<getdate() or dstop<getdate() then 0
else 1
end POLIS_S_BIT
  FROM [PDPRegStorage].[ut].[ut_PolReg_UsReg] usreg
  left join [PDPAccStorage].[dbo].[vw_omssmo_45307] smo on code_msk=code
  left join [PDPStdStorage].[dbo].[NSI_OKATO_insert] atd on 
  				substring(usreg.region,1,8)+'000'=atd.okato and atd.disabled=0 and atd.razdel=1

";


if(strlen($sql_where)>0)
	$tsql.="\n where ".$sql_where;

$tsql.="\n order by fam
--,im,ot";


/*Execute the query with a scrollable cursor so
  we can determine the number of rows returned.*/
//$cursorType = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
//$stmt = sqlsrv_query( $conn, $tsql,null,$cursorType);

$stmt = sqlsrv_query($conn, $tsql);

if( $stmt === false)
{
     echo "Error in query preparation/execution.\n";
     die( FormatErrors( sqlsrv_errors() ) );
}

if(sqlsrv_has_rows($stmt))
{
	$numRows = sqlsrv_num_rows($stmt);
	echo "<p>$numRows Row" . ($numRows == 1 ? "" : "s") . " Returned </p>";
	
	print '<table cellspacing="0" cellpadding="1" border="1" align="center"
	width="100%">
	<tbody>';
	echo '<tr>';
	
	$metadata=sqlsrv_field_metadata($stmt);
	
	$column_name="";

	//internal column names
	for ($i=0;$i < count($metadata);$i++)
	{
		$meta = $metadata[$i];
		//print_r($meta);
		$column_name=strtoupper($meta['Name']);
		
		if(get_column_visibility($column_name)==1)		
			echo '<td>' . $meta['Name'] . '</td>';
	}
	echo '</tr>';
	
	//human readable column names
	echo '<tr>';
	for ($i=0;$i < count($metadata);$i++)
	{
		$meta = $metadata[$i];
		$column_name=strtoupper($meta['Name']);
		//print_r($meta);
		$header=get_column_username($column_name,"&nbsp");
		
		if(get_column_visibility($column_name)==1)
			echo '<td>' . $header . '</td>';
	}
	echo '</tr>';


	/* Retrieve each row as an associative array and display the results.*/
	while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
	{
		if($row['POLIS_S_BIT']==1)
			$rowColor='Green';
		else
			if ($row['POLIS_S_BIT']==0)
			$rowColor='Red';
		else
			$rowColor='White';
		print '<tr bgcolor="' . $rowColor . '">';
		
		//print_r($row);
		
		for ($i=0;$i < count($row);$i++)
		{
			$column_name=$metadata[$i]['Name'];
					
			if(!get_column_visibility($column_name)==1)
				break;			
			
			$field=$row[$column_name];
			$text='';
			
			if (gettype($field)=="object" && (get_class($field)=="DateTime"))
			{
    			$text = $field->format('Y-m-d');
    			if($text=='1899-12-30')
    				$text="&nbsp";
			}
			else
				$text = trim($field);
				
			if($text=='') 
				$text ='&nbsp'; 
			
			echo '<td>' . $text . '</td>';
		}
		print "</tr> \n";
	}
	print '	</tbody>
	</table>';
}
/* Free statement and connection resources. */
sqlsrv_free_stmt( $stmt);
sqlsrv_close( $conn);


function get_month_records_count($conn)
{

	$tsql="select count(*) from [PDPRegStorage].[ut].[ut_PolReg_UsReg] usreg";

	$stmt = sqlsrv_query($conn, $tsql);

	if( $stmt === false)
	{
		echo "Error in query preparation/execution.\n";
		die( FormatErrors( sqlsrv_errors() ) );
	}

	if(sqlsrv_has_rows($stmt))
	{
		/* Retrieve each row as an associative array and display the results.*/
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
		{
			foreach ($row as $field)
			{
				return $field;
			}
		}
	}
	/* Free statement and connection resources. */
	sqlsrv_free_stmt( $stmt);
}

function get_usreg_version($conn)
{

	$tsql="SELECT [OtPer] FROM [PDPRegStorage].[ut].[ut_PolReg_Version]";

	$stmt = sqlsrv_query($conn, $tsql);

	if( $stmt === false)
	{
		echo "Error in query preparation/execution.\n";
		die( FormatErrors( sqlsrv_errors() ) );
	}

	if(sqlsrv_has_rows($stmt))
	{
		/* Retrieve each row as an associative array and display the results.*/
		while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
		{
			foreach ($row as $field)
			{
				return $field;
			}
		}
	}
	/* Free statement and connection resources. */
	sqlsrv_free_stmt( $stmt);
}

function get_column_visibility($name, $default = 1)
{
	global $fields_array;

	if (isset($fields_array[$name]['visible']))
		return $visible_flag=$fields_array[$name]['visible'];

	else
		return $default;
}
function get_column_username($name, $default = '')
{
	global $fields_array;

	if (isset($fields_array[$name]['name']))
		return $visible_flag=$fields_array[$name]['name'];

	else
		return $default;
}

?>

</body>
</html>

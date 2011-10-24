<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>web database gui</title>

<meta content="text/html; charset=windows-1251" name="Content">
<meta http-equiv="Content-Type"
	content="text/html; charset=windows-1251">
<meta name="keywords"
	content="web, database, gui">
<meta name="description"
	content="web database gui">

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

/* display ALL errors */
error_reporting(E_ALL);

/* Include configuration */
include("config.php");


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
//display_search_form();
//-------------------


$fields_array=array('DOMC_TYPE'=>'Тип документа',
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
'Q_NAME'=>'Наименование СМО');

$fields_search_array=array(
array("sql"=>"fam","name"=>"Фамилия","value"=>"","html"=>"<br>"),
array("sql"=>"im","name"=>"Имя","value"=>""),
array("sql"=>"ot","name"=>"Отчество","value"=>""),
array("sql"=>"birthday","name"=>"Дата рождения","value"=>"","type"=>"datetime"),
array("sql"=>"series","name"=>"Серия полиса","value"=>"","html"=>"<br>"),
array("sql"=>"number","name"=>"Номер полиса","value"=>"")
);

$month_array=array(
array("sql"=>"0811","name"=>"август 2011"),
array("sql"=>"0911","name"=>"сентябрь 2011")
);

echo '<form method=post>
	<select name="month">';

//выбор текущего месяца
$sql_month='0911';

$get_month=get_request_var_post("month");
$get_month=sanitize_search_string($get_month);
$month_selected_flag=false;

echo '<br>'.$get_month.'<br>';
for ($i=0;$i<count($month_array);$i++)
{
	echo '<option ';
	
	//select month from user request	
	if($get_month==$month_array[$i]['sql'])
	{
		echo 'selected ';
		$sql_month=$month_array[$i]['sql'];
		$month_selected_flag=true;
	}
	else 
	//select last month if no user request
	if(!$month_selected_flag &($i==(count($month_array)-1)))
	{
		echo 'selected ';
		$sql_month=$month_array[$i]['sql'];
	}
	
	echo 'value="'.$month_array[$i]['sql'].'">';
	echo $month_array[$i]['name'].'</option>';
}
	
echo '</select>';
//echo 'Всего записей:'.get_month_records_count($conn, $sql_month).'<br>';

//вывод полей для поиска с заполнением значений
for($i=0;$i<count($fields_search_array);$i++)
{
	$field=$fields_search_array[$i];
	$field['value']=get_request_var_post($field['sql']);
	$field['value']=sanitize_search_string($field['value']);
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
		if(strlen($sql_where)>0)
			$sql_where.=" and ";
		$sql_where.=$field['sql']." like '".$field['value']."%'";	
		//TODO datetime query
	}
}

$top_count=60;

/* Set up and execute the query. */
//$tsql = "SELECT TOP $top_count * FROM usreg$sql_month order by fam";

$tsql = "SELECT top $top_count
[enp]
      ,[domc_type]
      ,usreg.[series]
      ,usreg.[number]
      ,usreg.[code_msk]
      ,[fam]
      ,[im]
      ,[ot]
      ,[birthday]
      ,[sex]
      ,[region]
      ,[date_n]
      ,[date_e]
      ,[dstop]
      ,[ss]
--      ,[status]
,case 
when usreg.domc_type='00' then 'Лист регистрации'
when usreg.domc_type='01' then 'Полис до 01.05.2011'
when usreg.domc_type='02' then 'Временное свидетельство'
when usreg.domc_type='03' then 'Полис единого образца' 
end polis
,case
when isnull(dstop,0)<1 then 'действующий'
when dstop>getdate() then 'действующий'
else 'погашен с '+convert(varchar, dstop, 104)
end polis_s
,case
when isnull(dstop,0)<1 then 1
when dstop>getdate() then 1
else 0
end polis_s_bit
,tersmo.q_name
,(rtrim(atd_r.t_name)+', '+(rtrim(atd.t_name))+', '+rtrim(atd.c_name)) addrss
--,'|' '|',atd.*
--,atd_r.t_name atd_r_t_name
--,'|' '|',numoms.*
--,'|' '|',cmo_obl.*
--,'|' '|',tersmo.*

FROM usreg$sql_month usreg
left outer join atd on substring(usreg.region,1,5)=substring(atd.okato,1,5)
left outer join atd atd_r on substring(usreg.region,1,2)+'000000000'=atd_r.okato
left outer join cmo_obl on (usreg.code_msk=cmo_obl.cod_cmo and rtrim(cmo_obl.q_ogrn)<>'')
left outer join tersmo on (cmo_obl.q_ogrn=tersmo.q_ogrn and tersmo.c_t=50)
--left outer join numoms0811 numoms on (usreg.series=numoms.series and usreg.number=numoms.number)";

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
	//internal column names
	for ($i=0;$i < count($metadata);$i++)
	{
		$meta = $metadata[$i];
		//print_r($meta);
		echo '<td>' . $meta['Name'] . '</td>';
	}
	echo '</tr>';
	
	//human readable column names
	echo '<tr>';
	for ($i=0;$i < count($metadata);$i++)
	{
		$meta = $metadata[$i];
		//print_r($meta);
		if (isset($fields_array[strtoupper($meta['Name'])]))
			$header=$fields_array[strtoupper($meta['Name'])];
		else $header="&nbsp";
		echo '<td>' . $header . '</td>';
	}
	echo '</tr>';
	
	/* Retrieve each row as an associative array and display the results.*/
	while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC))
	{
		if($row['polis_s_bit']==1)
			$rowColor='Green';
		else
		if ($row['polis_s_bit']==0)
			$rowColor='Red';
		else 
			$rowColor='White';
		print '<tr bgcolor="' . $rowColor . '">';
		
		//print_r($row);
		$count = count($row);
		foreach ($row as $field)
		{
			$text='';
			
			if (get_class($field)=="DateTime")
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

function get_month_records_count($conn, $month)
{
	if(!strlen($month)>0)
	return -1;

	$tsql="select count(fam) from usreg".$month;

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

?>

</body>
</html>

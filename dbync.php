<?php 
global $dataset;

function updateConnection($database) {
	$host='localhost'; // имя хоста (уточняется у провайдера)
	$database=$database; // имя базы данных, которую вы должны создать
	$user=''; // заданное вами имя пользователя, либо определенное провайдером
	$pswd=''; // заданный вами пароль

	$dataset = mysql_connect($host, $user, $pswd) or die("Не могу соединиться с MySQL.");
	mysql_select_db($database) or die("Не могу подключиться к базе.");
	return $dataset;
}

function dbquery($q) {
	global $dataset;
	return mysql_query($q, $dataset);
}


function collect($dbq, $table) {
	$sqlInjectDataset = array();

	while ($row = mysql_fetch_assoc($dbq)) {
		$sqlInject = "INSERT INTO $table (";
		foreach ($row as $key => $value) {
			$sqlInject .= "$key,";
		}
		$sqlInject[strlen($sqlInject) - 1] = " ";
		$sqlInject .= ") VALUES (";
		foreach ($row as $key => $value) {
			$sqlInject .= "'$value',";
		}
		$sqlInject[strlen($sqlInject) - 1] = " ";
		$sqlInject .= ");";
		array_push($sqlInjectDataset, $sqlInject);
	}
	return $sqlInjectDataset;
}

function inject($data) {
	foreach ($data as $key => $value) {
		try {
			$res = dbquery($value);
		} catch (Exception $e) {}
	}
}



$dataset = updateConnection('main_db');//база-прототип
$posts = collect(dbquery("SELECT * FROM wp_posts WHERE post_type = 'portfolio'"), "wp_posts");
$meta = collect(dbquery("SELECT * FROM wp_postmeta"), "wp_postmeta");
mysql_close($dataset);



//целевая база данных
$dataset = updateConnection('target_db');
inject($posts);
inject($meta);
mysql_close($dataset);

//die(mysql_error());
?>
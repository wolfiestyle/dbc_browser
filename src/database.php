<?php
require('config.php');
$db_conn = mysql_connect($config['db_host'], $config['db_user'], $config['db_pass']);

function query_dbc($table, $id)
{
    global $config, $db_conn;
    mysql_select_db($config['dbc'], $db_conn);
    $query = "SELECT * FROM $table WHERE id = $id;";
    $result = mysql_query($query, $db_conn) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
    if ($num_rows > 1) die("Error: got $num_rows rows searching for primary key value $id in table $table, expected 1");
    return array($result, $num_rows);
}

function query_world($table, $id)
{
    global $config, $db_conn;
    mysql_select_db($config['world'], $db_conn);
    $query = "SELECT * FROM $table WHERE entry = $id";
    $result = mysql_query($query, $db_conn) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
    if ($num_rows > 1) die("Error: got $num_rows rows searching for primary key value $id in table $table, expected 1");
    return array($result, $num_rows);
}
?>

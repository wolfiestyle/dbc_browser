<?php
require("database.php");
require("spell_defines.php");
mysql_select_db($config['dbc'], $db_conn);

#read skip value (page offset in search results)
$skip = 0;
if (isset($_GET['s']))
{
    $skip = (int)$_GET['s'];
    unset($_GET['s']);
}

#outputs url-encoded GET parameteres
function print_get($skip)
{
    echo "s=$skip";
    foreach ($_GET as $key => $value)
        echo "&" . urlencode($key) . "=" . urlencode($value);
}
?>
<html>
<head>
<title>Search</title>
</head>
<body>
<p><a href=".">Index</a> / <a href="search.php?<?php print_get($skip); ?>">Search</a></p>
<h1>Search results</h1>
<?php
foreach ($_GET as $key => $value)
{
    $tval = trim($value);
    if ($tval == '')
        continue;
    $search_field = mysql_real_escape_string($key, $db_conn);
    $search_term = mysql_real_escape_string($tval, $db_conn);
    if ($query_cond != '')
        $query_cond .= ' AND ';
    #use substring search for text fields (TODO: set this and numeric inequality search as extra paramter)
    if ($search_field == 'SpellName' || $search_field == 'Rank' || $search_field == 'Description' || $search_field == 'Tooltip')
        $query_cond .= "$search_field LIKE '%$search_term%'";
    else
        $query_cond .= "$search_field = '$search_term'";
}
if ($query_cond == '') die('Error: Missing search parameters');
#the search query
$query = "SELECT * FROM Spell WHERE $query_cond LIMIT $skip, $config[results_per_page];";
echo "$query\n";
#this is only to get the total row count
$result = mysql_query("SELECT COUNT(*) FROM Spell WHERE $query_cond;", $db_conn) or die(mysql_error());
$row = mysql_fetch_row($result);
$total = $row[0];
if ($total > 0)
{
    $first = $skip + 1;
    $last = $skip + $config['results_per_page'];
    if ($last > $total) $last = $total;
    echo "<p>Showing results $first - $last (total $total spells)</p>\n";
    $result = mysql_query($query, $db_conn) or die(mysql_error());
    $num_rows = mysql_num_rows($result);
}
else
    echo "<p>No spells found</p>\n";
if ($num_rows > 0)
{
?>
<table border="1">
<tr><td><b>Id</b></td>
<td><b>SpellName</b></td>
<td><b>Rank</b></td>
<td><b>SpellIconID</b></td>
<td><b>SpellClass</b></td>
<td><b>BasePoints0</b></td>
<td><b>BasePoints1</b></td>
<td><b>BasePoints2</b></td></tr>
<?php
    while ($spell_info = mysql_fetch_assoc($result))
    {
        echo "<tr><td><a href=\"spell.php?id=$spell_info[Id]\">$spell_info[Id]</a></td>";
        echo "<td>$spell_info[SpellName]</td>";
        echo "<td>$spell_info[Rank]</td>";
        echo "<td>$spell_info[SpellIconID]</td><td>";
        if ($spell_info['SpellFamilyName'] != 0)
            echo $spell_family[$spell_info[SpellFamilyName]];
        echo "</td>";
        echo "<td>$spell_info[EffectBasePoints0]</td>";
        echo "<td>$spell_info[EffectBasePoints1]</td>";
        echo "<td>$spell_info[EffectBasePoints2]</td></tr>\n";
    }
    echo "</table>\n<p>";
    for ($i = 0; $i < $total; $i += $config['results_per_page'])
    {
        $first = $i + 1;
        $last = $i + $config['results_per_page'];
        if ($last > $total) $last = $total;
        if ($i > 0) echo "| ";
        echo "<a href=\"?";
        print_get($i);
        echo "\">$first-$last</a>\n";
    }
    echo "</p>\n";
}
?>
</body>
</html>

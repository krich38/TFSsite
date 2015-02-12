<?php require_once '../../module.php';

// Configure module version number
$response['version']['module'] = 1;

// Fetch number of rows
$rows = getValue($_GET['rows']);
if (!$rows || $rows == 0) $rows = 10;
else $rows = (int)$rows;

// Show which configuration is used
$response['config']['rows'] = $rows;

// Fetch top 10 players
$players = mysql_select_multi("SELECT `p`.`name`, `p`.`level`, `p`.`experience`, `p`.`vocation`, `p`.`lastlogin`, `z`.`created` FROM `players` AS `p` INNER JOIN `yz85_players` AS `z` ON `p`.`id` = `z`.`player_id` WHERE `p`.`group_id`<'2'  ORDER BY `p`.`experience` DESC LIMIT $rows;");
for ($i = 0; $i < count($players); $i++) {
	$players[$i]['vocation_name'] = $config['vocations'][$players[$i]['vocation']];
}
$response['data']['players'] = $players;


SendResponse($response);
?>
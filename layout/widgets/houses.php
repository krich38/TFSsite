<div class="well">
	<h3>Top 5 Guilds</h3>
	<hr class="bighr">
	<div class="inner">
		<?php require_once 'engine/init.php'; 

    // Cache the results 
    $cache = new Cache('engine/cache/topGuilds'); 
    if ($cache->hasExpired()) { 
        $guilds = mysql_select_multi("SELECT `g`.`id` AS `id`, `g`.`name` AS `name`, COUNT(`g`.`name`) as `frags` FROM `killers` k LEFT JOIN `player_killers` pk ON `k`.`id` = `pk`.`kill_id` LEFT JOIN `players` p ON `pk`.`player_id` = `p`.`id` LEFT JOIN `guild_ranks` gr ON `p`.`rank_id` = `gr`.`id`    LEFT JOIN `guilds` g ON `gr`.`guild_id` = `g`.`id` WHERE `k`.`unjustified` = 1 AND `k`.`final_hit` = 1 GROUP BY `name` ORDER BY `frags` DESC, `name` ASC LIMIT 0, 4;"); 
         
        $cache->setContent($guilds); 
        $cache->save(); 
    } else { 
        $guilds = $cache->load(); 
    } 

if (!empty($guilds) || $guilds !== false) { 
?> 

<table id="onlinelistTable" class="table table-striped table-hover"> 
    <tr class="yellow"> 
        <th>Name:</th> 
        <th>Frags:</th> 
    </tr> 
    <?php foreach ($guilds as $guild) { 

    $url = url("guilds.php?name=". $guild['name']);
    echo '<tr class="special" onclick="javascript:window.location.href=\'' . $url . '\'">'; ?> 
        <td><?php echo $guild['name']; ?></td>
        <td><?php echo $guild['frags']; ?></td> 
    </tr> 
    <?php } ?> 
</table> 

<?php 
} else echo '<h1>No frags yet.</h1>';?>
	</div>
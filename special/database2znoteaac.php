<?php
require '../config.php';
require '../engine/database/connect.php';
require '../engine/function/general.php';
require '../engine/function/users.php';
?>

<h1>Old database to yz85 AAC compatibility converter:</h1>
<p>Converting accounts and characters to work with yz85 AAC:</p>
<?php
	// some variables
	$updated_acc = 0;
	// $updated_acc += 1;
	$updated_char = 0;
	// $updated_char += 1;
	$updated_pass = 0;
	
	// install functions
	function fetch_all_accounts() {
			return mysql_select_multi("SELECT `id` FROM `accounts`");
	}
	
	function user_count_yz85_accounts() {
		$data = mysql_select_single("SELECT COUNT(`account_id`) AS `count` from `yz85_accounts`;");
		return ($data !== false) ? $data['count'] : 0;
	}
	
	function user_character_is_compatible($pid) {
		$data = mysql_select_single("SELECT COUNT(`player_id`) AS `count` from `yz85_players` WHERE `player_id` = '$pid';");
		return ($data !== false) ? $data['count'] : 0;
	}
	
	function fetch_yz85_accounts() {
			return mysql_select_multi("SELECT `account_id` FROM `yz85_accounts`");
	}
	// end install functions
	
	// count all accounts, yz85 accounts, find out which accounts needs to be converted.
	$all_account = fetch_all_accounts();
	$yz85_account = fetch_yz85_accounts();
	if ($all_account !== false) {
		if ($yz85_account != false) { // If existing yz85 compatible account exists:
			foreach ($all_account as $all) { // Loop through every element in yz85_account array
				if (!in_array($all, $yz85_account)) {
					$old_accounts[] = $all;
				}
			}
		} else {
			foreach ($all_account as $all) {
				$old_accounts[] = $all;
			}
		}
	}
	// end ^
	
	// Send count status
	if (isset($all_account) && $all_account !== false) {
		echo '<br>';
		echo 'Total accounts detected: '. count($all_account) .'.';
		
		if (isset($yz85_account)) {
			echo '<br>';
			echo 'yz85 compatible accounts detected: '. count($yz85_account) .'.';
			
			if (isset($old_accounts)) {
				echo '<br>';
				echo 'Old accounts detected: '. count($old_accounts) .'.';
			}
		} else {
			echo '<br>';
			echo 'yz85 compatible accounts detected: 0.';
		}
		echo '<br>';
		echo '<br>';
	} else {
		echo '<br>';
		echo 'Total accounts detected: 0.';
	}
	// end count status
	
	// validate accounts
	if (isset($old_accounts) && $old_accounts !== false) {
		$time = time();
		foreach ($old_accounts as $old) {
			// Get acc id
			$old_id = $old['id'];

			// Make acc data compatible:
			mysql_insert("INSERT INTO `yz85_accounts` (`account_id`, `ip`, `created`) VALUES ('$old_id', '0', '$time')");
			$updated_acc += 1;
			
			// Fetch unsalted password
			if ($config['TFSVersion'] == 'TFS_03' && $config['salt'] === true) {
				$password = user_data($old_id, 'password', 'salt');
				$p_pass = str_replace($password['salt'],"",$password['password']);
			}
			if ($config['TFSVersion'] == 'TFS_02' || $config['salt'] === false) {
				$password = user_data($old_id, 'password');
				$p_pass = $password['password'];
			}
			
			// Verify lenght of password is less than 28 characters (most likely a plain password)
			if (strlen($p_pass) < 28 && $old_id > 1) {
				// encrypt it with sha1
				if ($config['TFSVersion'] == 'TFS_02' || $config['salt'] === false) $p_pass = sha1($p_pass);
				if ($config['TFSVersion'] == 'TFS_03' && $config['salt'] === true) $p_pass = sha1($password['salt'].$p_pass);
				
				// Update their password so they are sha1 encrypted
				mysql_update("UPDATE `accounts` SET `password`='$p_pass' WHERE `id`='$old';");
				$updated_pass += 1;
			}
			
		}
	}
	
	// validate players
	if ($all_account !== false) {
		$time = time();
		foreach ($all_account as $all) {
			
			$chars = user_character_list_player_id($all);
			if ($chars !== false) {
				// since char list is not false, we found a character list
				
				// Lets loop through the character list
				foreach ($chars as $c) {
					// Is character not compatible yet?
					if (user_character_is_compatible($c) == 0) {
						// Then lets make it compatible:
						
						mysql_insert("INSERT INTO `yz85_players` (`player_id`, `created`, `hide_char`, `comment`) VALUES ('$c', '$time', '0', '')");
						$updated_char += 1;
						
					}
				}
			}
		}
	}
	
	echo "<br><b><font color=\"green\">SUCCESS</font></b><br><br>";
	echo 'Updated accounts: '. $updated_acc .'<br>';
	echo 'Updated characters: : '. $updated_char .'<br>';
	echo 'Detected:'. $updated_pass .' accounts with plain passwords. These passwords has been given sha1 encryption.<br>';
	echo '<br>All accounts and characters are compatible with yz85 AAC<br>';
?>
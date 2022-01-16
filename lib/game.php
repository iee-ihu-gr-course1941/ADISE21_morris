<?php

function show_status() {
	
	global $mysqli;
	
	check_abort();
	
	$sql = 'select * from game_status';
	$st = $mysqli->prepare($sql);

	$st->execute();
	$res = $st->get_result();

	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);

}

function check_abort() {
	global $mysqli;
	
	$sql = "update game_status set status='aborted', result=if(p_turn='W','B','W'),p_turn=null where p_turn is not null and last_change<(now()-INTERVAL 5 MINUTE) and status='started'";
	$st = $mysqli->prepare($sql);
	$r = $st->execute();
}

function update_status_setup($color, $val) {
	global $mysqli;
	
	if($color == "W")
		$sql = "update game_status set w_setup=?";
	elseif($color == "B")
		$sql = "update game_status set b_setup=?";
	$st = $mysqli->prepare($sql);
	$st->bind_param('i',$val);
	$st->execute();
}

function update_status_delete($color, $val) {
	global $mysqli;
	$p_turn = $color;
	if($val == 0) {
		if($p_turn=='W') {
			$p_turn='B';
		} else {
			$p_turn='W';
		}
	}
	if($color=='W') {
		$sql = "update game_status set p_turn=?, w_delete=?";
	} else {
		$sql = "update game_status set p_turn=?, b_delete=?";
	}

	$st = $mysqli->prepare($sql);
	$st->bind_param('si',$p_turn, $val);
	$st->execute();

	$st2=$mysqli->prepare('select count(*) as total_pieces from board where piece_color=?');
	$st2->bind_param('s',$p_turn);
	$st2->execute();
	$res2 = $st2->get_result();
	$total_pieces = $res2->fetch_assoc()['total_pieces'];

	$status = read_status();

	if(($status['w_setup'] == 1 && $status['b_setup'] == 1) && $total_pieces < 3) {
		$new_status = 'aborted';
		$st3 = $mysqli->prepare('update game_status set status=?, result=?,p_turn=null');
		$st3->bind_param('ss',$new_status,$color);
		$st3->execute();
		print_r("\nCongratulations! You won the game!");
	}
}

function update_game_status() {
	global $mysqli;
	
	$status = read_status();
	
	
	$new_status=null;
	$new_turn=null;
	
	$st3=$mysqli->prepare('select count(*) as aborted from players WHERE last_action< (NOW() - INTERVAL 5 MINUTE)');
	$st3->execute();
	$res3 = $st3->get_result();
	$aborted = $res3->fetch_assoc()['aborted'];
	if($aborted>0) {
		$sql = "UPDATE players SET username=NULL, token=NULL WHERE last_action< (NOW() - INTERVAL 5 MINUTE)";
		$st2 = $mysqli->prepare($sql);
		$st2->execute();
		if($status['status']=='started') {
			$new_status='aborted';
		}
	}

	
	$sql = 'select count(*) as c from players where username is not null';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	$active_players = $res->fetch_assoc()['c'];
	
	
	switch($active_players) {
		case 0: $new_status='not active'; break;
		case 1: $new_status='initialized'; break;
		case 2: $new_status='started'; 
				if($status['p_turn']==null) {
					$new_turn='W'; // It was not started before...
				}
				break;
	}

	$sql = 'update game_status set status=?, p_turn=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ss',$new_status,$new_turn);
	$st->execute();
	
	
	
}

function read_status() {
	global $mysqli;
	
	$sql = 'select * from game_status';
	$st = $mysqli->prepare($sql);

	$st->execute();
	$res = $st->get_result();
	$status = $res->fetch_assoc();
	return($status);
}
?>
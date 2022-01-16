<?php


function show_piece($x,$y) {
	global $mysqli;
	
	$sql = 'select * from board where x=? and y=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii',$x,$y);
	$st->execute();
	$res = $st->get_result();
	header('Content-type: application/json');
	print json_encode($res->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT);
}

function delete_piece($x,$y,$token) {
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}
	
	$color = current_color($token);
	if($color==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}
	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$color) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
	if($color == "W") {
		if($status['w_delete'] == 0) {
			header("HTTP/1.1 400 Bad Request");
			print json_encode(['errormesg'=>"You cannot remove a piece."]);
			exit;
		}
	} elseif($color == "B") {
		if($status['b_delete'] == 0) {
			header("HTTP/1.1 400 Bad Request");
			print json_encode(['errormesg'=>"You cannot remove a piece."]);
			exit;
		}
	}
	$orig_board=read_board();
	$board=convert_board($orig_board);
	if($board[$x][$y]['piece_color'] == $color) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You cannot remove your own piece."]);
		exit;
	}
	do_delete($x,$y);
	update_status_delete($color, 0);
}

function move_piece($x,$y,$x2,$y2,$token) {
	
	if($token==null || $token=='') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"token is not set."]);
		exit;
	}
	
	$color = current_color($token);
	if($color==null ) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"You are not a player of this game."]);
		exit;
	}
	$status = read_status();
	if($status['status']!='started') {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"Game is not in action."]);
		exit;
	}
	if($status['p_turn']!=$color) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"It is not your turn."]);
		exit;
	}
	if($color == "W") {
		if($status['w_delete'] == 1) {
			header("HTTP/1.1 400 Bad Request");
			print json_encode(['errormesg'=>"You must remove a piece first!"]);
			exit;
		}
	} elseif($color == "B") {
		if($status['b_delete'] == 1) {
			header("HTTP/1.1 400 Bad Request");
			print json_encode(['errormesg'=>"You must remove a piece first!"]);
			exit;
		}
	}
	$orig_board=read_board();
	$board=convert_board($orig_board);
	$n = add_valid_moves_to_piece($board,$color,$x,$y);
	if($n==0) {
		header("HTTP/1.1 400 Bad Request");
		print json_encode(['errormesg'=>"This piece cannot move."]);
		exit;
	}
	foreach($board[$x][$y]['moves'] as $i=>$move) {
		if($x2==$move['x'] && $y2==$move['y']) {
			$pieces_on_board = get_pieces_by_color($board, $color);
			// Scenario gia 3,1
			$millCount = 0;
			// Gia na doulepsei h for prepei kanoume to do_move kai sto $board manually
			$board[$x][$y]['piece_color'] = null;
			$board[$x2][$y2]['piece_color'] = $color;
			
			for($i = 1;$i <= 3;$i++) {
				print_r("Check x = $i y = $y2 color = $color\n");
				if($board[$i][$y2]['piece_color'] == $color) {
					$millCount += 1;
				}
			}

			if($y2 == 4) {
				for($i = 4;$i <= 6;$i++) {
					if($board[$i][$y2]['piece_color'] == $color) {
						$millCount += 1;
					}
				}
			}
			if($board[1][1]['piece_color'] == $color && $board[1][4]['piece_color'] == $color && $board[1][7]['piece_color'] == $color) {
				$millCount = 3;
			} elseif($board[1][2]['piece_color'] == $color && $board[1][4]['piece_color'] == $color && $board[1][6]['piece_color'] == $color) {
				$millCount = 3;
			}elseif($board[1][3]['piece_color'] == $color && $board[1][4]['piece_color'] == $color && $board[1][5]['piece_color'] == $color) {
				$millCount = 3;
			}elseif($board[2][1]['piece_color'] == $color && $board[2][2]['piece_color'] == $color && $board[2][3]['piece_color'] == $color) {
				$millCount = 3;
			}elseif($board[2][5]['piece_color'] == $color && $board[2][6]['piece_color'] == $color && $board[2][7]['piece_color'] == $color) {
				$millCount = 3;
			}elseif($board[3][3]['piece_color'] == $color && $board[4][4]['piece_color'] == $color && $board[3][5]['piece_color'] == $color) {
				$millCount = 3;
			}elseif($board[3][2]['piece_color'] == $color && $board[5][4]['piece_color'] == $color && $board[3][6]['piece_color'] == $color) {
				$millCount = 3;
			}elseif($board[3][1]['piece_color'] == $color && $board[6][4]['piece_color'] == $color && $board[3][7]['piece_color'] == $color) {
				$millCount = 3;
			}

			if($color == "W") {
				if($status['w_setup'] == 0 && $pieces_on_board == 8) {
					update_status_setup($color, 1);
				}
			} elseif($color == "B") {
				if($status['b_setup'] == 0 && $pieces_on_board == 8) {
					update_status_setup($color, 1);
				}
			}
			do_move($x,$y,$x2,$y2);
			print_r("\nmillCount = $millCount");
			if($millCount == 3) {
				update_status_delete($color, 1);
				print_r("You have formed a mill! You must delete a piece.");
			}
			exit;
		}
	}
	header("HTTP/1.1 400 Bad Request");
	print json_encode(['errormesg'=>"This move is illegal."]);
	exit;
}
		
function show_board($input) {
	global $mysqli;
	
	$b=current_color($input['token']);
	if($b) {
		show_board_by_player($b);
	} else {
		header('Content-type: application/json');
		print json_encode(read_board(), JSON_PRETTY_PRINT);
	}
}

function reset_board() {
	global $mysqli;
	$sql = 'call clean_board()';
	$mysqli->query($sql);
}

function read_board() {
	global $mysqli;
	$sql = 'select * from board';
	$st = $mysqli->prepare($sql);
	$st->execute();
	$res = $st->get_result();
	return($res->fetch_all(MYSQLI_ASSOC));
}

function convert_board(&$orig_board) {
	$board=[];
	foreach($orig_board as $i=>&$row) {
		$board[$row['x']][$row['y']] = &$row;
	} 
	return($board);
}

function show_board_by_player($b) {

	global $mysqli;

	$orig_board=read_board();
	$board=convert_board($orig_board);
	$status = read_status();
	if($status['status']=='started' && $status['p_turn']==$b && $b!=null) {
		// It my turn !!!!
		$n = add_valid_moves_to_board($board,$b);
		
		// Εάν n==0, τότε έχασα !!!!!
		// Θα πρέπει να ενημερωθεί το game_status.
	}
	header('Content-type: application/json');
	print json_encode($orig_board, JSON_PRETTY_PRINT);
}

function add_valid_moves_to_board(&$board,$b) {
	$number_of_moves=0;
	
	for($x=1;$x<6;$x++) {
		for($y=1;$y<7;$y++) {
			$number_of_moves+=add_valid_moves_to_piece($board,$b,$x,$y);
		}
	}
	return($number_of_moves);
}

function add_valid_moves_to_piece(&$board,$b,$x,$y) {
	$pieces_on_board=get_pieces_by_color($board, $b);
	$number_of_moves = valid_moves($board,$b,$x,$y,$pieces_on_board);

	/*if($board[$x][$y]['piece_color']==$b) {
		$number_of_moves+=pawn_moves($board,$b,$x,$y);
	}*/
	return($number_of_moves);
}

function get_pieces_by_color(&$board,$color) {
	$pieces_on_board = 0;
	foreach($board as $eleni=>$val){ 
		foreach($val as $ikea=>$v){ 
		    if($v['piece_color'] == $color)
				$pieces_on_board++;
		}
	}
	return($pieces_on_board);
}

function color_completed_setup($color) {
	$status = read_status();
	if($color == "W") {
		if($status['w_setup'] == 1) {
			return true;
		}
		return false;
	} elseif($color == "B") {
		if($status['b_setup'] == 1) {
			return true;
		}
		return false;
	}
	return false;
}

function valid_moves(&$board,$color,$x,$y,$pieces_on_board) {
	$directions = [
		[1,0],
		[-1,0],
		[0,1],
		[0,-1],

	
	];

	
	$valid_directions = [
		"1,1" => [ [2,1], [1,4] ],
		
		"2,1" => [ [3,1], [1,1], [2,2] ],
		
		"3,1" => [ [2,1], [3,4] ],
		//prwti seira
		
		"1,2" => [ [2,2], [2,4] ],
		
		"2,2" =>  [ [1,2], [3,2], [2,3], [2,1] ], 
		
		"3,2" => [ [2,2], [2,4] ],
		
		//2h seira
		
		"1,3" => [ [2,3], [3,4] ], 
		
		"2,3" => [ [1,3], [2,2], [3,3] ],
		
		"3,3" => [ [2,3], [4,4] ],
		
		//3h seira
		
		"1,4" => [ [2,4], [1,1], [1,7] ],
		
		"2,4" => [ [1,4], [3,4], [1,2], [1,6] ], 

		"3,4" => [ [2,4], [1,5], [1,3] ],

		"4,4" => [ [5,4], [3,3], [5,3] ],

		"5,4" => [ [4,4], [6,4], [3,2], [3,6] ],

		"6,4" => [ [5,4], [3,1], [3,7] ],	
		
		//4h seira 
		
		"1,5" => [ [2,5], [3,4] ], 
		
		"2,5" => [ [1,5], [3,5], [2,6] ],
		
		"3,5" => [ [2,5], [4,4] ], 
		
		//5h seira 
		
		"1,6" => [ [2,6], [1,4] ], 
		
		"2,6" => [ [1,6], [3,6], [2,5], [2,7] ],
		
		"3,6" => [ [2,6], [5,4] ], 
		
		//7h seira 
		
		"1,7" => [ [1,4], [2,7] ], 
		
		"2,7" => [ [1,7], [3,7], [2,6] ],
		
		"3,7" => [ [3,4], [2,7] ]
	];
	/*foreach($valid_directions["1,1"] as $v){
		print_r($v);
	}*/
	
	$moves=[];
	
	

	if(color_completed_setup($color) == false && $pieces_on_board < 9) {
		foreach($board as $eleni=>$val){ 
			foreach($val as $ikea=>$v){ 
				if($x == 0 && $y == 0 && $v['piece_color'] == null) {
					$move=['x'=>$v['x'], 'y'=>$v['y']];
					$moves[]=$move;
				}
			}
		}
		
		$board[$x][$y]['moves'] = $moves;
		return(sizeof($moves));
	}

	$stringDirection = strval($x) . "," . strval($y);
	foreach($valid_directions[$stringDirection] as $v1) {
		if($board[$v1[0]][$v1[1]]['piece_color'] == null) {
			$move=['x'=>$v1[0], 'y'=>$v1[1]];
			$moves[]=$move;
		}
		
	}
	/*foreach($directions as $d=>$direction) {
		$i=$x+$direction[0];
		$j=$y+$direction[1];
		if ( $i>=1 && $i<=8 && $j>=1 && $j<=8 && $board[$i][$j]['piece_color'] != $color) {
			$move=['x'=>$i, 'y'=>$j];
			$moves[]=$move;
		}
	}*/

	$board[$x][$y]['moves'] = $moves;
	return(sizeof($moves));
}

function do_delete($x, $y) {
	global $mysqli;
	
	$sql = "update board set piece_color=null where x=? and y=?";
	$st = $mysqli->prepare($sql);
	$st->bind_param('ii',$x,$y);
	$st->execute();

	header('Content-type: application/json');
	print json_encode(read_board(), JSON_PRETTY_PRINT);
}

function do_move($x,$y,$x2,$y2) {
	global $mysqli;
	$sql = 'call `move_piece`(?,?,?,?);';
	$st = $mysqli->prepare($sql);
	$st->bind_param('iiii',$x,$y,$x2,$y2 );
	$st->execute();

	header('Content-type: application/json');
	print json_encode(read_board(), JSON_PRETTY_PRINT);
}

function insert_pawn($x,$y,$color){
	echo $x . " " . $y . " " . $color;
	global $mysqli;
	$sql = 'update board set piece_color=? where x=? and y=?';
	$st = $mysqli->prepare($sql);
	$st->bind_param('sii',$color, $x, $y);
	$st->execute();

	header('Content-type: application/json');
	print json_encode(read_board(), JSON_PRETTY_PRINT);
}

?>
<?php
require_once __DIR__ . '/../../config/db.php';

function generate_match_row($match_data){
	$match_div = "<th scope='row'>{$match_data['id']}</th>";
	$match_div .= "<td>{$match_data['player1']}</td>";
	$match_div .= "<td>{$match_data['player2']}</td>";
	$board = $match_data['board_markings'];
	$match_div .= "<td>{$board}</td>";
	//$match_div .= "<div class='col border'>{$match_data['board_markings']}</div>";
	$match_div .= "<td>{$match_data['winner']}</td>";
	$match_div .= "<td><a href='?page=game&room_id={$match_data['id']}'>";
	$match_div .= <<< HERE
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-right-square-fill" viewBox="0 0 16 16">
  <path d="M14 0a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h12zM5.904 10.803 10 6.707v2.768a.5.5 0 0 0 1 0V5.5a.5.5 0 0 0-.5-.5H6.525a.5.5 0 1 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 .707.707z"/>
</svg>
</td>
HERE;
	return $match_div;
}

function generate_history_table($history_data){
	$match_history_div = "<table class='table'>";
	$match_history_div .= <<< HERE
  <thead>
	<tr>
	  <th scope='col'>#</th>
	  <th scope='col'>First</th>
	  <th scope='col'>Second</th>
	  <th scope='col'>Final Board</th>
	  <th scope='col'>Winner</th>
	</tr>
  </thead>
  <tbody>
HERE;

	//foreach ($row as $history_data){
	foreach ($history_data as $index => $row){
		$match_div = generate_match_row($row);

		$match_history_div .= "<tr>{$match_div}</th>\n";
	}
	return $match_history_div . "</tbody></table>";
}

$username = $_SESSION['username'];

if($username == 'alice'){
	$sql = "SELECT * FROM rooms";
	$result = $conn->query($sql);
	// MYSQLI_ASSOC keeps the column names as keys in the arrays
	$history_data = $result->fetch_all(MYSQLI_ASSOC);

	$match_history_table = generate_history_table($history_data);
	return;
}

// Prepare and execute the query using prepared statements
$sql = "SELECT * FROM rooms WHERE player1 = ? OR player2 = ?";
$statement = $conn->prepare($sql);

// Bind username, a string paramater
$statement->bind_param('ss', $username, $username);
$statement->execute();
$result = $statement->get_result();
$history_data = $result->fetch_all();

$match_history_table = generate_history_table($result);

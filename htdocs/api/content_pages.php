<?php
$page_list = [
	[
		"name"=>"lobby",
		"title"=>"Lobby",
	],
	[
		"name"=>"game",
		"title"=>"Match",
	],
	[
		"name"=>"history",
		"title"=>"Match History",
	],
	[
		"name"=>"about",
		"title"=>"About page",
	],
];

function checkPage($askedPage){
	global $page_list;
	foreach($page_list as $page){
		if($page['name'] == $askedPage){
			return true;
		}
	}
	return false;
}

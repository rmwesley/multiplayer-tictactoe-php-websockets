<?php
$page_list = array(
  array(
    "name"=>"home",
    "title"=>"TicTacToe"),
  array(
    "name"=>"lobby",
    "title"=>"About"),
  array(
    "name"=>"game",
    "title"=>"Match"),
  array(
    "name"=>"history",
    "title"=>"History"),
  array(
    "name"=>"contacts",
    "title"=>"About")
);

function checkPage($askedPage){
	global $page_list;
	foreach($page_list as $page){
		if($page['name'] == $askedPage){
			return true;
		}
	}
	return false;
}

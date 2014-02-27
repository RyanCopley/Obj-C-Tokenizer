<?php
require("tokenize.php");

$contents = file_get_contents("RCActiveRecord.m");

$tokens = tokenize($contents);
print_r( ($tokens));

function detokenize($tokens){
	$startNode = new node();
	$startNode->isRootNode();
	
}

class node{
	private $isStartNode = false;
	
	private $nodes=array();
	
	private $next;
	private $prev;
	
	public function addNode($_node){
		$nodes[]=$_node;
	}
	public function nextNode(){
		$next = new node();
		$next->setPrev($this);
		return $next;
	}
	public function isRootNode(){
		$isStartNode = true;
	}
	
	public function setPrev($_prev){
		$prev = $_prev;
	}
	
}
?>
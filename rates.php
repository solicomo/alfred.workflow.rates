<?php
/**
* Name: 		Rates
* Description: 	A Alfred workflow which converts currency with Fixer.io's free JSON API
* 				(http://fixer.io).
* Author: 		Soli Como (@solicomo)
* Revised: 		2016-05-12
* Version:		0.1.0
*/

require_once("workflows.php");

function safe_array_access($args) {
	$argc = func_num_args();
	$argv = func_get_args();
	$iter = $args;

	for($i = 1; $i < $argc; $i++) {
		if (is_array($iter) && array_key_exists($argv[$i], $iter) && isset($iter[$argv[$i]])) {
			$iter = $iter[$argv[$i]];
		} else {
			return(null);
		}
	}
	return($iter);
}

function convert($wf, $amount, $from, $to) {
	$data = $wf->request("http://api.fixer.io/latest?base=${from}&symbols=${to}");
	$rates = safe_array_access(json_decode($data, true), 'rates', $to);
	if (isset($rates)) {
		return ($amount * $rates);
	} else {
		return null;
	}
}

function main($query) {
	$args = array_filter(explode(" ", trim($query)));
	$argc = count($args);
	$wf = new Workflows();

	switch($argc) {
	case 3:
		$result = convert($wf, $args[0], strtoupper($args[1]), strtoupper($args[2]));
		$wf->result('rates_result', 'result', "$args[0] $args[1] = $result $args[2]", '', 'icon.png', 'yes', '');
		break;
	case 0:
	case 1:
	case 2:
	default:
		$wf->result('rates_0', 'usage', 'Rates', "USAGE: r COUNT FROM TO", 'icon.png', 'no', '1 USD CNY');
		break;
	}
	
	echo $wf->toxml();
}


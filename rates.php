<?php
/**
* Name: 		Rates
* Description: 	A Alfred workflow which converts currency with Fixer.io's free JSON API
* 				(http://fixer.io).
* Author: 		Soli Como (@solicomo)  Kyle He (@kyleehee)
* Revised: 		2017-10-17
* Version:		0.2.0
*/

require_once("workflows.php");


function convert($wf, $amount, $from, $to) {
	$data = $wf->request("http://api.fixer.io/latest?base=${from}&symbols=${to}");
	$rates = json_decode($data, true);
	if (is_null($rates) || !isset($rates['rates'])) {
		throw new Exception("Can not get currency rates");
	}
	$ret = array();
	foreach ($rates['rates'] as $cur => $rate) {
		$ret[$cur] = $rate * $amount;
	}
	return $ret;
}

function main($query) {
	$args = array_filter(explode(" ", trim(strtoupper($query))));
	$argc = count($args);
	$wf = new Workflows();

	switch($argc) {
	case 3:
		$fromValue = floatval($args[0]);
		$fromCurrency = $args[1];
		$toCurrencys = $args[2];
		try {
			$results = convert($wf, $fromValue, $fromCurrency, $toCurrencys);
		} catch (Exception $e) {
			$wf->result('rates_2', 'error', $e->getMessage(), "", 'icon.png', 'no', '');
		}

		foreach (explode(',', $toCurrencys) as $cur) {
			if (isset($results[$cur])) {
				$result = $results[$cur];
				$wf->result("rates_result_$i", 'result',
					"$fromValue $fromCurrency = $result $cur", '', 'icon.png', 'yes', '');
			} else {
				$wf->result("rates_result_$i", 'error',
					"Can not convert to $cur", 'Unacceptable Currency', 'icon.png', 'no', '');
			}
		}
		break;
	case 0:
	case 1:
	case 2:
	default:
		$wf->result('rates_0', 'usage', 'Rates', "USAGE: r COUNT FROM TO1,TO2,...", 'icon.png', 'no', '1 USD CNY,CAD');
		break;
	}

	echo $wf->toxml();
}


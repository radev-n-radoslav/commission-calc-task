<?php

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

use RadevNRadoslav\CommissionTask\Service\CommissionFeeCalculator;

if (isset($argc) && isset($argv[1])) {
	// Parse CSV to Arr
	$file = fopen($argv[1], 'r');

	if ($file !== false) {
		while (!feof($file) ) {
	        $lines[] = fgetcsv($file, 0, ',');
	    }
	    fclose($file);
	}

	$fees = new CommissionFeeCalculator($lines);
	foreach ($fees->calculate() as $fee) {
		echo "$fee \n";
	}
}
else {
	echo "A file path is not supplied. Exiting script.\n";
}

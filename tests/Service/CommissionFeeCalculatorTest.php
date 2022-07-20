<?php

declare(strict_types=1);

namespace RadevNRadoslav\CommissionTask\Tests\Service;

use RadevNRadoslav\CommissionTask\Service\CommissionFeeCalculator;
use PHPUnit\Framework\TestCase;
	
class CommissionFeeCalculatorTest extends TestCase
{
	/**
     * @var Calculator
     */
    private $calculator;

    /**
     * Get test data
     */
    public function getData(): array
    {
        // Parse CSV to Arr
        $file = fopen('input-data.csv', 'r');

        if ($file !== false) {
            while (!feof($file) ) {
                $lines[] = fgetcsv($file, 0, ',');
            }
            fclose($file);
        }

        return $lines;
    }

    public function setUp()
    {
        $this->calculator = new CommissionFeeCalculator($this->getData());
    }

    /**
     * @dataProvider dataProviderForTestCalculate
     */
    public function testCalculate(array $expectedResults)
    {
        $results = $this->calculator->calculate();
        foreach ($results as $key => $result) {
            $this->assertEquals($expectedResults[$key], $result);
        }
    }


    public function dataProviderForTestCalculate(): array
    {
        $dataLines = $this->getData();
        $expectedResults = [];
        foreach ($dataLines as $dataLine) {
            $expectedResults[] = $dataLine[6];
        }

        return [
            'testData' => [$expectedResults]   
        ];
    }
}
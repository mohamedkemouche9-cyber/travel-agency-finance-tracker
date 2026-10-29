<?php

declare(strict_types=1);

require __DIR__.'/../app/Services/Forecast.php';

use App\Services\Forecast;

$checks = 0;

function expectClose(float $actual, float $expected, string $message): void
{
    global $checks;
    if (abs($actual - $expected) > 0.000001) {
        throw new RuntimeException($message.': expected '.$expected.', got '.$actual);
    }
    $checks++;
}

function expectSeries(array $actual, array $expected, string $message): void
{
    if (count($actual) !== count($expected)) {
        throw new RuntimeException($message.': unexpected number of predictions');
    }
    foreach ($expected as $index => $value) {
        expectClose((float) $actual[$index], (float) $value, $message.' at '.$index);
    }
}

// A known straight-line series should extrapolate exactly.
$linear = Forecast::project([100, 120, 140], 3);
expectSeries($linear['predictions'], [160, 180, 200], 'Increasing linear trend');
expectClose($linear['model']['slope'], 20.0, 'Linear slope');
expectClose($linear['model']['r2'], 1.0, 'Perfect historical fit');

// Constant and sparse histories must remain well-defined.
expectSeries(Forecast::project([50, 50, 50], 2)['predictions'], [50, 50], 'Constant series');
expectSeries(Forecast::project([17], 2)['predictions'], [17, 17], 'Single observation');
expectSeries(Forecast::project([], 2)['predictions'], [0, 0], 'Empty history');
expectSeries(Forecast::project([4 => 10, 8 => 20], 2)['predictions'], [30, 40], 'Non-sequential keys');

// Expenses cannot become negative, while net balances may.
expectSeries(Forecast::project([30, 20, 10], 3, true)['predictions'], [0, 0, 0], 'Non-negative projection');
expectSeries(Forecast::project([30, 20, 10], 3, false)['predictions'], [0, -10, -20], 'Signed net projection');

echo 'Forecast smoke checks passed: '.$checks.PHP_EOL;

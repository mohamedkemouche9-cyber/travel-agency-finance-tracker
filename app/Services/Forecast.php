<?php

namespace App\Services;

/**
 * ============================================================================
 *  Forecast - simple financial prediction engine (service class).
 * ----------------------------------------------------------------------------
 *  Uses Ordinary Least Squares linear regression over the user's monthly
 *  totals to project income, spending and net balance for the coming months.
 *  The model is deliberately transparent (y = slope * x + intercept) so the
 *  prediction can be explained to the user, and the R-squared value is
 *  returned as a confidence indicator.
 * ============================================================================
 */
class Forecast
{
    /**
     * Fit y = slope * x + intercept over points (0, y0), (1, y1), ...
     *
     * @param  float[]  $y  observed monthly values, oldest first
     * @return array{slope: float, intercept: float, r2: float}
     */
    public static function regression(array $y): array
    {
        $y = array_values($y);
        $n = count($y);

        if ($n === 0) {
            return ['slope' => 0.0, 'intercept' => 0.0, 'r2' => 0.0];
        }
        if ($n === 1) {
            return ['slope' => 0.0, 'intercept' => (float) $y[0], 'r2' => 1.0];
        }

        $sumX = $sumY = $sumXY = $sumXX = 0.0;
        foreach ($y as $x => $value) {
            $sumX += $x;
            $sumY += $value;
            $sumXY += $x * $value;
            $sumXX += $x * $x;
        }

        $den = $n * $sumXX - $sumX * $sumX;
        $slope = $den == 0.0 ? 0.0 : ($n * $sumXY - $sumX * $sumY) / $den;
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Coefficient of determination R² (goodness of fit, 0..1)
        $meanY = $sumY / $n;
        $ssTot = $ssRes = 0.0;
        foreach ($y as $x => $value) {
            $fit = $slope * $x + $intercept;
            $ssTot += ($value - $meanY) ** 2;
            $ssRes += ($value - $fit) ** 2;
        }
        $r2 = $ssTot == 0.0 ? 1.0 : max(0.0, 1 - $ssRes / $ssTot);

        return ['slope' => $slope, 'intercept' => $intercept, 'r2' => $r2];
    }

    /**
     * Project a series a number of periods ahead.
     *
     * @param  float[]  $history  observed values, oldest first
     * @param  int  $ahead  how many future periods to predict
     * @param  bool  $clampZero  prevent negative predictions (used for
     *                           income/expense, which cannot go below zero)
     * @return array{model: array, predictions: float[]}
     */
    public static function project(array $history, int $ahead = 3, bool $clampZero = true): array
    {
        $model = self::regression($history);
        $n = count($history);

        $predictions = [];
        for ($i = 0; $i < $ahead; $i++) {
            $value = $model['slope'] * ($n + $i) + $model['intercept'];
            $predictions[] = round($clampZero ? max(0.0, $value) : $value, 2);
        }

        return ['model' => $model, 'predictions' => $predictions];
    }
}

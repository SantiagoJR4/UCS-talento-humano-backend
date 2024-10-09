<?php

namespace App\Service;

use DateTime;

class DateUtilities
{
    public function dateIntervals(array $array, array $startAndEnd): string
    {
        // Function to check if a value is a valid date, string, or null
        $isValidDate = function ($value) {
            return $value instanceof DateTime || is_string($value) || is_null($value);
        };
    
        // Loop through the array to validate start and end dates
        foreach ($array as $item) {
            if (!$isValidDate($item[$startAndEnd[0]]) || !$isValidDate($item[$startAndEnd[1]])) {
                return 'error';
            }
        }
    
        // Map through the array and format the start and end dates
        $result = array_map(function ($item) use ($startAndEnd) {
            $startDate = (new DateTime($item[$startAndEnd[0]]))->format('d/m/Y');
            $endDate = isset($item[$startAndEnd[1]]) && $item[$startAndEnd[1]] !== null
                ? (new DateTime($item[$startAndEnd[1]]))->format('d/m/Y')
                : 'hoy';
    
            return "De $startDate hasta $endDate";
        }, $array);
    
        return implode(",\n", $result);
    }

    function timeWorked(array $datesArray, array $startAndEnd): string
    {
        // Return '0 años, 0 meses y 0 días' if the array is empty
        if (count($datesArray) === 0) {
            return '0 años, 0 meses y 0 días';
        }

        // Sort the array by the start date
        usort($datesArray, function ($x, $y) use ($startAndEnd) {
            return $x[$startAndEnd[0]] <=> $y[$startAndEnd[0]];
        });

        // Convert strings to DateTime objects and handle the end date (set to today if null)
        $datesArray = array_map(function ($item) use ($startAndEnd) {
            return [
                $startAndEnd[0] => new DateTime($item[$startAndEnd[0]]),
                $startAndEnd[1] => isset($item[$startAndEnd[1]]) && $item[$startAndEnd[1]] !== null
                    ? new DateTime($item[$startAndEnd[1]])
                    : new DateTime() // Set to current date if end date is null
            ];
        }, $datesArray);

        $consolidatedDates = [array_shift($datesArray)];
        $a = 0;
        $b = 1;

        foreach ($datesArray as $i) {
            while ($a != $b) {
                // Consolidate overlapping dates
                if ($consolidatedDates[$a][$startAndEnd[1]] >= $i[$startAndEnd[0]] && $consolidatedDates[$a][$startAndEnd[1]] <= $i[$startAndEnd[1]]) {
                    $consolidatedDates[$a][$startAndEnd[1]] = $i[$startAndEnd[1]];
                    break;
                }
                // Add a new entry if there is no overlap
                if ($a === $b - 1 && $consolidatedDates[$a][$startAndEnd[1]] < $i[$startAndEnd[0]]) {
                    $consolidatedDates[] = [
                        $startAndEnd[0] => $i[$startAndEnd[0]],
                        $startAndEnd[1] => $i[$startAndEnd[1]]
                    ];
                    $b++;
                    break;
                }
                $a++;
            }
            $a = 0;
        }

        // Calculate the total difference in milliseconds
        $differenceFromConsolidatedDates = 0;
        foreach ($consolidatedDates as $i) {
            $differenceInMilliseconds = ($i[$startAndEnd[1]]->getTimestamp() * 1000) - ($i[$startAndEnd[0]]->getTimestamp() * 1000);
            $differenceFromConsolidatedDates += $differenceInMilliseconds + 86400000; // Add 1 day (86400000 ms)
        }

        // Convert the difference to years, months, and days
        $years = floor($differenceFromConsolidatedDates / (365.25 * 24 * 60 * 60 * 1000));
        $remainingMonths = floor(($differenceFromConsolidatedDates % (365.25 * 24 * 60 * 60 * 1000)) / (30.44 * 24 * 60 * 60 * 1000));
        $remainingDays = floor(($differenceFromConsolidatedDates % (30.44 * 24 * 60 * 60 * 1000)) / (24 * 60 * 60 * 1000));

        return "{$years} años, {$remainingMonths} meses y {$remainingDays} días";
    }

}
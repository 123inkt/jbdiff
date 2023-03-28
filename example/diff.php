<?php
declare(strict_types=1);

use DR\JBDiff\ComparisonPolicy;
use DR\JBDiff\JBDiff;
use DR\JBDiff\LineBlockTextIterator;

require_once __DIR__ . '/../vendor/autoload.php';

$text1 = $_POST['before'];
$text2 = $_POST['after'];
$mode  = (int)($_POST['mode'] ?? 1);
$view  = (int)($_POST['view'] ?? 1);

$policy = ComparisonPolicy::DEFAULT;
if ($mode === 2) {
    $policy = ComparisonPolicy::TRIM_WHITESPACES;
} elseif ($mode === 3) {
    $policy = ComparisonPolicy::IGNORE_WHITESPACES;
}

function formatSideBySide(LineBlockTextIterator $iterator): string
{
    $before = "";
    $after  = "";

    foreach ($iterator as [$change, $text]) {
        if ($change === LineBlockTextIterator::TEXT_UNCHANGED_BEFORE) {
            $before .= htmlspecialchars($text);
        } elseif ($change === LineBlockTextIterator::TEXT_UNCHANGED_AFTER) {
            $after .= htmlspecialchars($text);
        } elseif ($change === LineBlockTextIterator::TEXT_ADDED) {
            $after .= '<span style="background-color: #A6F3A6">' . htmlspecialchars($text) . '</span>';
        } elseif ($change === LineBlockTextIterator::TEXT_REMOVED) {
            $before .= '<span style="background-color: #F8CBCB">' . htmlspecialchars($text) . '</span>';
        }
    }

    $html = '<div style="display: grid;grid-template-columns:  1fr 1fr;grid-column-gap: 10px">';
    $html .= '<pre style="font-family: Monospaced, \'Courier New\'">' . $before . '</pre>';
    $html .= '<pre style="font-family: Monospaced, \'Courier New\'">' . $after . '</pre>';
    $html .= '</div>';

    return $html;
}

function formatInline(LineBlockTextIterator $iterator): string
{
    $html = '<pre style="font-family: Monospaced, \'Courier New\'">';
    foreach ($iterator as [$change, $text]) {
        if ($change === LineBlockTextIterator::TEXT_UNCHANGED_AFTER) {
            $html .= htmlspecialchars($text);
        } elseif ($change === LineBlockTextIterator::TEXT_ADDED) {
            $html .= '<span style="background-color: #A6F3A6">' . htmlspecialchars($text) . '</span>';
        } elseif ($change === LineBlockTextIterator::TEXT_REMOVED) {
            $html .= '<span style="background-color: #F8CBCB">' . htmlspecialchars($text) . '</span>';
        }
    }
    $html .= "</pre>\n";

    return $html;
}

// diff the content
$startTime  = microtime(true);
$iterator = (new JBDiff())->compareToIterator($text1, $text2, $policy);
$duration   = round(microtime(true) - $startTime, 3);

// format to html
if ($view === 1) {
    $html = formatSideBySide($iterator);
} else {
    $html = formatInline($iterator);
}

header('Content-Type: text/html; charset=utf-8');
header('X-duration: ' . $duration);
echo $html;

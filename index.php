<?php

require_once 'vendor/autoload.php';

$options = getopt('', ['file:', 'only-show-cheaper-items', 'no-color']);

$onlyShowCheaperItems = array_key_exists('only-show-cheaper-items', $options);
$shouldColorize = !array_key_exists('no-color', $options);

$reiChecker = new \PriceChecker\REIChecker($onlyShowCheaperItems, $shouldColorize);
$reiChecker->showAllPrices($options['file']);
$reiChecker->showPotentialSavings();

?>
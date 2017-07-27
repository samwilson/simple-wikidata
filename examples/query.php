<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Any PSR6 cache can be used.
$cache = new Stash\Pool(new \Stash\Driver\FileSystem());

$sparql = 'SELECT ?item WHERE {
  ?item wdt:P31 wd:Q54050
} LIMIT 5';
$query = new \Samwilson\SimpleWikidata\Query($sparql, 'en', $cache);
$hills = $query->getItems();
foreach ($hills as $hill) {
    $heights = $hill->getPropertyOfTypeQuantity('P2044');
    if (!$heights) {
        echo "No heights found for ".$hill->getLabel()."\n";
        continue;
    }
    $height = array_shift($heights);
    echo $hill->getLabel()." is ".$height['amount']." ".$height['unit']->getLabel()." high.\n";
}

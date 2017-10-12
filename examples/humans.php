<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Any PSR6 cache can be used.
$cache = new Stash\Pool( new \Stash\Driver\FileSystem() );

/** @var \Samwilson\SimpleWikidata\Items\Human $princeCharles */
$princeCharles = Samwilson\SimpleWikidata\Item::factory( 'Q43274', 'en', $cache );

echo $princeCharles->getLabel().":\n";

$refNum = 1;
$references = [];

/** @var \Samwilson\SimpleWikidata\Properties\Time[] $datesOfBirth */
$datesOfBirth = $princeCharles->getDatesOfBirth();
echo "  Date of birth: ".$datesOfBirth[0]->getDateTime()->format( 'j F, Y' )." ";
foreach ( $datesOfBirth[0]->getReferences() as $ref ) {
	if ( $ref->statedIn() ) {
		echo "[$refNum]";
		$references[$refNum] = $ref;
	}
}
echo "\n";

/** @var \Samwilson\SimpleWikidata\Properties\Item[] $fathers */
$fathers = $princeCharles->fathers();
echo "  Father: ".$fathers[0]->getItem()->getLabel() . " ";
foreach ( $fathers[0]->getReferences() as $ref ) {
	if ( $ref->statedIn() ) {
		echo "[$refNum]";
		$references[$refNum] = $ref;
	}
}
echo "\n";

foreach ( $references as $refNum => $ref ) {
	echo "  [$refNum] - " . $ref->statedIn()->getLabel()."\n";
}

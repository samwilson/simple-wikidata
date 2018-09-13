<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Any PSR6 cache can be used.
$cache = new Stash\Pool( new \Stash\Driver\FileSystem() );

// Search for anything.
$search = new \Samwilson\SimpleWikidata\Search( 'pride and prejudice', 'en', $cache );
$items = $search->getItems( 3 );
foreach ( $items as $item ) {
	$instanceOf = $item->getPropertyOfTypeItem(
		\Samwilson\SimpleWikidata\Item::PROP_INSTANCE_OF
	);
	$instanceOfLabel = isset( $instanceOf[0] ) ? $instanceOf[0]->getItem()->getLabel() : 'UNKNOWN';
	echo $item->getLabel().' ('.$instanceOfLabel.')'."\n";
}

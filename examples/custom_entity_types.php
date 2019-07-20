<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Any PSR6 cache can be used.
$cache = new Stash\Pool( new \Stash\Driver\FileSystem() );

// @codingStandardsIgnoreStart
/**
 * We want to be able to easily retrieve information
 * about things that are instances of railway station (Q55488),
 * so we register a new class that defines its INSTANCE_OF constant.
 * @link https://www.wikidata.org/wiki/Wikidata:WikiProject_Railways
 */
class RailwayStation extends \Samwilson\SimpleWikidata\Item {
	// @codingStandardsIgnoreEnd
	/**
	 * Every subclass of Item should define its 'instance of' ID.
	 */
	const INSTANCE_OF = 'Q55488';

	/**
	 * It can then contain any type-specific methods that are required.
	 * @return RailwayStation[]
	 */
	public function hasAdjacentStations() {
		return $this->getPropertyOfTypeItem( 'P197' );
	}

	/**
	 * Including queries.
	 * @return RailwayStation[]
	 */
	public function isAdjacentTo() {
		$sparql = "SELECT ?item WHERE { ?item wdt:P197 wd:" . $this->getId() . " }";
		$query = new \Samwilson\SimpleWikidata\Query( $sparql, $this->lang, $this->cache );
		// Each query result will also be a RailwayStation
		// (if they're correctly recorded as such on Wikidata).
		return $query->getItems();
	}
}

// Then the class must register itself.
RailwayStation::register();

// Then we can create items with the factory and use them.
/** @var RailwayStation $eustonStation */
$eustonStation = \Samwilson\SimpleWikidata\Item::factory( 'Q800751', 'en', $cache );
echo $eustonStation->getLabel()
	. " has " . count( $eustonStation->hasAdjacentStations() ) . " adjacent stations"
	. " and is adjacent to " . count( $eustonStation->isAdjacentTo() ) . " stations.\n";

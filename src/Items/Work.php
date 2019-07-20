<?php

namespace Samwilson\SimpleWikidata\Items;

use Psr\Cache\CacheItemPoolInterface;
use Samwilson\SimpleWikidata\Item;
use Samwilson\SimpleWikidata\Query;

/**
 * @link
 */
class Work extends Item {

	const ITEM_WORK = 'Q386724';
	const PROP_SUBTITLE = 'P1680';
	const PROP_GENRE = 'P136';
	const PROP_SUBJECT = 'P921';

	/**
	 * @param string $lang ISO639 language code.
	 * @param CacheItemPoolInterface $cache The cache.
	 * @return array|Item[]
	 */
	public static function getBookTypes( $lang = 'en', $cache ) {
		$sparql = "SELECT ?item WHERE {
			?item wdt:P279 wd:Q571 .
			?item rdfs:label ?label .
			FILTER(LANG(?label) = '$lang') .
			} ORDER BY ?label ";
		$query = new Query( $sparql, $lang );
		$query->setCache( $cache );
		$bookType = Item::factory( self::ITEM_WORK, $lang, $cache );
		return [ $bookType ] + $query->getItems();
	}

	/**
	 * @return bool|string
	 */
	public function getSubtitle() {
		return $this->getPropertyOfTypeText( self::PROP_SUBTITLE );
	}

	/**
	 * @param string $subtitle The new subtitle.
	 */
	public function setSubtitle( $subtitle ) {
		$this->setPropertyOfTypeText( self::PROP_SUBTITLE, $subtitle );
	}

	/**
	 * @param string $property A property identifier.
	 * @return array
	 */
	public function getPropertyOfTypeItems( $property ) {
		$entity = $this->getEntity( $this->id );
		if ( !isset( $entity['claims'][ $property ] ) ) {
			return [];
		}
		$items = [];
		foreach ( $entity['claims'][ $property ] as $authorClaim ) {
			$item_id  = $authorClaim['mainsnak']['datavalue']['value']['id'];
			$items[] = Item::factory( $item_id, $this->lang, $this->cache );
		}

		return $items;
	}

	/**
	 * @return array
	 */
	public function getAuthors() {
		return $this->getPropertyOfTypeItems( self::PROP_AUTHOR );
	}

	/**
	 * @return Item[]
	 */
	public function getSubjects() {
		return $this->getPropertyOfTypeItems( self::PROP_SUBJECT );
	}

	/**
	 * @return Item[]
	 */
	public function getEditions() {
		$sparql = "SELECT ?item WHERE {"
			. " ?item wdt:" . Edition::PROP_EDITION_OR_TRANSLATION_OF . " wd:" . $this->getId()
			. "}";
		$query = new Query( $sparql, $this->lang, $this->cache );
		$editions = $query->getItems();
		usort( $editions, function ( Item $a, Item $b ) {
			if ( $a instanceof Edition && $b instanceof Edition ) {
				return $a->getPublicationYear() - $b->getPublicationYear();
			}
			return 0;
		} );

		return $editions;
	}

	public function newEdition() {
	}

}

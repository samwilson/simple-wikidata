<?php

namespace Samwilson\SimpleWikidata\Items;

use Samwilson\SimpleWikidata\Item;

/**
 * @link https://www.wikidata.org/wiki/Q5
 */
class Human extends Item {

	const PROP_DATE_OF_BIRTH = 'P569';
	const PROP_PLACE_OF_BIRTH = 'P19';

	const PROP_DATE_OF_DEATH = 'P570';
	const PROP_PLACE_OF_DEATH = 'P20';

	const PROP_FATHER = 'P22';
	const PROP_MOTHER = 'P25';
	const PROP_SPOUSE = 'P26';
	const PROP_CHILD = 'P40';

	/**
	 * @return bool|\Samwilson\SimpleWikidata\Properties\Time[]
	 */
	public function getDatesOfBirth() {
		return $this->getPropertyOfTypeTime( self::PROP_DATE_OF_BIRTH );
	}

	/**
	 * @return bool|\Samwilson\SimpleWikidata\Properties\Time[]
	 */
	public function getDatesOfDeath() {
		return $this->getPropertyOfTypeTime( self::PROP_DATE_OF_DEATH );
	}

	/**
	 * @return \Samwilson\SimpleWikidata\Properties\Item[]
	 */
	public function fathers() {
		return $this->getPropertyOfTypeItem( self::PROP_FATHER );
	}

	/**
	 * @return \Samwilson\SimpleWikidata\Properties\Item[]
	 */
	public function mothers() {
		return $this->getPropertyOfTypeItem( self::PROP_FATHER );
	}

	/**
	 * @return \Samwilson\SimpleWikidata\Properties\Item[]
	 */
	public function spouses() {
		return $this->getPropertyOfTypeItem( self::PROP_SPOUSE );
	}

	/**
	 * @return \Samwilson\SimpleWikidata\Properties\Item[]
	 */
	public function children() {
		return $this->getPropertyOfTypeItem( self::PROP_CHILD );
	}
}

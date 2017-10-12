<?php

namespace Samwilson\SimpleWikidata\Items;

use Samwilson\SimpleWikidata\Item;

class Human extends Item {

	const PROP_DATE_OF_BIRTH = 'P569';
	const PROP_FATHER = 'P22';

		public function getDatesOfBirth() {
		return $this->getPropertyOfTypeTime( self::PROP_DATE_OF_BIRTH );
	 }

	/**
	 * @return Item[]
	 */
	public function fathers() {
		return $this->getPropertyOfTypeItem( self::PROP_FATHER );
	}

}

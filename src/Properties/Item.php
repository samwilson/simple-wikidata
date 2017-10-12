<?php

namespace Samwilson\SimpleWikidata\Properties;

use Samwilson\SimpleWikidata\Property;

class Item extends Property {

	/**
	 * @return \Samwilson\SimpleWikidata\Item
	 */
	public function getItem() {
		$itemId = $this->claim['mainsnak']['datavalue']['value']['id'];
		return \Samwilson\SimpleWikidata\Item::factory( $itemId, $this->lang, $this->cache );
	}
}

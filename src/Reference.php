<?php

namespace Samwilson\SimpleWikidata;

use Psr\Cache\CacheItemPoolInterface;

class Reference {

	const STATED_IN = 'P248';

	/** @var array */
	protected $data;

	/** @var string */
	protected $lang;

	/** @var CacheItemPoolInterface */
	protected $cache;

	/**
	 * @param string[] $data The data.
	 * @param string $lang ISO639 language code.
	 * @param CacheItemPoolInterface $cache The cache.
	 */
	public function __construct( $data, $lang, $cache ) {
		$this->data = $data;
		$this->lang = $lang;
		$this->cache = $cache;
	}

	/**
	 * @return Item|bool The item, or false if there isn't one.
	 */
	public function statedIn() {
		if ( !isset( $this->data['snaks'][self::STATED_IN] ) ) {
			return false;
		}
		foreach ( $this->data['snaks'][self::STATED_IN] as $snak ) {
			return Item::factory( $snak['datavalue']['value']['id'], $this->lang, $this->cache );
		}
	}
}

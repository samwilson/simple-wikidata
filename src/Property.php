<?php

namespace Samwilson\SimpleWikidata;

use Psr\Cache\CacheItemPoolInterface;

abstract class Property {

		/** @var string[] */
	protected $claim;

		/** @var string */
	protected $lang;

		/** @var CacheItemPoolInterface */
	protected $cache;

		public function __construct( $claim, $lang, $cache ) {
		$this->claim = $claim;
		$this->lang = $lang;
		$this->cache = $cache;
	 }

	/**
	 * @return Reference[]
	 */
	public function getReferences() {
		$references = [];
		if ( !isset( $this->claim['references'] ) ) {
			return $references;
		}
		foreach ( $this->claim['references'] as $ref ) {
			$references[] = new Reference( $ref, $this->lang, $this->cache );
		}
		return $references;
	}
}

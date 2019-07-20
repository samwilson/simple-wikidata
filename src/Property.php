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

	/**
	 * @param string $claim The claim data array.
	 * @param string $lang The language code.
	 * @param CacheItemPoolInterface $cache
	 */
	public function __construct( array $claim, $lang, CacheItemPoolInterface $cache ) {
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

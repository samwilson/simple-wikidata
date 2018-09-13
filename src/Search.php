<?php

namespace Samwilson\SimpleWikidata;

use Mediawiki\Api\FluentRequest;
use Mediawiki\Api\MediawikiApi;
use Psr\Cache\CacheItemPoolInterface;

class Search {

	/** @var string */
	protected $lang;

	/** @var CacheItemPoolInterface */
	protected $cache;

	/**
	 * Search constructor.
	 * @param string $searchTerm What to search for.
	 * @param string $lang Language to use for the search results.
	 * @param CacheItemPoolInterface $cache The cache to use.
	 */
	public function __construct( $searchTerm, $lang, CacheItemPoolInterface $cache ) {
		$this->searchTerm = $searchTerm;
		$this->lang = $lang;
		$this->cache = $cache;
	}

	/**
	 * @param string $limit The number of search results to return.
	 * @return Item[]
	 */
	public function getItems( $limit = 'max' ) {
		$api = MediawikiApi::newFromApiEndpoint( 'https://www.wikidata.org/w/api.php' );
		$req = FluentRequest::factory()
			->setAction( 'wbsearchentities' )
			->addParams( [
				'search' => $this->searchTerm,
				'type' => 'item',
				'limit' => $limit,
				'language' => 'en',
			] );
		$results = [];
		$response = $api->getRequest( $req );
		foreach ( $response['search'] as $info ) {
			$item = Item::factory( $info['id'], $this->lang, $this->cache );
			$results[] = $item;
		}
		return $results;
	}
}

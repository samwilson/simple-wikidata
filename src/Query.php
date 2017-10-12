<?php

namespace Samwilson\SimpleWikidata;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use SimpleXmlElement;

class Query {

	/** @var string */
	protected $query;

	/** @var string */
	protected $lang;

	/** @var CacheItemPoolInterface */
	protected $cache;

	/**
	 * Query constructor.
	 * @param string $query The Sparql query.
	 * @param string $lang The language.
	 * @param CacheItemPoolInterface $cache The cache.
	 */
	public function __construct( $query, $lang, CacheItemPoolInterface $cache ) {
		$this->query = $query;
		$this->lang = $lang;
		$this->cache = $cache;
	}

	/**
	 * Get the items.
	 * @return Item[] The results.
	 */
	public function getItems() {
		$xml = $this->getXml( $this->query );
		$results = [];
		foreach ( $xml->results->result as $res ) {
			$result = $this->getBindings( $res );
			$id = substr( $result['item'], strrpos( $result['item'], '/' ) + 1 );
			$item = Item::factory( $id, $this->lang, $this->cache );
			$results[] = $item;
		}
		return $results;
	}

	/**
	 * @param string $query The Sparql query.
	 * @return SimpleXmlElement
	 * @throws Exception
	 */
	protected function getXml( $query ) {
		$url = "https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=" . urlencode( $query );
		try {
			$result = file_get_contents( $url );
		} catch ( Exception $e ) {
			throw new Exception( "Unable to run query: <pre>" . htmlspecialchars( $query ) . "</pre>", 500 );
		}
		if ( empty( $result ) ) {
			$msg = "No result from query: <pre>" . htmlspecialchars( $query ) . "</pre>";
			throw new Exception( $msg, 500 );
		}
		$xml = new SimpleXmlElement( $result );
		return $xml;
	}

	/**
	 * @param SimpleXmlElement $xml The query result XML.
	 * @return array
	 */
	protected function getBindings( $xml ) {
		$out = [];
		foreach ( $xml->binding as $binding ) {
			if ( isset( $binding->literal ) ) {
				$out[(string)$binding['name']] = (string)$binding->literal;
			}
			if ( isset( $binding->uri ) ) {
				$out[(string)$binding['name']] = (string)$binding->uri;
			}
		}
		return $out;
	}

}

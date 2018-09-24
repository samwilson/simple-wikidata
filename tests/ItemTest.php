<?php

namespace Samwilson\SimpleWikidata\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Samwilson\SimpleWikidata\Item;
use Samwilson\SimpleWikidata\Tests\Fixture\ItemWithNoInstanceOf;
use Stash\Driver\BlackHole;
use Stash\Pool;

/**
 * @group integration
 */
class ItemTest extends TestCase {

	/** @var CacheItemPoolInterface */
	protected $cache;

	public function setUp() {
		parent::setUp();
		$this->cache = new Pool( new BlackHole() );
	}

	/**
	 * @covers \Samwilson\SimpleWikidata\Item::register()
	 */
	public function testRegisterSelf() {
		$msg = 'Samwilson\SimpleWikidata\Item::register should only be called on subclasses of Item';
		static::expectExceptionMessage( $msg );
		Item::register();
	}

	/**
	 * @covers \Samwilson\SimpleWikidata\Item::register()
	 */
	public function testRegisterIncorrectlyConfiguredClass() {
		static::expectExceptionMessage( 'Please set INSTANCE_OF for Samwilson\SimpleWikidata\Tests\Fixture\ItemWithNoInstanceOf' );
		ItemWithNoInstanceOf::register();
	}

	/**
	 * @covers \Samwilson\SimpleWikidata\Item::getId()
	 * @covers \Samwilson\SimpleWikidata\Item::getLabel()
	 * @covers \Samwilson\SimpleWikidata\Item::getWikidataUrl()
	 */
	public function testIdAndLabel() {
		$item = Item::factory( 'Q1', 'pt', $this->cache );
		$this->assertEquals( 'Q1', $item->getId() );
		$this->assertEquals( 'https://www.wikidata.org/wiki/Q1', $item->getWikidataUrl() );
		$this->assertEquals( 'universo', $item->getLabel() );
	}
}

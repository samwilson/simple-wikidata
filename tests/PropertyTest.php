<?php

namespace Samwilson\SimpleWikidata\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Samwilson\SimpleWikidata\Properties\Time;
use Stash\Driver\BlackHole;
use Stash\Pool;

/**
 * @group integration
 */
class PropertyTest extends TestCase {

	/** @var CacheItemPoolInterface */
	protected $cache;

	protected function setUp(): void {
		parent::setUp();
		$this->cache = new Pool( new BlackHole() );
	}

	/**
	 * @covers \Samwilson\SimpleWikidata\Properties\Time
	 */
	public function testIdAndLabel() {
		$prop = new Time( [
			'mainsnak' => [
				'datavalue' => [
					'value' => [
						'time' => '+2017-07-12T12:34Z',
					]
				]
			]
		], 'en', $this->cache );
		$this->assertEquals( '2017', $prop->getDateTime()->format( 'Y' ) );
		$this->assertEmpty( $prop->getReferences() );
	}

}

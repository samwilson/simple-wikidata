<?php

namespace Samwilson\SimpleWikidata\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Samwilson\SimpleWikidata\Item;
use Stash\Driver\BlackHole;
use Stash\Pool;

/**
 * @group integration
 */
class ItemTest extends TestCase {

    /** @var CacheItemPoolInterface */
    protected $cache;

    public function setUp()
    {
        parent::setUp();
        $this->cache = new Pool(new BlackHole());
    }

    public function testIdAndLabel()
    {
        $item = Item::factory('Q1', 'pt', $this->cache);
        $this->assertEquals('Q1', $item->getId());
        $this->assertEquals('https://www.wikidata.org/wiki/Q1', $item->getWikidataUrl());
        $this->assertEquals('universo', $item->getLabel());
    }
}

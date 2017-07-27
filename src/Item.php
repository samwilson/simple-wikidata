<?php

namespace Samwilson\SimpleWikidata;

use DateInterval;
use Exception;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Psr\Cache\CacheItemPoolInterface;
use Samwilson\SimpleWikidata\Properties\Time;
use Symfony\Component\DomCrawler\Crawler;

class Item
{

    const PROP_INSTANCE_OF = 'P31';
    const PROP_TITLE = 'P1476';
    const PROP_IMAGE = 'P18';
    const PROP_AUTHOR = 'P50';

    /** @var string */
    protected $id;

    /** @var MediawikiApi */
    protected $wdApi;

    /** @var string */
    protected $lang;

    /** @var CacheItemPoolInterface */
    protected $cache;

    /** @var string The base URL of Wikidata, with trailing slash. */
    protected $wikidataUrlBase = 'https://www.wikidata.org/wiki/';
    
    private function __construct($id, $lang, CacheItemPoolInterface $cache)
    {
        if (!is_string($id) || preg_match('/[QP][0-9]*/i', $id) !== 1) {
            throw new Exception("Not a valid ID: " . var_export($id, true));
        }
        $this->id = $id;
        $this->wdApi = new MediawikiApi('https://www.wikidata.org/w/api.php');
        $this->entities = [];
        $this->lang = $lang;
        $this->cache = $cache;
    }

    /**
     * Create a new Item object with class based on the item's 'instance of' statement.
     *
     * @param string $id
     * @param string $lang
     *
     * @return Item
     */
    public static function factory($id, $lang, CacheItemPoolInterface $cache)
    {
        $item = new Item($id, $lang, $cache);
        foreach ($item->getPropertyOfTypeItem($id, self::PROP_INSTANCE_OF) as $instanceOf) {
            // Try to find a class mating the 'instance of' name.
            $possibleClassName = __NAMESPACE__ . '\\InstancesOf\\' . ucfirst($instanceOf->getLabel());
            if (class_exists($possibleClassName)) {
                // This won't re-request the metadata, because that's cached.
                $specificItem = new $possibleClassName($id, $lang, $cache);
                return $specificItem;
            }
        }

        // If we're here, just leave it as a basic Item.
        $item->setCache($cache);
        return $item;
    }

    /**
     * Create a new Item (with no label or description or anything; just an ID).
     * @param string $lang
     * @param CacheItemPoolInterface $cache
     *
     * @return Item
     */
    public static function create($lang, CacheItemPoolInterface $cache)
    {
        $wdWpOauth = new WdWpOauth();
        $params = [
            'action' => 'wbeditentity',
            'new' => 'item',
            'data' => '{}',
        ];
        //$wdWpOauth->getOauthClient()->setExtraParams($params);
        $newItem = $wdWpOauth->makeCall($params, true);
        return self::factory($newItem->entity->id, $lang, $cache);
    }

    public function setCache(CacheItemPoolInterface $cache_item_pool)
    {
        $this->cache = $cache_item_pool;
    }

    /**
     * Get the ID (Q-number) of this item.
     * @return string|bool The ID or false if it couldn't be determined.
     */
    public function getId()
    {
        $entity = $this->getEntity($this->id);
        return isset($entity['id']) ? $entity['id'] : false;
    }

    /**
     * Get this item's label.
     * @return string
     */
    public function getLabel()
    {
        $entity = $this->getEntity( $this->id );
        if ( ! empty( $entity['labels'][ $this->lang ]['value'] ) ) {
            // Use the label if there is one.
            return $entity['labels'][ $this->lang ]['value'];
        }
        // Or just use the ID.
        return $entity['id'];
    }

    public function getWikidataUrl()
    {
        return $this->wikidataUrlBase.$this->id;
    }

    public function getStandardProperties($type = 'work')
    {
        if ($type !== 'work') {
            $type = 'edition';
        }
        $cacheKey = $type . '_item_property_IDs';
        if ($this->cache->hasItem($cacheKey)) {
            $propIds = $this->cache->getItem($cacheKey)->get();
        } else {
            $domCrawler = new Crawler();
            $domCrawler->addHtmlContent(file_get_contents('https://www.wikidata.org/wiki/Wikidata:WikiProject_Books'));
            $propCells = $domCrawler->filterXPath("//h3/span[@id='" . ucfirst($type) . "_item_properties']/../following-sibling::table[1]//td[2]/a");
            $propIds = [];
            $propCells->each(function (Crawler $node, $i) use (&$propIds) {
                $propId = $node->text();
                $propIds[] = $propId;
            });
            $cacheItem = $this->cache->getItem($cacheKey)
                ->expiresAfter(new DateInterval('PT1H'))
                ->set($propIds);
            $this->cache->save($cacheItem);
        }
        $workProperties = [];
        foreach ($propIds as $propId) {
            $workProperties[] = Item::factory($propId, $this->lang, $this->cache);
        }

        return $workProperties;
    }

    /**
     * @param $propertyId
     * @param string $dateFormat
     * @return bool|Time[]
     */
    protected function getPropertyOfTypeTime($propertyId)
    {
        $times = [];
        $entity = $this->getEntity();
        if (!isset($entity['claims'][$propertyId])) {
            return $times;
        }
        foreach ($entity['claims'][$propertyId] as $claims) {
            foreach ($claims as $claim) {
                //var_dump($claim);exit();
                $times[] = new Time($claim['datavalue']['value']);
//                
//                $timeValue = $claim['datavalue']['value']['time'];
//                // Ugly workaround for imprecise dates. :-(
//                if (preg_match('/([0-9]{1,4})-00-00/', $timeValue, $matches) === 1) {
//                    $timeValue = $matches[1];
//                    return $timeValue;
//                }
//                $time = strtotime($timeValue);
//                return date($dateFormat, $time);
            }
        }
        return $times;
    }

    /**
     * Get the Item that is referred to by the specified item's property.
     *
     * @param string $itemId
     * @param string $propertyId
     *
     * @return Item[]
     */
    protected function getPropertyOfTypeItem($itemId, $propertyId)
    {
        $entity = $this->getEntity($itemId);
        if (!isset($entity['claims'][$propertyId])) {
            return [];
        }
        $items = [];
        foreach ($entity['claims'][$propertyId] as $claim) {
            $items[] = Item::factory($claim['mainsnak']['datavalue']['value']['id'], $this->lang, $this->cache);
        }

        return $items;
    }

    public function setPropertyOfTypeItem($property, $itemId)
    {
        $itemIdNumeric = substr($itemId, 1);

        // First see if this property already exists, and that it is different from what's being set.
        $entity = $this->getEntity($this->id);
        if (!empty($entity['claims'][$property])) {
            // Get the first claim, and update it if necessary.
            $claim = array_shift($entity['claims'][$property]);
            if ($claim['mainsnak']['datavalue']['value']['id'] == $itemId) {
                // Already is the required value, no need to change.
                return;
            }
            $claim['mainsnak']['datavalue']['value']['id'] = $itemId;
            $claim['mainsnak']['datavalue']['value']['numeric-id'] = $itemIdNumeric;
            $apiParams = [
                'action' => 'wbsetclaim',
                'claim' => wp_json_encode($claim),
            ];
        }

        // If no claim was found (and modified) above, create a new claim.
        if (!isset($apiParams)) {
            $apiParams = [
                'action' => 'wbcreateclaim',
                'entity' => $this->getId(),
                'property' => $property,
                'snaktype' => 'value',
                'value' => wp_json_encode(['entity-type' => 'item', 'numeric-id' => $itemIdNumeric]),
            ];
        }

        // Save the property.
        $wdWpOauth = new WdWpOauth();
        $wdWpOauth->makeCall($apiParams, true);

        // Clear the cache.
        $this->cache->deleteItem($this->getEntityCacheKey($this->id));

    }

    public function getPropertyOfTypeUrl($entityId, $propertyId)
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return false;
        }
        $urls = [];
        foreach ($entity['claims'][$propertyId] as $claim) {
            $urls[] = $claim['mainsnak']['datavalue']['value'];
        }

        return $urls;
    }

    public function getPropertyOfTypeExternalIdentifier($entityId, $propertyId)
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return false;
        }
        $idents = [];
        foreach ($entity['claims'][$propertyId] as $claim) {
            $qualifiers = [];
            if (!isset($claim['qualifiers'])) {
                continue;
            }
            foreach ($claim['qualifiers'] as $qualsInfo) {
                foreach ($qualsInfo as $qualInfo) {
                    $qualProp = Item::factory($qualInfo['property'], $this->lang, $this->cache);
                    $propLabel = $qualProp->getLabel();
                    if (!isset($qualifiers[$propLabel])) {
                        $qualifiers[$propLabel] = [];
                    }
                    $qualifiers[$propLabel][] = $qualInfo['datavalue']['value'];
                }
            }
            $idents[] = [
                'qualifiers' => $qualifiers,
                'value' => $claim['mainsnak']['datavalue']['value'],
            ];
        }

        return $idents;
    }

    /**
     * Get a single-valued text property.
     * @param string $property One of the PROP_* constants.
     * @return string|boolean The value, or false if it can't be found.
     */
    public function getPropertyOfTypeText($property)
    {
        $entity = $this->getEntity($this->id);
        if (isset($entity['claims'][$property])) {
            // Use the first title.
            foreach ($entity['claims'][$property] as $t) {
                if (!isset($t['mainsnak']['datavalue']['value']['language'])) {
                    var_dump($t['mainsnak']['datavalue']['value']);
                    exit();
                }
                if ($t['mainsnak']['datavalue']['value']['language'] == $this->lang
                    && !empty($t['mainsnak']['datavalue']['value']['text'])
                ) {
                    return $t['mainsnak']['datavalue']['value']['text'];
                }
            }
        }
        return false;
    }

    /**
     * Literal data field for a quantity that relates to some kind of well-defined unit. The actual unit goes in the data values that is entered.
     *   - amount – implicit part of the string (mapping of unit prefix is unclear)
     *   - unit – implicit part of the string that defaults to "1" (mapping to standardizing body is unclear)
     *   - upperbound - quantity's upper bound
     *   - lowerbound - quantity's lower bound
     * @param $property
     * @return mixed[]|bool If it's not false it's an array with 'amount', 'unit', etc.
     */
    public function getPropertyOfTypeQuantity($property)
    {
        $quantities = [];
        $entity = $this->getEntity($this->id);
        if (!isset($entity['claims'][$property])) {
            return false;
        }
        foreach ($entity['claims'][$property] as $t) {
            $quantity = $t['mainsnak']['datavalue']['value'];
            $unitId = substr($quantity['unit'], strlen($this->wikidataUrlBase)+1);
            $quantity['unit'] = Item::factory($unitId, $this->lang, $this->cache);
            $quantities[] = $quantity;
        }
        return $quantities;
    }

    /**
     * Set a single-valued text property.
     * @param string $property One of the PROP_* constants.
     * @param string $value The value.
     */
    public function setPropertyOfTypeText($property, $value)
    {
        // First see if this property already exists, and that it is different from what's being set.
        $entity = $this->getEntity($this->id);
        if (!empty($entity['claims'][$property])) {
            // Find this language's claim (if there is one).
            foreach ($entity['claims'][$property] as $claim) {
                if ($claim['mainsnak']['datavalue']['value']['language'] == $this->lang) {
                    // Modify this claim's text value.
                    $titleClaim = $claim;
                    $titleClaim['mainsnak']['datavalue']['value']['text'] = $value;
                    $setTitleParams = [
                        'action' => 'wbsetclaim',
                        'claim' => \GuzzleHttp\json_encode($titleClaim),
                    ];
                    continue;
                }
            }
        }

        // If no claim was found (and modified) above, create a new claim.
        if (!isset($setTitleParams)) {
            $setTitleParams = [
                'action' => 'wbcreateclaim',
                'entity' => $this->getId(),
                'property' => $property,
                'snaktype' => 'value',
                'value' => \GuzzleHttp\json_encode(['text' => $value, 'language' => $this->lang]),
            ];
        }

        // Save the property.
        $wdWpOauth = new WdWpOauth();
        $wdWpOauth->makeCall($setTitleParams, true);

        // Clear the cache.
        $this->cache->deleteItem($this->getEntityCacheKey($this->id));
    }

    public function getInstanceOf()
    {
        $instancesOf = $this->getPropertyOfTypeItem($this->getId(), self::PROP_INSTANCE_OF);
        return array_shift($instancesOf);
    }

    /**
     * Does this item exist?
     * @return bool
     */
    public function exists()
    {
        return $this->getId() !== false;
    }

    public function getWikipediaIntro()
    {
        $cacheKey = 'wikipedia-intro-' . $this->id . $this->lang;
        if ($this->cache->hasItem($cacheKey)) {
            return $this->cache->getItem($cacheKey)->get();
        }
        $entity = $this->getEntity($this->id);
        if (!isset($entity['sitelinks'])) {
            return [];
        }
        foreach ($entity['sitelinks'] as $sitelink) {
            if ($sitelink['site'] == $this->lang . 'wiki') {
                $api = new MediawikiApi('https://' . $this->lang . '.wikipedia.org/w/api.php');
                $req = new SimpleRequest('query', [
                    'prop' => 'extracts',
                    'exintro' => true,
                    'titles' => $sitelink['title'],
                ]);
                $response = $api->getRequest($req);
                $page = array_shift($response['query']['pages']);
                $out = [
                    'title' => $page['title'],
                    'html' => $page['extract'],
                ];
                $cacheItem = $this->cache->getItem($cacheKey)
                    ->expiresAfter(new DateInterval('P1D'))
                    ->set($out);
                $this->cache->save($cacheItem);

                return $out;
            }
        }

        return [];
    }

    /**
     * Get the raw entity data from the 'wbgetentities' API call.
     * @param string $id
     * @param bool $ignoreCache
     * @return bool
     */
    public function getEntity($id = null, $ignoreCache = false)
    {
        $idActual = $id ?: $this->id;
        $cacheKey = $this->getEntityCacheKey($idActual);
        if (!$ignoreCache && $this->cache->hasItem($cacheKey)) {
            return $this->cache->getItem($cacheKey)->get();
        }
        $metadataRequest = new SimpleRequest('wbgetentities', ['ids' => $idActual]);
        $itemResult = $this->wdApi->getRequest($metadataRequest);
        if (!isset($itemResult['success']) || !isset($itemResult['entities'][$id])) {
            return false;
        }
        $metadata = $itemResult['entities'][$idActual];
        $cacheItem = $this->cache->getItem($cacheKey)
            ->expiresAfter(new DateInterval('PT10M'))
            ->set($metadata);
        $this->cache->save($cacheItem);
        return $metadata;
    }

    /**
     * @param $id
     *
     * @return string
     */
    protected function getEntityCacheKey($id)
    {
        return 'entities' . $id;
    }
}

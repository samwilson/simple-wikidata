Simple Wikidata
===============

This is a simple (and limited, by design) to interact with Wikidata from PHP.

## Querying

```php
// The Sparql must return an ?item column.
$sparql = "SELECT ?item WHERE { ?item wdt:P31 wd:Q54050 } LIMIT 5";
$people = new \Samwilson\SimpleWikidata\Query($sparql, 'en', $cache);
foreach ($people->getItems() as $person) {
    // Each is an Item.
    $person->getLabel();
    $person->getPropertyOfTypeItem();
    $person->getPropertyOfTypeQuantity();
    $person->getPropertyOfTypeIdentifier(); // External identifier
    $person->getPropertyOfTypeUrl();
    $person->getPropertyOfTypeTime();
    $person->getPropertyOfTypeString(); // No language
    $person->getPropertyOfTypeText(); // Has language
    $person->getPropertyOfTypeCoord();
    $person->getPropertyOfTypeFile(); // Commons media
}
```


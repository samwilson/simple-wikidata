Simple Wikidata
===============

This is a simple (and limited, by design) package for interacting with Wikidata from PHP.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/samwilson/simple-wikidata/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/samwilson/simple-wikidata/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/samwilson/simple-wikidata/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/samwilson/simple-wikidata/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/samwilson/simple-wikidata/badges/build.png?b=master)](https://scrutinizer-ci.com/g/samwilson/simple-wikidata/build-status/master)

## Example

```php
// The Sparql must return an ?item column.
$sparql = "SELECT ?item WHERE { ?item wdt:P31 wd:Q54050 } LIMIT 5";
$cache = new \Stash\Pool(new \Stash\Driver\FileSystem());
$people = new \Samwilson\SimpleWikidata\Query($sparql, 'en', $cache);
foreach ($people->getItems() as $person) {
    // Each $person is an Item object.
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

## Licence

GPL3.0+

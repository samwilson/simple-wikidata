<?php

namespace Samwilson\SimpleWikidata\InstancesOf;

use Samwilson\SimpleWikidata\Item;

class Human extends Item {

    const PROP_DATE_OF_BIRTH = 'P569';

    public function getDatesOfBirth()
    {
        return $this->getPropertyOfTypeTime(self::PROP_DATE_OF_BIRTH);
    }

}
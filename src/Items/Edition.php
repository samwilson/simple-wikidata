<?php

namespace Samwilson\SimpleWikidata\Items;

use Samwilson\SimpleWikidata\Item;

class Edition extends Item {

	const PROP_EDITION_OR_TRANSLATION_OF = 'P629';
	const PROP_WIKISOURCE_INDEX_PAGE = 'P1957';
	const PROP_SCANNED_FILE_ON_COMMONS = 'P996';
	const PROP_INTERNET_ARCHIVE_ID = 'P724';
	const PROP_PUBLICATION_DATE = 'P577';
	const PROP_PUBLISHER = 'P123';

	/**
	 * @return string
	 */
	public function getPublicationYear() {
		$publicationYears = $this->getPropertyOfTypeTime( static::PROP_PUBLICATION_DATE );
		return $publicationYears[0]->getDateTime()->format( 'Y' );
	}

	/**
	 * @return \Samwilson\SimpleWikidata\Properties\Item[]
	 */
	public function getPublishers() {
		return $this->getPropertyOfTypeItem( static::PROP_PUBLISHER );
	}

	/**
	 * @return array|bool
	 */
	public function getWikisourceIndexPages() {
		return $this->getPropertyOfTypeUrl( $this->getId(), static::PROP_WIKISOURCE_INDEX_PAGE );
	}

	/**
	 * @return array
	 */
	public function internetArchiveIds() {
		return $this->getPropertyOfTypeExternalIdentifier(
			$this->getId(),
			self::PROP_INTERNET_ARCHIVE_ID
		);
	}

	/**
	 * Get information about the Wikisource sitelink.
	 * An edition should only ever be present on one Wikisource.
	 * @return string[]
	 */
	public function getWikisourceLink() {
		$entity = $this->getEntity( $this->id );
		if ( !isset( $entity['sitelinks'] ) ) {
			return [];
		}
		foreach ( $entity['sitelinks'] as $sitelink ) {
			if ( strpos( $sitelink['site'], 'wikisource' ) !== false ) {
				$lang = substr( $sitelink['site'], 0, strpos( $sitelink['site'], 'wikisource' ) );
				return [
					'title' => $sitelink['title'],
					'url' => "https://$lang.wikisource.org/wiki/".$sitelink['title'],
					'lang' => $lang,
				];
			}
		}
		return [];
	}

}

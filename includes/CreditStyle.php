<?php

namespace MediaWiki\Extension\EditCredit;

use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\WikiModule;

class CreditStyle extends WikiModule {
	protected function getPages( Context $context ) {
		$pages = [];
		$pages['MediaWiki:EditCredit.css'] = [ 'type' => 'style' ];
		return $pages;
	}

	public function getType() {
		return self::LOAD_STYLES;
	}

	public function getGroup() {
		return 'ext.editcredit';
	}
}

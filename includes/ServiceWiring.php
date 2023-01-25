<?php

use MediaWiki\Extension\EditCredit\EditCreditQuery;
use MediaWiki\MediaWikiServices;

return [
	'EditCredit.EditCreditQuery' => static function ( MediaWikiServices $services ): EditCreditQuery {
		return new EditCreditQuery(
			$services->getActorNormalization(),
			$services->getDBLoadBalancer(),
			$services->getConfigFactory(),
			$services->getHookContainer()
		);
	}
];

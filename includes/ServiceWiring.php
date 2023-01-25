<?php

use MediaWiki\Extension\EditCredit\EditCreditQuery;
use MediaWiki\MediaWikiServices;

return [
	'EditCredit.Query' => static function ( MediaWikiServices $services ): EditCreditQuery {
		return new EditCreditQuery(
			$services->getService('EditCountNeue.EditCountQuery'),
			$services->getDBLoadBalancer(),
			$services->getConfigFactory(),
			$services->getHookContainer()
		);
	}
];

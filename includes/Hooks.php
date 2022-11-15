<?php

namespace MediaWiki\Extension\EditCredit;

use MediaWiki\Extension\EditCredit\Tags;
use MediaWiki\Hook\ParserFirstCallInitHook;

class Hooks
{
    public static function onParserFirstCallInit($parser)
    {
        $parser->setHook('edit-credit', [Tags::class, 'renderTagEditCredit']);
    }
}

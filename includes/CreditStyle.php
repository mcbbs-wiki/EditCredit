<?php

namespace MediaWiki\Extension\EditCredit;

use ResourceLoaderContext;
use ResourceLoaderWikiModule;

class CreditStyle extends ResourceLoaderWikiModule
{
    protected function getPages(ResourceLoaderContext $context)
    {
        $pages = [];
        $pages['MediaWiki:EditCredit.css'] = ['type' => 'style'];
        return $pages;
    }
    public function getType()
    {
        return self::LOAD_STYLES;
    }
    public function getGroup()
    {
        return 'ext.editcredit';
    }
}

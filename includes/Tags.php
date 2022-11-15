<?php

namespace MediaWiki\Extension\EditCredit;

use Parser;
use PPFrame;
use Html;
use MediaWiki\MediaWikiServices;

class Tags
{
    public static function renderTagEditCredit($input, array $args, Parser $parser, PPFrame $frame)
    {
        $parser->getOutput()->addModuleStyles('ext.editcredit.styles');
        if (isset($args['username'])) {
            $userFactory = MediaWikiServices::getInstance()->getUserFactory();
            $username = trim($args['username']);
            $user = $userFactory->newFromName($username);
            $credit = CreditCalc::calcCredit($user);
            $level = CreditCalc::calcLevel($credit);
            $type = $args['type'] ?? 'level';
            $class = 'user-score-credit ' . $level['class'];
            $display = $credit;
            if ($type === 'level') {
                $class = 'user-score-level ' . $level['class'];
                $display = $level['level'];
            } 
            return Html::element('span', ['class' => $class], $display);
        } else {
            return '';
        }
    }
}

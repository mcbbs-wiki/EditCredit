<?php

namespace MediaWiki\Extension\EditCredit;

use MediaWiki\Extension\EditCount\EditCountQuery;
use MediaWiki\MediaWikiServices;
use User;

class CreditCalc
{
    public static function calcCredit(User $user)
    {
        $credit = 0;
        $ns = EditCountQuery::queryAllNamespaces($user);
        $hooks = MediaWikiServices::getInstance()->getHookContainer();
        @$hooks->run("CalcUserCredit", [$ns, &$credit]);
        return $credit;
    }
    public static function calcLevel(int $credit)
    {
        $config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'EditCredit' );
        $scores=$config->get( 'CreditLevels' );
        $levelCSS=$config->get( 'CreditCSSClass' );
        $lvl = 0;
        foreach ($scores as $index => $score) {
            if ($credit < $score) {
                $lvl = $index;
                break;
            }
        }
        $class = '';
        foreach ($levelCSS as $least => $level) {
            if ($credit < $least) {
                $class = $level;
                break;
            }
        }
        return ['level' => $lvl, 'class' => $class];
    }
}

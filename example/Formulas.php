<?php

// Formula examples
class EditCreditFormulas {
    public static function saltEditCredit($ns,&$credit){
        $credit=floor($ns[0]*3+
        $ns[10]*2.5+
        $ns[12]*2+
        $ns[4]+$ns[14]+
        $ns[6]/4+
        ($ns[1]+$ns[5]+$ns[11])/8);
    }
}
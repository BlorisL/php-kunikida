<?php
namespace Kunikida;

class Translate extends \Kunikida\AService {

    public function __construct() {
        parent::__construct('translate');
    }

    public function request(string $method, \stdClass $values, $next = false) {
        return parent::request($method, $values, $next);
        /*$tmp = null;
        if($result instanceof stdClass):
            switch($this->service):
                case 'libretranslate.de': case 'lt.vern.cc':
                    break;
            endswitch;
        endif;
        return $tmp;*/
    }
}
?>
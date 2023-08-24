<?php
namespace Kunikida;

include_once "AService.php";

class Geo extends \Kunikida\AService {
    private string $language;
    private \Kunikida\Translate $translate;
    
    public function __construct(string $language) { 
        parent::__construct('geo', ['daily']);
        $this->translate = new \Kunikida\Translate();
        $this->language = $language;
    }

    public function request(string $method, \stdClass $values, bool $next = false) {
        $tmp = array();
        switch($this->getServiceName()):
            case 'locationiq.com': $values->{"accept-language"} = $this->language; break;
        endswitch;
        $result = parent::request($method, $values, $next);
        if(!empty($result)):
            switch($this->getServiceName()):
                case 'geocodify.com': 
                    if($result->meta->code == 200 && count($result->response->features) > 0):
                        $items = [];
                        $text = '';
                        foreach($result->response->features as $item):
                            $obj = new \stdClass();
                            $region = $this->valid($item->properties, 'macroregion') ? $item->properties->macroregion : $item->properties->region;
                            $obj->label = "{$item->properties->name}, {$region}, {$item->properties->country}";//$this->getTranslate($item->properties->label);
                            $obj->lat = $item->geometry->coordinates[1];
                            $obj->lon = $item->geometry->coordinates[0];
                            $text .= $obj->label . "\n";
                            $items[] = clone $obj;
                        endforeach;
                        $text = explode("\n", $this->getTranslate($text));
                        foreach($items as $key => &$item):
                            $item->label = $text[$key];
                        endforeach;
                        $tmp = $items;
                    endif;
                    break;
                case 'locationiq.com': 
                    if(is_array($result) && count($result) > 0):
                        $items = [];
                        foreach($result as $item):
                            $obj = new \stdClass();
                            $obj->lat = $item->lat;
                            $obj->lon = $item->lon;
                            $obj->label = $item->display_name;
                            $items[] = clone $obj;
                        endforeach;
                        $tmp = $items;
                    endif;
                    break;
                case 'geokeo.com': 
                    if($result->status == 'ok' && count($result->results) > 0):
                        $items = [];
                        $text = '';
                        foreach($result->results as $item):
                            $obj = new \stdClass();
                            $obj->label = "{$item->address_components->name}, {$item->address_components->country}";
                            $obj->lat = $item->geometry->location->lat;
                            $obj->lon = $item->geometry->location->lng;
                            $text .= $obj->label . "\n";
                            $items[] = clone $obj;
                        endforeach;
                        $text = explode("\n", $this->getTranslate($text));
                        foreach($items as $key => &$item):
                            $item->label = $text[$key];
                        endforeach;
                        $tmp = $items;
                    endif;
                    break;
            endswitch;
        endif;
        return $tmp;
    }

    // Vincenty great circle distance
    public function getDistance(float $fromLat, float $fromLon, float $toLat, float $toLon, $earthRadius = 6371000) {
        // convert from degrees to radians
        $latFrom = deg2rad($fromLat);
        $lonFrom = deg2rad($fromLon);
        $latTo = deg2rad($toLat);
        $lonTo = deg2rad($toLon);
        
        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
        
        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

    private function getTranslate(string $phrase, string $langFrom = 'en') {
        $params = new \stdClass();
        $params->q = $phrase;
        $params->source = $langFrom;
        $params->target = $this->language;
        $res = $this->translate->request('translate', $params);
        if(!empty($res) && $this->valid($res, 'translatedText')):
            $phrase = $res->translatedText;
        endif;
        return $phrase;
    }
}
?>
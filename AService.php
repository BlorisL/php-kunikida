<?php
namespace Kunikida;

abstract class AService {
    protected string $name;
    protected array $rModes;
    protected array $rServices;
    protected \stdClass $service;
    protected \stdClass $services;

    protected function __construct(string $type, array $modes = [], array $services = []) {
        $tmp = file_get_contents("options.json");
        if(!empty($tmp)):
            $tmp = json_decode($tmp);
            if($this->valid($tmp, 'services') && $this->valid($tmp->services, $type)):
                $this->services = $tmp->services->{$type};
                $this->randMode($modes)->randService($services);
            endif;
        endif;
    }

    private function randMode(array $modes = []): AService {
        $rModes = array_keys(get_object_vars($this->services));
        shuffle($rModes);
        foreach($modes as $key => $mode):
            $tmpMode = array_search($mode, $rModes);
            if($tmpMode !== false):
                array_splice($rModes, $tmpMode, 1);
            else:
                unset($modes[$key]);
            endif;
        endforeach;
        $this->rModes = array_merge($modes, $rModes);
        return $this;
    }

    private function randService(array $services = [], bool $next = false): AService {
        $rServices = array_keys(get_object_vars($this->getMode($next)));
        shuffle($rServices);
        foreach($services as $key => $service):
            $tmpService = array_search($service, $rServices);
            if($tmpService !== false):
                array_splice($rServices, $tmpService, 1);
            else:
                unset($services[$key]);
            endif;
        endforeach;
        $this->rServices = array_merge($services, $rServices);
        return $this;
    }

    private function getMode(bool $next = false): \stdClass {
        if(count($this->rModes) > 0):
            $mode = $this->rModes[0];
            if(!$next && $this->valid($this->services, $mode)):
                $tmp = $this->services->{$mode};
            elseif($next && count($this->rModes) > 0):
                array_splice($this->rModes, 0, 1);
                $tmp = $this->getMode();
                $this->randService();
            else:
                $tmp = new \stdClass();
                $this->rServices = [];
            endif;
        else:
            $tmp = new \stdClass();
            $this->rServices = [];
        endif;
        return $tmp;
    }

    private function getService(bool $next = false, bool $nextMode = false): \stdClass {
        if(count($this->rServices) > 0):
            $mode = $this->getMode($nextMode);
        else:
            $mode = $this->getMode(true);
        endif;
        if(count($this->rServices) > 0):
            $service = $this->rServices[0];
            if(!$next && $this->valid($mode, $service)):
                $tmp = $mode->{$service};
            elseif($next && count($this->rServices) > 0):
                array_splice($this->rServices, 0, 1);
                $tmp = $this->getService();
            else:
                $tmp = $this->getService(false, true);
            endif;
        else:
            $tmp = false;
        endif;
        return $tmp;
    }

    protected function getServiceName(bool $next = false): string { 
        $tmp = '';
        $service = $this->getService($next);
        if($service):
            if($this->valid($service, 'name')):
                $tmp = $service->name;
            else:
                $tmp = $this->getServiceName(true);
            endif;
        endif;
        return $tmp;
    }

    public function request(string $name, \stdClass $values, bool $next = false) {
        $tmp = null;
        $service = $this->getService($next);
        if($service && $this->valid($service, 'url') && $this->valid($service->endpoints, $name) && $this->valid($service->endpoints->{$name}, 'name')):
            $method = $service->endpoints->{$name};
            $context  = array('http' => array('timeout' => 5));
            switch($service->http->method):
                case 'GET':
                    $api = "{$service->url}{$method->name}?" . http_build_query($this->getEndpoint($name, $values));
                    break;
                case 'POST':
                    $api = "{$service->url}{$method->name}";
                    $context['http']['header'] = $service->http->headers;
                    $context['http']['method'] = 'POST';
                    $context['http']['content'] = http_build_query($this->getEndpoint($name, $values));
                    break;
            endswitch;
            $tmp = @file_get_contents($api, false, stream_context_create($context));
            if(!empty($tmp)):
                $tmp = json_decode($tmp);
            endif;
            if(empty($tmp)):
                $tmp = \Kunikida\AService::request($name, $values, true);
            endif;
        endif;
        return $tmp;
    }
    
    protected function getEndpoint(string $method, \stdClass $values): array {
        $tmp = array();
        $service = $this->getService();
        if($service):
            if($this->valid($service->endpoints, $method)):
                $method = $service->endpoints->{$method};
                if($this->valid($method, 'params')):
                    $tmp = array();
                    if($this->valid($service, 'key') && $this->valid($service->key, 'name') && $this->valid($service->key, 'value')):
                        $tmp[$service->key->name] = $service->key->value;
                    endif;
                    foreach($method->params as $label => $item):
                        if(isset($values->{$label})):
                            $value = $values->{$label};
                        elseif(!is_null($item)):
                            $value = $item;
                        else:
                            continue;
                        endif;
                        $tmp[$label] = $value;
                    endforeach;
                endif;
            endif;
        endif;
        return $tmp;
    }

    protected function valid($item, string $property, bool $flag = true) {
        return isset($item->{$property}) && ($flag ? !empty($item->{$property}): true);
    }
}
?>
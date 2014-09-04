<?php
    
use Vibius\Facade\Facade;
use Vibius\Facade\Interfaces\FacadeInterface;

class {$className} extends Facade implements FacadeInterface{
    
    public static function getFacadeIdentifier(){
        return new {$providerName};
    }

}

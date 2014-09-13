<?php

namespace Vibius\Facade;

use \Vibius\Container\Container as Container;
use Exception;

class Facade{

    /**
     * Method used to handle static calls to aliasies.
     */
    public static function __callStatic($method, $parameters){
        $container = Container::open('aliases');

        $class = get_called_class();
        $class = strtolower($class);
        if( !$container->exists($class)){
            $newClass = $class::getFacadeIdentifier();
            $container->add($class, $newClass); 
        }

        
        if( $container->exists($class) ){
            $instance = $container->get($class);
        }

        if( !method_exists($instance, $method) ){
            throw new Exception(" Method ($method) of class ($class) does not exist");
        }
        return call_user_func_array(array($instance, $method), $parameters);
    }


}

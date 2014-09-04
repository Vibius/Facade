<?php

namespace Vibius\Facade;
use Exception;
use Vibius\Container\Container as Container;

/**
 * @author Matej Sima <matej.sima@gmail.com>
 * @category Vibius PHP Framework Component
 * @package Vibius\Facade
 */
class AliasManager{

    /**
     * @var string Path to the folder where aliases are stored(cached).
     */
    private $aliasCache = 'system/aliases/';

    /**
     * @var array Holds already found aliases
     */
    private $aliases = [];


    function __construct(){
        $this->verifyAliasCache();
        $this->path = dirname(__FILE__).'/';
    }

    /**
     * This method is used to register an alias autoloader, as an addition to composer.
     */
    public function registerAutoloader(){
        $aliasManager = $this;
        spl_autoload_register(function($class) use($aliasManager) {
            $class = strtolower($class);
            if( !$aliasManager->checkIfAliasExists($class) ){
                if( !$aliasManager->findAlias($class) ){
                    throw new Exception("Class was not found! ($class)");
                }
            }

            $aliasManager->loadAlias($class);
        });
    }

    /**
     * This method is used to check if folder of alias cache is valid (exists, readable, writeable).
     * @throws Exception When alias cache is not valid folder.
     */
    public function verifyAliasCache(){
        if( !file_exists(BASEPATH.$this->aliasCache) || !is_readable(BASEPATH.$this->aliasCache) || !is_writable(BASEPATH.$this->aliasCache)){
            throw new Exception('Alias folder does not exist or readable & writeable');
        }
    }

    /**
     * This method is used to check if alias exists in the cache
     * @param string $name Name of the alias to be checked
     * @return boolean True if alias exists in the cache
     */
    public function checkIfAliasExists($name){

        if( isset($this->aliases[$name]) ){
            return true;
        }

        $aliasPath = BASEPATH.$this->aliasCache.$name.".php";
        if( file_exists($aliasPath) ){
            $this->aliases[$name] = true;
            return true;
        }
    }

    /**
     * Method is used to load existing aliase from the cache
     * @param string $class Name of the alias to be loaded
     */
    public function loadAlias($class){
            require_once(BASEPATH.$this->aliasCache.$class.'.php');
    }

    /**
     * Method is used to delete existing aliase from the cache
     * @param string $class Name of the alias to be deleted
     */
    public function deleteAlias($class){
            unlink(BASEPATH.$this->aliasCache.$class.'.php');
    }

    /**
     * This method is used to find a component from composer's psr4, which serves requested alias.
     * @param string $class Name of the alias to be looked for in psr4 autoloading array.
     * @return boolean True if alias was found and created.
     */
    public function findAlias($class){

        $container = Container::open('aliases');

        if( $container->exists($class) ){
            $instance = $container->get($class);
            
            $config['provider'] = $instance;
            if( is_object($instance) ){
                 Container::open('instances')->add($class, $instance);
                 $config['provider'] = 'PlaceHolderClass';
            }
            $result = $this->createAlias($class, $config);
            return $result;
        }

        $psr4 = require BASEPATH.'vendor/composer/autoload_psr4.php';
        $manifestResolver = new ManifestResolver($psr4);

        $manifestResolver->findManifests();

        foreach ($manifestResolver->manifests as $manifest => $manifestSrc) {
            $manifest = $manifestResolver->getManifest($manifestSrc);
            $manifestData = $manifestResolver->verifyManifest($manifest);
            //debug here
            foreach ($manifestData['components'] as $component => $componentConfig) {
                if( $manifestResolver->verifyComponent($componentConfig) ){
                    if( $class === $componentConfig['alias'] ){
                        $componentSrc = explode('../manifest.php',$manifestSrc)[0];
                        $result = $this->createAlias($class, $componentConfig);
                        return $result;
                    }
                }
            }
        }
    }

    /**
     * Method is used to load a template for alias creation.
     */
    public function getAliasTemplate(){
        if( !isset($this->template) ){
            $this->template = file_get_contents($this->path.'AliasTemplate.php');
        }
        return $this->template;
    }

    /**
     * Method is used to create an alias, from specified parameters.
     * @param string $name Name of the alias to be created
     * @param string $config Configuration array of created alias
     */
    public function createAlias($name, $config){
        $handle = fopen(BASEPATH.$this->aliasCache.$name.'.php','w+');
        if(!$handle){
            throw new Exception('Unable to create alias, fopen not successful');
        }
        $template = $this->getAliasTemplate();
        $template = str_replace('{$className}', $name, $template);
        $template = str_replace('{$providerName}', $config['provider'], $template);
        
        fwrite($handle, $template);
        fclose($handle);
        $this->aliases[$name] = true;
        return true;
    }

    public function Hi(){
        echo "hi!";
    }

    public function findAllAliases(){
        $aliases  = glob(BASEPATH.$this->aliasCache.'*'); 
        return $aliases;
    }

    public function deleteAllAliases(){
        $aliases = $this->findAllAliases();
        foreach ($aliases as $alias) {
            $this->deleteAlias(explode('.php', explode(BASEPATH.$this->aliasCache,$alias)[1])[0]);
        }
    }


}
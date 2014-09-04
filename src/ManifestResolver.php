<?php

namespace Vibius\Facade;
use Exception;

class ManifestResolver{

    /**
     * @var array Array of manifest source path
     */
    public $manifests;

    function __construct($src){
        $this->src = $src;
    }

    /**
     * Function is used to find manifests in psr4 loaded packages
     */
    public function findManifests(){
        foreach ($this->src as $component => $source) {
            $src = $source[0]."/../manifest.php";
            if( file_exists($src) && is_readable($src) ){
                $this->manifests[$component] = $src;
            }
        }
    }

    /**
     * This function is used to get a manifest file by it's aboslute path
     * @param string $src Path to manifest file
     * @return array Content of the manifest file
     */
    public function getManifest($src){
        $manifest = require $src;
        return $manifest;
    }

    /**
     * @param array $manifest
     * @return array If the manifest passed validation
     */
    public function verifyManifest($manifest){
        if( isset($manifest['vibius']) && $manifest['vibius'] === true && isset($manifest['components']) ){
            return $manifest;
        }
    }

    /**
     * Method is used to fetch components from manifest file
     * @param array $manifest 
     * @throws Exception Manifest provided has no components
     */
    public function getComponents($manifest){
        if( isset($manifest['components']) ){
            return $manifest['components'];
        }
        throw new Exception('Manifest provided has no components');
    }


    public function verifyComponent($component){
        if( !isset($component['alias']) ){
            throw new Exception("Component has no alias ($component)");
        }
        if( !isset($component['provider']) ){
            throw new Exception("Component has no provider ($component)");
        }
        /*if( !isset($component['interface']) ){
            throw new Exception("Component has no interface ($component)");
        }*/
        return true;
    }

}
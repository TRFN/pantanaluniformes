<?php
class __paths {
    public static function get(){
        $pathRoute = new stdClass;
        $pathRoute->root      =  dirname(dirname(dirname(__FILE__)));
        $pathRoute->www       =  dirname($pathRoute->root);
        $pathRoute->controls  = "{$pathRoute->root}/controls";
        $pathRoute->database  = "{$pathRoute->root}/database";
        $pathRoute->engine    = "{$pathRoute->root}/engine";
        $pathRoute->languages = "{$pathRoute->root}/languages";
        $pathRoute->templates = "{$pathRoute->root}/templates";
        $pathRoute->models    = "{$pathRoute->root}/models";
        $pathRoute->plugins   = "{$pathRoute->root}/plugins";
        $pathRoute->layouts   = "{$pathRoute->root}/layouts";

        return $pathRoute;
    }
}
?>

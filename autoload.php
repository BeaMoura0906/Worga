<?php

function loadClass( $class ) {

    $tmp = explode('\\', $class);
    $srcPath = $tmp[1];
    $controllerPath = $tmp[2];

    if( $tmp[3] === 'Entity'){
        $subfolder = $tmp[3];
        $classFile = $tmp[4];
        require $srcPath . '/' . $controllerPath . '/' . $subfolder . '/' . $classFile . '.php';
    } else {
        $classFile = $tmp[3];
        require $srcPath . '/' . $controllerPath . '/' . $classFile . '.php';
    }

}

spl_autoload_register('loadClass');
<?php

use Symfony\Component\DependencyInjection\Definition;

$composerAutoloadPaths = [
    __DIR__.'/../../../autoload.php',
    __DIR__.'/../vendor/autoload.php',
];

$policyCheckFolders = [];
foreach ($composerAutoloadPaths as $autoload) {
    if (file_exists($autoload)) {
        $autoload = require $autoload;
        $autoloadPsr4Prefixes = $autoload->getPrefixesPsr4();
        $policyCheckFolders = $autoloadPsr4Prefixes['EdisonLabs\PolicyVerification\\'];
        break;
    }
}

// Filter paths.
$policyCheckFolders = array_filter($policyCheckFolders, function ($path) {
    return strpos($path, 'EdisonLabs/PolicyVerification') !== false;
});

// Register services.
if ($policyCheckFolders) {
    $definition = new Definition();
    $definition
        ->setPublic(true);

    foreach ($policyCheckFolders as $folder) {
        // $this is a reference to the current loader
        $this->registerClasses(
            $definition,
            'EdisonLabs\\PolicyVerification\\',
            $folder
        );
    }
}

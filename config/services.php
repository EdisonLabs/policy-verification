<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

// Set composer vendor path.
$composerSources = [
    __DIR__.'/../../../autoload.php',
    __DIR__.'/../vendor/autoload.php',
];

$composerVendor = null;
foreach ($composerSources as $autoload) {
    if (file_exists($autoload)) {
        $composerVendor = str_replace('/autoload.php', '', $autoload);
        break;
    }
}

// Find packages using the Policy check classes.
$finder = new Finder();
$finder->directories();
$finder->followLinks();
$finder->in("$composerVendor/..");
$finder->name('EdisonLabs');

// Register services.
if ($finder->count() !== 0) {
    $definition = new Definition();
    $definition
    ->addArgument('%policy-verification.data%')
    ->setAutowired(true)
    ->setAutoconfigured(true)
    ->setPublic(true);

    foreach ($finder as $folder) {
        // $this is a reference to the current loader
        $this->registerClasses(
            $definition,
            'EdisonLabs\\PolicyVerification\\',
            $folder->getRealPath().'/PolicyVerification'
        );
    }
}

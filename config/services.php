<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

// Set composer vendor path.
$composerVendor = str_replace('/autoload.php', '', COMPOSER_INSTALL);

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

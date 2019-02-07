<?php

namespace EdisonLabs\PolicyVerification;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Class ContainerBuilder.
 */
class ContainerBuilder
{
    const SERVICES_PHP_FILE = '../config/services.php';

    protected $containerBuilder;

    /**
     * ContainerBuilder constructor.
     *
     * @param array $data The custom data array.
     *
     * @throws \Exception
     */
    public function __construct(array $data = [])
    {
        $containerBuilder = new SymfonyContainerBuilder();
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load(self::SERVICES_PHP_FILE);

        $containerBuilder->setParameter('policy-verification.data', $data);
        $containerBuilder->compile();

        $this->containerBuilder = $containerBuilder;
    }

    /**
     * Returns the container builder instance.
     *
     * @return SymfonyContainerBuilder Container builder instance.
     */
    public function getContainerBuilder()
    {
        return $this->containerBuilder;
    }
}

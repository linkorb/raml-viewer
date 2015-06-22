<?php

namespace RamlServer;

use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SecurityServiceProvider as SilexSecurityServiceProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;
use RuntimeException;

class Application extends SilexApplication
{
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this->configureParameters();
        $this->configureService();
        $this->configureRoutes();
        $this->configureTemplateEngine();
        // $this->configureSecurity();
    }

    private function getConfigFromParameters()
    {
        if (!$this->offsetExists('parameters')) {
            $parser = new YamlParser();
            $this['parameters'] = $parser->parse(file_get_contents(__DIR__.'/../app/config/parameters.yml'));
        }

        return $this['parameters'];
    }

    private function configureParameters()
    {
        $this['debug'] = false;
        $parameters = $this->getConfigFromParameters();
        if (isset($parameters['debug'])) {
            $this['debug'] = !!$parameters['debug'];
        }
    }

    private function configureService()
    {
    }

    private function configureRoutes()
    {
        $locator = new FileLocator(array(__DIR__.'/../app/config'));
        $loader = new YamlFileLoader($locator);
        $this['routes'] = $loader->load('routes.yml');
    }

    private function configureTemplateEngine()
    {
        $this->register(new TwigServiceProvider(), array(
            'twig.path' => array(
                __DIR__.'/../templates/',
            ),
        ));
    }

    private function configureSecurity()
    {
        $this->register(new SilexSecurityServiceProvider(), array());

        $parameters = $this->getConfigFromParameters();
        $security = $parameters['security'];

        if (isset($security['encoder'])) {
            $digest = '\\Symfony\\Component\\Security\\Core\\Encoder\\'.$security['encoder'];
            $this['security.encoder.digest'] = new $digest(true);
        }

        $this['security.firewalls'] = array(
            'default' => array(
                'stateless' => true,
                'pattern' => '^/',
                'http' => true,
                'users' => $this->getUserSecurityProvider(),
            ),
        );
    }

    private function getUserSecurityProvider()
    {
        $parameters = $this->getConfigFromParameters();
        foreach ($parameters['security']['providers'] as $provider => $providerConfig) {
            switch ($provider) {
                // case 'JsonFile':
                //     return new \LinkORB\Skeleton\Security\JsonFileUserProvider(__DIR__.'/../'.$providerConfig['path']);
                // case 'Pdo':
                //     $dbmanager = new DatabaseManager();
                //
                //     return new \LinkORB\Skeleton\Security\PdoUserProvider($dbmanager->getPdo($providerConfig['database']));
                case 'UserBase':
                    return new \UserBase\Client\UserProvider(
                        new \UserBase\Client\Client(
                            $providerConfig['url'],
                            $providerConfig['username'],
                            $providerConfig['password']
                        )
                    );
                default:
                    break;
            }
        }
        throw new RuntimeException('Cannot find any security provider');
    }
}

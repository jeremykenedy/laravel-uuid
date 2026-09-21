<?php

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use jeremykenedy\Uuid\Uuid;
use jeremykenedy\Uuid\UuidServiceProvider;
use PHPUnit\Framework\TestCase;

class UuidServiceProviderTest extends TestCase
{
    public function testProviderRegistersTheLegacyUuidRule()
    {
        $factory = new class {
            public $extensions = [];

            public function extend($name, $callback)
            {
                $this->extensions[$name] = $callback;
            }
        };
        $app = new Container;
        $app->instance('validator', $factory);
        $original = Facade::getFacadeApplication();
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();

        try {
            (new UuidServiceProvider($app))->boot();
            $this->assertArrayHasKey('uuid', $factory->extensions);
            $rule = $factory->extensions['uuid'];
            $uuid = Uuid::generate(4);

            foreach ([$uuid, $uuid->string, $uuid->bytes, $uuid->hex, $uuid->urn] as $value) {
                $this->assertTrue($rule('id', $value, [], null));
            }

            foreach ([null, 'invalid', [], new stdClass] as $value) {
                $this->assertFalse($rule('id', $value, [], null));
            }
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($original);
        }
    }

    public function testLaravelValidatorAcceptsUuidsAndRejectsInvalidInput()
    {
        $app = new Container;
        $factory = new Factory(new Translator(new ArrayLoader, 'en'), $app);
        $app->instance('validator', $factory);
        $original = Facade::getFacadeApplication();
        Facade::setFacadeApplication($app);
        Facade::clearResolvedInstances();

        try {
            (new UuidServiceProvider($app))->boot();

            foreach ([1, 3, 4, 5] as $version) {
                $value = (string) Uuid::generate($version, 'example.com', Uuid::NS_DNS);
                $this->assertTrue($factory->make(['id' => $value], ['id' => 'required|uuid'])->passes());
            }

            foreach ([null, '', 'invalid', [], ['invalid']] as $value) {
                $this->assertFalse($factory->make(['id' => $value], ['id' => 'required|uuid'])->passes());
            }
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($original);
        }
    }

    public function testLaravelAliasResolvesTheExistingClass()
    {
        $metadata = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);
        $loader = AliasLoader::getInstance();
        $loader->alias('Uuid', $metadata['extra']['laravel']['aliases']['Uuid']);
        $loader->load('Uuid');

        $this->assertInstanceOf(Uuid::class, \Uuid::generate(4));
        $this->assertSame(1, \Uuid::generate()->version);
    }

    public function testLaravelDiscoversTheProviderAndAlias()
    {
        if (!class_exists(PackageManifest::class)) {
            $this->markTestSkipped('Package discovery was introduced in Laravel 5.5.');
        }

        $files = new Filesystem;
        $directory = sys_get_temp_dir().'/uuid-discovery-'.bin2hex(random_bytes(8));
        $files->makeDirectory($directory.'/vendor/composer', 0755, true);
        $metadata = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);
        $files->put($directory.'/vendor/composer/installed.json', json_encode([$metadata]));

        try {
            $manifest = new PackageManifest($files, $directory, $directory.'/packages.php');
            $manifest->build();
            $this->assertContains(UuidServiceProvider::class, $manifest->providers());
            $this->assertSame(Uuid::class, $manifest->aliases()['Uuid']);
        } finally {
            $files->deleteDirectory($directory);
        }
    }
}

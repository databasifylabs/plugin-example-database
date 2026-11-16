<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\DTOs\Databases\ConnectionProfile;
use App\Plugins\AbstractPlugin;

final class ExampleDatabasePlugin extends AbstractPlugin
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/scripts', ExampleDriver::ID);

        $this->databasify()->database(ExampleDriver::ID)
            ->label('Example Engine')
            ->category('Application')
            ->image('ghcr.io/databasifylabs/databasify-example')
            ->versions('13', [['value' => '13', 'label' => 'Example (SQLite 3)']])
            ->connection(new ConnectionProfile(
                uriScheme: 'example',
                uriQuery: '',
                tlsParameter: 'tls',
                tlsMode: 'on',
                command: 'openssl s_client -quiet -connect {host}:{port}',
                fixedUsername: null,
                selectsDatabase: true,
                envKeys: ['host' => 'EXAMPLE_HOST', 'port' => 'EXAMPLE_PORT', 'username' => 'EXAMPLE_USERNAME', 'password' => 'EXAMPLE_PASSWORD', 'database' => 'EXAMPLE_DATABASE'],
                commandNote: 'The example answers with a banner only. Its users and databases are managed through Databasify.',
            ))
            ->driver(ExampleDriver::class)
            ->register();
    }
}

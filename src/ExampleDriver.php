<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\Engines\Data\EngineFacts;
use App\Engines\Data\HostProfile;
use App\Engines\Data\Ports;
use App\Engines\Drivers\AbstractEngineDriver;
use App\Enums\Engines\Feature;
use App\Support\Databases\ClientPorts;

final readonly class ExampleDriver extends AbstractEngineDriver
{
    public const string ID = 'example';

    public const string SERVER = 'example-server';

    public const string CATALOG = '/srv/example/data/catalog.db';

    public static function facts(string $line): EngineFacts
    {
        return new EngineFacts(
            features: [Feature::Accounts],
            reservedUsers: [],
            reservedDatabases: [],
            ports: new Ports(ClientPorts::CLIENT, null, []),
            hostProfile: new HostProfile([]),
        );
    }

    public function nodes(): ExampleNodes
    {
        return new ExampleNodes($this, new ExampleState);
    }

    public function cluster(): ExampleCluster
    {
        return new ExampleCluster($this, new ExampleState);
    }

    public function config(): ExampleConfig
    {
        return new ExampleConfig($this, new ExampleState);
    }

    public function accounts(): ExampleAccounts
    {
        return new ExampleAccounts($this);
    }

    protected function queryRunner(): string
    {
        return 'example::_query';
    }

    protected function sqlQuoting(): SqliteSql
    {
        return new SqliteSql;
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return ['catalog' => self::CATALOG, 'server' => self::SERVER, 'port' => $this->node->database->clientPort()];
    }
}

<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\DTOs\Databases\NodeStatus;
use App\Engines\Contracts\Nodes;
use App\Engines\Data\DefaultAccount;
use App\Engines\Data\Grant;
use App\Engines\Data\NodeConfig;
use App\Engines\Data\NodeMetrics;
use App\Engines\Data\Operation;
use App\Engines\Data\Secrets;
use App\Engines\Drivers\Support\IndentedJson;
use App\Engines\Drivers\Support\NodeState;
use App\Engines\Drivers\TlsPair;
use App\Enums\Databases\GrantAccess;
use App\Enums\Databases\NodeRole;

final readonly class ExampleNodes implements Nodes
{
    public function __construct(
        private ExampleDriver $driver,
        private ExampleState $state,
    ) {}

    public function configure(NodeConfig $config): void
    {
        $this->driver->run('example::nodes.configure', [
            'manifest' => IndentedJson::of(['processes' => [[
                'name' => ExampleDriver::SERVER,
                'argv' => ['socat', "OPENSSL-LISTEN:{$this->driver->node->database->clientPort()},reuseaddr,fork,cert=/srv/example/tls/node.pem,cafile=/srv/example/tls/ca.pem,verify=0", 'SYSTEM:echo example ready'],
                'user' => ExampleDriver::ID,
                'enabled' => true,
                'restart' => 'always',
                'restartSec' => '2s',
                'stopSignal' => 'TERM',
                'stopTimeout' => '10s',
            ]], 'schedules' => []]),
            'request' => NodeState::requestJsonOf($config),
        ]);
        $this->driver->query('example::accounts.bootstrap');

        if ($this->state->savedRoleOf($this->driver) !== null) {
            return;
        }

        $follows = $config->readOnly || ($config->clustered && $config->primary !== null && $config->primary !== $config->ip);
        $this->state->saveRole($this->driver, $follows ? NodeRole::Replica : NodeRole::Primary);
    }

    public function start(): void
    {
        $this->driver->run('example::nodes.start');
    }

    public function restart(): void
    {
        $this->driver->run('example::nodes.restart');
    }

    public function wake(bool $all): void
    {
        $this->driver->run('example::nodes.start');
    }

    public function reset(int $peers, array $hosts): void
    {
        $this->driver->run('example::nodes.reset');
    }

    public function bootstrap(Secrets $secrets, ?DefaultAccount $account): void
    {
        if ($account === null) {
            return;
        }

        $this->driver->accounts()->createDatabase($account->database);
        $this->driver->accounts()->createUser($account->user, $account->password, [new Grant($account->database, GrantAccess::Write)], null);
    }

    public function installTls(string $certificate, string $key, string $ca): void
    {
        TlsPair::checkPair($certificate, $key, $ca);

        $this->driver->run('example::nodes.tls-install', ['pem' => rtrim($certificate, "\n")."\n".$key, 'ca' => $ca]);
    }

    public function ready(string $key, bool $expectPrimary): Operation
    {
        return $this->driver->task($key, 'example::nodes.ready');
    }

    public function status(): NodeStatus
    {
        $reply = $this->driver->read('example::nodes.status');

        if (! $reply->bool('active')) {
            return NodeStatus::stopped();
        }

        return NodeStatus::reported($this->state->savedRoleOf($this->driver) ?? NodeRole::Replica, $reply->string('version'));
    }

    public function metrics(): NodeMetrics
    {
        return NodeMetrics::fromPayload($this->driver->queryJson('example::nodes.metrics'));
    }
}

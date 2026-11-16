<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\DTOs\Databases\QuorumCount;
use App\Engines\Contracts\Cluster;
use App\Engines\Data\Operation;
use App\Enums\Databases\NodeRole;
use App\Models\DatabaseNode;

final readonly class ExampleCluster implements Cluster
{
    public function __construct(
        private ExampleDriver $driver,
        private ExampleState $state,
    ) {}

    public function enable(string $key): Operation
    {
        return $this->driver->task($key, 'example::cluster.settle');
    }

    public function disable(): void
    {
        $this->state->saveRole($this->driver, NodeRole::Primary);
    }

    public function failover(string $targetIp): void
    {
        $nodes = $this->driver->node->database->nodesWithServers();
        $isTarget = fn (DatabaseNode $node): bool => $node->privateIp() === $targetIp;

        $nodes->filter($isTarget)->each(fn (DatabaseNode $node) => $this->state->saveRole(new ExampleDriver($node), NodeRole::Primary));
        $nodes->reject($isTarget)->each(fn (DatabaseNode $node) => rescue(fn () => $this->state->saveRole(new ExampleDriver($node), NodeRole::Replica), report: false));
    }

    public function forceQuorum(bool $othersDown): void {}

    public function membership(array $members): void {}

    public function rejoin(string $key, bool $readOnly): Operation
    {
        $this->state->saveRole($this->driver, NodeRole::Replica);

        return $this->driver->task($key, 'example::cluster.settle');
    }

    public function forget(string $ip): void {}

    public function position(): ?int
    {
        return null;
    }

    public function quorum(): QuorumCount
    {
        $members = $this->driver->node->database->membersWithServers();

        return new QuorumCount($members->count(), $members->filter(fn (DatabaseNode $node): bool => in_array($node->role, [NodeRole::Primary, NodeRole::Replica], true))->count());
    }
}

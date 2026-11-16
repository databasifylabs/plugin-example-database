<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\Engines\Data\NodeConfig;
use App\Engines\Drivers\Support\NodeState;
use App\Enums\Databases\NodeRole;
use App\Exceptions\Engines\EngineBusy;

final readonly class ExampleState
{
    private const string REQUEST = 'state/node.json';

    private const string ROLE = 'state/role';

    public function __construct(private NodeState $nodeState = new NodeState) {}

    public function requestOf(ExampleDriver $driver): NodeConfig
    {
        return $this->nodeState->requestOf($driver, self::REQUEST) ?? throw new EngineBusy('run node configure first');
    }

    public function savedRoleOf(ExampleDriver $driver): ?NodeRole
    {
        return NodeRole::tryFrom(trim($driver->getFile(self::ROLE) ?? ''));
    }

    public function saveRole(ExampleDriver $driver, NodeRole $role): void
    {
        $driver->putFile(self::ROLE, $role->value, 0o600);
    }
}

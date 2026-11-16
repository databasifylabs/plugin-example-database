<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\Engines\Contracts\Accounts;
use App\Engines\Data\AccountList;
use App\Enums\Databases\PasswordEncryption;
use Illuminate\Support\Facades\Hash;

final readonly class ExampleAccounts implements Accounts
{
    public function __construct(private ExampleDriver $driver) {}

    public function list(): AccountList
    {
        return AccountList::fromPayload($this->driver->queryJson('example::accounts.list'));
    }

    public function createDatabase(string $name): void
    {
        $this->driver->query('example::accounts.create-database', ['name' => $name]);
    }

    public function dropDatabase(string $name): void
    {
        $this->driver->query('example::accounts.drop-database', ['name' => $name]);
        $this->driver->run('example::accounts.remove-database-file', ['name' => $name]);
    }

    public function createUser(string $name, string $password, array $grants, ?PasswordEncryption $encryption): void
    {
        $this->driver->query('example::accounts.create-user', ['name' => $name, 'hash' => Hash::make($password), 'grants' => $grants]);
    }

    public function setPassword(string $name, string $password, ?PasswordEncryption $encryption): void
    {
        $this->driver->query('example::accounts.set-password', ['name' => $name, 'hash' => Hash::make($password)]);
    }

    public function grant(string $name, array $grants, bool $replication): void
    {
        $this->driver->query('example::accounts.grant', ['name' => $name, 'grants' => $grants]);
    }

    public function dropUser(string $name): void
    {
        $this->driver->query('example::accounts.drop-user', ['name' => $name]);
    }
}

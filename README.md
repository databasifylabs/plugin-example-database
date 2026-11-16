# Example Database Plugin

An example [Databasify](https://github.com/databasifylabs/databasify) plugin for people writing a new database engine. It adds **Example Engine** (`example`), a small SQLite engine with its own driver and flat scripts you can copy.

A community engine is two pieces:

- **its images**, built from [`databasifylabs/engine-template`](https://github.com/databasifylabs/engine-template): the database, its tools and the [engine SDK](https://github.com/databasifylabs/engine-sdk) 1.0 runtime. The template's image is this example's image, `ghcr.io/databasifylabs/databasify-example`: SQLite and `socat` on Debian 13;
- **this plugin**: it registers the engine and ships its **driver**, the PHP classes and Blade scripts Databasify runs inside the container.

## What is real and what is simulated

The example is for reading and copying. It holds toy data and does not replicate.

- **Real:** the registration, the driver and its groups, query views run through `query()` and `queryJson()`, bash views run through `run()`, `read()` and `task()`, quoting through `SqlQuoting`, typed errors, TLS files, the supervisor unit and the readiness wait.
- **Simulated:** the database is a catalog file (`/srv/example/data/catalog.db`) with `users`, `databases` and `grants` tables, plus one SQLite file per database. The port answers with a banner over TLS; data is reached only through Databasify. Clustering is a saved role per node (`/srv/example/state/role`): `failover`, `enable`, `rejoin` and `disable` only save or keep roles, `forceQuorum`, `membership` and `forget` do nothing, `position` is `null`, and `quorum` counts the members Databasify records as online.

## Try it

1. Download this repository as a zip.
2. In Databasify 0.2 or later, open **Plugins**, install the zip and accept the community-engine warning.
3. Create a database and pick **Example Engine**.

## File layout

```
composer.json                          name, version, autoload and extra.databasify
src/ExampleDatabasePlugin.php          registers the engine and its scripts
src/ExampleDriver.php                  facts, groups, query runner and quoting
src/SqliteSql.php                      SqlQuoting: doubled single quotes, double-quoted names
src/ExampleState.php                   the node's saved request and role
src/ExampleNodes.php                   Nodes: configure and its process manifest, start, TLS, ready, status, metrics
src/ExampleCluster.php                 Cluster (simulated)
src/ExampleConfig.php                  Config: one setting, journal_mode
src/ExampleAccounts.php                Accounts: users, databases, grants
resources/scripts/_query.blade.php     the query runner
resources/scripts/nodes/               bash views, plus the metrics query view
resources/scripts/cluster/             bash view
resources/scripts/config/              query view
resources/scripts/accounts/            query views, plus one bash view that removes a file
public/icon.svg                        plugin icon
public/icons/database-providers/example.svg
```

## Registration

`ExampleDatabasePlugin::boot()` loads the scripts under the engine id and registers the engine:

```php
$this->loadViewsFrom(__DIR__.'/../resources/scripts', ExampleDriver::ID);

$this->databasify()->database(ExampleDriver::ID)
    ->label('Example Engine')
    ->category('Application')
    ->image('ghcr.io/databasifylabs/databasify-example')
    ->versions('13', [['value' => '13', 'label' => 'Example (SQLite 3)']])
    ->connection(/* ... */)
    ->driver(ExampleDriver::class)
    ->register();
```

- `extra.databasify.engines` in `composer.json` (`["example"]`) lists every engine id the plugin registers. Databasify reads it before any plugin code runs and asks the admin to confirm a community engine.
- `extra.databasify.requires` (`^0.2`): drivers arrived in Databasify 0.2.
- `image()`: Databasify picks the newest release tag of the database's line and pins its digest. The image's `io.databasify.engine` label must equal the engine id: it names the container (`databasify-example`) and its data folder (`/srv/example`).
- `versions()`: each line is a tag prefix in the image. The template's line is `13` because its base is Debian 13.

## The driver

`ExampleDriver` extends `App\Engines\Drivers\AbstractEngineDriver`. It declares its facts, hands out its groups (`nodes`, `cluster` and `config` are required; `accounts` is optional and listed in `features`), names its query runner and returns its quoting:

```php
protected function queryRunner(): string
{
    return 'example::_query';
}

protected function sqlQuoting(): SqliteSql
{
    return new SqliteSql;
}
```

Every group method makes one call. Decisions stay in PHP; scripts run top to bottom.

### A query view: `createUser`

```php
$this->driver->query('example::accounts.create-user', ['name' => $name, 'hash' => Hash::make($password), 'grants' => $grants]);
```

`resources/scripts/accounts/create-user.blade.php` is only SQL, every value through `$sql`, the driver's `SqliteSql`:

```blade
INSERT INTO users (name, password_hash) VALUES ({!! $sql->string($name) !!}, {!! $sql->string($hash) !!}) ON CONFLICT (name) DO UPDATE SET password_hash = excluded.password_hash;
@include('example::accounts._grants')
```

`query()` renders the view, base64-encodes it and runs the runner, `_query.blade.php`, which pipes it into `sqlite3` as the engine user and turns a failure into a typed error with `databasify_fail` (`already_exists` for a unique constraint, `sql` for the rest). `queryJson()` does the same and returns the one JSON value the query prints, as `list` does. The password is hashed in PHP, so the plain password never reaches the server.

### A bash view: `ready`

```php
return $this->driver->task($key, 'example::nodes.ready');
```

```blade
@include('engines._runtime')
wait_until 60 2 unit_active {!! $sh->arg($server) !!} || fail_task 'the example server did not start within 60s'
wait_until 60 2 listening_on {{ $port }} || fail_task 'nothing listened on port {{ $port }} within 60s'
echo 'The example server is ready.'
```

`task()` starts a long step on the server and Databasify polls it, so the wait stays on the server. `engines._runtime` is Databasify's shared kit: `databasify_fail`, `fail_task`, `result`, `wait_until`, `unit_active`, `unit_do` and `listening_on`. `engines._write` adds `engine_directory` and `write_file`.

## Turning it into a real engine

1. **Images:** create a repository from [`databasifylabs/engine-template`](https://github.com/databasifylabs/engine-template), point its base at your database's official image and publish `ghcr.io/<owner>/databasify-<engine>`. Make the package public, because Databasify pulls images without credentials.
2. **Plugin:** copy this repository, change the engine id in `ExampleDriver::ID` and `extra.databasify.engines`, and set the label, category, image, lines and connection.
3. **Driver:** keep the groups and replace the scripts: your client in `_query`, your processes in the manifest `ExampleNodes::configure` builds, your statements in the query views, and real replication in `ExampleCluster`. Write your own `SqlQuoting` if your database quotes differently.

Read more in [Writing plugins](https://docs.databasify.io/plugins/writing-plugins/) and Databasify's [`adding-database-engine`](https://github.com/databasifylabs/databasify/tree/0.x/.claude/skills/adding-database-engine) skill.
<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\Engines\Contracts\Config;
use App\Engines\Settings\Setting;

final readonly class ExampleConfig implements Config
{
    private const string JOURNAL_MODE = 'journal_mode';

    private const string DEFAULT_JOURNAL_MODE = 'wal';

    public function __construct(
        private ExampleDriver $driver,
        private ExampleState $state,
    ) {}

    public function apply(): void
    {
        $settings = $this->state->requestOf($this->driver)->settings;

        $this->driver->query('example::config.apply', ['mode' => $settings[self::JOURNAL_MODE] ?? self::DEFAULT_JOURNAL_MODE]);
    }

    public function sync(): void {}

    public function pendingRestart(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [Setting::choice(self::JOURNAL_MODE, 'Journal mode', 'How SQLite keeps its rollback journal.', ['wal', 'delete'], self::DEFAULT_JOURNAL_MODE)];
    }

    public function tune(): array
    {
        return [];
    }
}

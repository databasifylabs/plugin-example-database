<?php

declare(strict_types=1);

namespace Databasify\ExampleDatabase;

use App\Engines\Contracts\SqlQuoting;
use App\Engines\Scripts\Shell;

final readonly class SqliteSql implements SqlQuoting
{
    public function string(string $value): string
    {
        return "'".str_replace("'", "''", Shell::withoutNul($value))."'";
    }

    public function name(string $value): string
    {
        return '"'.str_replace('"', '""', Shell::withoutNul($value)).'"';
    }
}

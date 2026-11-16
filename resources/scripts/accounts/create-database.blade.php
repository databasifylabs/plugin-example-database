INSERT INTO databases (name) VALUES ({!! $sql->string($name) !!}) ON CONFLICT (name) DO NOTHING;
ATTACH DATABASE {!! $sql->string("/srv/example/data/databases/{$name}.db") !!} AS created;
CREATE TABLE IF NOT EXISTS created.example (id INTEGER PRIMARY KEY);
DETACH DATABASE created;

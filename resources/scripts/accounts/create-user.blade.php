INSERT INTO users (name, password_hash) VALUES ({!! $sql->string($name) !!}, {!! $sql->string($hash) !!}) ON CONFLICT (name) DO UPDATE SET password_hash = excluded.password_hash;
@include('example::accounts._grants')

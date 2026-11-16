UPDATE users SET password_hash = {!! $sql->string($hash) !!} WHERE name = {!! $sql->string($name) !!};

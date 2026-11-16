DELETE FROM grants WHERE user = {!! $sql->string($name) !!};
DELETE FROM users WHERE name = {!! $sql->string($name) !!};

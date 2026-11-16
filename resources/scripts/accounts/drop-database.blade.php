DELETE FROM grants WHERE database = {!! $sql->string($name) !!};
DELETE FROM databases WHERE name = {!! $sql->string($name) !!};

DELETE FROM grants WHERE user = {!! $sql->string($name) !!};
@foreach ($grants as $grant)
INSERT INTO grants (user, database, access) VALUES ({!! $sql->string($name) !!}, {!! $sql->string($grant->database) !!}, {!! $sql->string($grant->access->value) !!});
@endforeach

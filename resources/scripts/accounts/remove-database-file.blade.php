@include('engines._runtime')
rm -f {!! $sh->arg("/srv/example/data/databases/{$name}.db") !!}
echo 'Removed the database file.'

@include('engines._runtime')
@include('engines._write')
ROOT=/srv/example
for directory in conf data data/databases state tls; do
    engine_directory "$directory"
done
printf '%s' {!! $sh->arg($manifest) !!} | write_file "$ROOT/conf/processes.json" 600 true || true
printf '%s' {!! $sh->arg($request) !!} | write_file "$ROOT/state/node.json" 600 true || true
unit_do daemon-reload
echo 'Wrote the example configuration.'

@include('engines._runtime')
@include('engines._write')
ROOT=/srv/example
engine_directory tls
printf '%s' {!! $sh->arg($pem) !!} | write_file "$ROOT/tls/node.pem" 600 true || true
printf '%s' {!! $sh->arg($ca) !!} | write_file "$ROOT/tls/ca.pem" 600 true || true
if unit_active {!! $sh->arg($server) !!}; then
    unit_do restart {!! $sh->arg($server) !!}
fi
echo 'Installed the TLS certificate.'

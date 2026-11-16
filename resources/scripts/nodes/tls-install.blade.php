@include('engines._runtime')
@include('engines._write')
ROOT=/srv/example
engine_directory tls
printf '%s' {!! $sh->arg($pem) !!} | write_file "$ROOT/tls/node.pem" 600 true || true
printf '%s' {!! $sh->arg($ca) !!} | write_file "$ROOT/tls/ca.pem" 600 true || true
unit_do try-restart {!! $sh->arg($server) !!}
echo 'Installed the TLS certificate.'

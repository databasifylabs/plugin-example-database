@include('engines._runtime')
unit_do stop {!! $sh->arg($server) !!}
rm -f /srv/example/state/role
echo 'Stopped the example server and cleared its role.'

@include('engines._runtime')
unit_do restart {!! $sh->arg($server) !!}
echo 'Restarted the example server.'

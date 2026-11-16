@include('engines._runtime')
unit_do start {!! $sh->arg($server) !!}
echo 'Started the example server.'

@include('engines._runtime')
wait_until 30 2 unit_active {!! $sh->arg($server) !!} || fail_task 'the example server did not run within 30s'
echo 'The example node follows the primary.'

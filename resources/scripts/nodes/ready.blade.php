@include('engines._runtime')
wait_until 60 2 unit_active {!! $sh->arg($server) !!} || fail_task 'the example server did not start within 60s'
wait_until 60 2 listening_on {{ $port }} || fail_task 'nothing listened on port {{ $port }} within 60s'
echo 'The example server is ready.'

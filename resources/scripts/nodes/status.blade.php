@include('engines._runtime')
active=false
unit_active {!! $sh->arg($server) !!} && active=true
result "$(jq -cn --argjson active "$active" --arg version "$(sqlite3 --version | cut -d' ' -f1)" '{active: $active, version: $version}')"

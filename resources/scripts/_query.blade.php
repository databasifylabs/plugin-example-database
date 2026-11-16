@include('engines._runtime')
errors=$(mktemp)
output=$(printf '%s' {!! $sh->arg($query) !!} | base64 -d | setpriv --reuid=64999 --regid=64999 --clear-groups sqlite3 -bail {!! $sh->arg($catalog) !!} 2> "$errors") && status=0 || status=$?
message=$(cat "$errors"); rm -f "$errors"
if [ "$status" -ne 0 ]; then
    [[ $message == *'UNIQUE constraint failed'* ]] && databasify_fail already_exists "$message"
    databasify_fail sql "$message"
fi
@if ($json)
result "$output"
@else
printf '%s' "$output"
@endif

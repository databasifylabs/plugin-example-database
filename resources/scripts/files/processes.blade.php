{
    "processes": [
        {
            "name": "{{ $server }}",
            "argv": ["socat", "OPENSSL-LISTEN:{{ $port }},reuseaddr,fork,cert=/srv/example/tls/node.pem,cafile=/srv/example/tls/ca.pem,verify=0", "SYSTEM:echo example ready"],
            "user": "example",
            "enabled": true,
            "restart": "always",
            "restartSec": "2s",
            "stopSignal": "TERM",
            "stopTimeout": "10s"
        }
    ],
    "schedules": []
}

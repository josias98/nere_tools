param(
    [switch]$Reset
)

if ($Reset) {
    docker compose down -v
}

docker compose up -d

Write-Host "Site: http://localhost:8080"
Write-Host "DB:   localhost:3307 / wordpress / wordpress"

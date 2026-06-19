param(
    [string] $Url = "https://curl.se/ca/cacert.pem"
)

$ErrorActionPreference = "Stop"

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$certDir = Join-Path $projectRoot "storage\app\certs"
$certPath = Join-Path $certDir "cacert.pem"

New-Item -ItemType Directory -Force -Path $certDir | Out-Null

Invoke-WebRequest -Uri $Url -OutFile $certPath -UseBasicParsing

Write-Host "CA bundle downloaded."
Write-Host "Path: $certPath"

param(
    [string] $CertSubject = "CN=Nere Tools Local HTTPS",
    [string] $CertFriendlyName = "Nere Tools Local HTTPS",
    [string] $PfxPassphrase = "nere-tools-local-dev"
)

$ErrorActionPreference = "Stop"

$projectRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$certDir = Join-Path $projectRoot "storage\local-certs"
$pfxPath = Join-Path $certDir "localhost.pfx"
$cerPath = Join-Path $certDir "localhost.cer"
$securePassword = ConvertTo-SecureString $PfxPassphrase -AsPlainText -Force

New-Item -ItemType Directory -Force -Path $certDir | Out-Null

$cert = Get-ChildItem Cert:\CurrentUser\My |
    Where-Object { $_.Subject -eq $CertSubject -and $_.NotAfter -gt (Get-Date).AddDays(30) } |
    Sort-Object NotAfter -Descending |
    Select-Object -First 1

if (-not $cert) {
    $cert = New-SelfSignedCertificate `
        -Subject $CertSubject `
        -FriendlyName $CertFriendlyName `
        -DnsName "localhost" `
        -CertStoreLocation "Cert:\CurrentUser\My" `
        -KeyAlgorithm RSA `
        -KeyLength 2048 `
        -HashAlgorithm SHA256 `
        -KeyExportPolicy Exportable `
        -NotAfter (Get-Date).AddYears(2)
}

Export-PfxCertificate -Cert $cert -FilePath $pfxPath -Password $securePassword -Force | Out-Null
Export-Certificate -Cert $cert -FilePath $cerPath -Force | Out-Null

$trustedCert = Get-ChildItem Cert:\CurrentUser\Root |
    Where-Object { $_.Thumbprint -eq $cert.Thumbprint } |
    Select-Object -First 1

if (-not $trustedCert) {
    certutil -user -addstore Root $cerPath | Out-Null
}

Write-Host "Local HTTPS certificate is ready."
Write-Host "PFX: $pfxPath"
Write-Host "Trusted subject: $CertSubject"

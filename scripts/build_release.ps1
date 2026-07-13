param(
    [string] $OutputPath = "",
    [switch] $IncludeUploads
)

$ErrorActionPreference = "Stop"

$projectRoot = (Resolve-Path -LiteralPath (Join-Path $PSScriptRoot "..")).Path
if ([string]::IsNullOrWhiteSpace($OutputPath)) {
    $OutputPath = "music-release-{0}.zip" -f (Get-Date -Format "yyyyMMdd-HHmmss")
}

$outputFullPath = if ([System.IO.Path]::IsPathRooted($OutputPath)) {
    [System.IO.Path]::GetFullPath($OutputPath)
} else {
    [System.IO.Path]::GetFullPath((Join-Path $projectRoot $OutputPath))
}

$requiredFiles = @(
    "artisan",
    "bootstrap/app.php",
    "composer.json",
    "composer.lock",
    "public/index.php",
    "vendor/autoload.php"
)

foreach ($requiredFile in $requiredFiles) {
    $candidate = Join-Path $projectRoot $requiredFile
    if (-not (Test-Path -LiteralPath $candidate -PathType Leaf)) {
        throw "Missing $requiredFile. Run `composer install --no-dev --optimize-autoloader` before building the release."
    }
}

$tar = Get-Command tar.exe -ErrorAction SilentlyContinue
if (-not $tar) {
    throw "tar.exe was not found. On this Windows machine, install/enable tar or upload vendor after running Composer locally."
}

$outputDirectory = Split-Path -Path $outputFullPath -Parent
if ($outputDirectory) {
    New-Item -ItemType Directory -Path $outputDirectory -Force | Out-Null
}

if (Test-Path -LiteralPath $outputFullPath) {
    Remove-Item -LiteralPath $outputFullPath -Force
}

$includeCandidates = @(
    "app",
    "bootstrap",
    "config",
    "database",
    "public",
    "resources",
    "routes",
    "storage/app",
    "storage/app/private",
    "storage/app/public",
    "storage/framework",
    "storage/framework/cache",
    "storage/framework/cache/data",
    "storage/framework/sessions",
    "storage/framework/testing",
    "storage/framework/views",
    "storage/logs",
    "vendor",
    ".env.example",
    ".htaccess",
    "artisan",
    "composer.json",
    "composer.lock"
)

$includePaths = $includeCandidates | Where-Object {
    Test-Path -LiteralPath (Join-Path $projectRoot $_)
}

$excludePaths = @(
    "public/hot",
    "public/public.zip",
    "public/storage",
    "public/ftp-upload.php",
    "public/fix-admin.php",
    "public/reset-admin.php",
    "storage/app/body.json",
    "storage/app/cookies.txt",
    "storage/framework/cache/data/*",
    "storage/framework/sessions/*",
    "storage/framework/testing/*",
    "storage/framework/views/*",
    "storage/logs/*"
)

if (-not $IncludeUploads) {
    $excludePaths += "storage/app/public/*"
}

$tarArgs = @("-a", "-cf", $outputFullPath, "-C", $projectRoot)
foreach ($excludePath in $excludePaths) {
    $tarArgs += "--exclude=$excludePath"
}
$tarArgs += $includePaths

& $tar.Source @tarArgs
if ($LASTEXITCODE -ne 0) {
    throw "Release package build failed with tar exit code $LASTEXITCODE."
}

Write-Host "Created release package: $outputFullPath"
Write-Host "Extract it into /home/musictow/public_html so vendor/autoload.php sits at /home/musictow/public_html/vendor/autoload.php."
Write-Host "After extracting, make sure .env exists on the server, then run: php artisan optimize:clear"
Write-Host "If public storage files must be served, run: php artisan storage:link"

param(
    [Parameter(Mandatory = $true)]
    [string] $HostName,

    [int] $Port = 21,

    [Parameter(Mandatory = $true)]
    [string] $Username,

    [Parameter(Mandatory = $true)]
    [string] $LocalPath,

    [string] $RemotePath = "/public_html",

    [switch] $PlainFtp
)

$ErrorActionPreference = "Stop"

function Convert-ToPlainText {
    param([securestring] $SecureString)

    $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($SecureString)
    try {
        [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
    } finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
    }
}

function Join-FtpPath {
    param(
        [string] $Left,
        [string] $Right
    )

    $cleanLeft = $Left.TrimEnd("/")
    $cleanRight = $Right.TrimStart("/")

    if ([string]::IsNullOrWhiteSpace($cleanLeft)) {
        return "/" + $cleanRight
    }

    if ([string]::IsNullOrWhiteSpace($cleanRight)) {
        return $cleanLeft
    }

    return "$cleanLeft/$cleanRight"
}

function New-FtpRequest {
    param(
        [string] $Uri,
        [string] $Method,
        [System.Net.NetworkCredential] $Credential,
        [bool] $UseSsl
    )

    $request = [System.Net.FtpWebRequest]::Create($Uri)
    $request.Method = $Method
    $request.Credentials = $Credential
    $request.EnableSsl = $UseSsl
    $request.UseBinary = $true
    $request.KeepAlive = $false
    $request.Timeout = 30000
    $request.ReadWriteTimeout = 30000

    return $request
}

function Ensure-RemoteDirectory {
    param(
        [string] $DirectoryPath,
        [System.Net.NetworkCredential] $Credential,
        [bool] $UseSsl
    )

    $parts = $DirectoryPath.Trim("/").Split("/", [StringSplitOptions]::RemoveEmptyEntries)
    $current = ""

    foreach ($part in $parts) {
        $current = Join-FtpPath $current $part
        $uri = "ftp://$HostName`:$Port$current"
        $request = New-FtpRequest -Uri $uri -Method ([System.Net.WebRequestMethods+Ftp]::MakeDirectory) -Credential $Credential -UseSsl $UseSsl

        try {
            $response = $request.GetResponse()
            $response.Close()
            Write-Host "Created remote directory: $current"
        } catch [System.Net.WebException] {
            $response = $_.Exception.Response
            if ($response) {
                $response.Close()
            }
        }
    }
}

function Upload-File {
    param(
        [string] $SourceFile,
        [string] $DestinationFile,
        [System.Net.NetworkCredential] $Credential,
        [bool] $UseSsl
    )

    $remoteDirectory = Split-Path $DestinationFile.Replace("\", "/") -Parent
    $remoteDirectory = $remoteDirectory.Replace("\", "/")
    Ensure-RemoteDirectory -DirectoryPath $remoteDirectory -Credential $Credential -UseSsl $UseSsl

    $uri = "ftp://$HostName`:$Port$DestinationFile"
    $request = New-FtpRequest -Uri $uri -Method ([System.Net.WebRequestMethods+Ftp]::UploadFile) -Credential $Credential -UseSsl $UseSsl

    $bytes = [System.IO.File]::ReadAllBytes($SourceFile)
    $request.ContentLength = $bytes.Length

    $stream = $request.GetRequestStream()
    try {
        $stream.Write($bytes, 0, $bytes.Length)
    } finally {
        $stream.Close()
    }

    $response = $request.GetResponse()
    try {
        Write-Host "Uploaded: $SourceFile -> $DestinationFile"
    } finally {
        $response.Close()
    }
}

$resolvedLocalPath = Resolve-Path -LiteralPath $LocalPath
$useSsl = -not $PlainFtp.IsPresent
$password = Read-Host "FTP password for $Username@$HostName" -AsSecureString
$credential = [System.Net.NetworkCredential]::new($Username, (Convert-ToPlainText $password))

if ($PlainFtp) {
    Write-Warning "Plain FTP can expose your password. Use FTPS unless your host does not support it."
} else {
    Write-Host "Using FTPS. If your host rejects SSL, rerun with -PlainFtp."
}

if (Test-Path -LiteralPath $resolvedLocalPath -PathType Leaf) {
    $remoteFile = Join-FtpPath $RemotePath ([System.IO.Path]::GetFileName($resolvedLocalPath))
    Upload-File -SourceFile $resolvedLocalPath -DestinationFile $remoteFile -Credential $credential -UseSsl $useSsl
    exit 0
}

$basePath = (Get-Item -LiteralPath $resolvedLocalPath).FullName.TrimEnd("\")
$files = Get-ChildItem -LiteralPath $basePath -Recurse -File

foreach ($file in $files) {
    $relativePath = $file.FullName.Substring($basePath.Length).TrimStart("\").Replace("\", "/")
    $remoteFile = Join-FtpPath $RemotePath $relativePath
    Upload-File -SourceFile $file.FullName -DestinationFile $remoteFile -Credential $credential -UseSsl $useSsl
}

Write-Host "Upload complete."

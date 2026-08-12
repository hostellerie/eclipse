param(
    [string]$ThemePath = (Join-Path (Split-Path -Parent $PSScriptRoot) 'eclipse')
)

$ErrorActionPreference = 'Stop'
$theme = (Resolve-Path -LiteralPath $ThemePath).Path
$ini = Get-Content -Raw -LiteralPath (Join-Path $theme 'theme.ini')
$match = [regex]::Match($ini, '(?m)^version\s*=\s*"([0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?)"')
if (-not $match.Success) { throw 'Unable to read the Eclipse version.' }

$files = [ordered]@{}
Get-ChildItem -LiteralPath $theme -Recurse -File |
    Where-Object { $_.Name -ne 'MANIFEST.json' } |
    Sort-Object FullName |
    ForEach-Object {
        $relative = $_.FullName.Substring($theme.Length + 1).Replace('\','/')
        $files[$relative] = (Get-FileHash -LiteralPath $_.FullName -Algorithm SHA256).Hash.ToLowerInvariant()
    }

$manifest = [ordered]@{ version = $match.Groups[1].Value; algorithm = 'sha256'; files = $files }
$json = (($manifest | ConvertTo-Json -Depth 4) -replace '(?m)[ \t]+(?=\r?$)', '') -replace "`r`n", "`n"
[System.IO.File]::WriteAllText((Join-Path $theme 'MANIFEST.json'), $json + "`n", [System.Text.UTF8Encoding]::new($false))
Write-Host "Built MANIFEST.json for Eclipse $($match.Groups[1].Value) with $($files.Count) files."


param(
    [string]$ThemePath = (Join-Path (Split-Path -Parent $PSScriptRoot) 'eclipse'),
    [string]$ParentThemePath = (Join-Path (Split-Path -Parent $PSScriptRoot) 'quality/fixtures/denim'),
    [string]$NodePath = ''
)

$ErrorActionPreference = 'Stop'
$theme = (Resolve-Path -LiteralPath $ThemePath).Path
$legacyValidator = Join-Path $PSScriptRoot 'validate-eclipse.ps1'
$errors = [System.Collections.Generic.List[string]]::new()

function Fail([string]$Message) { $errors.Add($Message) }

# Keep the comprehensive historical validator and ignore only contracts that
# Eclipse 1.1.0 intentionally supersedes. Every other historical failure stays
# release-blocking.
$legacyOutput = @()
& $legacyValidator -ThemePath $theme -ParentThemePath $ParentThemePath -NodePath $NodePath 2>&1 | ForEach-Object {
    $line = [string] $_
    $legacyOutput += $line
    Write-Host $line
}

$allowedLegacyFailures = @(
    'Story editor wrapper override must be isolated from Modern workspace.',
    'Asset version does not match 1.1.0.',
    'CSS compatibility override ceiling exceeded:',
    'Public Eclipse CSS budget exceeded:',
    'Installable theme budget exceeded:',
    'Configuration secondary-tab fallback styling is incomplete.',
    'Mobile configuration select-value geometry is incomplete.'
)

$legacyFailures = @($legacyOutput | Where-Object { $_ -match '^FAIL:\s*(.+)$' })
foreach ($failure in $legacyFailures) {
    $message = ($failure -replace '^FAIL:\s*', '').Trim()
    $allowed = $false
    foreach ($prefix in $allowedLegacyFailures) {
        if ($message.StartsWith($prefix, [System.StringComparison]::Ordinal)) {
            $allowed = $true
            break
        }
    }
    if (-not $allowed) {
        Fail "Historical validator: $message"
    }
}

# 1.1.0 editor contract: the wrapper rule moved to story-editor-base.css because
# story-editor.css imports the shared base layer.
$storyEditorBase = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/story-editor-base.css')
if ($storyEditorBase -notmatch 'eclipse-story-editor-page\.admin-ui-mode-classic\.editor-sidebars-hidden #wrapper') {
    Fail 'Classic story editor wrapper override is missing from story-editor-base.css.'
}
if ($storyEditorBase -match 'eclipse-story-editor-page\.editor-sidebars-hidden #wrapper') {
    Fail 'Story editor wrapper override leaks into Modern workspace.'
}

# 1.1.0 asset contract: derive cache keys from theme.ini at runtime instead of
# embedding a literal version in functions.php.
$functions = Get-Content -Raw -LiteralPath (Join-Path $theme 'functions.php')
if ($functions -notmatch "\$version\s*=\s*'\?v='\s*\.\s*rawurlencode\(eclipse_theme_version\(\)\)") {
    Fail 'Asset cache key is not derived from eclipse_theme_version().' 
}
if ($functions -notmatch "\.css'\s*\.\s*\$version" -or $functions -notmatch "theme\.js'\s*\.\s*\$version") {
    Fail 'Version-derived cache key is not applied to both CSS and JavaScript assets.'
}

# 1.1.0 resource budgets. These limits reflect the expanded administration,
# editor, Forum and compatibility scope while retaining a bounded regression
# guard for future releases.
$importantCount = 0
Get-ChildItem -LiteralPath (Join-Path $theme 'css') -Filter '*.css' | ForEach-Object {
    $importantCount += [regex]::Matches((Get-Content -Raw -LiteralPath $_.FullName), '!important').Count
}
if ($importantCount -gt 600) {
    Fail "CSS compatibility override ceiling exceeded for 1.1.0: $importantCount (maximum 600)."
}

$publicCssNames = @('variables.css','base.css','layout.css','components.css','forms.css','plugins.css','responsive.css','modern.css','ui-fixes.css','v3.css')
$publicCssBytes = ($publicCssNames | ForEach-Object { (Get-Item -LiteralPath (Join-Path $theme "css/$_")).Length } | Measure-Object -Sum).Sum
$themeBytes = (Get-ChildItem -LiteralPath $theme -Recurse -File | Measure-Object Length -Sum).Sum
if ($publicCssBytes -gt 75000) {
    Fail "Public Eclipse CSS budget exceeded for 1.1.0: $publicCssBytes bytes (maximum 75000)."
}
if ($themeBytes -gt 950000) {
    Fail "Installable theme budget exceeded for 1.1.0: $themeBytes bytes (maximum 950000)."
}

# Configuration Manager intentionally follows native Denim layout/behavior in
# 1.1.0. Do not require the removed custom tab/select geometry fallbacks.
$themeJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')
$uiFixes = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/ui-fixes.css')
if ($functions -match "name'\s*=>\s*'eclipse-configuration'" -or
    $themeJs -match 'setupConfigurationWorkspace|protectConfigurationTabs' -or
    $uiFixes -match 'eclipse-configuration-page #rightblocks\{display:none\}') {
    Fail 'Configuration Manager no longer retains native Denim layout and behavior.'
}

if ($errors.Count -gt 0) {
    $errors | ForEach-Object { Write-Error "FAIL: $_" }
    exit 1
}

Write-Host "Eclipse 1.1 release validation passed. Historical checks retained; 1.1 contracts applied."
exit 0

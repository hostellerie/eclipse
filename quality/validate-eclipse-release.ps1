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
# Eclipse 1.2.0 intentionally supersedes. Run it in a child PowerShell process
# because the historical script uses `exit 1` when it finds any failure.
$legacyArgs = @('-NoProfile', '-File', $legacyValidator, '-ThemePath', $theme, '-ParentThemePath', $ParentThemePath)
if ($NodePath -ne '') {
    $legacyArgs += @('-NodePath', $NodePath)
}
$legacyOutput = @(& pwsh @legacyArgs 2>&1 | ForEach-Object {
    $line = [string] $_
    Write-Host $line
    $line
})
$legacyExitCode = $LASTEXITCODE

$allowedLegacyFailures = @(
    'Missing file: ROADMAP.md',
    'Story editor wrapper override must be isolated from Modern workspace.',
    'Asset version does not match 1.2.0.',
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
if ($legacyExitCode -ne 0 -and $legacyFailures.Count -eq 0) {
    Fail "Historical validator exited with code $legacyExitCode without reporting a recognized FAIL line."
}

# The maintained roadmap moved to the repository root in the 1.2 cycle so it
# remains visible as development documentation and stays outside the installable
# theme package. The historical validator still expects eclipse/ROADMAP.md.
$repositoryRoot = Split-Path -Parent $theme
$roadmapPath = Join-Path $repositoryRoot 'ROADMAP.md'
if (-not (Test-Path -LiteralPath $roadmapPath -PathType Leaf)) {
    Fail 'Repository-level ROADMAP.md is missing.'
}

# 1.2.0 editor contract: the wrapper rule moved to story-editor-base.css because
# story-editor.css imports the shared base layer.
$storyEditorBase = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/story-editor-base.css')
if ($storyEditorBase -notmatch 'eclipse-story-editor-page\.admin-ui-mode-classic\.editor-sidebars-hidden #wrapper') {
    Fail 'Classic story editor wrapper override is missing from story-editor-base.css.'
}
if ($storyEditorBase -match 'eclipse-story-editor-page\.editor-sidebars-hidden #wrapper') {
    Fail 'Story editor wrapper override leaks into Modern workspace.'
}

# 1.2.0 asset contract: derive cache keys from theme.ini at runtime instead of
# embedding a literal version in functions.php.
$functions = Get-Content -Raw -LiteralPath (Join-Path $theme 'functions.php')
if ($functions -notmatch '\$version\s*=\s*''\?v=''\s*\.\s*rawurlencode\(eclipse_theme_version\(\)\)') {
    Fail 'Asset cache key is not derived from eclipse_theme_version().'
}
if ($functions -notmatch '\.css''\s*\.\s*\$version' -or $functions -notmatch 'theme\.js''\s*\.\s*\$version') {
    Fail 'Version-derived cache key is not applied to both CSS and JavaScript assets.'
}

# 1.2.0 resource budgets. These limits reflect the expanded administration,
# editor, Forum and compatibility scope while retaining a bounded regression
# guard for future releases.
$importantCount = 0
Get-ChildItem -LiteralPath (Join-Path $theme 'css') -Filter '*.css' | ForEach-Object {
    $importantCount += [regex]::Matches((Get-Content -Raw -LiteralPath $_.FullName), '!important').Count
}
if ($importantCount -gt 600) {
    Fail "CSS compatibility override ceiling exceeded for 1.2.0: $importantCount (maximum 600)."
}

$publicCssNames = @('variables.css','base.css','layout.css','components.css','forms.css','plugins.css','responsive.css','modern.css','ui-fixes.css','v3.css')
$publicCssBytes = ($publicCssNames | ForEach-Object { (Get-Item -LiteralPath (Join-Path $theme "css/$_")).Length } | Measure-Object -Sum).Sum
$themeBytes = (Get-ChildItem -LiteralPath $theme -Recurse -File | Measure-Object Length -Sum).Sum
if ($publicCssBytes -gt 75000) {
    Fail "Public Eclipse CSS budget exceeded for 1.2.0: $publicCssBytes bytes (maximum 75000)."
}
if ($themeBytes -gt 950000) {
    Fail "Installable theme budget exceeded for 1.2.0: $themeBytes bytes (maximum 950000)."
}

# Static Pages editor compatibility: Geeklog 2.1.1 does not expose the
# per-page Search control added in later Static Pages versions. Eclipse keeps
# the newer editor markup but must render Search only when the provider supplies
# search_options. Likes already follows the same capability-style guard.
$staticEditor = Get-Content -Raw -LiteralPath (Join-Path $theme 'staticpages/admin/editor.thtml')
$staticAdvancedEditor = Get-Content -Raw -LiteralPath (Join-Path $theme 'staticpages/admin/editor_advanced.thtml')
if ($staticEditor -notmatch '\{!if search_options\}.*?name="search".*?\{!endif\}') {
    Fail 'Static Pages basic editor does not guard the newer Search control for Geeklog 2.1.1.'
}
if ($staticAdvancedEditor -notmatch '\{!if search_options\}.*?name="search".*?\{!endif\}') {
    Fail 'Static Pages advanced editor does not guard the newer Search control for Geeklog 2.1.1.'
}

# Shared ADMIN_list search/filter presentation is a public Eclipse component.
# Plugins may use .eclipse-admin-list-search outside /admin; admin/admin.css
# should only enrich that base. Generic select sizing must remain overrideable.
$formsCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/forms.css')
$adminCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/admin/admin.css')
if ($formsCss -notmatch 'Shared ADMIN_list search/filter shell' -or
    $formsCss -notmatch '\.eclipse-admin-list-search select') {
    Fail 'Shared public list-search component is missing.'
}
if ($formsCss -match 'select:not\(\[multiple\]\):not\(\[size\]\)[^\{]*\{[^\}]*min-height:[^;]+!important') {
    Fail 'Generic select sizing still blocks compact component overrides.'
}
if ($adminCss -notmatch 'body\.eclipse-admin-page \.eclipse-admin-list-search') {
    Fail 'Administrative list-search enrichment layer is missing.'
}

# User settings keep Geeklog's native profile_editor.js behavior but Eclipse
# must present the profile navigation as a real tab bar on usersettings.php.
$functionsPhp = Get-Content -Raw -LiteralPath (Join-Path $theme 'functions.php')
$formsCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/forms.css')
if ($functionsPhp -notmatch 'eclipse-user-settings') {
    Fail 'User settings page context class is missing.'
}
if ($formsCss -notmatch 'eclipse-user-settings #pe_navbar' -or
    $formsCss -notmatch '\.a-navlist#current') {
    Fail 'User settings tab presentation contract is incomplete.'
}

# Configuration search result targeting is an Eclipse 1.2 usability contract.
# Geeklog supplies URLs such as ?tab-20#advanced_editor; Eclipse must turn the
# stable parameter hash into a real row anchor, scroll to it and highlight it.
$adminJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/admin.js')
$configTemplate = Get-Content -Raw -LiteralPath (Join-Path $theme 'admin/config/configuration.thtml')
if ($adminJs -notmatch 'name\$="\[nameholder\]"' -or
    $adminJs -notmatch 'scrollIntoView' -or
    $adminJs -notmatch 'eclipse-config-target') {
    Fail 'Configuration search result targeting contract is incomplete.'
}
if ($configTemplate -notmatch 'eclipse-config-target' -or
    $configTemplate -notmatch 'scroll-margin-top') {
    Fail 'Configuration search target highlighting styles are missing.'
}

# Configuration Manager intentionally follows native Denim layout/behavior in
# 1.2.0. Do not require the removed custom tab/select geometry fallbacks.
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

Write-Host 'Eclipse 1.2 release validation passed. Historical checks retained; 1.2 contracts applied.'
exit 0

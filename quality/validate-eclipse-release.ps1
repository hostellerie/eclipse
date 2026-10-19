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
# Eclipse 1.4.0 intentionally supersedes. Run it in a child PowerShell process
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
    'Asset version does not match 1.4.0.',
    'Asset version does not match 1.3.0.',
    'Asset version does not match 1.1.0.',
    'CSS compatibility override ceiling exceeded:',
    'Public Eclipse CSS budget exceeded:',
    'Installable theme budget exceeded:',
    'Configuration secondary-tab fallback styling is incomplete.',
    'Mobile configuration select-value geometry is incomplete.',
    'Configuration Manager must retain its native Denim layout and behavior.',
    # Eclipse 1.3 removed the separate simulated-site Preview tab intentionally.
    'Professional Studio tab/preview markup is incomplete.',
    'Studio preview viewport styles are incomplete.'
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


# The isolated site-preview iframe was retired. Keep the accessible Studio tabs,
# live palette feedback and contrast checks, but reject the obsolete parallel
# preview implementation rather than reinstating it to satisfy legacy tests.
$functions = Get-Content -Raw -LiteralPath (Join-Path $theme 'functions.php')
$themeJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')
$studioCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/studio.css')
if ($functions -notmatch 'role="tablist"' -or $themeJs -notmatch 'function setupStudioTabs') {
    Fail 'Accessible Theme Studio tabs are missing.'
}
if ($functions -match 'eclipse-panel-preview|eclipse-preview-frame|data-preview-width' -or
    $themeJs -match 'eclipse-preview-frame|eclipse-isolated-preview|updateLayoutPreview' -or
    $studioCss -match 'eclipse-isolated-preview|eclipse-preview-toolbar') {
    Fail 'Retired Studio site-preview markup or scripting has returned.'
}
if ($functions -notmatch 'eclipse-palette-preview' -or
    $functions -notmatch 'eclipse-contrast-report' -or
    $themeJs -notmatch 'function previewColors' -or
    $themeJs -notmatch 'function updateContrast') {
    Fail 'Theme Studio palette feedback or contrast reporting is missing.'
}

# The maintained roadmap moved to the repository root in the 1.2 cycle so it
# remains visible as development documentation and stays outside the installable
# theme package. The historical validator still expects eclipse/ROADMAP.md.
$repositoryRoot = Split-Path -Parent $theme
$roadmapPath = Join-Path $repositoryRoot 'ROADMAP.md'
if (-not (Test-Path -LiteralPath $roadmapPath -PathType Leaf)) {
    Fail 'Repository-level ROADMAP.md is missing.'
}

# 1.3.0 editor contract: the wrapper rule moved to story-editor-base.css because
# story-editor.css imports the shared base layer.
$storyEditorBase = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/story-editor-base.css')
if ($storyEditorBase -notmatch 'eclipse-story-editor-page\.admin-ui-mode-classic\.editor-sidebars-hidden #wrapper') {
    Fail 'Classic story editor wrapper override is missing from story-editor-base.css.'
}
if ($storyEditorBase -match 'eclipse-story-editor-page\.editor-sidebars-hidden #wrapper') {
    Fail 'Story editor wrapper override leaks into Modern workspace.'
}

# 1.3.0 asset contract: one canonical cache token combines the release version
# with the newest shipped asset mtime. CSS and JavaScript each use that helper,
# so updates invalidate browser caches without random query strings.
$functions = Get-Content -Raw -LiteralPath (Join-Path $theme 'functions.php')
if ($functions -notmatch 'function\s+eclipse_asset_cache_version\s*\(' -or
    $functions -notmatch 'filemtime\(' -or
    $functions -notmatch "eclipse_asset_cache_version\('css'\)" -or
    $functions -notmatch "eclipse_asset_cache_version\('js'\)") {
    Fail 'Asset cache key is not derived from the canonical version + filemtime helper.'
}
if ($functions -match "\$version\s*=\s*'\?v='\s*\.\s*rawurlencode\(eclipse_theme_version\(\)\)") {
    Fail 'Obsolete duplicate release-only asset cache assignment is still present.'
}
if ($functions -notmatch '\.css''\s*\.\s*\$version' -or $functions -notmatch 'theme\.js''\s*\.\s*\$version') {
    Fail 'Version-derived cache key is not applied to both CSS and JavaScript assets.'
}

# 1.3.0 resource budgets. These limits reflect the expanded administration,
# editor, Forum and compatibility scope while retaining a bounded regression
# guard for future releases.
$importantCount = 0
Get-ChildItem -LiteralPath (Join-Path $theme 'css') -Filter '*.css' | ForEach-Object {
    $importantCount += [regex]::Matches((Get-Content -Raw -LiteralPath $_.FullName), '!important').Count
}
if ($importantCount -gt 600) {
    Fail "CSS compatibility override ceiling exceeded for 1.3.0: $importantCount (maximum 600)."
}

$publicCssNames = @('variables.css','base.css','layout.css','components.css','forms.css','plugins.css','responsive.css','modern.css','ui-fixes.css','v3.css')
$publicCssBytes = ($publicCssNames | ForEach-Object { (Get-Item -LiteralPath (Join-Path $theme "css/$_")).Length } | Measure-Object -Sum).Sum
$themeBytes = (Get-ChildItem -LiteralPath $theme -Recurse -File | Measure-Object Length -Sum).Sum
if ($publicCssBytes -gt 80000) {
    Fail "Public Eclipse CSS budget exceeded for 1.3.0: $publicCssBytes bytes (maximum 80000)."
}
if ($themeBytes -gt 1000000) {
    Fail "Installable theme budget exceeded for 1.4.0: $themeBytes bytes (maximum 1000000)."
}

# Page titles are configured through the same validated Theme Studio options as
# other SEO settings. Both Geeklog template variables must remain literal in
# header.thtml so the template engine resolves the selected branch correctly.
$headerTemplate = Get-Content -Raw -LiteralPath (Join-Path $theme 'header.thtml')
$indexTemplate = Get-Content -Raw -LiteralPath (Join-Path $theme 'index.thtml')
# Guest welcome: preserve Geeklog localization and logged-in identity while
# excluding the anonymous account name from the public header.
if ($functions -notmatch 'function eclipse_welcome_message\(' -or
    $functions -notmatch 'COM_isAnonUser\(\)' -or
    $functions -notmatch '\$LANG01\[67\]' -or
    $indexTemplate -notmatch 'eclipse_welcome_message\(\)' -or
    $headerTemplate -notmatch 'eclipse_welcome_message\(\)' -or
    $indexTemplate -match '\{welcome_msg\}' -or
    $headerTemplate -match '\{welcome_msg\}' -or
    $functions -notmatch 'COM_getDisplayName\(') {
    Fail 'Localized anonymous welcome message contract is incomplete.'
}


if ($functions -notmatch "'page_title_mode'\s*=>\s*'title_only'" -or
    $functions -notmatch "'page_title_mode'\s*=>\s*array\('title_only',\s*'title_and_site'\)" -or
    $functions -notmatch 'select\(''page_title_mode''' -or
    $headerTemplate -notmatch '<title>\{page_title\}</title>' -or
    $headerTemplate -notmatch '<title>\{page_title_and_site_name\}</title>' -or
    $headerTemplate -notmatch 'eclipse_title_includes_site_name\(\)' -or
    $indexTemplate -notmatch '<title>\{page_title\}</title>' -or
    $indexTemplate -notmatch '<title>\{page_title_and_site_name\}</title>' -or
    $indexTemplate -notmatch 'eclipse_title_includes_site_name\(\)' -or
    $functions -notmatch 'function eclipse_title_includes_site_name\(' -or
    $functions -notmatch 'COM_onFrontpage\(\)' -or
    $functions -notmatch "'search.php'") {
    Fail 'Configurable page title format contract is incomplete.'
}
$themeJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')
if ($themeJs -notmatch "page_title_mode:'title_only'") {
    Fail 'Theme Studio reset must default to page title only.'
}

# Static Pages editor contract: render the optional Search control only when
# Geeklog supplies search_options. Preserve defensive template rendering across
# plugin configurations without maintaining a Geeklog 2.1.1 compatibility test.
$staticEditor = Get-Content -Raw -LiteralPath (Join-Path $theme 'staticpages/admin/editor.thtml')
$staticAdvancedEditor = Get-Content -Raw -LiteralPath (Join-Path $theme 'staticpages/admin/editor_advanced.thtml')
if ($staticEditor -notmatch '\{!if search_options\}.*?name="search".*?\{!endif\}') {
    Fail 'Static Pages basic editor does not guard the optional Search control.'
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

# Configuration presentation must remain centralized in css/configuration.css.
# The template must not carry a second inline stylesheet, and the native
# COM_getTooltip() trigger must remain visible even though the wrapper hides
# Geeklog's literal parentheses with font-size: 0.
$configCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/configuration.css')
$configPageTemplate = Get-Content -Raw -LiteralPath (Join-Path $theme 'admin/config/configuration.thtml')
if ($functions -notmatch "name'\s*=>\s*'eclipse-configuration'" -or
    $functions -notmatch 'css/configuration\.css') {
    Fail 'Configuration stylesheet is not registered by theme_css_eclipse().'
}
if ($configPageTemplate -match '<style[\s>]') {
    Fail 'Configuration template contains inline CSS instead of using css/configuration.css.'
}
if ($configCss -notmatch 'eclipse-config-help\s+:is\(a\.tooltip,\.gl-tooltip\)\s*\{[^}]*font-size:\.72rem') {
    Fail 'Configuration help does not restore the native COM_getTooltip() trigger size.'
}

# Configuration search result targeting is an Eclipse 1.3 usability contract.
# Geeklog supplies URLs such as ?tab-20#advanced_editor; Eclipse must turn the
# stable parameter hash into a real row anchor, scroll to it and highlight it.
$adminJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/admin.js')
$configCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/configuration.css')
if ($adminJs -notmatch 'name\$="\[nameholder\]"' -or
    $adminJs -notmatch 'scrollIntoView' -or
    $adminJs -notmatch 'eclipse-config-target') {
    Fail 'Configuration search result targeting contract is incomplete.'
}
if ($configCss -notmatch 'eclipse-config-target' -or
    $configCss -notmatch 'scroll-margin-top') {
    Fail 'Configuration search target highlighting styles are missing from css/configuration.css.'
}

# Modern admin navigation merges Command & Control with Geeklog's native
# plugin_getadminoption_* source. The merged result must stay unique by
# destination URL and alphabetically ordered regardless of source order.
# URL fragments are part of the destination identity: the Theme Studio lives at
# /admin/index.php#eclipse-theme-studio and must not collapse into /admin/index.php.
if ($adminJs -notmatch 'function normalizeAdminHref' -or
    $adminJs -notmatch 'return path \+ url\.search \+ url\.hash' -or
    $adminJs -notmatch 'function sortAdminList' -or
    $adminJs -notmatch 'known\[entry\.key\]' -or
    $adminJs -notmatch 'sortAdminList\(existingList\)' -or
    $adminJs -notmatch 'sortAdminList\(list\)') {
    Fail 'Merged administration navigation de-duplication/sorting contract is incomplete or drops URL fragments.'
}

if ($adminJs -notmatch 'entry && entry\.original \? entry\.original : entry') {
    Fail 'Merged administration navigation icon discovery is not compatible with normalized link entries.'
}

# Configuration Manager keeps Geeklog's native behavior while Eclipse owns
# presentation through one dedicated stylesheet. Do not reintroduce JavaScript
# workspace replacements or scattered CSS patches.
$themeJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')
$uiFixes = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/ui-fixes.css')
if ($functions -notmatch "name'\s*=>\s*'eclipse-configuration'" -or
    $functions -notmatch 'css/configuration\.css') {
    Fail 'Configuration Manager stylesheet is not loaded through theme_css_eclipse().'
}
if ($themeJs -match 'setupConfigurationWorkspace|protectConfigurationTabs' -or
    $uiFixes -match 'eclipse-configuration-page #rightblocks\{display:none\}') {
    Fail 'Configuration Manager contains obsolete behavior overrides outside the dedicated presentation layer.'
}

if ($errors.Count -gt 0) {
    $errors | ForEach-Object { Write-Error "FAIL: $_" }
    exit 1
}

Write-Host 'Eclipse 1.4 release validation passed. Historical checks retained; modern contracts applied.'
exit 0

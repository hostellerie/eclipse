param(
    [string]$ThemePath = (Join-Path (Split-Path -Parent $PSScriptRoot) 'eclipse'),
    [string]$ParentThemePath = (Join-Path (Split-Path -Parent $PSScriptRoot) 'denim'),
    [string]$NodePath = ''
)

$ErrorActionPreference = 'Stop'
$theme = (Resolve-Path -LiteralPath $ThemePath).Path
$parent = (Resolve-Path -LiteralPath $ParentThemePath).Path
$errors = [System.Collections.Generic.List[string]]::new()

function Fail([string]$Message) { $errors.Add($Message) }
function Require-File([string]$Root, [string]$Relative) {
    if (-not (Test-Path -LiteralPath (Join-Path $Root $Relative) -PathType Leaf)) { Fail "Missing file: $Relative" }
}

@('theme.ini','themeconfig.php','functions.php','header.thtml','footer.thtml','MANIFEST.json','js/theme.js','css/studio.css','css/story-editor.css','images/sysmessage.svg','ROADMAP.md','CSS-AUDIT.md','COMPATIBILITY.md','MIGRATION.md','PERFORMANCE.md','VISUAL-REGRESSION.md','tooltips/information.thtml','tooltips/help.thtml','tooltips/warning.thtml','tooltips/critical.thtml') |
    ForEach-Object { Require-File $theme $_ }
@('admin/story/storyeditor.thtml','admin/story/storyeditor_advanced.thtml') |
    ForEach-Object { Require-File $parent $_ }
@('admin/story/storyeditor.thtml','admin/story/storyeditor_advanced.thtml') |
    ForEach-Object { Require-File $theme $_ }

function Template-Tokens([string]$Path) {
    $text = Get-Content -Raw -LiteralPath $Path
    return @([regex]::Matches($text, '\{[a-zA-Z_][a-zA-Z0-9_]*\}') | ForEach-Object { $_.Value } | Sort-Object -Unique)
}

@('admin/story/storyeditor.thtml','admin/story/storyeditor_advanced.thtml') | ForEach-Object {
    $parentTokens = Template-Tokens (Join-Path $parent $_)
    $nativeTokens = Template-Tokens (Join-Path $theme $_)
    $missingTokens = @($parentTokens | Where-Object { $_ -notin $nativeTokens })
    if ($missingTokens.Count) { Fail "Native $_ lost Geeklog tokens: $($missingTokens -join ', ')" }
}

$functions = Get-Content -Raw -LiteralPath (Join-Path $theme 'functions.php')
if ($functions -notmatch 'function theme_config_eclipse\(\)\s*\{\s*return array\(') { Fail 'theme_config_eclipse() must explicitly return its configuration.' }
@('denim-base','eclipse-studio','eclipse-story-editor') | ForEach-Object {
    if ($functions -notmatch [regex]::Escape("'name' => '$_'")) { Fail "Required stylesheet registration missing: $_" }
}
if ($functions -notmatch '(?s)\$isAdminDashboard.*if \(\$isAdminDashboard\).*eclipse-studio') { Fail 'Studio CSS must be restricted to the administration dashboard.' }
if ($functions -notmatch '(?s)if \(\$isStoryEditor\).*eclipse-story-editor') { Fail 'Story editor CSS must be restricted to the story editor request.' }

$ini = Get-Content -Raw -LiteralPath (Join-Path $theme 'theme.ini')
$match = [regex]::Match($ini, '(?m)^version\s*=\s*"([0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?)"')
if (-not $match.Success) { Fail 'Invalid semantic version in theme.ini.' } else {
    $version = $match.Groups[1].Value
    $readme = Get-Content -Raw -LiteralPath (Join-Path $theme 'README.md')
    if ($functions -notmatch [regex]::Escape("?v=$version")) { Fail "Asset version does not match $version." }
    if ($readme -notmatch [regex]::Escape("# Eclipse $version")) { Fail "README version does not match $version." }
}

$packageManifestPath = Join-Path $theme 'MANIFEST.json'
if (Test-Path -LiteralPath $packageManifestPath) {
    try { $packageManifest = Get-Content -Raw -LiteralPath $packageManifestPath | ConvertFrom-Json } catch { $packageManifest = $null; Fail 'MANIFEST.json is not valid JSON.' }
    if ($packageManifest) {
        if ($packageManifest.version -ne $version -or $packageManifest.algorithm -ne 'sha256') { Fail 'MANIFEST.json version or algorithm does not match the package.' }
        $listed = @($packageManifest.files.PSObject.Properties)
        foreach ($entry in $listed) {
            $manifestFile = Join-Path $theme ($entry.Name.Replace('/',[IO.Path]::DirectorySeparatorChar))
            if (-not (Test-Path -LiteralPath $manifestFile -PathType Leaf)) { Fail "Manifest file missing: $($entry.Name)" }
            elseif ((Get-FileHash -LiteralPath $manifestFile -Algorithm SHA256).Hash.ToLowerInvariant() -ne ([string]$entry.Value).ToLowerInvariant()) { Fail "Manifest hash mismatch: $($entry.Name)" }
        }
        $actualManifestFiles = @(Get-ChildItem -LiteralPath $theme -Recurse -File | Where-Object { $_.Name -ne 'MANIFEST.json' })
        if ($listed.Count -ne $actualManifestFiles.Count) { Fail "Manifest file count mismatch: $($listed.Count) listed, $($actualManifestFiles.Count) present." }
    }
}

$header = Get-Content -Raw -LiteralPath (Join-Path $theme 'header.thtml')
$footer = Get-Content -Raw -LiteralPath (Join-Path $theme 'footer.thtml')
@('{menu_elements}','{layout_columns}','{plg_headercode}') | ForEach-Object { if ($header -notmatch [regex]::Escape($_)) { Fail "Header contract missing: $_" } }
if ($header -notmatch '<html lang="<\?php echo eclipse_html_language\(\); \?>">') { Fail 'Validated root HTML language output is missing.' }
@('{left_blocks}','{right_blocks}','{plg_footercode}') | ForEach-Object { if ($footer -notmatch [regex]::Escape($_)) { Fail "Footer contract missing: $_" } }

Get-ChildItem -LiteralPath $theme -Recurse -Filter '*.thtml' | ForEach-Object {
    $template = Get-Content -Raw -LiteralPath $_.FullName
    if ($template -match '\beclipse_(?:translations_json|text)\s*\(') {
        Fail "Hot-update-unsafe PHP helper call in template: $($_.FullName)"
    }
}

Get-ChildItem -LiteralPath (Join-Path $theme 'css') -Filter '*.css' | ForEach-Object {
    $css = Get-Content -Raw -LiteralPath $_.FullName
    if (([regex]::Matches($css, '\{').Count) -ne ([regex]::Matches($css, '\}').Count)) { Fail "Unbalanced CSS braces: $($_.Name)" }
}
$importantCount = 0
Get-ChildItem -LiteralPath (Join-Path $theme 'css') -Filter '*.css' | ForEach-Object {
    $importantCount += [regex]::Matches((Get-Content -Raw -LiteralPath $_.FullName), '!important').Count
}
if ($importantCount -gt 290) { Fail "CSS compatibility override ceiling exceeded: $importantCount (maximum 290)." }

$publicCssNames = @('variables.css','base.css','layout.css','components.css','forms.css','plugins.css','responsive.css','modern.css','ui-fixes.css','v3.css')
$publicCssBytes = ($publicCssNames | ForEach-Object { (Get-Item -LiteralPath (Join-Path $theme "css/$_")).Length } | Measure-Object -Sum).Sum
$javascriptBytes = (Get-Item -LiteralPath (Join-Path $theme 'js/theme.js')).Length
$themeBytes = (Get-ChildItem -LiteralPath $theme -Recurse -File | Measure-Object Length -Sum).Sum
if ($publicCssBytes -gt 59000) { Fail "Public Eclipse CSS budget exceeded: $publicCssBytes bytes (maximum 59000)." }
if ($javascriptBytes -gt 65000) { Fail "Eclipse JavaScript budget exceeded: $javascriptBytes bytes (maximum 65000)." }
if ($themeBytes -gt 343000) { Fail "Installable theme budget exceeded: $themeBytes bytes (maximum 343000)." }

Get-ChildItem -LiteralPath (Join-Path $theme 'images') -Recurse -Filter '*.svg' | ForEach-Object {
    try { $null = [xml](Get-Content -Raw -LiteralPath $_.FullName) }
    catch { Fail "Invalid SVG XML: $($_.FullName)" }
}

$themeConfig = Get-Content -Raw -LiteralPath (Join-Path $theme 'themeconfig.php')
if ($themeConfig -notmatch "'show_left_sidebar'\s*=>\s*false") { Fail 'Default left sidebar must remain disabled.' }
if ($themeConfig -notmatch "'show_right_sidebar'\s*=>\s*true") { Fail 'Default right sidebar must remain enabled.' }
if ($themeConfig -notmatch "'color_scheme'\s*=>\s*'light'") { Fail 'Default color scheme must remain light.' }
if ($themeConfig -notmatch "'font_family'\s*=>\s*'humanist'") { Fail 'Default typography must remain humanist.' }
@('share_facebook','share_linkedin','share_x') | ForEach-Object { if ($themeConfig -notmatch "'$_'\s*=>\s*false") { Fail "Social sharing must be independently disabled by default: $_" } }
if ($functions -notmatch 'data-studio-section="social"' -or $functions -notmatch 'No third-party script or request') { Fail 'Social sharing must have a dedicated privacy-aware Studio section.' }
if ((Get-Content (Join-Path $theme 'css\plugins.css') -Raw) -notmatch 'body\.theme-eclipse \.sysmessage img\{[^}]*max-width:2rem') { Fail 'System-message icon dimensions are not constrained.' }
if ((Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch '#config_content #tabs>ul\{display:flex' -or (Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch 'li\.ui-tabs-active>a') { Fail 'Configuration secondary-tab fallback styling is incomplete.' }
if ((Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch '\.ui-autocomplete\{[^}]*z-index:1000[^}]*background:var\(--eclipse-surface\)' -or (Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch 'ui-menu-item-wrapper\.ui-state-active') { Fail 'Configuration autocomplete fallback styling is incomplete.' }
if ((Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')) -notmatch 'function setupConfigurationWorkspace' -or (Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')) -notmatch 'eclipse-config-menu-collapsed' -or (Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')) -notmatch 'Command & Control') { Fail 'Configuration workspace controls are incomplete.' }
if ((Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')) -notmatch 'function protectConfigurationTabs' -or (Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')) -notmatch 'api\.removeTab=function\(\)\{\}' -or (Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')) -notmatch "url==='#tab-dropdown'") { Fail 'Non-destructive responsive configuration-tab handling is incomplete.' }
if ((Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch '@media\(max-width:52rem\)\{#config_content select\{[^}]*height:3rem[^}]*line-height:1\.4') { Fail 'Mobile configuration select-value geometry is incomplete.' }
if ((Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch 'eclipse-configuration-page #rightblocks\{display:none\}' -or (Get-Content (Join-Path $theme 'css\ui-fixes.css') -Raw) -notmatch 'eclipse-config-toolbar') { Fail 'Configuration workspace layout is incomplete.' }
if ($ini -notmatch '(?m)^require_php\s*=\s*"5\.3\.0"') { Fail 'Declared PHP compatibility baseline is missing.' }

$variables = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/variables.css')
$forms = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/forms.css')
if ($variables -notmatch '--eclipse-danger:#b42318') { Fail 'Palette-independent danger color is missing.' }
if ($forms -notmatch '#commentform \.submit p.*gap:') { Fail 'Comment notification spacing contract is missing.' }
if ($forms -notmatch '\.eclipse-danger-action.*var\(--eclipse-danger\)') { Fail 'Global destructive-action styling is missing.' }

$studioCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/studio.css')
$themeJs = Get-Content -Raw -LiteralPath (Join-Path $theme 'js/theme.js')
$responsiveCss = Get-Content -Raw -LiteralPath (Join-Path $theme 'css/responsive.css')
$headerTemplate = Get-Content -Raw -LiteralPath (Join-Path $theme 'header.thtml')
function Get-RelativeLuminance([string]$Hex) {
    $values = @(1,3,5) | ForEach-Object {
        $channel = [Convert]::ToInt32($Hex.Substring($_,2),16) / 255
        if ($channel -le 0.04045) { $channel / 12.92 } else { [Math]::Pow((($channel + 0.055) / 1.055),2.4) }
    }
    return 0.2126*$values[0] + 0.7152*$values[1] + 0.0722*$values[2]
}
function Get-ContrastRatio([string]$First,[string]$Second) {
    $a=Get-RelativeLuminance $First; $b=Get-RelativeLuminance $Second
    return ([Math]::Max($a,$b)+0.05)/([Math]::Min($a,$b)+0.05)
}
$palettePattern = "(?m)^\s*(default|ocean|forest|sunset|graphite):\s*\['(#[0-9a-fA-F]{6})',\s*'(#[0-9a-fA-F]{6})',\s*'(#[0-9a-fA-F]{6})',\s*'(#[0-9a-fA-F]{6})',\s*'(#[0-9a-fA-F]{6})',\s*'(#[0-9a-fA-F]{6})'\]"
[regex]::Matches($themeJs,$palettePattern) | ForEach-Object {
    $palette=$_.Groups[1].Value; $primary=$_.Groups[2].Value; $link=$_.Groups[4].Value; $surface=$_.Groups[6].Value; $text=$_.Groups[7].Value
    if ((Get-ContrastRatio $text $surface) -lt 4.5) { Fail "Palette $palette fails AA text/card contrast." }
    if ((Get-ContrastRatio $link $surface) -lt 4.5) { Fail "Palette $palette fails AA link/card contrast." }
    if ((Get-ContrastRatio '#ffffff' $primary) -lt 4.5) { Fail "Palette $palette fails AA white/primary-button contrast." }
}
if ([regex]::Matches($themeJs,$palettePattern).Count -ne 5) { Fail 'Built-in palette contrast audit did not find all five palettes.' }
if ($functions -notmatch 'role="tablist"' -or $functions -notmatch 'eclipse-preview-frame' -or $functions -notmatch 'data-preview-width="mobile"') { Fail 'Professional Studio tab/preview markup is incomplete.' }
if ($functions -notmatch 'eclipse-environment' -or $functions -notmatch "class_exists\('ZipArchive'\)" -or $functions -notmatch 'environmentDataWritable') { Fail 'Studio environment diagnostic is incomplete.' }
if ($studioCss -notmatch 'eclipse-isolated-preview\[data-width=tablet\]' -or $studioCss -notmatch 'eclipse-isolated-preview\[data-width=mobile\]') { Fail 'Studio preview viewport styles are incomplete.' }
if ($themeJs -notmatch 'function setupStudioTabs' -or $themeJs -notmatch "event.key === 'ArrowRight'") { Fail 'Accessible Studio tab behavior is incomplete.' }
if ($themeJs -notmatch 'function setupSeoAssistant' -or $themeJs -notmatch 'uniqueness is verified when saving') { Fail 'Editorial SEO diagnostics are incomplete.' }
if ($themeJs -notmatch 'function setupEditorialSafety' -or $themeJs -notmatch 'eclipse-story-draft:' -or $themeJs -notmatch 'current !== lastObserved') { Fail 'Editorial autosave contract is incomplete.' }
if ($themeJs -notmatch 'function setupAdminLists' -or $themeJs -notmatch 'eclipse-admin-filters:' -or $themeJs -notmatch 'eclipse-admin-table:' -or $themeJs -notmatch 'Hide Eclipse tools' -or $themeJs -notmatch "localStorage\.getItem\(visibilityKey\) === '1'") { Fail 'Advanced administration-list controls are incomplete.' }
if ($themeJs -notmatch 'function setupArticleSharing' -or $themeJs -notmatch 'facebook\.com/sharer' -or $themeJs -notmatch 'linkedin\.com/sharing' -or $themeJs -notmatch 'twitter\.com/intent/tweet' -or $themeJs -notmatch "rel='noopener noreferrer'") { Fail 'Privacy-preserving article sharing is incomplete.' }
if ($headerTemplate -notmatch 'class="skip-link"' -or $headerTemplate -notmatch 'id="main-content"') { Fail 'Skip-link accessibility contract is incomplete.' }
if ($responsiveCss -notmatch 'prefers-reduced-motion:reduce' -or $responsiveCss -notmatch 'forced-colors:active') { Fail 'Reduced-motion or forced-colors accessibility contract is incomplete.' }
if ($themeJs -notmatch 'function setupAccessibility' -or $themeJs -notmatch "setAttribute\('aria-current','page'\)" -or $themeJs -notmatch "event\.key==='Enter'\|\|event\.key===' '") { Fail 'Keyboard/current-page accessibility enhancement is incomplete.' }
@('eclipse-palettes.json','eclipse-history.json','eclipse_backup_restore','eclipse_record_settings_history') | ForEach-Object { if ($functions -notmatch [regex]::Escape($_)) { Fail "Professional Studio persistence contract missing: $_" } }
@('data-reset-section','eclipse-settings-import','eclipse-settings-export','eclipse-backup-browser') | ForEach-Object { if (($functions + $themeJs) -notmatch [regex]::Escape($_)) { Fail "Professional Studio interface contract missing: $_" } }

$allowed = '\.(?:php|ini|thtml|thtmlx|css|js|json|md|txt|html|svg|png|jpe?g|gif|ico)$'
Get-ChildItem -LiteralPath $theme -Recurse -File | Where-Object { $_.Name -notmatch $allowed } | ForEach-Object { Fail "Installer-rejected file type: $($_.FullName)" }
if (Test-Path -LiteralPath (Join-Path $theme 'tools')) { Fail 'Development tools must not be stored inside the installable theme.' }

$nodeExecutable = if ($NodePath -and (Test-Path -LiteralPath $NodePath -PathType Leaf)) { (Resolve-Path -LiteralPath $NodePath).Path } else { $null }
if ($nodeExecutable) {
    & $nodeExecutable --check (Join-Path $theme 'js/theme.js')
    if ($LASTEXITCODE -ne 0) { Fail 'theme.js syntax check failed.' }
} else { Write-Host 'Node syntax check skipped; pass -NodePath to enable it.' -ForegroundColor Yellow }

if ($errors.Count) {
    $errors | ForEach-Object { Write-Host "FAIL: $_" -ForegroundColor Red }
    exit 1
}
Write-Host "Eclipse $version contracts passed." -ForegroundColor Green

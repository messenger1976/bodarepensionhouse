# Build a debug APK for sideloading / testing.
# Requirements: Node.js, Android SDK (ANDROID_HOME), JDK 21+

$ErrorActionPreference = 'Stop'
$MobileRoot = $PSScriptRoot
$AndroidRoot = Join-Path $MobileRoot 'android'
$OutputDir = Join-Path $MobileRoot 'dist'

function Resolve-JavaHome {
    $candidates = @(
        (Join-Path $MobileRoot '.tools\jdk-21'),
        'C:\Program Files\Microsoft\jdk-21.0.12.101-hotspot',
        'C:\Program Files\Microsoft\jdk-21.0.12.1-hotspot',
        $env:JAVA_HOME
    ) | Where-Object { $_ -and (Test-Path (Join-Path $_ 'bin\java.exe')) }

    if ($candidates) {
        return @($candidates)[0]
    }

    $found = Get-ChildItem (Join-Path $MobileRoot '.tools') -Filter 'java.exe' -Recurse -ErrorAction SilentlyContinue |
        Select-Object -First 1
    if ($found) {
        return (Split-Path (Split-Path $found.FullName))
    }

    throw 'JDK 21+ not found. Install Microsoft OpenJDK 21 or extract it under mobile/.tools/.'
}

if (-not $env:ANDROID_HOME) {
    $defaultSdk = Join-Path $env:LOCALAPPDATA 'Android\Sdk'
    if (Test-Path $defaultSdk) {
        $env:ANDROID_HOME = $defaultSdk
    } else {
        throw 'ANDROID_HOME is not set and the default Android SDK path was not found.'
    }
}

$env:JAVA_HOME = Resolve-JavaHome
$env:PATH = "$env:JAVA_HOME\bin;$env:ANDROID_HOME\platform-tools;$env:PATH"

$gradlePropsPath = Join-Path $AndroidRoot 'gradle.properties'
$javaHomeEscaped = ($env:JAVA_HOME -replace '\\', '\\\\')
$gradleLines = Get-Content $gradlePropsPath | Where-Object { $_ -notmatch '^org\.gradle\.java\.home=' }
$gradleLines += "org.gradle.java.home=$javaHomeEscaped"
Set-Content -Path $gradlePropsPath -Value $gradleLines -Encoding ASCII

Push-Location $MobileRoot
try {
    npm install
    npx cap sync android
    $googleServices = Join-Path $AndroidRoot 'app\google-services.json'
    if (-not (Test-Path $googleServices)) {
        Write-Host ""
        Write-Host "Note: google-services.json not found in android/app/." -ForegroundColor Yellow
        Write-Host "Push notifications will crash if enabled without it. See mobile/FIREBASE_SETUP.md" -ForegroundColor Yellow
    }
    Push-Location $AndroidRoot
    & .\gradlew.bat assembleDebug --no-daemon
    Pop-Location

    $apk = Join-Path $AndroidRoot 'app\build\outputs\apk\debug\app-debug.apk'
    if (-not (Test-Path $apk)) {
        throw "APK not found at $apk"
    }

    New-Item -ItemType Directory -Force -Path $OutputDir | Out-Null
    $dest = Join-Path $OutputDir 'bodare-pension-house-debug.apk'
    Copy-Item $apk $dest -Force
    Write-Host ""
    Write-Host "Debug APK ready:" -ForegroundColor Green
    Write-Host $dest
} finally {
    Pop-Location
}

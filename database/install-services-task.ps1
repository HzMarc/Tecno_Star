param([switch]$Apply, [switch]$Verify)
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$php = 'C:\xampp\php\php.exe'
$phpWindowless = 'C:\xampp\php\php-win.exe'
$worker = Join-Path $PSScriptRoot 'process-services.php'
if (-not (Test-Path -LiteralPath $php -PathType Leaf)) { throw 'No se encuentra PHP de XAMPP.' }
if (-not (Test-Path -LiteralPath $phpWindowless -PathType Leaf)) { throw 'No se encuentra php-win.exe para ejecutar el mantenimiento sin consola.' }
$digest = [Security.Cryptography.SHA256]::Create()
try { $tag = ([BitConverter]::ToString($digest.ComputeHash([Text.Encoding]::UTF8.GetBytes($projectRoot.ToLowerInvariant())))).Replace('-', '').Substring(0, 8) }
finally { $digest.Dispose() }
$taskName = 'TecnoStar-Services-' + $tag
$command = "& '" + $php.Replace("'", "''") + "' '" + $worker.Replace("'", "''") + "' *> `$null; exit `$LASTEXITCODE"
$encoded = [Convert]::ToBase64String([Text.Encoding]::Unicode.GetBytes($command))
$legacyArguments = '-NoLogo -NoProfile -NonInteractive -WindowStyle Hidden -EncodedCommand ' + $encoded
$arguments = '"' + $worker + '"'
$existing = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
$matchesCurrent = $existing -and $existing.Actions.Count -eq 1 -and $existing.Actions.Execute -eq $phpWindowless -and $existing.Actions.Arguments -eq $arguments -and $existing.Actions.WorkingDirectory -eq $projectRoot
$matchesLegacy = $existing -and $existing.Actions.Count -eq 1 -and $existing.Actions.Execute -eq "$env:SystemRoot\System32\WindowsPowerShell\v1.0\powershell.exe" -and $existing.Actions.Arguments -eq $legacyArguments -and $existing.Actions.WorkingDirectory -eq $projectRoot
if ($Verify) {
    if (-not $existing) { throw 'No existe la tarea de mantenimiento de servicios.' }
    if (-not $matchesCurrent) { throw 'La tarea no utiliza el ejecutor sin consola de este proyecto. Usa -Apply para actualizar una tarea anterior reconocida.' }
    $info = Get-ScheduledTaskInfo -TaskName $taskName
    Write-Output ('Tarea: ' + $taskName + '; estado: ' + $existing.State + '; ultimo resultado: ' + $info.LastTaskResult)
    exit 0
}
if (-not $Apply) {
    Write-Output ('Simulacion: procesar correos y retencion de archivos cada minuto mientras el usuario tenga sesion abierta. Tarea: ' + $taskName + '. Usa -Apply para instalar.')
    exit 0
}
if ($existing -and -not ($matchesCurrent -or $matchesLegacy)) { throw 'Ya existe una tarea diferente con ese nombre. No se reemplaza.' }
$identity = [Security.Principal.WindowsIdentity]::GetCurrent().Name
$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
$action = New-ScheduledTaskAction -Execute $phpWindowless -Argument $arguments -WorkingDirectory $projectRoot
$trigger = New-ScheduledTaskTrigger -Once -At ((Get-Date).AddMinutes(1)) -RepetitionInterval (New-TimeSpan -Minutes 1)
$settings = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew -ExecutionTimeLimit (New-TimeSpan -Minutes 10) -StartWhenAvailable -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries
$definition = New-ScheduledTask -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description ('Cola de correo y archivos privados de servicios de Tecno Star: ' + $projectRoot)
Register-ScheduledTask -TaskName $taskName -InputObject $definition -Force | Out-Null
Write-Output ('Mantenimiento de servicios instalado: ' + $taskName + '. No almacena credenciales ni crea cuentas; se ejecuta con permisos limitados y ventana oculta.')

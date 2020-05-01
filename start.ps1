Param (
  [Parameter(Mandatory=$False)] [Switch] $ForceBuild = $False
)

If (-Not (Test-Path -Path "docker-compose.yml")) {
  Throw "YAML file not found; execute script in root directory"
}
If (-Not $(Get-Command "docker-compose" -ErrorAction SilentlyContinue)) {
  Throw "Download and install Docker: https://download.docker.com/win/stable/Docker%20Desktop%20Installer.exe"
}

$process = Start-Process -FilePath docker-compose -ArgumentList down -PassThru -Wait -NoNewWindow
If ($process.ExitCode -ne 0) {
  Throw "Unable to terminate Docker service for Expensify"
}
If ($ForceBuild) {
  $process = Start-Process -FilePath docker-compose -ArgumentList build -PassThru -Wait -NoNewWindow
  If ($process.ExitCode -ne 0) {
    Throw "Unable to build Docker service for Expensify"
  }
}
$process = Start-Process -FilePath docker-compose -ArgumentList "up", "--remove-orphans", "-d" -PassThru -Wait -NoNewWindow
If ($process.ExitCode -ne 0) {
  Throw "Unable to start Docker service for Expensify"
}

$process = Start-Process -FilePath docker -ArgumentList "exec", "php-fpm", "sh", "-c", "`"composer install`"" -PassThru -Wait -NoNewWindow
If ($process.ExitCode -ne 0) {
  Throw "Unable to install Expensify PHP bindings and other required dependencies"
}

Start-Process "http://localhost:32765"

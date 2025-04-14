<?php

namespace App\Services;

use App\Models\Server;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class ServerManagerService
{
    protected string $supervisorConfDir;
    protected string $minecraftUser;

    public function __construct()
    {
        $this->minecraftUser = config('craft-deck.user');
        $this->supervisorConfDir = config('craft-deck.supervisor_config');
    }

    public function createServer(Server $server, string $jarFile): void
    {
        $this->generateSupervisorConfig($server, $jarFile);
        $this->reloadSupervisor();
    }

    public function startServer(string $serverName): \Illuminate\Contracts\Process\ProcessResult
    {
        return Process::run("sudo supervisorctl start {$serverName}");
    }

    public function stopServer(string $serverName): \Illuminate\Contracts\Process\ProcessResult
    {
        return Process::run("sudo supervisorctl stop {$serverName}");
    }

    public function restartServer(string $serverName): \Illuminate\Contracts\Process\ProcessResult
    {
        return Process::run("sudo supervisorctl restart {$serverName}");
    }

    public function serverStatus(string $serverName): \Illuminate\Contracts\Process\ProcessResult
    {
        return Process::run("sudo supervisorctl status {$serverName}");
    }

    public function deleteServer(string $serverName): void
    {
        $this->stopServer($serverName);

        $configPath = "{$this->supervisorConfDir}/{$serverName}.conf";
        if (File::exists($configPath)) {
            Process::run("sudo rm {$configPath}");
        }

        $this->reloadSupervisor();
    }

    protected function generateSupervisorConfig(Server $server, string $jarFile): void
    {
        // Ensure the supervisor configuration directory exists
        if (!File::exists($this->supervisorConfDir)) {
            $result = Process::run("sudo -n mkdir -p {$this->supervisorConfDir}");

            if(!$result->successful()) {
                info($result->errorOutput());
                throw new \Exception($result->errorOutput());
            }
        }

        $config = <<<EOL
[program:{$server->name}]
directory={$server->path}
command=java -Xmx{$server->ram_mb}M -Xms{$server->ram_mb}M -jar {$jarFile} nogui
autostart=true
autorestart=true
user={$this->minecraftUser}
stdout_logfile={$server->path}/server.log
stderr_logfile={$server->path}/server-error.log
EOL;

        $tempFile = storage_path("app/{$server->name}.conf");
        File::put($tempFile, $config);

        // Move it into place with sudo
        $result = Process::run("sudo -n mv {$tempFile} {$this->supervisorConfDir}/{$server->name}.conf");

        if(!$result->successful()) {
            throw new \Exception($result->errorOutput());
        }
    }

    protected function reloadSupervisor(): void
    {
        Process::run('sudo supervisorctl reread');
        Process::run('sudo supervisorctl update');
    }

    public function provisionServerFiles(Server $server, string $jarPath): string
    {
        $serverDirectory = $server->path;

        // Create the server directory
        Process::run("sudo mkdir -p {$serverDirectory}");

        // Copy the JAR file into the server directory
        $jarFileName = basename($jarPath);
        $serverJarPath = "{$serverDirectory}/{$jarFileName}";
        Process::run("sudo cp {$jarPath} {$serverJarPath}");

        // Set ownership
        Process::run("sudo chown -R {$this->minecraftUser}:{$this->minecraftUser} {$serverDirectory}");

        // Accept EULA
        $eulaPath = "{$serverDirectory}/eula.txt";
        Process::run("echo 'eula=true' | sudo tee {$eulaPath}");
        Process::run("sudo chown {$this->minecraftUser}:{$this->minecraftUser} {$eulaPath}");

        return $serverJarPath; // return path to the jar INSIDE the server directory
    }

}

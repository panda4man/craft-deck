<?php

namespace App\Filament\Resources\ServerResource\Pages;

use App\Filament\Resources\ServerResource;
use App\Models\Server;
use App\Services\JarService;
use App\Services\ServerManagerService;
use Filament\Resources\Pages\CreateRecord;

class CreateServer extends CreateRecord
{
    protected static string $resource = ServerResource::class;

    protected function afterCreate(): void
    {
        /** @var Server $record */
        $record = $this->record;

        // Download the JAR
        $jarDownloader = new JarService;
        $jarPath = $jarDownloader->downloadVanilla($record->version);

        // Provision server files (make folder, copy jar, set perms, write eula.txt)
        $serverManager = new ServerManagerService;
        $serverJarPath = $serverManager->provisionServerFiles(
            server: $record,
            jarPath: $jarPath
        );

        // Create the supervisor config
        $serverManager->createServer(
            server: $record,
            jarFile: basename($serverJarPath), // Just the JAR file name
        );
    }
}

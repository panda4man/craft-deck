<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class JarService
{
    protected string $versionManifestUrl = 'https://piston-meta.mojang.com/mc/game/version_manifest_v2.json';

    /**
     * Fetch all available Vanilla Minecraft versions (cached for 1 hour).
     */
    public function fetchAvailableVersions(): array
    {
        return Cache::remember('minecraft_versions', 3600, function () {
            $response = Http::timeout(30)->get($this->versionManifestUrl);

            if ($response->failed()) {
                throw new \Exception('Failed to fetch available Minecraft versions.');
            }

            $data = $response->json();

            return collect($data['versions'] ?? [])
                ->pluck('id', 'id')
                ->toArray();
        });
    }

    /**
     * Download a Vanilla Minecraft server jar for a specific version.
     */
    public function downloadVanilla(string $version): string
    {
        // Fetch version manifest (cached fetch)
        $manifest = Cache::remember('minecraft_version_manifest', 3600, function () {
            $response = Http::timeout(30)->get($this->versionManifestUrl);

            if ($response->failed()) {
                throw new \Exception('Failed to fetch Minecraft version manifest.');
            }

            return $response->json();
        });

        // Find the URL for the specific version
        $versionData = collect($manifest['versions'] ?? [])
            ->firstWhere('id', $version);

        if (!$versionData) {
            throw new \Exception("Version {$version} not found in manifest.");
        }

        $versionJsonUrl = $versionData['url'];

        // Fetch detailed version data (no caching for this step to ensure latest info)
        $versionDetail = Http::timeout(30)->get($versionJsonUrl)->json();

        $serverDownloadUrl = $versionDetail['downloads']['server']['url'] ?? null;

        if (!$serverDownloadUrl) {
            throw new \Exception("No server jar download found for version {$version}.");
        }

        $jarDisk = config('craft-deck.jars.disk');
        $jarPath = config('craft-deck.jars.path');

        $savePath = Storage::disk($jarDisk)->path(
            config('craft-deck.jars.path') . "/vanilla-{$version}.jar"
        );

        // Ensure the directory exists
        Storage::disk($jarDisk)->makeDirectory($jarPath);

        // Download and save the jar
        Http::timeout(300)
            ->withOptions(['sink' => $savePath])
            ->get($serverDownloadUrl);

        return $savePath;
    }
}

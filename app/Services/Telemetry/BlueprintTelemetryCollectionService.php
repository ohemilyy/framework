<?php

namespace Pterodactyl\Services\Telemetry;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Pterodactyl\BlueprintFramework\Services\PlaceholderService\BlueprintPlaceholderService;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Console\BlueprintConsoleLibrary as BlueprintExtensionLibrary;

class BlueprintTelemetryCollectionService
{
  /**
   * BlueprintTelemetryCollectionService constructor.
   */
  public function __construct(
    private BlueprintExtensionLibrary $blueprint,
    private BlueprintPlaceholderService $placeholderService,
  ) {
  }

  /**
   * Collects telemetry data and sends it to the Blueprint Telemetry Service.
   */
  public function __invoke(): void
  {
    try {
      $data = $this->collect();
    } catch (\Exception) {
      return;
    }

    Http::post($this->placeholderService->api_url() . '/api/telemetry', $data);
  }

  /**
   * Collects telemetry data and returns it as an array.
   *
   * @throws \Pterodactyl\Exceptions\Model\DataValidationException
   */
  public function collect(): array
  {
    $uuid = $this->blueprint->dbGet('blueprint', 'uuid');
    if (is_null($uuid)) {
      $uuid = Uuid::uuid4()->toString();
      $this->blueprint->dbSet('blueprint', 'uuid', $uuid);
    }

    return [
      'id' => $uuid,
      'telemetry_version' => 1,

      'blueprint' => [
        'version' => $this->placeholderService->version(),
        'extensions' => $this->blueprint->extensions()->toArray(),
        'developer' => $this->blueprint->dbGet('blueprint', 'developer', 'false') === "true",
        'docker' => file_exists('/.dockerenv'),
      ],

      'panel' => [
        'version' => config('app.version'),
        'phpVersion' => phpversion(),

        'drivers' => [
          'backup' => [
            'type' => config('backups.default'),
          ],

          'cache' => [
            'type' => config('cache.default'),
          ],

          'database' => [
            'type' => config('database.default'),
            'version' => DB::getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
          ],
        ],
      ],
    ];
  }
}
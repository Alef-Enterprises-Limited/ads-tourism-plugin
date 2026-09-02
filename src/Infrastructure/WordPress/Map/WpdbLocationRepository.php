<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRole;
use InvalidArgumentException;
use stdClass;
use wpdb;

final readonly class WpdbLocationRepository implements LocationRepository
{
    public function __construct(private wpdb $database) {}

    public function tableName(): string
    {
        return $this->database->prefix . 'ads_tourism_locations';
    }

    public function replaceForEntity(int $entityPostId, array $locations): void
    {
        $this->database->query('START TRANSACTION');

        try {
            $deleted = $this->database->query($this->database->prepare(
                "DELETE FROM {$this->tableName()} WHERE entity_post_id = %d",
                $entityPostId,
            ));

            if ($deleted === false) {
                throw new \RuntimeException('The tourism locations could not be replaced.');
            }

            $now = gmdate('Y-m-d H:i:s');

            foreach ($locations as $location) {
                $inserted = $this->database->insert($this->tableName(), [
                    'entity_post_id' => $entityPostId,
                    'label' => $location->label,
                    'location_role' => $location->role->value,
                    'latitude' => $location->coordinates->latitude,
                    'longitude' => $location->coordinates->longitude,
                    'is_primary' => $location->isPrimary ? 1 : 0,
                    'show_on_map' => $location->showOnMap ? 1 : 0,
                    'sort_order' => $location->sortOrder,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($inserted === false) {
                    throw new \RuntimeException('A tourism location could not be saved.');
                }
            }

            if ($this->database->query('COMMIT') === false) {
                throw new \RuntimeException('The tourism locations could not be committed.');
            }
        } catch (\Throwable $exception) {
            $this->database->query('ROLLBACK');

            throw $exception;
        }
    }

    public function findForEntity(int $entityPostId, bool $mapOnly = false): array
    {
        $mapClause = $mapOnly ? ' AND show_on_map = 1' : '';
        $query = $this->database->prepare(
            "SELECT label, location_role, latitude, longitude, is_primary, show_on_map, sort_order
            FROM {$this->tableName()}
            WHERE entity_post_id = %d{$mapClause}
            ORDER BY sort_order ASC, id ASC",
            $entityPostId,
        );
        $rows = $this->database->get_results($query);

        if (!is_array($rows)) {
            return [];
        }

        $locations = [];

        foreach ($rows as $row) {
            if (!$row instanceof stdClass) {
                continue;
            }

            $role = LocationRole::tryFrom((string) ($row->location_role ?? ''));

            if ($role === null || !is_numeric($row->latitude ?? null) || !is_numeric($row->longitude ?? null)) {
                continue;
            }

            try {
                $locations[] = new LocationPoint(
                    $entityPostId,
                    new Coordinates((float) $row->latitude, (float) $row->longitude),
                    sanitize_text_field((string) ($row->label ?? '')),
                    $role,
                    (bool) ($row->is_primary ?? false),
                    (bool) ($row->show_on_map ?? true),
                    max(0, (int) ($row->sort_order ?? 0)),
                );
            } catch (InvalidArgumentException) {
                continue;
            }
        }

        return $locations;
    }

    public function deleteForEntity(int $entityPostId): int
    {
        $deleted = $this->database->query($this->database->prepare(
            "DELETE FROM {$this->tableName()} WHERE entity_post_id = %d",
            $entityPostId,
        ));

        return $deleted === false ? 0 : $deleted;
    }
}

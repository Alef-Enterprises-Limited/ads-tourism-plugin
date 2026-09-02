<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Location;

use AlefDigitalSolutions\ADSTourism\Application\Location\LocationService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use InvalidArgumentException;
use WP_Post;

final readonly class LocationRestFields
{
    public const FIELD_NAME = 'ads_tourism_locations';

    public function __construct(private LocationService $locations) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            register_rest_field($contentType->value, self::FIELD_NAME, [
                'get_callback' => [$this, 'get'],
                'update_callback' => [$this, 'update'],
                'schema' => [
                    'description' => __('Repeatable tourism record locations.', 'ads-tourism'),
                    'type' => 'array',
                    'context' => ['view', 'edit'],
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'role' => ['type' => 'string'],
                            'latitude' => ['type' => 'number'],
                            'longitude' => ['type' => 'number'],
                            'is_primary' => ['type' => 'boolean'],
                            'show_on_map' => ['type' => 'boolean'],
                            'sort_order' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * @param array<string, mixed>|WP_Post $post
     * @return list<array<string, bool|float|int|string>>
     */
    public function get(array|WP_Post $post): array
    {
        $postId = $post instanceof WP_Post ? $post->ID : (int) ($post['id'] ?? 0);

        return array_map(
            static fn(LocationPoint $location): array => $location->toArray(),
            $this->locations->find($postId),
        );
    }

    /** @param mixed $value */
    public function update(mixed $value, WP_Post $post): bool
    {
        if (!current_user_can('edit_post', $post->ID)) {
            throw new InvalidArgumentException('You do not have permission to edit this tourism record.');
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('Tourism locations must be an array.');
        }

        $values = [];

        foreach ($value as $location) {
            if (!is_array($location)) {
                throw new InvalidArgumentException('Each tourism location must be an object.');
            }

            $values[] = $location;
        }

        $this->locations->replaceFromArray($post->ID, $values);

        return true;
    }
}

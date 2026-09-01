<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual;

use AlefDigitalSolutions\ADSTourism\Application\Multilingual\TranslationAdapterInterface;

final readonly class TranslationResolver
{
    public function __construct(
        private TranslationAdapterInterface $defaultAdapter,
        private MultilingualSettings $settings,
    ) {}

    public function adapter(): TranslationAdapterInterface
    {
        $adapter = apply_filters('ads_tourism_translation_adapter', $this->defaultAdapter);

        return $adapter instanceof TranslationAdapterInterface ? $adapter : $this->defaultAdapter;
    }

    public function postId(int $postId, string $postType): ?int
    {
        $adapter = $this->adapter();

        if (!$adapter->isAvailable()) {
            return $postId;
        }

        return $adapter->translatedPostId($postId, $postType)
            ?? ($this->settings->fallbackToOriginal() ? $postId : null);
    }

    public function termId(int $termId, string $taxonomy): ?int
    {
        $adapter = $this->adapter();

        if (!$adapter->isAvailable()) {
            return $termId;
        }

        return $adapter->translatedTermId($termId, $taxonomy)
            ?? ($this->settings->fallbackToOriginal() ? $termId : null);
    }
}

<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Multilingual;

interface TranslationAdapterInterface
{
    public function key(): string;

    public function isAvailable(): bool;

    public function currentLanguage(): string;

    public function translatedPostId(int $postId, string $postType): ?int;

    public function translatedTermId(int $termId, string $taxonomy): ?int;
}

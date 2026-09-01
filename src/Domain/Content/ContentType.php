<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Content;

enum ContentType: string
{
    case PLACE = 'ads_place';
    case ACTIVITY = 'ads_activity';
    case STAY = 'ads_stay';
    case OPERATOR = 'ads_operator';
    case PACKAGE = 'ads_package';

    public function isHierarchical(): bool
    {
        return $this === self::PLACE;
    }

    public function rewriteBase(): string
    {
        return match ($this) {
            self::PLACE => 'places',
            self::ACTIVITY => 'things-to-do',
            self::STAY => 'places-to-stay',
            self::OPERATOR => 'tour-operators',
            self::PACKAGE => 'packages',
        };
    }

    /**
     * @return list<string>
     */
    public function supportedEditorFeatures(): array
    {
        $features = [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'revisions',
            'custom-fields',
            'author',
        ];

        if ($this->isHierarchical()) {
            $features[] = 'page-attributes';
        }

        return $features;
    }
}

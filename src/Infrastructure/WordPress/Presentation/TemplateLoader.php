<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Application\Presentation\TemplateCandidateResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Presentation\TemplateKind;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;

final readonly class TemplateLoader
{
    public function __construct(
        private string $pluginFile,
        private TemplateCandidateResolver $candidates,
    ) {}

    public function register(): void
    {
        add_filter('single_template', [$this, 'single'], 20);
        add_filter('archive_template', [$this, 'archive'], 20);
        add_filter('taxonomy_template', [$this, 'taxonomy'], 20);
    }

    public function single(string $selectedTemplate): string
    {
        $contentType = ContentType::tryFrom((string) get_post_type());

        return $contentType === null
            ? $selectedTemplate
            : $this->resolve(TemplateKind::SINGLE, $contentType->value, $selectedTemplate);
    }

    public function archive(string $selectedTemplate): string
    {
        $postType = get_query_var('post_type');
        $postType = is_array($postType) ? (string) reset($postType) : (string) $postType;
        $contentType = ContentType::tryFrom($postType);

        return $contentType === null
            ? $selectedTemplate
            : $this->resolve(TemplateKind::ARCHIVE, $contentType->value, $selectedTemplate);
    }

    public function taxonomy(string $selectedTemplate): string
    {
        $taxonomy = TourismTaxonomy::tryFrom((string) get_query_var('taxonomy'));

        return $taxonomy === null
            ? $selectedTemplate
            : $this->resolve(TemplateKind::TAXONOMY, $taxonomy->value, $selectedTemplate);
    }

    private function resolve(TemplateKind $kind, string $objectName, string $selectedTemplate): string
    {
        $selectedName = basename($selectedTemplate, '.php');

        if (str_starts_with($selectedName, $kind->value . '-' . $objectName)) {
            return $selectedTemplate;
        }

        $builderManaged = (bool) apply_filters(
            'ads_tourism_template_is_builder_managed',
            false,
            $selectedTemplate,
            $kind->value,
            $objectName,
        );

        if ($builderManaged) {
            return $selectedTemplate;
        }

        $candidates = $this->candidates->resolve($kind, $objectName);
        $filteredCandidates = apply_filters(
            'ads_tourism_template_candidates',
            $candidates,
            $kind->value,
            $objectName,
        );
        $themeTemplate = locate_template(
            is_array($filteredCandidates) ? array_values(array_filter($filteredCandidates, 'is_string')) : $candidates,
            false,
            false,
        );
        $resolved = $themeTemplate !== ''
            ? $themeTemplate
            : plugin_dir_path($this->pluginFile) . 'templates/' . $kind->value . '.php';
        $filtered = apply_filters(
            'ads_tourism_template_path',
            $resolved,
            $selectedTemplate,
            $kind->value,
            $objectName,
        );

        return is_string($filtered) && $filtered !== '' && is_readable($filtered) ? $filtered : $selectedTemplate;
    }
}

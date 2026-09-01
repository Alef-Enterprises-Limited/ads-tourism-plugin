<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\SEO;

final class SchemaGraphMerger
{
    /**
     * @param array<int|string, array<string, mixed>> $graph
     * @param array<string, mixed>                   $candidate
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function appendWithoutDuplicate(array $graph, array $candidate): array
    {
        $candidateId = (string) ($candidate['@id'] ?? '');
        $candidateUrl = (string) ($candidate['url'] ?? '');
        $candidateTypes = $this->types($candidate['@type'] ?? []);

        if ($candidateId === '' || $candidateTypes === []) {
            return $graph;
        }

        foreach ($graph as $node) {
            if ((string) ($node['@id'] ?? '') === $candidateId) {
                return $graph;
            }

            $sameEntity = $candidateUrl !== '' && (string) ($node['url'] ?? '') === $candidateUrl;
            $overlappingTypes = array_intersect($candidateTypes, $this->types($node['@type'] ?? [])) !== [];

            if ($sameEntity && $overlappingTypes) {
                return $graph;
            }
        }

        unset($candidate['@context']);
        $graph[] = $candidate;

        return $graph;
    }

    /** @return list<string> */
    private function types(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];

        return array_values(array_filter(
            array_map(static fn(mixed $type): string => is_string($type) ? $type : '', $values),
        ));
    }
}

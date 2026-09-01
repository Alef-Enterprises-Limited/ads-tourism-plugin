<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Presentation\TemplateKind;

final class TemplateCandidateResolver
{
    /**
     * @return list<string>
     */
    public function resolve(TemplateKind $kind, string $objectName = ''): array
    {
        $candidates = [];

        if ($objectName !== '') {
            $candidates[] = sprintf('ads-tourism/%s-%s.php', $kind->value, $objectName);
        }

        $candidates[] = sprintf('ads-tourism/%s.php', $kind->value);

        return $candidates;
    }
}

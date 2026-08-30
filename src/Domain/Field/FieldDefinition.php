<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Field;

final readonly class FieldDefinition
{
    /**
     * @param array<string, string> $options
     */
    public function __construct(
        public string $key,
        public string $label,
        public FieldType $type = FieldType::TEXT,
        public string $description = '',
        public array $options = [],
        public mixed $default = null,
        public bool $editable = true,
        public bool $administratorsOnly = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function restSchema(): array
    {
        $schema = ['type' => $this->type->restType()];

        if ($this->options !== []) {
            $schema['enum'] = array_keys($this->options);
        }

        if ($this->type === FieldType::ARRAY) {
            $schema['items'] = [
                'type' => 'object',
                'additionalProperties' => true,
            ];
        }

        if ($this->type === FieldType::OBJECT) {
            $schema['additionalProperties'] = true;
        }

        if ($this->default !== null) {
            $schema['default'] = $this->default;
        }

        return $schema;
    }
}

<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Field;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Workflow\VerificationStatus;

final class RecordFieldSchema
{
    private const PREFIX = 'ads_tourism_';

    /**
     * @return list<FieldDefinition>
     */
    public function for(ContentType $contentType): array
    {
        return [...$this->common(), ...$this->specific($contentType)];
    }

    public function find(ContentType $contentType, string $key): ?FieldDefinition
    {
        foreach ($this->for($contentType) as $field) {
            if ($field->key === $key) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return list<FieldDefinition>
     */
    private function common(): array
    {
        return [
            $this->field('external_id', 'External ID'),
            $this->field('summary', 'Summary', FieldType::TEXTAREA),
            $this->field(
                'layout_mode',
                'Layout mode',
                FieldType::SELECT,
                options: [
                    'standard' => 'Standard template',
                    'standard_custom' => 'Standard template with custom content',
                    'full_custom' => 'Fully custom content',
                ],
                default: 'standard',
            ),
            $this->field(
                'custom_content_position',
                'Custom content position',
                FieldType::SELECT,
                options: [
                    'before' => 'Before template content',
                    'after' => 'After template content',
                    'template_slot' => 'Configured template slot',
                ],
                default: 'after',
            ),
            $this->field(
                'verification_status',
                'Verification status',
                FieldType::SELECT,
                options: VerificationStatus::labels(),
                default: VerificationStatus::UNVERIFIED->value,
            ),
            $this->field('source_name', 'Source name'),
            $this->field('source_reference', 'Source reference'),
            $this->field('source_url', 'Source URL', FieldType::URL),
            $this->field('date_collected', 'Date collected', FieldType::DATE),
            $this->field('last_verified_at', 'Last verified at', FieldType::DATETIME, editable: false),
            $this->field('verified_by_user_id', 'Verified by user ID', FieldType::INTEGER, editable: false),
            $this->field(
                'verification_notes',
                'Verification notes',
                FieldType::TEXTAREA,
                administratorsOnly: true,
            ),
            $this->field(
                'publication_notes',
                'Publication notes',
                FieldType::TEXTAREA,
                administratorsOnly: true,
            ),
            $this->field('manual_order', 'Manual order', FieldType::INTEGER, default: 0),
            $this->field('external_featured_media_url', 'External featured-media URL', FieldType::URL),
            $this->field(
                'external_featured_media_url_type',
                'External URL type',
                FieldType::SELECT,
                options: ['absolute' => 'Absolute', 'relative' => 'Relative'],
                default: 'absolute',
            ),
            $this->field('display_fallback_overrides', 'Display fallback overrides', FieldType::OBJECT),
            $this->field('seo_schema_override', 'SEO schema override', FieldType::OBJECT),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function specific(ContentType $contentType): array
    {
        return match ($contentType) {
            ContentType::PLACE => $this->placeFields(),
            ContentType::ACTIVITY => $this->activityFields(),
            ContentType::STAY => $this->stayFields(),
            ContentType::OPERATOR => $this->operatorFields(),
            ContentType::PACKAGE => $this->packageFields(),
        };
    }

    /**
     * @return list<FieldDefinition>
     */
    private function placeFields(): array
    {
        return [
            $this->field('physical_address', 'Physical address', FieldType::TEXTAREA),
            ...$this->coordinateFields(),
            $this->field('altitude_metres', 'Altitude (metres)', FieldType::NUMBER),
            $this->field('map_zoom', 'Map zoom', FieldType::INTEGER),
            $this->field('opening_hours', 'Opening hours or visitor access', FieldType::TEXTAREA),
            $this->field('entry_fee_information', 'Entry fee information', FieldType::TEXTAREA),
            ...$this->contactFields(),
            $this->field('best_time_to_visit', 'Best time to visit', FieldType::TEXTAREA),
            $this->field('visitor_advice', 'Visitor advice', FieldType::TEXTAREA),
            $this->field('accessibility_notes', 'Accessibility notes', FieldType::TEXTAREA),
            $this->field('safety_notes', 'Safety notes', FieldType::TEXTAREA),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function activityFields(): array
    {
        return [
            $this->field('typical_duration', 'Typical duration'),
            $this->field(
                'difficulty_level',
                'Difficulty level',
                FieldType::SELECT,
                options: [
                    'easy' => 'Easy',
                    'moderate' => 'Moderate',
                    'challenging' => 'Challenging',
                    'expert' => 'Expert',
                ],
            ),
            $this->field('minimum_age', 'Minimum age', FieldType::INTEGER),
            $this->field('fitness_requirements', 'Fitness requirements', FieldType::TEXTAREA),
            $this->field('equipment_required', 'Equipment required', FieldType::TEXTAREA),
            $this->field('what_to_bring', 'What to bring', FieldType::TEXTAREA),
            $this->field('season_notes', 'Season or availability notes', FieldType::TEXTAREA),
            $this->field('price_guidance', 'Price guidance', FieldType::TEXTAREA),
            $this->field('safety_information', 'Safety information', FieldType::TEXTAREA),
            $this->field('accessibility_information', 'Accessibility information', FieldType::TEXTAREA),
            $this->field('enquiry_url', 'Enquiry URL', FieldType::URL),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function stayFields(): array
    {
        return [
            $this->field('address', 'Address', FieldType::TEXTAREA),
            ...$this->coordinateFields(),
            ...$this->contactFields(),
            $this->field('check_in_notes', 'Check-in notes', FieldType::TEXTAREA),
            $this->field('check_out_notes', 'Check-out notes', FieldType::TEXTAREA),
            $this->field('price_from', 'Price from', FieldType::NUMBER),
            $this->field('price_currency', 'Price currency'),
            $this->field('price_notes', 'Price notes', FieldType::TEXTAREA),
            $this->field('room_summary', 'Room summary', FieldType::TEXTAREA),
            $this->field('accessibility_information', 'Accessibility information', FieldType::TEXTAREA),
            $this->field('airport_transfer_information', 'Airport transfer information', FieldType::TEXTAREA),
            $this->field('booking_url', 'External booking or enquiry URL', FieldType::URL),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function operatorFields(): array
    {
        return [
            $this->field('business_name', 'Business name'),
            $this->field('trading_name', 'Trading name'),
            $this->field('contact_person', 'Contact person'),
            ...$this->contactFields(),
            $this->field('office_address', 'Office address', FieldType::TEXTAREA),
            ...$this->coordinateFields(),
            $this->field('registration_notes', 'Licence or registration notes', FieldType::TEXTAREA),
            $this->field('booking_instructions', 'Booking instructions', FieldType::TEXTAREA),
            $this->field('operating_hours', 'Operating hours', FieldType::TEXTAREA),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function packageFields(): array
    {
        return [
            $this->field('duration_days', 'Duration (days)', FieldType::INTEGER),
            $this->field('duration_nights', 'Duration (nights)', FieldType::INTEGER),
            $this->field('meeting_point', 'Meeting point', FieldType::TEXTAREA),
            $this->field('meeting_point_latitude', 'Meeting-point latitude', FieldType::NUMBER),
            $this->field('meeting_point_longitude', 'Meeting-point longitude', FieldType::NUMBER),
            $this->field('minimum_participants', 'Minimum participants', FieldType::INTEGER),
            $this->field('maximum_participants', 'Maximum participants', FieldType::INTEGER),
            $this->field('adult_price', 'Adult price', FieldType::NUMBER),
            $this->field('child_price', 'Child price', FieldType::NUMBER),
            $this->field('group_price', 'Group price', FieldType::NUMBER),
            $this->field('price_from', 'Price from', FieldType::NUMBER),
            $this->field('price_currency', 'Price currency'),
            $this->field(
                'price_basis',
                'Price basis',
                FieldType::SELECT,
                options: [
                    'per_person' => 'Per person',
                    'per_group' => 'Per group',
                    'per_night' => 'Per night',
                    'fixed' => 'Fixed',
                    'contact_provider' => 'Contact provider',
                ],
            ),
            $this->field('inclusions', 'Inclusions', FieldType::TEXTAREA),
            $this->field('exclusions', 'Exclusions', FieldType::TEXTAREA),
            $this->field('what_to_bring', 'What to bring', FieldType::TEXTAREA),
            $this->field('booking_conditions', 'Booking conditions', FieldType::TEXTAREA),
            $this->field('cancellation_conditions', 'Cancellation conditions', FieldType::TEXTAREA),
            $this->field('availability_notes', 'Availability notes', FieldType::TEXTAREA),
            $this->field('catalogue_cta_label', 'Catalogue call-to-action label'),
            $this->field('catalogue_cta_url', 'Catalogue call-to-action URL', FieldType::URL),
            $this->field(
                'commerce_mode',
                'Commerce mode',
                FieldType::SELECT,
                options: [
                    'catalogue' => 'Catalogue',
                    'enquiry' => 'Enquiry',
                    'woocommerce' => 'WooCommerce',
                ],
                default: 'catalogue',
            ),
            $this->field('itinerary', 'Structured itinerary', FieldType::ARRAY, default: []),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function coordinateFields(): array
    {
        return [
            $this->field('latitude', 'Latitude', FieldType::NUMBER),
            $this->field('longitude', 'Longitude', FieldType::NUMBER),
        ];
    }

    /**
     * @return list<FieldDefinition>
     */
    private function contactFields(): array
    {
        return [
            $this->field('telephone', 'Telephone'),
            $this->field('email', 'Email', FieldType::EMAIL),
            $this->field('website_url', 'Website URL', FieldType::URL),
            $this->field('social_links', 'Social links', FieldType::OBJECT),
        ];
    }

    /**
     * @param array<string, string> $options
     */
    private function field(
        string $key,
        string $label,
        FieldType $type = FieldType::TEXT,
        string $description = '',
        array $options = [],
        mixed $default = null,
        bool $editable = true,
        bool $administratorsOnly = false,
    ): FieldDefinition {
        return new FieldDefinition(
            self::PREFIX . $key,
            $label,
            $type,
            $description,
            $options,
            $default,
            $editable,
            $administratorsOnly,
        );
    }
}

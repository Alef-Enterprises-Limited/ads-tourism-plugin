<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Presentation;

use AlefDigitalSolutions\ADSTourism\Application\Presentation\CustomCssSanitizer;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\BoilerplateStyles;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\PresentationSettings;
use PHPUnit\Framework\TestCase;

final class PresentationSettingsTest extends TestCase
{
    private PresentationSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settings = new PresentationSettings(
            new CustomCssSanitizer(),
            new BoilerplateStyles(dirname(__DIR__, 3) . '/ads-tourism.php'),
        );
    }

    public function testBundledScopesHaveReadableBoilerplate(): void
    {
        self::assertCount(12, $this->settings->scopes());

        foreach (array_keys($this->settings->scopes()) as $scope) {
            self::assertNotSame('', $this->settings->defaultCss($scope));
        }
    }

    public function testCssSanitizationRemovesUnsafeConstructs(): void
    {
        self::assertStringNotContainsString('<script', $this->settings->sanitizeCss('<script>alert(1)</script> .x{color:red}'));
        self::assertSame('', $this->settings->sanitizeCss($this->settings->defaultCss('global')));
    }

    public function testResetControlRequiresConfirmationAndUsesDedicatedNonce(): void
    {
        ob_start();
        $this->settings->renderResetField();
        $markup = (string) ob_get_clean();

        self::assertStringContainsString(PresentationSettings::RESET_CONFIRMATION_FIELD, $markup);
        self::assertStringContainsString(PresentationSettings::RESET_NONCE_FIELD, $markup);
        self::assertStringContainsString('window.confirm', $markup);
    }
}

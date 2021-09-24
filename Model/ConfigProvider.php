<?php

declare(strict_types=1);

namespace Oneserv\ExamplePaymentMethod\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /** @var string */
    public const CODE = 'oneserv_example_payment_method';

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function getConfig(): array
    {
        return [];
    }
}

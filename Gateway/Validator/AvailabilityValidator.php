<?php

declare(strict_types=1);

namespace Oneserv\ExamplePaymentMethod\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class AvailabilityValidator
 */
class AvailabilityValidator extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        return $this->createResult(false);
    }
}

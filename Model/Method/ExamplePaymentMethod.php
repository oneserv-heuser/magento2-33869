<?php

declare(strict_types=1);

namespace Oneserv\ExamplePaymentMethod\Model\Method;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Class ExamplePaymentMethod
 */
class ExamplePaymentMethod extends Adapter
{
    private State $state;

    /**
     * ExamplePaymentMethod constructor.
     *
     * @param ManagerInterface $eventManager
     * @param ValueHandlerPoolInterface $valueHandlerPool
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     * @param string $formBlockType
     * @param string $infoBlockType
     * @param State $state
     * @param CommandPoolInterface|null $commandPool
     * @param ValidatorPoolInterface|null $validatorPool
     * @param CommandManagerInterface|null $commandExecutor
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        string $code,
        string $formBlockType,
        string $infoBlockType,
        State $state,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor,
            $logger
        );
        $this->state = $state;
    }

    /**
     * @inheritdoc
     * This is a temporary workaround for https://github.com/magento/magento2/issues/33869.
     * It sets the info instance before the method gets executed. Otherwise, the validator doesn't get called
     * correctly.
     */
    public function isAvailable(CartInterface $quote = null)
    {
        // remove this line if you want the availability validator to be called
        return parent::isAvailable($quote);
        if ($quote === null) {
            return parent::isAvailable($quote);
        }
        /** @var Quote $quote */
        /*
         * This is needed to avoid issues when creating a new order via adminhtml. There is an error as the quote has
         * empty data and somewhere deep in magento it causes an issue.
         */
        try {
            if (
                $this->state->getAreaCode() === Area::AREA_ADMINHTML &&
                $quote->getDataByKey(Quote::KEY_STORE_ID) === null
            ) {
                return parent::isAvailable($quote);
            }
        } catch (LocalizedException $exception) {
            return parent::isAvailable($quote);
        }

        $this->setInfoInstance($quote->getPayment());
        return parent::isAvailable($quote);
    }
}

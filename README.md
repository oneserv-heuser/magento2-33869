# magento2-33869

This is an example for https://github.com/magento/magento2/issues/33869.

## Prerequisites to reproduce the bug

1. At least Magento 2.4.0 installation
2. A product with price > 0

## Steps to reproduce the bug

1. Install this module
   1. Execute ``composer config repositories.oneserv-magento2-33869 composer https://github.com/oneserv-heuser/magento2-33869``
   2. Execute ``composer require oneserv/magento2-33869 @dev``
2. Execute ``bin/magento module:enable Oneserv_ExamplePaymentMethod``
3. Execute ``bin/magento setup:upgrade``
4. Create an empty cart via GraphQl
5. Add your product to the cart
6. Request all available payment methods

## Expected result
The payment method ``oneserv_example_payment_method`` should not be available as the 
[AvailabilityValidator](Gateway/Validator/AvailabilityValidator.php) always returns false.

The payment method was created exactly as stated in the [docs for creating payment methods](https://devdocs.magento.com/guides/v2.4/payments-integrations/bk-payments-integrations.html).


## Actual result
The method is always available.

## Cause of the problem
The cause of the problem is that [Adapter::isAvailable()](https://github.com/magento/magento2/blob/ed5c06d68ec4bf77ad6c03e0af28a133b1193b66/app/code/Magento/Payment/Model/Method/Adapter.php#L280) gets called from [MethodList::getAvailableMethods()](https://github.com/magento/magento2/blob/ed5c06d68ec4bf77ad6c03e0af28a133b1193b66/app/code/Magento/Payment/Model/MethodList.php#L70). The Adapter::isAvailable() method then tries to get the infoInstance as seen on line [290](https://github.com/magento/magento2/blob/ed5c06d68ec4bf77ad6c03e0af28a133b1193b66/app/code/Magento/Payment/Model/Method/Adapter.php#L290). If it's null it will not execute the availability validator from the validatorPool which would return false.
The infoInstance is ALWAYS null in this case as the method instance gets freshly created from it's factory. The MethodList::getAvailableMethods() sets the infoInstance AFTER it calls the Adapter::isAvailable() method. So there is no way the validator gets called when using the new Payment Provider Gateway.

I guess this issue was never found as all of magentos payment methods are using the deprecated [AbstractMethod class](https://github.com/magento/magento2/blob/ed5c06d68ec4bf77ad6c03e0af28a133b1193b66/app/code/Magento/Payment/Model/Method/AbstractMethod.php). I think this should be fixed asap as the method MethodList::getAvailableMethods() MUST work for all payment methods, especially if they're implemented the way they should be and not with deprecated classes.

## Possible workarounds
I've implemented a workaround for this bug. It can be found in the [Model of the payment method](Model/Method/ExamplePaymentMethod.php).
There I overwrite the isAvailable() function. At the moment the fix is not executed. To see the fix in action just 
remove line 80 from the [Model of the payment method](Model/Method/ExamplePaymentMethod.php) and request all 
available payment methods from the cart again. The payment method ``oneserv_example_payment_method`` is not 
available anymore.

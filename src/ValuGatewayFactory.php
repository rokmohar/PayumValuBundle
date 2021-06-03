<?php

namespace RokMohar\PayumValuBundle;

use RokMohar\PayumValuBundle\Action\Api\CreateCaptureAction;
use RokMohar\PayumValuBundle\Action\CancelAction;
use RokMohar\PayumValuBundle\Action\CaptureAction;
use RokMohar\PayumValuBundle\Action\GetPaymentStatusAction;
use RokMohar\PayumValuBundle\Action\NotifyAction;
use RokMohar\PayumValuBundle\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class ValuGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config): void
    {
        parent::populateConfig($config);

        $config->defaults([
            'payum.factory_name' => 'valu',
            'payum.factory_title' => 'Valu',
            'payum.template.payment_status' => '@RokMoharPayumValuBundle/Action/payment_status.html.twig',
            'payum.action.cancel' => new CancelAction(),
            'payum.action.capture' => new CaptureAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.get_payment_status' => function (ArrayObject $config) {
                return new GetPaymentStatusAction($config['payum.template.payment_status']);
            },
            'payum.action.api.capture' => new CreateCaptureAction(),
        ]);

        if (!$config->offsetExists('payum.api')) {
            $config['payum.default_options'] = $this->getDefaultOptions();
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = $this->getRequiredOptions();

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return $this->initApi($config->getArrayCopy());
            };
        }

        $config['payum.paths'] = array_replace(
            ['RokMoharPayumValuBundle' => __DIR__ . '/Resources/views'],
            $config['payum.paths'] ?? []
        );
    }

    /**
     * @param mixed[] $config
     * @return ValuApi
     */
    protected function initApi(array $config): ValuApi
    {
        return new ValuApi($config, $config['payum.http_client'], $config['httplug.message_factory']);
    }

    /**
     * @return mixed[]
     */
    protected function getDefaultOptions(): array
    {
        return ['sandbox' => false];
    }

    /**
     * @return string[]
     */
    protected function getRequiredOptions(): array
    {
        return ['tarifficationId'];
    }
}

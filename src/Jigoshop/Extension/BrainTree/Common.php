<?php

namespace Jigoshop\Extension\BrainTree;

use Jigoshop\Extension\BrainTree\Common\Method;
use Jigoshop\Integration\Helper\Render;
use Jigoshop\Integration;
use Jigoshop\Container;
use Jigoshop\Entity\Order;
use Jigoshop\Service;


class Common
{
    public function __construct()
    {
        add_action('jigoshop\order\after\\' . Order\Status::COMPLETED, __CLASS__ . "\\Method::onCompleted");
        add_action('jigoshop\order\after\\' . Order\Status::CANCELLED, __CLASS__ . "\\Method::onCancelled");

        Integration::addPsr4Autoload(__NAMESPACE__ . '\\', __DIR__);
        Render::addLocation('braintree', JIGOSHOP_BRAINTREE_GATEWAY_DIR);
        $di = Integration::getService('di');
        $di->services->setDatails('jigoshop.payment.braintree', __NAMESPACE__ . '\\Common\\Method', array(
            'jigoshop.options',
            'jigoshop.service.cart',
            'jigoshop.service.order',
            'jigoshop.messages'
        ));
        $di->services->setDatails('jigoshop.api.braintree', __NAMESPACE__ . '\\Common\\Api', array(
            'jigoshop.options',
            'jigoshop.service.order',
            'jigoshop.messages'
        ));
        $di->triggers->add('jigoshop.service.payment', 'jigoshop.service.payment', 'addMethod',
            array('jigoshop.payment.braintree'));

    }
}

new Common();
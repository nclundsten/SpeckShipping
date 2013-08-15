<?php

namespace SpeckShipping\Event;

use SpeckShipping\Entity\CostModifier\CostModifierInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class Shipping
{
    use ServiceLocatorAwareTrait;

    public function getModuleConfig()
    {
        return $this->getServiceLocator()->get('speckshipping_config');
    }

    //default logic for shipping cost
    public function cartShippingCost($e)
    {
        $shippingClasses = $e->getParam('shipping_classes');
        $costObject      = $e->getParam('cost');
        $cost = $costObject->value;

        foreach ($shippingClasses as $sc) {
            if ($sc->getCost() > $cost) {
                $cost = $sc->getCost();
            }
        }
        $costObject->value = $cost;
    }


    //IncrementalQty crap
    public function swmShippingCost($e)
    {

    }

    public function shippingClassCostModifiers($e)
    {
        $sc = $e->getParam('shipping_class');
        $costMods = $sc->get('cost_modifiers');
        if (null === $costMods) {
            return;
        }

        $config = $this->getModuleConfig();

        foreach ($costMods as $mod) {
            if (!array_key_exists($mod['name'], $config['cost_modifiers'])) {
                return;
            }
            $cm = new $config['cost_modifiers'][$mod['name']];
            $cm->setOptions(isset($mod['options']) ? $mod['options'] : array());
            $cm->setShippingClass($sc);
            $cm->setServiceLocator($this->getServiceLocator());
            $cm->adjustCost();
        }

        return;
    }

    public function shippingClassForCartItem($e)
    {
        $item     = $e->getParam('cart_item');
        $metaFqcn = get_class($item->getMetadata());
        $config   = $this->getModuleConfig();
        if (!array_key_exists($metaFqcn, $config['shipping_class_resolvers'])) {
            return;
        }
        $sl = $this->getServiceLocator();
        $resolver = $sl->get($config['shipping_class_resolvers'][$metaFqcn]);
        $resolver->setServiceLocator($this->getServiceLocator());
        $resolver->setCartItem($item);

        return $resolver->resolveShippingClass();
    }
}

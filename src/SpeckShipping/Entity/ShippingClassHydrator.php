<?php

namespace SpeckShipping\Entity;

class ShippingClassHydrator
{
    public function hydrate(array $data, $model)
    {
        if (!$model instanceOf ShippingClass) {
            $this->notShippingClassException($model);
        }

        return $model->setClassId($data['class_id'])
            ->setName($data['name'])
            ->setMeta(json_decode($data['meta']));
    }

    public function notShippingClassException($m)
    {
        //todo: "expected instance of shipping class, got: class or type "
        throw new \Exception("expected instance of shippingclass");
    }

    public function extract($model)
    {
        if (!$model instanceOf ShippingClass) {
            $this->notShippingClassException($model);
        }

        return array(
            'class_id' => $model->getClassId(),
            'name'     => $model->getName(),
            'meta'     => json_encode($model->getMeta()),
        );
    }
}
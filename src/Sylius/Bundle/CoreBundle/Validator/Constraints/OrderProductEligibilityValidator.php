<?php

namespace Sylius\Bundle\CoreBundle\Validator\Constraints;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Arkadiusz Krakowiak <arkadiusz.k.e@gmail.com>
 */
final class OrderProductEligibilityValidator extends ConstraintValidator
{
    /**
     * @param OrderInterface $value
     *
     * {@inheritdoc}
     */
    public function validate($order, Constraint $constraint)
    {
        if (!$order instanceof OrderInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'This validator can only work with "%s", but got "%s".',
                    OrderInterface::class,
                    get_class($order)
                )
            );
        }

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            if (!$orderItem->getProduct()->isEnabled()) {
                $this->context->addViolation(
                    $constraint->message,
                    ['%productName%' => $orderItem->getProduct()->getName()]
                );
            }
        }
    }
}

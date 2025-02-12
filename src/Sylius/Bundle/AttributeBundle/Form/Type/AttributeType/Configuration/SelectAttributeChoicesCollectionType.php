<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\AttributeBundle\Form\Type\AttributeType\Configuration;

use Ramsey\Uuid\Uuid;
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SelectAttributeChoicesCollectionType extends AbstractType
{
    private string $defaultLocaleCode;

    public function __construct(TranslationLocaleProviderInterface $localeProvider)
    {
        $this->defaultLocaleCode = $localeProvider->getDefaultLocaleCode();
    }

    /**
     * @psalm-suppress InvalidScalarArgument Some weird magic going on here, not sure about refactor
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null !== $data) {
                $fixedData = [];
                foreach ($data as $key => $values) {
                    if (!is_int($key)) {
                        $fixedData[$key] = $this->resolveValues($values);

                        continue;
                    }

                    if (!array_key_exists($this->defaultLocaleCode, $values)) {
                        continue;
                    }

                    $key = (string) $key;
                    $newKey = $this->getUniqueKey();
                    $fixedData[$newKey] = $this->resolveValues($values);

                    if ($form->offsetExists($key)) {
                        $type = $form->get($key)->getConfig()->getType()->getInnerType()::class;
                        $options = $form->get($key)->getConfig()->getOptions();

                        $form->remove($key);
                        $form->add($newKey, $type, $options);
                    }
                }

                $event->setData($fixedData);
            }
        });
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_select_attribute_choices_collection';
    }

    private function getUniqueKey(): string
    {
        return Uuid::uuid1()->toString();
    }

    private function resolveValues(array $values): array
    {
        $fixedValues = [];
        foreach ($values as $locale => $value) {
            if ('' !== $value && null !== $value) {
                $fixedValues[$locale] = $value;
            }
        }

        return $fixedValues;
    }
}

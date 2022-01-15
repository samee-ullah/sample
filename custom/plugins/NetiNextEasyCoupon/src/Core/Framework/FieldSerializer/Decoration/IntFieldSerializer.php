<?php

declare(strict_types=1);

namespace NetInventors\NetiNextEasyCoupon\Core\Framework\FieldSerializer\Decoration;

use NetInventors\NetiNextEasyCoupon\Core\Content\TypeContainingEntity;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\Validation\WriteConstraintViolationException;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IntFieldSerializer extends AbstractFieldSerializer
{
    /**
     * @var AbstractFieldSerializer
     */
    private $coreService;

    public function __construct(
        ValidatorInterface $validator,
        DefinitionInstanceRegistry $definitionRegistry,
        AbstractFieldSerializer $coreService
    ) {
        parent::__construct($validator, $definitionRegistry);

        $this->coreService = $coreService;
    }

    /**
     * @param Field             $field
     * @param EntityExistence   $existence
     * @param KeyValuePair      $data
     * @param WriteParameterBag $parameters
     *
     * @return \Generator
     */
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        $this->validateType($field, $data, $parameters->getDefinition(), $parameters->getPath());

        return $this->coreService->encode($field, $existence, $data, $parameters);
    }

    /**
     * @param Field $field
     * @param mixed $value
     *
     * @return int|null
     */
    public function decode(Field $field, $value): ?int
    {
        return $this->coreService->decode($field, $value);
    }

    protected function validateType(
        Field $field,
        KeyValuePair $data,
        EntityDefinition $definition,
        string $path
    ): void {
        if (!\is_subclass_of($definition->getEntityClass(), TypeContainingEntity::class)) {
            return;
        }

        $propertyName = preg_replace('/(?<!^)[A-Z]/', '_$0', $field->getPropertyName());
        if (!\is_string($propertyName)) {
            return;
        }

        $prefixConstant = 'PREFIX_' . strtoupper($propertyName);
        if (!defined($definition->getEntityClass() . "::${prefixConstant}")) {
            return;
        }

        $constraint          = new Choice();
        $constraint->choices = \call_user_func(
            [$definition->getEntityClass(), 'getValidTypes'],
            constant($definition->getEntityClass() . "::${prefixConstant}")
        );
        $violationList       = new ConstraintViolationList();
        $violations          = $this->validator->validate($data->getValue(), $constraint);

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $fieldName = $data->getKey();

            // correct pointer for json fields with pre-defined structure
            if ($violation->getPropertyPath()) {
                $property  = str_replace('][', '/', $violation->getPropertyPath());
                $property  = trim($property, '][');
                $fieldName .= '/' . $property;
            }

            $fieldName = '/' . $fieldName;

            $violationList->add(
                new ConstraintViolation(
                    $violation->getMessage() . ' (' . $data->getValue() . ')',
                    $violation->getMessageTemplate(),
                    $violation->getParameters(),
                    $violation->getRoot(),
                    $fieldName,
                    $violation->getInvalidValue(),
                    $violation->getPlural(),
                    $violation->getCode(),
                    $violation->getConstraint(),
                    $violation->getCause()
                )
            );

            if ($violationList->count() > 0) {
                throw new WriteConstraintViolationException($violationList, $path);
            }
        }
    }
}

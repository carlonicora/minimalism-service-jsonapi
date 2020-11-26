<?php
namespace CarloNicora\Minimalism\Services\JsonDataMapper\Builders\Interfaces;

use CarloNicora\Minimalism\Core\Services\Factories\ServicesFactory;

interface RelationshipBuilderInterface extends CallableInterface, BuilderLinksInterface
{
    /**
     * RelationshipBuilderInterface constructor.
     * @param ServicesFactory $services
     * @param string $name
     * @param int $type
     * @param AttributeBuilderInterface $attribute
     * @param string $fieldName
     */
    public function __construct(ServicesFactory $services, string $name, int $type, AttributeBuilderInterface $attribute, string $fieldName);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getType(): int;



    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $isRequired
     */
    public function setIsRequired(bool $isRequired): void;

    /**
     * @return string|null
     */
    public function getResourceBuilderName(): ?string;

    /**
     * @return AttributeBuilderInterface
     */
    public function getAttribute(): AttributeBuilderInterface;

    /**
     * @return string|null
     */
    public function getManyToManyRelationshipTableName(): ?string;

    /**
     * @return string|null
     */
    public function getManyToManyRelationshipField(): ?string;

    /**
     * @return string
     */
    public function getResourceObjectName(): string;

    /**
     * @return string|null
     */
    public function getManyToManyRelationshipTableClass(): ?string;

    /**
     * @return array|null
     */
    public function getManyToManyAdditionalValues(): ?array;
}
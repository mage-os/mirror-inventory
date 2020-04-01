<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\IsProductSalableResultExtensionInterface;
use Magento\InventorySalesApi\Api\Data\IsProductSalableResultInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterface;

/**
 * @inheritDoc
 */
class ProductSalableResult implements IsProductSalableResultInterface
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @var bool
     */
    private $isSalable;

    /**
     * @var IsProductSalableResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param string $sku
     * @param bool $isSalable
     * @param ProductSalabilityErrorInterface[] $errors
     * @param IsProductSalableResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $sku,
        bool $isSalable,
        array $errors = [],
        IsProductSalableResultExtensionInterface $extensionAttributes = null
    ) {
        $this->sku = $sku;
        $this->isSalable = $isSalable;
        $this->extensionAttributes = $extensionAttributes;
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritDoc
     */
    public function isSalable(): bool
    {
        return $this->isSalable;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(IsProductSalableResultExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?IsProductSalableResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
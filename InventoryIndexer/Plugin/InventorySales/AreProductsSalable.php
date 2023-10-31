<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Plugin\InventorySales;

use Magento\InventorySales\Model\AreProductsSalable as AreProductsSalableInventorySales;
use Magento\InventorySalesApi\Api\Data\IsProductSalableResultInterfaceFactory;

/**
 * Define if products are salable in a bulk operation.
 */
class AreProductsSalable
{
    /**
     * @var IsProductSalableResultInterfaceFactory
     */
    private IsProductSalableResultInterfaceFactory $isProductSalableResultFactory;

    /**
     * @var \Magento\InventoryIndexer\Model\AreProductsSalable
     */
    private \Magento\InventoryIndexer\Model\AreProductsSalable $areProductsSalable;

    /**
     * @param IsProductSalableResultInterfaceFactory $isProductSalableResultFactory
     * @param \Magento\InventoryIndexer\Model\AreProductsSalable $areProductSalable
     */
    public function __construct(
        IsProductSalableResultInterfaceFactory $isProductSalableResultFactory,
        \Magento\InventoryIndexer\Model\AreProductsSalable $areProductSalable
    ) {
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
        $this->areProductsSalable = $areProductSalable;
    }

    /**
     * Define if products are salable in a bulk operation instead of iterating through each sku.
     *
     * @param AreProductsSalableInventorySales $subject
     * @param callable $proceed
     * @param array|string[] $skus
     * @param int $stockId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        AreProductsSalableInventorySales $subject,
        callable $proceed,
        array $skus,
        int $stockId
    ): array {
        $results = [];

        $salabilityResults = $this->areProductsSalable->execute($skus, $stockId);

        foreach ($salabilityResults as $sku => $isSalable) {
            $results[] = $this->isProductSalableResultFactory->create(
                [
                    'sku' => $sku,
                    'stockId' => $stockId,
                    'isSalable' => $isSalable,
                ]
            );
        }

        return $results;
    }
}

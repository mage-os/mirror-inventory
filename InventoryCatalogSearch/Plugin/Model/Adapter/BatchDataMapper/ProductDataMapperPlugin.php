<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogSearch\Model\Elasticsearch\Adapter\DataMapper\Stock as StockDataMapper;
use Magento\InventoryCatalogSearch\Model\ResourceModel\Inventory;

class ProductDataMapperPlugin
{
    /**
     * @var StockDataMapper
     */
    private $stockDataMapper;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * ProductDataMapper plugin constructor
     *
     * @param StockDataMapper $stockDataMapper
     * @param Inventory $inventory
     */
    public function __construct(StockDataMapper $stockDataMapper, Inventory $inventory)
    {
        $this->stockDataMapper = $stockDataMapper;
        $this->inventory = $inventory;
    }

    /**
     * Map more attributes
     *
     * @param ProductDataMapper $subject
     * @param mixed $documents
     * @param mixed $documentData
     * @param mixed $storeId
     * @param mixed $context
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterMap(
        ProductDataMapper $subject,
        $documents,
        $documentData,
        $storeId,
        $context
    ) {
        $this->inventory->saveRelation(array_keys($documents));

        foreach ($documents as $productId => $document) {
            $document = array_merge($document, $this->stockDataMapper->map($productId, $storeId));
            $documents[$productId] = $document;
        }

        $this->inventory->clearRelation();

        return $documents;
    }
}

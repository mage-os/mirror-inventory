<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Model\GetStockItemsDataInterface;

/**
 * @inheritdoc
 */
class GetStockItemsData implements GetStockItemsDataInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private StockIndexTableNameResolverInterface $stockIndexTableNameResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private DefaultStockProviderInterface $defaultStockProvider;

    /**
     * @param ResourceConnection $resource
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ResourceConnection $resource,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->resource = $resource;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus, int $stockId): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();
        $results = [];

        if ($this->defaultStockProvider->getId() === $stockId) {
            $select->from(
                ['stock_status' => $this->resource->getTableName('cataloginventory_stock_status')],
                [
                    GetStockItemDataInterface::SKU => 'product_entity.sku',
                    GetStockItemDataInterface::QUANTITY => 'stock_status.qty',
                    GetStockItemDataInterface::IS_SALABLE => 'stock_status.stock_status',
                ]
            )->join(
                ['product_entity' => $this->resource->getTableName('catalog_product_entity')],
                'stock_status.product_id = product_entity.entity_id',
                []
            )->where(
                'product_entity.sku IN (?)',
                $skus
            );
        } else {
            $select->from(
                $this->stockIndexTableNameResolver->execute($stockId),
                [
                    GetStockItemsDataInterface::SKU => IndexStructure::SKU,
                    GetStockItemsDataInterface::QUANTITY => IndexStructure::QUANTITY,
                    GetStockItemsDataInterface::IS_SALABLE => IndexStructure::IS_SALABLE,
                ]
            )->where(
                IndexStructure::SKU . ' IN (?)',
                $skus
            );
        }

        $stockItemRows = $connection->fetchAll($select) ?: [];

        if (!empty($stockItemRows)) {
            foreach ($stockItemRows as $row) {
                $results[$row['sku']] = [
                    GetStockItemsDataInterface::QUANTITY => $row['quantity'],
                    GetStockItemsDataInterface::IS_SALABLE => $row['is_salable'],
                ];
            }
        }

        return $results;
    }
}

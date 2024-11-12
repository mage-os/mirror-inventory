<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory\Model\Stock\StockItemRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Full as FullProductIndexer;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

class StockItemRepositoryPlugin
{
    /**
     * @var FullProductIndexer
     */
    private $fullProductIndexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $inventoryIndexer;

    /**
     * @var GetSourceItemsBySku
     */
    private $getSourceItemsBySku;

    /**
     * @param FullProductIndexer $fullProductIndexer
     * @param InventoryIndexer $inventoryIndexer
     * @param ProductRepositoryInterface $productRepository
     * @param GetSourceItemsBySku $getSourceItemsBySku
     */
    public function __construct(
        FullProductIndexer $fullProductIndexer,
        InventoryIndexer $inventoryIndexer,
        ProductRepositoryInterface $productRepository,
        getSourceItemsBySku $getSourceItemsBySku
    ) {
        $this->fullProductIndexer = $fullProductIndexer;
        $this->inventoryIndexer = $inventoryIndexer;
        $this->productRepository = $productRepository;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
    }

    /**
     * Complex reindex after product stock item has been saved.
     *
     * @param StockItemRepository $subject
     * @param StockItemInterface $stockItem
     * @return StockItemInterface
     * @throws NoSuchEntityException
     */
    public function afterSave(StockItemRepository $subject, StockItemInterface $stockItem): StockItemInterface
    {
        $product = $this->productRepository->getById($stockItem->getProductId());
        $this->fullProductIndexer->executeRow($product->getId());
        $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());
        $sourceItemIds = [];

        foreach ($sourceItems as $sourceItem) {
            $sourceItemIds[] = $sourceItem->getId();
        }
        $this->inventoryIndexer->executeList($sourceItemIds);
        return $stockItem;
    }
}

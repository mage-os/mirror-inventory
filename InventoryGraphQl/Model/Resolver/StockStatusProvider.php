<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\InventoryGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\Catalog\Model\Product\Type;

/**
 * @inheritdoc
 */
class StockStatusProvider implements ResolverInterface
{
    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var AreProductsSalableInterface|null
     */
    private $areProductsSalable;

    /**
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param AreProductsSalableInterface $areProductsSalable
     */
    public function __construct(
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        AreProductsSalableInterface $areProductsSalable
    ) {
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->areProductsSalable = $areProductsSalable;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!array_key_exists('model', $value) || !$value['model'] instanceof ProductInterface) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product ProductInterface */
        $product = $value['model'];

        $productSku = ($product->getTypeId() === TYPE::TYPE_BUNDLE || !empty($product->getOptions()))
            ? $value['sku'] : $product->getSku();
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $result = $this->areProductsSalable->execute([$productSku], $stockId);
        $result = current($result);

        return $result->isSalable() ? 'IN_STOCK' : 'OUT_OF_STOCK';
    }
}

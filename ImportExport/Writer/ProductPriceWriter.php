<?php

namespace Oro\Bundle\AkeneoBundle\ImportExport\Writer;

use Oro\Bundle\PricingBundle\Builder\CombinedPriceListsBuilderFacade;
use Oro\Bundle\PricingBundle\ImportExport\Writer\ProductPriceWriter as BaseWriter;

class ProductPriceWriter extends BaseWriter
{
    /**
     * @var CombinedPriceListsBuilderFacade
     */
    private $priceBuilder;

    public function setPriceBuilder(CombinedPriceListsBuilderFacade $priceBuilder) {
        $this->priceBuilder = $priceBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        parent::write($items);

        $this->priceBuilder->rebuildAll(time());
    }

}

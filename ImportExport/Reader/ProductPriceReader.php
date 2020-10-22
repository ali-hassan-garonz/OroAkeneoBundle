<?php

namespace Oro\Bundle\AkeneoBundle\ImportExport\Reader;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\AkeneoBundle\ImportExport\AkeneoIntegrationTrait;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class ProductPriceReader extends IteratorBasedReader
{
    use AkeneoIntegrationTrait;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    public function setDoctrineHelper(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    protected function initializeFromContext(ContextInterface $context)
    {
        parent::initializeFromContext($context);
        $this->setImportExportContext($context);

        $items = $this->stepExecution
                ->getJobExecution()
                ->getExecutionContext()
                ->get('items') ?? [];

        $prices = [];

        foreach ($items as &$item) {
            if (!isset($item['values']) || !is_array($item['values'])) {
                continue;
            }

            foreach ($item['values'] as $values) {
                foreach ($values as $value) {
                    if ('pim_catalog_price_collection' !== $value['type']) {
                        continue;
                    }

                    foreach ($value['data'] as &$price) {
                        $price['sku'] = $item['sku'];
                        if ($price != array_filter($price)) {
                            continue;
                        }

                        $prices[$price['sku'] . '_' . $price['currency']] = $price;
                    }
                }
            }
        }

        $this->setProductModelPrices($items, $prices);

        $this->stepExecution->setReadCount(0);

        $this->setSourceIterator(new \ArrayIterator($prices));
    }

    private function setProductModelPrices(&$items, &$prices)
    {
        $currency = $this->getActiveCurrency();

        if (!$currency){
            return;
        }

        foreach ($items as &$item) {
            $price['sku'] = $item['code'] ?? null;
            $price['currency'] = $currency;
            $price['amount'] = "0";

            $priceIdentifier = $price['sku'] . '_' . $price['currency'];
            if (empty($price['sku']) || isset($prices[$priceIdentifier])){
                continue;
            }

            $prices[$priceIdentifier] = $price;
        }
    }

    private function getActiveCurrency()
    {
        $this->getTransport();
        $currencies = $this->transport->getAkeneoActiveCurrencies();

        return $currencies[0] ?? null;
    }
}

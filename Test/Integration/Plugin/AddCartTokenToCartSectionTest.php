<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Test\Integration\Plugin;

use Magento\Checkout\CustomerData\Cart as CartSection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Area;
use Magento\Framework\Interception\PluginList\PluginList;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Yireo\SyncGraphQlSessionWithFrontend\Plugin\AddCartTokenToCartSection;

#[AppArea(Area::AREA_FRONTEND)]
class AddCartTokenToCartSectionTest extends TestCase
{
    public function testPluginIsRegistered(): void
    {
        $pluginList = Bootstrap::getObjectManager()->get(PluginList::class);

        $plugins = $pluginList->get(CartSection::class, []);
        $this->assertArrayHasKey('AddCartTokenToCartSection', $plugins);
        $this->assertSame(AddCartTokenToCartSection::class, $plugins['AddCartTokenToCartSection']['instance']);

        $customerPlugins = $pluginList->get('Magento\\Customer\\CustomerData\\Customer', []);
        $this->assertArrayNotHasKey('AddCustomerTokenToCustomerSection', $customerPlugins);
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     */
    public function testGuestQuoteGetsMaskedId(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $quote = $objectManager->create(QuoteCollection::class)
            ->addFieldToFilter('reserved_order_id', 'test_order_1')
            ->getFirstItem();
        $quote->setCustomerId(null)->setCustomerIsGuest(true)->save();

        $checkoutSession = $objectManager->get(CheckoutSession::class);
        $checkoutSession->setQuoteId($quote->getId());

        $maskedId = $objectManager->get(QuoteIdToMaskedQuoteIdInterface::class)->execute((int)$quote->getId());
        $this->assertNotEmpty($maskedId);

        $plugin = $objectManager->get(AddCartTokenToCartSection::class);
        $result = $plugin->afterGetSectionData($objectManager->get(CartSection::class), []);
        $this->assertSame($maskedId, $result['masked_id'] ?? null);
    }
}

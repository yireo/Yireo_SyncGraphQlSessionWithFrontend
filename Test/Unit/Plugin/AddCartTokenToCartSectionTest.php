<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Test\Unit\Plugin;

use Magento\Checkout\CustomerData\Cart as CartSection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\SyncGraphQlSessionWithFrontend\Plugin\AddCartTokenToCartSection;

class AddCartTokenToCartSectionTest extends TestCase
{
    private CheckoutSession&MockObject $checkoutSession;
    private QuoteIdToMaskedQuoteIdInterface&MockObject $quoteIdToMaskedQuoteId;
    private CartSection&MockObject $cartSection;

    protected function setUp(): void
    {
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->quoteIdToMaskedQuoteId = $this->createMock(QuoteIdToMaskedQuoteIdInterface::class);
        $this->cartSection = $this->createMock(CartSection::class);
    }

    public function testWithoutQuoteId(): void
    {
        $this->checkoutSession->method('getQuoteId')->willReturn(null);
        $this->checkoutSession->expects($this->never())->method('getQuote');
        $this->quoteIdToMaskedQuoteId->expects($this->never())->method('execute');

        $this->assertSame(['foo' => 'bar'], $this->callPlugin(['foo' => 'bar']));
    }

    public function testGuestQuoteGetsMaskedId(): void
    {
        $this->setQuote(42, null);
        $this->quoteIdToMaskedQuoteId->method('execute')->with(42)->willReturn('abc123');

        $this->assertSame(['foo' => 'bar', 'masked_id' => 'abc123'], $this->callPlugin(['foo' => 'bar']));
    }

    public function testCustomerQuoteGetsNoMaskedId(): void
    {
        $this->setQuote(42, 7);
        $this->quoteIdToMaskedQuoteId->expects($this->never())->method('execute');

        $this->assertArrayNotHasKey('masked_id', $this->callPlugin([]));
    }

    public function testMissingQuoteGetsNoMaskedId(): void
    {
        $this->setQuote(42, null);
        $this->quoteIdToMaskedQuoteId->method('execute')->willThrowException(new NoSuchEntityException());

        $this->assertSame([], $this->callPlugin([]));
    }

    public function testEmptyMaskedIdIsSkipped(): void
    {
        $this->setQuote(42, null);
        $this->quoteIdToMaskedQuoteId->method('execute')->willReturn('');

        $this->assertArrayNotHasKey('masked_id', $this->callPlugin([]));
    }

    private function setQuote(int $quoteId, ?int $customerId): void
    {
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCustomerId'])
            ->getMock();
        $quote->method('getCustomerId')->willReturn($customerId);

        $this->checkoutSession->method('getQuoteId')->willReturn($quoteId);
        $this->checkoutSession->method('getQuote')->willReturn($quote);
    }

    private function callPlugin(array $result): array
    {
        $plugin = new AddCartTokenToCartSection($this->checkoutSession, $this->quoteIdToMaskedQuoteId);
        return $plugin->afterGetSectionData($this->cartSection, $result);
    }
}

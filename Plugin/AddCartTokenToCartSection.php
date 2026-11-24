<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Plugin;

use Magento\Checkout\CustomerData\Cart as CartSection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

class AddCartTokenToCartSection
{
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
    }

    /**
     * @param CartSection $cartSection
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public function afterGetSectionData(CartSection $cartSection, array $result): array
    {
        $quoteId = (int)$this->checkoutSession->getQuoteId();
        if ($quoteId < 1) {
            return $result;
        }

        try {
            if ($this->checkoutSession->getQuote()->getCustomerId()) {
                return $result;
            }

            $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
        } catch (NoSuchEntityException|LocalizedException) {
            return $result;
        }

        if ($maskedId !== '') {
            $result['masked_id'] = $maskedId;
        }

        return $result;
    }
}

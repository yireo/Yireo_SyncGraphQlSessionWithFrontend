<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\CustomerData\Cart as CartSection;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\FilterBuilderFactory;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

class AddCartTokenToCartSection
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var FilterBuilderFactory
     */
    private $filterBuilderFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * AddTokenToCustomerData constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param FilterBuilderFactory $filterBuilderFactory
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        FilterBuilderFactory $filterBuilderFactory,
        CheckoutSession $checkoutSession,
        \Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->cartRepository = $cartRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->filterBuilderFactory = $filterBuilderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * @param CartSection $cartSection
     * @param $result
     * @return array
     */
    public function afterGetSectionData(CartSection $cartSection, $result): array
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        if (empty($quoteId)) {
            return $result;
        }

        try {
            $result['masked_id'] = $this->quoteIdToMaskedQuoteId->execute((int)$quoteId);
        } catch (NoSuchEntityException $e) {
        }

        return $result;
    }

    /**
     * @param string $quoteId
     * @return CartInterface
     * @throws NotFoundException
     */
    private function getQuoteFromQuoteId(int $quoteId): CartInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->filterBuilderFactory->create();
        $filterBuilder->setField('quote_id');
        $filterBuilder->setValue($quoteId);
        $filter = $filterBuilder->create();

        $searchCriteriaBuilder->addFilter($filter);
        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResults = $this->cartRepository->getList($searchCriteria);
        $items = $searchResults->getItems();

        if (count($items) < 1) {
            throw new NotFoundException(__('No quote found'));
        }

        return array_shift($items);
    }
}

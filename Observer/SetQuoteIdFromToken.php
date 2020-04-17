<?php
declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\Quote;

/**
 * Class SetQuoteIdFromToken
 * @package Yireo\SyncGraphQlSessionWithFrontend\Observer
 */
class SetQuoteIdFromToken implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    private $request;


    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;


    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Quote
     */
    private $quote;
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * SetQuoteIdFromToken constructor.
     * @param RequestInterface $request
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Session $checkoutSession
     * @param Cart $cart
     * @param Quote $quote
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        RequestInterface $request,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Session $checkoutSession,
        Cart $cart,
        Quote $quote,
        CartRepositoryInterface $quoteRepository
    )
    {

        $this->request = $request;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->quote = $quote;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $graphqlToken = (string)$this->request->getParam('graphql_token');
        if (!$graphqlToken) {
            return;
        }

        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($graphqlToken, 'masked_id');
        $quoteId = (int)$quoteIdMask->getQuoteId();
        if (!$quoteId) {
            return;
        }

        $quote = $this->quoteRepository->get($quoteId);
        $quote->setIsActive(1);
        $this->quoteRepository->save($quote);

        $this->checkoutSession->setQuoteId($quoteId); // @todo: Works only if URL is /checkout or /checkout/cart
    }
}

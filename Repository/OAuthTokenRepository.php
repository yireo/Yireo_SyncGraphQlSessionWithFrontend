<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Repository;

use Magento\Framework\Exception\NotFoundException;
use Magento\Integration\Model\Oauth\Token as TokenModel;
use Magento\Integration\Model\Oauth\TokenFactory;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory as TokenCollectionFactory;

class OAuthTokenRepository
{
    /**
     * @var TokenFactory
     */
    private $tokenFactory;

    /**
     * @var TokenCollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * OAuthTokenRepository constructor.
     * @param TokenFactory $tokenFactory
     * @param TokenCollectionFactory $tokenCollectionFactory
     */
    public function __construct(
        TokenFactory $tokenFactory,
        TokenCollectionFactory $tokenCollectionFactory
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
    }

    /**
     * @param string $token
     * @return TokenModel
     * @throws NotFoundException
     */
    public function getByToken(string $token): TokenModel
    {
        $tokenModel = $this->tokenFactory->create()->loadByToken($token);
        if (!$tokenModel->getId()) {
            throw new NotFoundException(__('Token not found'));
        }

        return $tokenModel;
    }

    /**
     * @param int $customerId
     * @return TokenModel
     * @throws NotFoundException
     */
    public function getByCustomerId(int $customerId): TokenModel
    {
        $tokenCollection = $this->tokenCollectionFactory->create();
        $tokenCollection->addFilter('customer_id', $customerId);
        $tokenCollection->addOrder('created_at', 'DESC');
        $tokenCollection->setPageSize(1);
        $tokenCollection->load();

        if ($tokenCollection->count() < 1) {
            throw new NotFoundException(__('Token not found'));
        }

        /** @var TokenModel $tokenModel */
        $tokenModel = $tokenCollection->getFirstItem();
        return $tokenModel;
    }
}

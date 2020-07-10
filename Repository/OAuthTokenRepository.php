<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Repository;

use Magento\Framework\Exception\NotFoundException;
use Magento\Integration\Model\Oauth\Token as TokenModel;

class OAuthTokenRepository
{
    /**
     * @var TokenModel
     */
    private $tokenModel;

    /**
     * OAuthTokenRepository constructor.
     * @param TokenModel $tokenModel
     */
    public function __construct(
        TokenModel $tokenModel
    ) {
        $this->tokenModel = $tokenModel;
    }

    /**
     * @param string $token
     * @return TokenModel
     */
    public function getByToken(string $token): TokenModel
    {
        $tokenModel = $this->tokenModel->load($token, 'token');
        if (!$tokenModel instanceof TokenModel) {
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
        $tokenCollection = $this->tokenModel->getCollection();
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

<?php

declare(strict_types=1);

namespace Yireo\SyncGraphQlSessionWithFrontend\Plugin;

use Magento\Customer\CustomerData\Customer as CustomerSection;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Yireo\SyncGraphQlSessionWithFrontend\Repository\OAuthTokenRepository;

class AddCustomerTokenToCustomerSection
{
    /**
     * @var OAuthTokenRepository
     */
    private $OAuthTokenRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        OAuthTokenRepository $OAuthTokenRepository,
        CustomerSession $customerSession
    ) {
        $this->OAuthTokenRepository = $OAuthTokenRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param CustomerSection $customerSection
     * @param $result
     * @return array
     */
    public function afterGetSectionData(CustomerSection $customerSection, $result): array
    {
        $customerId = $this->customerSession->getCustomerId();
        try {
            $tokenModel = $this->OAuthTokenRepository->getByCustomerId((int)$customerId);
            $result['customer_token'] = $tokenModel->getToken();
        } catch (NotFoundException $e) {
        }

        return $result;
    }
}

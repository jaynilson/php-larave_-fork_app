<?php

namespace Webkul\SocialLogin\Repositories;

use Illuminate\Container\Container as App;
use Webkul\Core\Eloquent\Repository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;

class CustomerSocialAccountRepository extends Repository
{
    /**
     * CustomerRepository object
     *
     * @var \Webkul\Customer\Repositories\CustomerRepository
     */
    protected $customerRepository;

    /**
     * CustomerGroupRepository object
     *
     * @var \Webkul\Customer\Repositories\CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * Create a new reposotory instance.
     *
     * @param  \Webkul\Customer\Repositories\CustomerRepository       $customerRepository
     * @param  \Webkul\Customer\Repositories\CustomerGroupRepository  $customerGroupRepository
     * @param  \Illuminate\Container\Container  $app
     * @return void
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerGroupRepository $customerGroupRepository,
        App $app
    )
    {
        $this->customerRepository = $customerRepository;

        $this->customerGroupRepository = $customerGroupRepository;

        $this->_config = request('_config');

        parent::__construct($app);
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return 'Webkul\SocialLogin\Contracts\CustomerSocialAccount';
    }

    /**
     * @param  array  $providerUser
     * @param  string  $provider
     * @return void
     */
    public function findOrCreateCustomer($providerUser, $provider)
    {
        $account = $this->findOneWhere([
            'provider_name' => $provider,
            'provider_id'   => $providerUser->getId(),
        ]);
  
        if ($account) {
            return $account->customer;
        } else {
            $customer = $this->customerRepository->findOneByField('email', $providerUser->getEmail());
 
            if (! $customer) {
                $names = $this->getFirstLastName($providerUser->getName());

                $customer = $this->customerRepository->create([
                    'email'             => $providerUser->getEmail(),
                    'first_name'        => $names['first_name'],
                    'last_name'         => $names['last_name'],
                    'status'            => 1,
                    'is_verified'       => core()->getConfigData('customer.settings.email.verification') ? 0 : 1,
                    'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id
                ]);
            }
 
            $this->create([
                'customer_id'   => $customer->id,
                'provider_id'   => $providerUser->getId(),
                'provider_name' => $provider,
            ]);
 
            return $customer;
        }
    }

    /**
     * Returns first and last name from name
     *
     * @param  string  $name
     * @return string
     */
    public function getFirstLastName($name)
    {
        $name = trim($name);

        $lastName = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);

        $firstName = trim( preg_replace('#' . $lastName . '#', '', $name) );

        return [
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ];
    }
}
<?php
namespace Mgurrapucustomer\Import\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class for CustomerCreate
 */
class CustomerCreate
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * CustomerCreate constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Create customers based on provided data.
     *
     * @param array $customerData An array containing customer data.
     * @return bool True on success.
     * @throws CouldNotSaveException If an error occurs while saving the customer.
     */
    public function createCustomer($customerData)
    {
        try {
            foreach ($customerData as $data) {
                // Create a new customer instance
                $firstName = $data[0] ?? $data['fname'] ?? '';
                $lastName = $data[1] ?? $data['lname'] ?? '';
                $emailaddress = $data[2] ?? $data['emailaddress'] ?? '';
                if (!empty($firstName) && !empty($lastName) && !empty($emailaddress)) {
                    $customer = $this->customerFactory->create();
                    $customer->setFirstname($firstName);
                    $customer->setLastname($lastName);
                    $customer->setEmail($emailaddress);

                    // Save the customer
                    $this->customerRepository->save($customer);
                }
            }
            return true;
        } catch (InputException $e) {
            // Handle invalid input data
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (StateException $e) {
            // Handle invalid state
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            // Handle other localized exceptions
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            // Handle generic exceptions
            throw new CouldNotSaveException(__('An error occurred while saving the customer.'));
        }
    }
}

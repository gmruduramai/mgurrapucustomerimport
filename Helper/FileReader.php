<?php
namespace Mgurrapucustomer\Import\Helper;

use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Xml\Parser;
use Mgurrapucustomer\Import\Model\CustomerCreate;

/**
 * Helper class for reading various file formats.
 */
class FileReader
{
    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var CustomerCreate
     */
    protected $customerCreate;

    /**
     * @var Parser
     */
    protected $xmlParser;

    /**
     * Constructor.
     * 
     * @param Csv $csv The CSV file handler.
     * @param File $file The file handler.
     * @param DecoderInterface $jsonDecoder The JSON decoder.
     * @param CustomerCreate $customerCreate The customer create model.
     * @param Parser $xmlParser The XML parser.
     */
    public function __construct(
        Csv $csv,
        File $file,
        DecoderInterface $jsonDecoder,
        CustomerCreate $customerCreate,
        Parser $xmlParser
    ) {
        $this->csv = $csv;
        $this->file = $file;
        $this->jsonDecoder = $jsonDecoder;
        $this->customerCreate = $customerCreate;
        $this->xmlParser = $xmlParser;
    }

    /**
     * Reads data from a CSV file and creates customers.
     * 
     * @param string $filePath The path to the CSV file.
     * @throws \Exception If an error occurs while reading the CSV file.
     */
    public function readCsv($filePath)
    {
        try {
            // Read the CSV file content
            $customerData = $this->csv->getData($filePath);
            unset($customerData[0]);
            $this->customerCreate->createCustomer(
                $customerData
            );
        } catch (\Exception $e) {
            throw new \Exception("Error reading CSV file: " . $e->getMessage());
        }
    }

    /**
     * Reads data from a JSON file and creates customers.
     * 
     * @param string $filePath The path to the JSON file.
     * @throws \Exception If an error occurs while reading the JSON file.
     */
    public function readJson($filePath)
    {
        try {
            // Read the JSON file content
            $jsonString = $this->file->read($filePath);

            // Decode the JSON string
            $customerData = $this->jsonDecoder->decode($jsonString);
            $this->customerCreate->createCustomer(
                $customerData
            );
        } catch (\Exception $e) {
            throw new \Exception("Error reading JSON file: " . $e->getMessage());
        }
    }

    /**
     * Reads data from a TXT file and creates customers.
     * 
     * @param string $filePath The path to the TXT file.
     * @throws \Exception If an error occurs while reading the TXT file.
     */
    public function readTxt($filePath)
    {
        try {
            // Read the TXT file content
            $customerData = file($filePath);
            $finalCustomerData = [];
            foreach ($customerData as $key => $line) {
                // Explode the line into an array using comma as the delimiter
                $values = explode(',', $line);

                // Skip the first line which contains headers
                if ($key === 0) {
                    continue;
                }

                // Add the values to the result array
                $finalCustomerData[$key] = $values;
            }
            $this->customerCreate->createCustomer(
                $finalCustomerData
            );
        } catch (\Exception $e) {
            throw new \Exception("Error reading TXT file: " . $e->getMessage());
        }
    }

    /**
     * Reads data from an XML file and creates customers.
     * 
     * @param string $filePath The path to the XML file.
     * @throws \Exception If an error occurs while reading the XML file.
     */
    public function readXml($filePath)
    {
        try {
            // Read the XML file content
            $xmlString = file_get_contents($filePath);
            $result = $this->xmlParser->loadXML($xmlString);
            $customerData = $result->xmlToArray();
            if ($customerData && $customerData['root']['row']) {
                $this->customerCreate->createCustomer(
                    $customerData['root']['row']
                );
            }
        } catch (\Exception $e) {
            throw new \Exception("Error reading XML file: " . $e->getMessage());
        }
    }
}
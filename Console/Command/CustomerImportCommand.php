<?php
namespace Mgurrapucustomer\Import\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Mgurrapucustomer\Import\Helper\FileReader;

class CustomerImportCommand extends Command
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var FileReader
     */
    protected $fileReader;
    
    /**
     * Constructor.
     * 
     * @param DirectoryList $directoryList The directory list.
     * @param FileReader $fileReader The file reader helper.
     */
    public function __construct(
        DirectoryList $directoryList,
        FileReader $fileReader
    ) {
        parent::__construct();
        $this->directoryList = $directoryList;
        $this->fileReader = $fileReader;
    }

    /**
     * Configures the command.
     */
    protected function configure()
    {
        $this->setName('customer:import')
            ->setDescription('Imports customers from source using specified profile')
            ->addArgument('profile', InputArgument::REQUIRED, 'Profile name')
            ->addArgument('source', InputArgument::REQUIRED, 'Source');
    }

    /**
     * Executes the command.
     * 
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int The command exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $profileName = $input->getArgument('profile');
        $source = $input->getArgument('source');
        $filePath = $this->directoryList->getRoot() . "/$source";
        // Check if the file exists
        if (!file_exists($filePath)) {
            $output->writeln('<error>File not found: ' . $filePath . '</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $fileType = $this->getFileType($filePath);
        $this->processFile($filePath, $fileType);
        $output->writeln("Imported customers using profile: $profileName from source: $source");
        return 0;
    }

    /**
     * Gets the file type based on the file extension.
     * 
     * @param string $filePath The file path.
     * @return string The file type.
     */
    public function getFileType($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        return strtolower($extension);
    }

    /**
     * Processes the file based on its type.
     * 
     * @param string $filePath The file path.
     * @param string $fileType The file type.
     * @throws \InvalidArgumentException If the file type is unsupported.
     */
    public function processFile($filePath, $fileType)
    {
        //for PDF and Excel file requires additional libraries PHPExcel or PhpSpreadsheet and TCPDF or FPDF.
        switch ($fileType) {
            case 'csv':
                return $this->fileReader->readCsv($filePath);
            case 'json':
                return $this->fileReader->readJson($filePath);
            case 'txt':
                return $this->fileReader->readTxt($filePath);
            case 'xml':
                return $this->fileReader->readXml($filePath);
            default:
                throw new \InvalidArgumentException("Unsupported file type: $fileType");
        }
    }
}
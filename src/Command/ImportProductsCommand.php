<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Entity\Products;
use Psr\Log\LoggerInterface;

class ImportProductsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:import:products';

    /**
     * @var string
     */
    private $importDirectory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(string $importDirectory, LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->importDirectory = $importDirectory;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
        ->setDescription('Update products data in database table products')
        ->setHelp('This command allows you to update data in database table products...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            '============',
            'Import Products',
            '============',
        ]);
        
        $output->writeln([
            'Check if csv file exist',
            '============',
        ]);

        $csvDirectory = $this->importDirectory.'/csv';

        $files = scandir($csvDirectory);
        $files=array_diff($files,['.']);
        $files=array_diff($files,['..']);

        if (!empty($files)) {
            foreach ($files as $file) {
                if (strstr($file, 'products') === false)
                    continue;
                
                $output->writeln([
                    'Start import '.$file,
                    '============',
                ]);  

                if (($handle = fopen($csvDirectory.'/'.$file, "r")) !== false) { 
                    $x = 0;
                    while (($data = fgetcsv($handle)) !== false) {
                        $productDataFromCsv = explode(';', $data[0]);

                        if ($x === 0) {
                            $nameKey = array_search('nazwa', $productDataFromCsv);
                            $indexKey = array_search('index', $productDataFromCsv);
                            $categoryKey = array_search('kategoria', $productDataFromCsv);
                            $x++;
                            continue;
                        }

                        $product = new products();
                        if ($nameKey !== false)
                            $product->setName($productDataFromCsv[$nameKey]);
                        if ($indexKey !== false)
                            $product->setSku($productDataFromCsv[$indexKey]);
                        if ($categoryKey !== false && !empty($productDataFromCsv[$categoryKey]))
                            $product->setCategoryId((int) $productDataFromCsv[$categoryKey]);

                        if (!$this->entityManager->isOpen()) {
                            $this->entityManager = $this->entityManager->create(
                                $this->entityManager->getConnection(),
                                $this->entityManager->getConfiguration()
                            );
                        }

                        $this->entityManager->persist($product);
                        try {
                            $this->entityManager->flush();
                        } catch (UniqueConstraintViolationException $e) {
                            $this->logger->info('Product with name: '.
                                ($nameKey !== false?$productDataFromCsv[$nameKey]:'').
                                ', index: '.($indexKey !== false?$productDataFromCsv[$indexKey]:'').
                                ', category: '.($categoryKey !== false?$productDataFromCsv[$categoryKey]:'').
                                ' is duplicated'); 
                        }
                    }
                    fclose($handle);
                    unlink($csvDirectory.'/'.$file);
                }
                $output->writeln([
                    'End import '.$file,
                    '============',
                ]); 
            } 
        } else {
            $output->writeln([
                'There aren\'t any csv file',
                '============',
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

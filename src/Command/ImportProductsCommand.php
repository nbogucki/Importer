<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Products;

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

    public function __construct(string $importDirectory, EntityManagerInterface $entityManager)
    {
        $this->importDirectory = $importDirectory;
        $this->entityManager = $entityManager;
        
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
                $output->writeln([
                    'Start import '.$file,
                    '============',
                ]);  

                if (($handle = fopen($csvDirectory.'/'.$file, "r")) !== false) { 
                    $x = 0;
                    while (($data = fgetcsv($handle)) !== false) {
                        if ($x === 0) {
                            $x++;
                            continue;
                        }

                        $productDataFromCsv = explode(';', $data[0]);
                        
                        $product = new products();
                        $product->setName($productDataFromCsv[0]);
                        $product->setSku($productDataFromCsv[1]);

                        $this->entityManager->persist($product);
                        $this->entityManager->flush();
                    }
                    fclose($handle);
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

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}

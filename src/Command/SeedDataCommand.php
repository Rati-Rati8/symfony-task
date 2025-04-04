<?php

namespace App\Command;

use App\Entity\Client;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed-data',
    description: 'Seed initial data: 2 clients with 8 accounts (different currencies & balances)',
)]
class SeedDataCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->em->createQuery('DELETE FROM App\Entity\Transaction')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Account')->execute();
        $this->em->createQuery('DELETE FROM App\Entity\Client')->execute();

        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD', 'PLN'];

        for ($i = 1; $i <= 2; $i++) {
            $client = new Client();
            $client->setName("Client $i");
            $this->em->persist($client);

            for ($j = 0; $j < 4; $j++) {
                $currency = $currencies[(($i - 1) * 4 + $j)];
                $account = new Account();
                $account->setClient($client);
                $account->setCurrency($currency);
                $account->setBalance((string)random_int(1000, 10000));
                $this->em->persist($account);
            }
        }

        $this->em->flush();

        $output->writeln('<info>Seeded 2 clients and 8 accounts.</info>');

        return Command::SUCCESS;
    }
}

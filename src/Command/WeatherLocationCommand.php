<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:location',
    description: 'Displays the weather forecast for a given location ID.',
)]
class WeatherLocationCommand extends Command
{
    public function __construct(
        private LocationRepository $locationRepository,
        private WeatherUtil $weatherUtil,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Location ID')] int $id,
    ): int {
        $location = $this->locationRepository->find($id);

        if (!$location) {
            $io->error(sprintf('Location with ID %d not found.', $id));
            return Command::FAILURE;
        }

        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        $io->writeln(sprintf('Location: %s', $location->getCity()));

        $io->table(['Date', 'Temperature'], array_map(fn($m) => [
            $m->getDate()->format('Y-m-d'),
            $m->getCelsius(),
        ], $measurements));

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Service\WeatherUtil;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:country-city',
    description: 'Displays the weather forecast for a given country code and city name.',
)]
class WeatherCountryCityCommand extends Command
{
    public function __construct(
        private WeatherUtil $weatherUtil,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Argument('Country code')] string $countryCode,
        #[Argument('City name')] string $city,
    ): int {
        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($countryCode, $city);

        if (empty($measurements)) {
            $io->error(sprintf('No measurements found for city "%s" in country "%s".', $city, $countryCode));
            return Command::FAILURE;
        }

        $io->writeln(sprintf('Location: %s (%s)', $city, strtoupper($countryCode)));

        $io->table(['Date', 'Temperature'], array_map(fn($m) => [
            $m->getDate()->format('Y-m-d'),
            $m->getCelsius(),
        ], $measurements));

        return Command::SUCCESS;
    }
}

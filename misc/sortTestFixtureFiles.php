<?php

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\AbstractParser;
use DeviceDetector\Parser\Device\AbstractDeviceParser;

include __DIR__ . '/../vendor/autoload.php';

AbstractParser::setVersionTruncation(AbstractParser::VERSION_TRUNCATION_NONE);

$fixtureFiles = glob(__DIR__ . '/../Tests/fixtures/*.yml');

$overwrite = !empty($argv[1]) && '--f' === $argv[1];
$data      = [];

foreach ($fixtureFiles as $file) {
    if ('unknown' !== basename($file, '.yml') && !in_array(preg_replace('/-[0-9]+$/', '', str_replace('_', ' ', basename($file, '.yml'))), array_keys(AbstractDeviceParser::getAvailableDeviceTypes()))) {
        continue;
    }

    $fileFixtures = Spyc::YAMLLoad(file_get_contents($file));

    foreach ($fileFixtures as $fixture) {
        if ($overwrite) {
            $fixture = DeviceDetector::getInfoFromUserAgent($fixture['user_agent']);
        }

        $data[$fixture['device']['type']][] = $fixture;
    }
}

foreach ($data as $deviceType => $fixtures) {
    $fixtures = array_values(array_map('unserialize', array_unique(array_map('serialize', $fixtures))));

    usort($fixtures, static function ($a, $b) {
        if (empty($b)) {
            return -1;
        }

        if (@$a['device']['brand'] === @$b['device']['brand']) {
            if ($a['device']['model'] === $b['device']['model']) {
                if (@$a['os']['name'] === @$b['os']['name']) {
                    if (@$a['os']['version'] === @$b['os']['version']) {
                        if (@$a['client']['name'] === @$b['client']['name']) {
                            if (@$a['client']['version'] === @$b['client']['version']) {
                                if ($a['user_agent'] === $b['user_agent']) {
                                    return 0;
                                }

                                return strtolower($a['user_agent']) < strtolower($b['user_agent']) ? -1 : 1;
                            }

                            return (isset($a['client']['version']) ? $a['client']['version'] : '') < (isset($b['client']['version']) ? $b['client']['version'] : '') ? -1 : 1;
                        }

                        return (isset($a['client']['name']) ? $a['client']['name'] : '') < (isset($b['client']['name']) ? $b['client']['name'] : '') ? -1 : 1;
                    }

                    return (isset($a['os']['version']) ? $a['os']['version'] : '') < (isset($b['os']['version']) ? $b['os']['version'] : '') ? -1 : 1;
                }

                return (isset($a['os']['name']) ? $a['os']['name'] : '') < (isset($b['os']['name']) ? $b['os']['name'] : '') ? -1 : 1;
            }

            return (isset($a['device']['model']) ? $a['device']['model'] : '') < (isset($b['device']['model']) ? $b['device']['model'] : '') ? -1 : 1;
        }

        return (isset($a['device']['brand']) ? $a['device']['brand'] : '') < (isset($b['device']['brand']) ? $b['device']['brand'] : '') ? -1 : 1;
    });

    $chunks = array_chunk($fixtures, 500);

    foreach ($chunks as $nr => $chunk) {
        $content = Spyc::YAMLDump($chunk, false, 0);

        $content = str_replace(": ON\n", ": 'ON'\n", $content);
        $content = str_replace(": NO\n", ": 'NO'\n", $content);

        if (empty($deviceType)) {
            $deviceType = 'unknown';
        }

        if ($nr > 0) {
            file_put_contents(
                sprintf(
                    __DIR__ . '/../Tests/fixtures/%s-%s.yml',
                    str_replace(' ', '_', $deviceType),
                    $nr
                ),
                $content
            );
        } else {
            file_put_contents(sprintf(__DIR__ . '/../Tests/fixtures/%s.yml', str_replace(' ', '_', $deviceType)), $content);
        }
    }
}

shell_exec("sed -i -e 's/version: \\([^\"].*\\)/version: \"\\1\"/g' " . __DIR__ . '/../Tests/fixtures/*.yml');

$botFixtures = Spyc::YAMLLoad(file_get_contents(__DIR__ . '/../Tests/fixtures/bots.yml'));

foreach ($botFixtures as &$fixture) {
    if (!$overwrite) {
        continue;
    }

    $fixture = DeviceDetector::getInfoFromUserAgent($fixture['user_agent']);
}

usort($botFixtures, static function ($a, $b) {
    if (empty($b)) {
        return -1;
    }

    if (@$a['bot']['name'] === @$b['bot']['name']) {
        if ($a['user_agent'] === $b['user_agent']) {
            return 0;
        }

        return strtolower($a['user_agent']) < strtolower($b['user_agent']) ? -1 : 1;
    }

    return @$a['bot']['name'] < @$b['bot']['name'] ? -1 : 1;
});

file_put_contents(__DIR__ . '/../Tests/fixtures/bots.yml', Spyc::YAMLDump($botFixtures, false, 0));

echo "done.\n";

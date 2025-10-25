<?php

// Simple YAML to JSON converter for OpenAPI spec
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$yamlContent = file_get_contents(__DIR__ . '/docs/openapi.yaml');
$data = Yaml::parse($yamlContent);
$json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

file_put_contents(__DIR__ . '/storage/api-docs/api-docs.json', $json);

echo "✓ Converted openapi.yaml to api-docs.json\n";

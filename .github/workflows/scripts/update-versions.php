// scripts/update-versions.php

<?php

// Get the version from the GitHub release tag, removing the 'v' prefix if present
$version = ltrim($argv[1], 'v');

// Update composer.json
$composerJson = json_decode(file_get_contents('composer.json'), true);
$composerJson['version'] = $version;
file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Update package.json
$packageJson = json_decode(file_get_contents('package.json'), true);
$packageJson['version'] = $version;
file_put_contents('package.json', json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

?>

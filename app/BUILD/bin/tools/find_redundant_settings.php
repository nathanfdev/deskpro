<?php

echo "Searching... (may take several minutes)".PHP_EOL;

// Directories to search within
$searchInDirs = [
    // System
    // -----------------------------------------------------------------------------
    __DIR__.'/../../src',

    // Front end "web"
    // -----------------------------------------------------------------------------
    __DIR__.'/../../../../www/assets/BUILD/web/app',
    __DIR__.'/../../../../www/assets/BUILD/web/bundles',
    __DIR__.'/../../../../www/assets/BUILD/web/javascripts',
    __DIR__.'/../../../../www/assets/BUILD/web/loader',

    // Front end "pub"
    // -----------------------------------------------------------------------------
    __DIR__.'/../../../../www/assets/BUILD/pub/bin',
    __DIR__.'/../../../../www/assets/BUILD/pub/build-tools',
    __DIR__.'/../../../../www/assets/BUILD/pub/src',
];

// Avoid PHP execution for settings to stop kernel/auto-loading
preg_match_all(
    '/(\\\'|\")(.*)(\\\'|\").*=>/',
    file_get_contents(__DIR__.'/../../sys/config/settings.php'),
    $matches
);

// Clean up and escape settings keys ready for grep
$settingKeys = array_map(function ($key) {
    return [$key, str_replace('.', '\.', $key)];
}, array_filter($matches[2]));

// Grep for each setting
$redundantSettingKeys = [];
foreach ($settingKeys as list($settingKey, $settingKeyEscaped)) {
    $flag = false;
    foreach ($searchInDirs as $searchInDir) {
        echo '.';
        if (!empty(trim(`cd {$searchInDir} && grep -Rl "{$settingKeyEscaped}" *`))) {
            $flag &= true;
        }
    }
    if (false === $flag) {
        $redundantSettingKeys[] = $settingKey;
    }
}

$outputFile = sys_get_temp_dir().'/'.sprintf('redundant_settings_%s.txt', date('YmdHis'));
$h = fopen($outputFile, 'a');
foreach ($redundantSettingKeys as $redundantSettingKey) {
    fwrite($h, $redundantSettingKey.PHP_EOL);
}
fclose($h);

echo PHP_EOL."Done.".PHP_EOL;
echo " + Output file: {$outputFile}".PHP_EOL;

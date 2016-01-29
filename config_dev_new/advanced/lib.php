<?php

function dev_config_read_file($f)
{
    $f = explode("\n", trim(file_get_contents(__DIR__.'/../'.$f)));
    $f = array_values(array_filter($f, function ($l) { return !($l[0] === '#' || $l[0] === ';' || trim($l[0]) === ''); }));

    return trim($f[0]);
}

function dev_config_read_full_file($f)
{
    return trim(file_get_contents(__DIR__.'/../'.$f));
}
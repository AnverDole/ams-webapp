<?php

require_once __DIR__ . "/../app/flow.php";
checkFlowAccess();


require_once __DIR__ . "/../app/configs.php";

function assetUrl($url, $enableCatchBursting = false)
{
    global $config;

    if (!$enableCatchBursting)  return globalUrlPath($url);

    if ($config->environment == "local") {
        return globalUrlPath($url . "?v=" . time());
    } else if ($config->environment == "production") {
        return globalUrlPath($url . "?v=" . $config->catch_version_suffix);
    }

    return globalUrlPath($url);
}

function urlPath($path)
{
    global $urlConfig;

    $path = preg_replace("/^\//", "", $path);
    $root = preg_replace("/\/$/", "", $urlConfig->root);

    return $root . "/" . $path;
}
function globalUrlPath($path)
{
    global $urlConfig;

    $url =  $urlConfig->domain . "/" . $urlConfig->root . "/" . $path;
    return str_replace(':/', '://', trim(preg_replace('/\/+/', '/', $url), '/'));
}

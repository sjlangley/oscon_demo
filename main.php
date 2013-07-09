<?php

require_once 'config.php';
require_once "Twig/Autoloader.php";
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($loader, $twig_options);

$template = $twig->loadTemplate('hello.tpl');
echo $template->render(['name' => 'Stuart']);

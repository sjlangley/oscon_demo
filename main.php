<?php

require_once "Twig/Autoloader.php";
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates/');
$twig = new Twig_Environment($loader, [
  'cache' => 'gs://oscon-demo.appspot.com/cache',
]);

$template = $twig->loadTemplate('hello.tpl');

echo $template->render(['name' => 'Stuart']);

echo 'Hello, world!';
<?php

require '../bootstrap.php';

use App\Controller\ProducerController;


$producerController = new ProducerController();

$producerController->createOne();
$producerController->getAll();
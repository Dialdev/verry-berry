<?php

require_once 'include/autoloader.php';

(new classes\FacebookFeed)->create();

(new classes\GoogleFeed)->create();

(new classes\YmlFeed)->create();

(new classes\CsvFeed)->create();

(new classes\GoogleCsvFeed)->create();

<?php
$arUrlRewrite=array (
  3 => 
  array (
    'CONDITION' => '#^/catalog/([a-zA-Z0-9_-]*)/page-([\\d]+)/.*#',
    'RULE' => 'SECTION_CODE=$1&PAGEN_1=$2',
    'ID' => NULL,
    'PATH' => '/catalog/index.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/catalog/([a-zA-Z0-9_-]*)/(.*)/.*#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/404.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/product/([a-zA-Z0-9_-]*)/[^\\?].*#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/404.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/catalog/([a-zA-Z0-9_-]*)/.*#',
    'RULE' => 'SECTION_CODE=$1',
    'ID' => NULL,
    'PATH' => '/catalog/index.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/product/([a-zA-Z0-9_-]*)/.*#',
    'RULE' => 'ELEMENT_CODE=$1',
    'ID' => NULL,
    'PATH' => '/product/index.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobileapp/jn.php',
    'SORT' => 100,
  ),
  9 => 
  array (
    'CONDITION' => '#^/exclusive/page-([\\d]+)/.*#',
    'RULE' => 'PAGEN_1=$1',
    'ID' => NULL,
    'PATH' => '/exclusive/index.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  8 => 
  array (
    'CONDITION' => '#^/api/v1/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/api/v1/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  10 => 
  array (
    'CONDITION' => '#^/test/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog',
    'PATH' => '/test/index.php',
    'SORT' => 100,
  ),
);

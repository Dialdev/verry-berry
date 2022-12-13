<?
//Отключаем статистику Bitrix
define("NO_KEEP_STATISTIC", true);
//Подключаем движок
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
//устанавливаем тип ответа как xml документ
header('Content-Type: application/xml; charset=utf-8');


$array_pages = array();

//Простые текстовые страницы: начало
$array_pages[] = array(
   	'NAME' => 'Главная страница',
   	'URL' => '/',
);
$array_pages[] = array(
   	'NAME' => 'О нас',
   	'URL' => '/o-nas/',
);
$array_pages[] = array(
   	'NAME' => 'Оплата',
   	'URL' => '/payment/',
);
$array_pages[] = array(
   	'NAME' => 'Доставка',
   	'URL' => '/delivery/',
);
$array_pages[] = array(
	'NAME' => 'Самовывоз',
   	'URL' => '/pickup/',
);
$array_pages[] = array(
   	'NAME' => 'Помощь',
   	'URL' => '/faq/',
);
$array_pages[] = array(
   	'NAME' => 'Контакты',
   	'URL' => '/contacts/',
);
$array_pages[] = array(
	'NAME' => 'Эксклюзив',
	'URL' => '/exclusive/',
);
$array_pages[] = array(
	'NAME' => 'Акции',
	'URL' => '/sales/',
);
$array_pages[] = array(
	'NAME' => 'Карта сайта',
	'URL' => '/karta/',
);
//Простые текстовые страницы: конец


$array_iblocks_id = array('5'); //ID инфоблоков, разделы и элементы которых попадут в карту сайта
if(CModule::IncludeModule("iblock"))
{
	foreach($array_iblocks_id as $iblock_id)
	{
		//Список разделов
   		$res = CIBlockSection::GetList(
	      	array(),
	      	Array(
	         	"IBLOCK_ID" => $iblock_id,
	         	"ACTIVE" => "Y"
	      	),
      		false,
    		array(
    		"ID",
    		"NAME",
    		"SECTION_PAGE_URL",
    		"TIMESTAMP_X"
    	));
   		while($ob = $res->GetNext())
   		{
			$array_pages_iblock[] = array(
			   	'NAME' => $ob['NAME'],
			   	'URL' => $ob['SECTION_PAGE_URL'],
			   	'LASTMOD' => $ob['TIMESTAMP_X']
			);
   		}
		//Список элементов
   		$res = CIBlockElement::GetList(
	      	array(),
	      	Array(
	         	"IBLOCK_ID" => $iblock_id,
	         	"ACTIVE_DATE" => "Y",
	         	"ACTIVE" => "Y"
	      	),
      		false,
      		false,
    		array(
    		"ID",
    		"NAME",
    		"DETAIL_PAGE_URL",
    		"TIMESTAMP_X"
    	));
   		while($ob = $res->GetNext())
   		{
			$array_pages_iblock[] = array(
			   	'NAME' => $ob['NAME'],
			   	'URL' => $ob['DETAIL_PAGE_URL'],
			   	'LASTMOD' => $ob['TIMESTAMP_X']
			);
   		}
	}
}

//Создаём XML документ: начало
//echo '<pre>'; print_r($array_pages); echo '</pre>';
$xml_content = '';
$xml_content_iblock = '';
$dateformat = 'Y-m-d';
$site_url = 'https://'.$_SERVER['HTTP_HOST'];
$quantity_elements = 0;
foreach($array_pages as $v){
	$quantity_elements++;
	if ($quantity_elements == 1){
		$priority = 1;
	} else {
		$priority = 0.8;
	}
	$page_url = mb_substr( $v['URL']."index.php", 1);
	$lastmod = date($dateformat, filemtime($page_url));
	$xml_content.='
		<url>
			<loc>'.$site_url.$v['URL'].'</loc>
			<lastmod>'.$lastmod.'</lastmod>
			<priority>'.$priority.'</priority>
			<changefreq>weekly</changefreq>
		</url>
	';
}
foreach($array_pages_iblock as $v){
	$quantity_elements++;
	$priority = 0.6;
	$lastmod = date($dateformat, MakeTimeStamp($v['LASTMOD'], "DD.MM.YYYY HH:MI:SS"));
	$xml_content_iblock.='
		<url>
			<loc>'.$site_url.$v['URL'].'</loc>
			<lastmod>'.$lastmod.'</lastmod>
			<priority>'.$priority.'</priority>
			<changefreq>weekly</changefreq>
		</url>
	';
}
$quantity_elements = 0;

//Создаём XML документ: конец

//Выводим документ
echo '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	'.$xml_content.''.$xml_content_iblock.'
</urlset>
';
?>
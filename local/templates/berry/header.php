<?php

use Bitrix\Main\Page\Asset;
use Natix\Data\Bitrix\UserContainerInterface;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \CMain $APPLICATION */

$asset = Asset::getInstance();

/** @var UserContainerInterface $userContainer  */
$userContainer = \Natix::$container->get(UserContainerInterface::class);
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="ie=edge" http-equiv="X-UA-Compatible">
        <meta name="facebook-domain-verification" content="791ik1vwbhao3qqqtlwgrwh8i8bbno" />


        <?php
        $asset->addCss( SITE_TEMPLATE_PATH . '/template_styles.css');
		$asset->addCss( SITE_TEMPLATE_PATH . '/styles_new.css');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/main.js?v=1');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/common.js?v=1');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/vue.js?v=1');
        $asset->addJs(SITE_TEMPLATE_PATH . '/js/utils.js?v=1');
        $asset->addJs('https://widget.cloudpayments.ru/bundles/cloudpayments');

$APPLICATION->ShowHead();

        ?>
<title><?php $APPLICATION->ShowTitle(); ?></title>
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_TEMPLATE_PATH; ?>/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_TEMPLATE_PATH; ?>/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_TEMPLATE_PATH; ?>/favicon/favicon-16x16.png">
        <link rel="manifest" href="<?php echo SITE_TEMPLATE_PATH; ?>/favicon/site.webmanifest">
        <link rel="mask-icon" href="<?php echo SITE_TEMPLATE_PATH; ?>/favicon/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="theme-color" content="#ffffff">
        <!-- Facebook Pixel Code -->
        <script data-skip-moving="true" type="text/javascript">
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '2086760161623295');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none"
                       src="https://www.facebook.com/tr?id=2086760161623295&ev=PageView&noscript=1"
            /></noscript>
        <!-- End Facebook Pixel Code -->

        <!-- Google Tag Manager -->
        <script data-skip-moving="true">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-PCT9MQS');</script>
        <!-- End Google Tag Manager -->
        
        <!-- Rating Mail.ru counter -->
        <script data-skip-moving="true" type="text/javascript">
            var _tmr = window._tmr || (window._tmr = []);
            _tmr.push({id: "3208143", type: "pageView", start: (new Date()).getTime(), pid: "USER_ID"});
            (function (d, w, id) {
                if (d.getElementById(id)) return;
                var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
                ts.src = "https://top-fwz1.mail.ru/js/code.js";
                var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
                if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
            })(document, window, "topmailru-code");
        </script>
        <!-- //Rating Mail.ru counter -->
    </head>

<body class="<?php echo $APPLICATION->ShowViewContent('body-css-class'); ?>">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PCT9MQS"
                      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- Rating Mail.ru counter (noscript) -->
    <noscript><div><img src="https://top-fwz1.mail.ru/counter?id=3208143;js=na" style="border:0;position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div></noscript>
    <!-- //Rating Mail.ru counter (noscript) -->
<?php $APPLICATION->ShowPanel(); ?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="position: absolute; width: 0; height: 0">
<symbol viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg" id="exit">
<path d="M26.0704 11.9067C26.9118 11.9067 27.5938 11.1669 27.5938 10.2542V6.61016C27.5938 2.96532 24.8601 0 21.5 0L6.09375 0C2.73366 0 0 2.96532 0 6.61016V32.3898C0 36.0346 2.73366 39 6.09375 39H21.5C24.8601 39 27.5938 36.0346 27.5938 32.3898C27.5938 31.4772 26.9118 30.7373 26.0704 30.7373C25.229 30.7373 24.5469 31.4772 24.5469 32.3898C24.5469 34.2123 23.1801 35.6949 21.5 35.6949H6.09375C4.4137 35.6949 3.04688 34.2123 3.04688 32.3898V6.61016C3.04688 4.78774 4.4137 3.30508 6.09375 3.30508H21.5C23.1801 3.30508 24.5469 4.78774 24.5469 6.61016V10.2542C24.5469 11.1669 25.229 11.9067 26.0704 11.9067Z" fill="#182037"/>
<path d="M26.0704 27.0933C26.9118 27.0933 27.5938 27.8331 27.5938 28.7458V32.3898C27.5938 36.0347 24.8601 39 21.5 39L6.09375 39C2.73366 39 0 36.0347 0 32.3898V6.61022C0 2.96537 2.73366 0 6.09375 0H21.5C24.8601 0 27.5938 2.96537 27.5938 6.61022C27.5938 7.52282 26.9118 8.26273 26.0704 8.26273C25.229 8.26273 24.5469 7.52282 24.5469 6.61022C24.5469 4.78774 23.1801 3.30511 21.5 3.30511H6.09375C4.4137 3.30511 3.04688 4.78774 3.04688 6.61022V32.3898C3.04688 34.2123 4.4137 35.6949 6.09375 35.6949H21.5C23.1801 35.6949 24.5469 34.2123 24.5469 32.3898V28.7458C24.5469 27.8331 25.229 27.0933 26.0704 27.0933Z" fill="#182037"/>
<path d="M33.4563 21.0363L14.8187 21.0363C14.2052 21.0363 13.7 20.5454 13.7 19.9302C13.7 19.3151 14.2053 18.8242 14.8187 18.8242L33.7093 18.8242L29.9473 14.5418L29.9467 14.5411C29.5406 14.0762 29.592 13.3752 30.0582 12.9723L30.0586 12.9719C30.5225 12.5725 31.2264 12.6181 31.6329 13.076L31.7086 13.1612L31.7086 13.1623L36.9117 19.064C37.1656 19.2802 37.3092 19.599 37.2995 19.9328L37.2995 19.9334C37.2892 20.268 37.1264 20.5787 36.8586 20.7796L31.5802 25.9776C31.1441 26.407 30.439 26.4076 30.0028 25.9785C29.5679 25.5571 29.5614 24.8669 29.9888 24.4377L29.9885 24.4374L29.9985 24.4283C29.9987 24.4281 29.9992 24.4277 30.0001 24.4268L30.0042 24.4229L33.4563 21.0363Z" fill="#182037" stroke="#182037" stroke-width="0.6"/>
</symbol>
</svg>
<div class="main">
    <div class="container firstContainer">
        <div class="firstInner"></div>
    </div>
    <header class="header">
        <div class="container">
            <div class="header__inner">
                <div class="header-top">
                    <?php $APPLICATION->IncludeComponent(
                        'bitrix:menu',
                        'top',
                        [
                            'ROOT_MENU_TYPE' => 'top',
                            'MENU_CACHE_TYPE' => 'A',
                            'MENU_CACHE_TIME' => '3600',
                            'MENU_CACHE_USE_GROUPS' => 'Y',
                            'MENU_CACHE_GET_VARS' => '',
                            'MAX_LEVEL' => '1',
                            'CHILD_MENU_TYPE' => '',
                            'USE_EXT' => 'N',
                            'DELAY' => 'N',
                            'ALLOW_MULTI_SELECT' => 'N',
                        ],
                        false
                    ); ?>

                    <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                        [
                            'AREA_FILE_SHOW' => 'file',
                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/header_phone.php',
                            'EDIT_TEMPLATE' => ''
                        ],
                        false
                    );?>
					<?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                        [
                            'AREA_FILE_SHOW' => 'file',
							'PATH' => SITE_TEMPLATE_PATH . '/page_templates/header_socials_icons.php',
                            'EDIT_TEMPLATE' => ''
                        ],
                        false
                    );?>
                    <?php if ($userContainer->isAuthorized()) { 
					
					$json = file_get_contents("http://".$_SERVER["SERVER_NAME"]."/api/v1/sale/order/bonus/?USER_ID=".$USER->GetID());
					$bonus = json_decode($json, true);
					?>
                        <a class="header__user" href="/personal/history/">
                            <span class="text">
                                <?php echo sprintf(
                                    'Здравствуйте, <span>%s %s</span>',
                                    $userContainer->getFirstName(),
                                    $userContainer->getLastName()
                                ); ?>
                            </span>
							<span class="user-bonus">У вас <span class="user-bonus__count"><?=$bonus["data"]["CURRENT_BUDGET_FORMATTED"]?></span></span>
                       </a>
                    <?php } else { ?>
                        <div class="header__user" data-micromodal-trigger="modal-login" id="auth_open_modal">
                            <span class="text"><span>Авторизоваться</span></span>
                        </div>
                    <?php } ?>

                </div>
                <div class="header-main">
                    <div class="hamburger hamburger--slider popup-mobile-link">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div><span>Меню</span>
                    </div>
                    <a class="logo" href="/">
                        <svg><use xlink:href="#logo"></use></svg>
                    </a>

                    <?php
                    $APPLICATION->IncludeComponent('bitrix:menu', 'catalog',
                        [
                            'COMPONENT_TEMPLATE' => 'catalog',
                            'ROOT_MENU_TYPE' => 'catalog',	// Тип меню для первого уровня
                            'MENU_CACHE_TYPE' => 'N',	// Тип кеширования
                            'MENU_CACHE_TIME' => 3600,	// Время кеширования (сек.)
                            'MENU_CACHE_USE_GROUPS' => 'Y',	// Учитывать права доступа
                            'MENU_CACHE_GET_VARS' => [    // Значимые переменные запроса
                                                          0 => '',
                            ],
                            'MAX_LEVEL' => 1,	// Уровень вложенности меню
                            'CHILD_MENU_TYPE' => '',	// Тип меню для остальных уровней
                            'USE_EXT' => 'Y',	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                            'DELAY' => 'N',	// Откладывать выполнение шаблона меню
                            'ALLOW_MULTI_SELECT' => 'N',	// Разрешить несколько активных пунктов одновременно
                        ],
                        false
                    );
                    ?>

                    <?php $APPLICATION->IncludeComponent('natix:geo.location', 'header'); ?>

                    <div class="header__icons">
                        <?php /* @todo вывести после внедрения функционала избранных товаров
                        <a class="header-icon header-icon_like" href="#">
                        <div class="count">3</div>
                        <svg><use xlink:href="#heart"></use></svg>
                        </a>*/?>
                        <a class="header-icon header-icon_basket js-basket-small" href="/personal/order/make/">
                            <?php $APPLICATION->IncludeFile(SITE_DIR . '/local/include/basket_small.php'); ?>
                        </a>
                    </div>
                </div>
                <div class="overlay" data-dismiss="popup"></div>
                <div class="popup-mobile">
                    <div class="container">
                        <div class="popup-mobile__inner">
                            <div class="mobile-sm">
                                <?php
                                $APPLICATION->IncludeComponent('bitrix:menu', 'catalog.mobile',
                                    [
                                        'COMPONENT_TEMPLATE' => 'catalog.mobile',
                                        'ROOT_MENU_TYPE' => 'catalog',	// Тип меню для первого уровня
                                        'MENU_CACHE_TYPE' => 'N',	// Тип кеширования
                                        'MENU_CACHE_TIME' => 3600,	// Время кеширования (сек.)
                                        'MENU_CACHE_USE_GROUPS' => 'Y',	// Учитывать права доступа
                                        'MENU_CACHE_GET_VARS' => [    // Значимые переменные запроса
                                                                      0 => '',
                                        ],
                                        'MAX_LEVEL' => 1,	// Уровень вложенности меню
                                        'CHILD_MENU_TYPE' => '',	// Тип меню для остальных уровней
                                        'USE_EXT' => 'Y',	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                                        'DELAY' => 'N',	// Откладывать выполнение шаблона меню
                                        'ALLOW_MULTI_SELECT' => 'N',	// Разрешить несколько активных пунктов одновременно
                                    ],
                                    false
                                );
                                ?>
                                <div class="hr"></div>
                                <?php if ($userContainer->isAuthorized()) { ?>
                                    <a class="header__user" href="/personal/history/">
                                        <span class="text">
                                            <?php echo sprintf(
                                                'Здравствуйте, <span>%s %s</span>',
                                                $userContainer->getFirstName(),
                                                $userContainer->getLastName()
                                            ); ?>
                                        </span>
                                    </a>
                                    <a class="mobile-next" href="/personal/history/">
                                        <div class="mobile-lk-link">Личный кабинет</div>
                                    </a>
                                <?php } else { ?>
                                    <div class="mobile-next" data-micromodal-trigger="modal-login">
                                        <div class="mobile-lk-link">Войти в личный кабинет</div>
                                    </div>
                                <?php } ?>

                                <?php $APPLICATION->IncludeComponent('natix:geo.location', 'mobile'); ?>

                                <div class="hr"></div>
                                <div class="mobile-contact">
                                    <div class="mobile-contact__title">Контакты</div>
                                    <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                                        [
                                            'AREA_FILE_SHOW' => 'file',
                                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/mobile_header_phone.php',
                                            'EDIT_TEMPLATE' => ''
                                        ],
                                        false
                                    );?>
                                    <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                                        [
                                            'AREA_FILE_SHOW' => 'file',
                                            'PATH' => SITE_TEMPLATE_PATH . '/page_templates/mobile_header_email.php',
                                            'EDIT_TEMPLATE' => ''
                                        ],
                                        false
                                    );?>
                                </div>
                                <div class="hr"></div>
                            </div>
                            <?php $APPLICATION->IncludeComponent(
                                'bitrix:menu',
                                'top-mobile',
                                [
                                    'ROOT_MENU_TYPE' => 'top',
                                    'MENU_CACHE_TYPE' => 'A',
                                    'MENU_CACHE_TIME' => '3600',
                                    'MENU_CACHE_USE_GROUPS' => 'Y',
                                    'MENU_CACHE_GET_VARS' => '',
                                    'MAX_LEVEL' => '1',
                                    'CHILD_MENU_TYPE' => '',
                                    'USE_EXT' => 'N',
                                    'DELAY' => 'N',
                                    'ALLOW_MULTI_SELECT' => 'N',
                                ],
                                false
                            ); ?>
                            <?php
                            if ($userContainer->isAuthorized()) {
                                $APPLICATION->IncludeComponent(
                                    'bitrix:menu',
                                    'header-personal',
                                    [
                                        'ROOT_MENU_TYPE' => 'personal',
                                        'MENU_CACHE_TYPE' => 'N',
                                        'MENU_CACHE_TIME' => '3600',
                                        'MENU_CACHE_USE_GROUPS' => 'Y',
                                        'MENU_CACHE_GET_VARS' => '',
                                        'MAX_LEVEL' => '1',
                                        'CHILD_MENU_TYPE' => '',
                                        'USE_EXT' => 'Y',
                                        'DELAY' => 'N',
                                        'ALLOW_MULTI_SELECT' => 'N',
                                    ],
                                    false
                                );
                            } ?>

                            <div class="navBlock">
                                <a class="navBlock__title" href="mailto:veryberrylab@gmail.com">veryberrylab@gmail.com</a>
                                <div class="navBlock__title">Контакты</div>
                                <?php $APPLICATION->IncludeComponent(
                                    'bitrix:news.list',
                                    'social_mobile',
                                    [
                                        'ACTIVE_DATE_FORMAT' => 'd.m.Y',
                                        'ADD_SECTIONS_CHAIN' => 'N',
                                        'AJAX_MODE' => 'N',
                                        'AJAX_OPTION_ADDITIONAL' => '',
                                        'AJAX_OPTION_HISTORY' => 'N',
                                        'AJAX_OPTION_JUMP' => 'N',
                                        'AJAX_OPTION_STYLE' => 'Y',
                                        'CACHE_FILTER' => 'N',
                                        'CACHE_GROUPS' => 'Y',
                                        'CACHE_TIME' => '36000000',
                                        'CACHE_TYPE' => 'A',
                                        'CHECK_DATES' => 'Y',
                                        'DETAIL_URL' => '',
                                        'DISPLAY_BOTTOM_PAGER' => 'Y',
                                        'DISPLAY_DATE' => 'N',
                                        'DISPLAY_NAME' => 'Y',
                                        'DISPLAY_PICTURE' => 'Y',
                                        'DISPLAY_PREVIEW_TEXT' => 'N',
                                        'DISPLAY_TOP_PAGER' => 'N',
                                        'FIELD_CODE' => [],
                                        'FILTER_NAME' => '',
                                        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                                        'IBLOCK_ID' => '13',
                                        'IBLOCK_TYPE' => 'content',
                                        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                                        'INCLUDE_SUBSECTIONS' => 'N',
                                        'MESSAGE_404' => '',
                                        'NEWS_COUNT' => '20',
                                        'PAGER_BASE_LINK_ENABLE' => 'N',
                                        'PAGER_DESC_NUMBERING' => 'N',
                                        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                                        'PAGER_SHOW_ALL' => 'N',
                                        'PAGER_SHOW_ALWAYS' => 'N',
                                        'PAGER_TEMPLATE' => '.default',
                                        'PAGER_TITLE' => 'Новости',
                                        'PARENT_SECTION' => '',
                                        'PARENT_SECTION_CODE' => '',
                                        'PREVIEW_TRUNCATE_LEN' => '',
                                        'PROPERTY_CODE' => [],
                                        'SET_BROWSER_TITLE' => 'N',
                                        'SET_LAST_MODIFIED' => 'N',
                                        'SET_META_DESCRIPTION' => 'N',
                                        'SET_META_KEYWORDS' => 'N',
                                        'SET_STATUS_404' => 'N',
                                        'SET_TITLE' => 'N',
                                        'SHOW_404' => 'N',
                                        'SORT_BY1' => 'SORT',
                                        'SORT_BY2' => 'SORT',
                                        'SORT_ORDER1' => 'ASC',
                                        'SORT_ORDER2' => 'ASC',
                                        'STRICT_SECTION_CHECK' => 'N',
                                        'COMPONENT_TEMPLATE' => 'social_mobile'
                                    ],
                                    false
                                );?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
<?php if ($APPLICATION->GetCurDir() !== '/') {
    $APPLICATION->IncludeComponent(
        'bitrix:breadcrumb',
        '.default',
        [
            'START_FROM' => '0',
            'PATH' => '',
            'SITE_ID' => '-',
        ],
        false
    );
}

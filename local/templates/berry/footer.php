<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \CMain $APPLICATION */
/** @var \Natix\Data\Bitrix\UserContainerInterface $userContainer */
$userContainer = \Natix::$container->get(\Natix\Data\Bitrix\UserContainerInterface::class);
?>

    <div class="container">
        <div class="footerTop">
            <a class="footerTop__logoMob" href="/">
                <svg width="24" height="33">
                    <use xlink:href="#berry"></use>
                </svg>
            </a>
            <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                [
                    'AREA_FILE_SHOW' => 'file',
                    'PATH' => SITE_TEMPLATE_PATH . '/page_templates/footer_phone.php',
                    'EDIT_TEMPLATE' => ''
                ],
                false
            );?>
            <a class="footerTop__logo" href="/">
                <svg>
                    <use xlink:href="#logo"></use>
                </svg>
            </a>

            <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                [
                    'AREA_FILE_SHOW' => 'file',
                    'PATH' => SITE_TEMPLATE_PATH . '/page_templates/footer_email.php',
                    'EDIT_TEMPLATE' => ''
                ],
                false
            );?>
        </div>
        <div class="footer">
            <div class="footer__items">
                <?php
                $APPLICATION->IncludeComponent('bitrix:menu', 'footer_catalog',
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

                <?php $APPLICATION->IncludeComponent(
                    'bitrix:menu',
                    'footer',
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

                <div class="footer__item" data-move="footer-contact-get">
                    <div class="footer__contact">
                        <div class="footer__title">Контакты</div>
                        <?php $APPLICATION->IncludeComponent(
                            'bitrix:news.list',
                            'footer_social',
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
                                'COMPONENT_TEMPLATE' => 'footer_social'
                            ],
                            false
                        );?>
                    </div>
                    <div class="footer__address" style="visibility: hidden">
                        <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                            [
                                'AREA_FILE_SHOW' => 'file',
                                'PATH' => SITE_TEMPLATE_PATH . '/page_templates/footer_address.php',
                                'EDIT_TEMPLATE' => ''
                            ],
                            false
                        );?>
                    </div>
                </div>

                <div class="footer__item footer__item_last">
                    <div data-move="footer-contact-set"></div>
                    <?php 
                    if ($userContainer->isAuthorized()) {
                        $APPLICATION->IncludeComponent(
                            'bitrix:menu',
                            'footer-personal',
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
                    <div class="footer__hot b-hot">
                        <a class="b-hot__link b-hot__link_dark" href="/exclusive/">
                            <div class="b-hot__linkIcon">
                                <svg width="27" height="19" stroke="#182037" fill="#182037">
                                    <use xlink:href="#crown"></use>
                                </svg>
                            </div>Эксклюзив
                        </a>
                        <a class="b-hot__link b-hot__link_red" href="/sales/">
                            <div class="b-hot__linkIcon">
                                <svg width="27" height="27" stroke="#D52A3F" fill="#D52A3F">
                                    <use xlink:href="#star"></use>
                                </svg>
                            </div>Акции
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footerBottom">
            <div>
                <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/copyright.php',
                        'EDIT_TEMPLATE' => ''
                    ],
                    false
                );?>
            </div>

            <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                [
                    'AREA_FILE_SHOW' => 'file',
                    'PATH' => SITE_TEMPLATE_PATH . '/page_templates/privacy_policy_link.php',
                    'EDIT_TEMPLATE' => ''
                ],
                false
            );?>

            <div>
                <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/sitemap.php',
                        'EDIT_TEMPLATE' => ''
                    ],
                    false
                );?>
            </div>

            <!--div data-empty></div-->

            <div class="footerBottom__develop">
                <?php $APPLICATION->IncludeComponent('bitrix:main.include','',
                    [
                        'AREA_FILE_SHOW' => 'file',
                        'PATH' => SITE_TEMPLATE_PATH . '/page_templates/developer.php',
                        'EDIT_TEMPLATE' => ''
                    ],
                    false
                );?>
            </div>
            <div style="width: 100%">ИП Нарыжных Екатерина Александровна, ИНН 772914747473, ОГРНИП 319774600471801. Юр.адрес 119501, г. Москва, ул.Веерная 42</div>
        </div>
    </div>
</div>

<?php
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/fast_order.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/login.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/location.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/phone-auth.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/register.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/recover.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/confirm_code.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/new_password.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/info.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/order_ok.php');
$APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/modals/gallery.php');

if ($_GET['open_auth'] === 'Y') {
    $asset->addJs(SITE_TEMPLATE_PATH . '/js/open_modal.js');
}
?>
<script>
    const modalConfig = {
        disableScroll: true,
        awaitOpenAnimation: true,
        awaitCloseAnimation: true,
        onShow: modal => {
            const container = modal.querySelector('.modal__container');
            // console.log(container.scrollHeight);
            // console.log(container.offsetHeight);

            if (container.scrollHeight <= container.offsetHeight) {
                container.classList.add('v-center')
            }
            modal.dispatchEvent(new CustomEvent("modal-open", { bubbles: true }));
        }
    };

    MicroModal.init(modalConfig);
</script>
<?php $asset->addJs(SITE_TEMPLATE_PATH . '/js/jquery-3.3.1.min.js'); ?>
<script>jQuery.noConflict()</script>
<?php $asset->addJs(SITE_TEMPLATE_PATH . '/js/setLocation.js?v=1.2'); ?>
<?php $asset->addJs(SITE_TEMPLATE_PATH . '/js/phone-auth.js?v=1.0'); ?>
<script src="//code.jivosite.com/widget/7qsEoNvZEr" async></script>
</body>
</html>

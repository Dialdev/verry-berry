<?php

namespace Natix\UI;

use Bitrix\Main\Context;
use Bitrix\Main\Web;

/**
 * Кастомизированный класс битрикса \Bitrix\Main\UI\PageNavigation
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class PageNavigation extends \Bitrix\Main\UI\PageNavigation
{
    /**
     * Initializes the navigation from URI.
     */
    public function initFromUri()
    {
        $navParams = array();

        $request = Context::getCurrent()->getRequest();

        if (($value = $request->getQuery($this->id)) !== null) {
            //parameters are in the QUERY_STRING
            $params = explode('-', $value);
            for ($i = 0, $n = count($params); $i < $n; $i += 2) {
                $navParams[$params[$i]] = $params[$i+1];
            }
        } else {
            //probably parametrs are in the SEF URI
            $matches = [];
            if (preg_match("'/page-([\\d]+)?'", $request->getRequestUri(), $matches)) {
                $navParams['page'] = $matches[1];
                if (isset($matches[3])) {
                    $navParams['size'] = $matches[3];
                }
            }
        }

        if (isset($navParams['size'])) {
            //set page size from user request
            if (in_array($navParams['size'], $this->pageSizes)) {
                $this->setPageSize((int)$navParams['size']);
            }
        }

        if (isset($navParams['page'])) {
            if ($navParams['page'] === 'all' && $this->allowAll == true) {
                //show all records in one page
                $this->allRecords = true;
            } else {
                //set current page within boundaries
                $currentPage = (int)$navParams['page'];
                if ($currentPage >= 1) {
                    if ($this->recordCount !== null) {
                        $maxPage = $this->getPageCount();
                        if ($currentPage > $maxPage) {
                            $currentPage = $maxPage;
                        }
                    }
                    $this->setCurrentPage($currentPage);
                }
            }
        }
    }

    /**
     * Returns an URI with navigation parameters compatible with initFromUri().
     * @param Web\Uri $uri
     * @param bool $sef SEF mode.
     * @param string $page Page number.
     * @param string $size Page size.
     * @return Web\Uri
     */
    public function addParams(Web\Uri $uri, $sef, $page, $size = null): Web\Uri
    {
        if ($sef == true) {
            $this->clearParams($uri, $sef);

            $path = $uri->getPath();
            $pos = strrpos($path, '/');
            $path = substr($path, 0, $pos+1).'page-'.$page.'/'.($size !== null ? 'size-'.$size.'/' : '').substr($path, $pos+1);
            $uri->setPath($path);
        } else {
            $uri->addParams(array($this->id => 'page-'.$page.($size !== null? '-size-'.$size : '')));
        }
        return $uri;
    }

    /**
     * Clears an URI from navigation parameters and returns it.
     * @param Web\Uri $uri
     * @param bool $sef SEF mode.
     * @return Web\Uri
     */
    public function clearParams(Web\Uri $uri, $sef): Web\Uri
    {
        if ($sef === true) {
            $path = $uri->getPath();
            $path = preg_replace("'/page-([\\d]|all)+(/size-([\\d]+))?'", '', $path);
            $uri->setPath($path);
        } else {
            $uri->deleteParams(array($this->id));
        }
        return $uri;
    }
}

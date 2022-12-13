<?php

namespace Natix\Module\Api\Slim;

/**
 * @author Artem Luchnikov <artem@luchnikov.ru>
 */
class Response extends \Slim\Http\Response
{
    private $debugMode = false;

    /**
     * @param mixed $data
     * @param null $status
     * @param int $encodingOptions
     * @return \Slim\Http\Response
     */
    public function withJson($data, $status = null, $encodingOptions = 0)
    {
        if ($this->debugMode) {
            $encodingOptions |= JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        }

        return parent::withJson($data, $status, $encodingOptions);
    }

    /**
     * @param bool $debugMode
     */
    public function setDebugMode(bool $debugMode)
    {
        $this->debugMode = $debugMode;
    }
}

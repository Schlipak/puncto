<?php

namespace Puncto;

use Puncto\Interfaces\IRequest;
use \Throwable;

class Request extends Bootstrapable implements IRequest
{
    protected function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{StringHelper::toCamelCase($key)} = $value;
        }
    }

    public function getBody()
    {
        $result = [];

        if ($this->requestMethod === "POST") {
            foreach ($_POST as $key => $value) {
                $result[$key] = filter_var($_POST[$key], FILTER_SANITIZE_SPECIAL_CHARS);
            }

            // @codeCoverageIgnoreStart
            try {
                $json = json_decode(file_get_contents('php://input'));

                if ($json) {
                    foreach ($json as $key => $value) {
                        $result[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                    }
                }
            } catch (Throwable $err) {
                $result['status'] = 'error';
                $result['message'] = 'Invalid input format';
            }
            // @codeCoverageIgnoreEnd
        }

        return $result;
    }

    public function httpAcceptFormats()
    {
        return array_map(function ($format) {
            $parts = explode(';', trim($format));
            return ['format' => trim($parts[0]), 'q' => isset($parts[1]) ? trim($parts[1]) : null];
        }, explode(',', $this->httpAccept));
    }

    public function accepts($format)
    {
        foreach ($this->httpAcceptFormats() as $accepted) {
            if ($accepted['format'] === $format) {
                return true;
            }
        }

        return false;
    }

    /** @codeCoverageIgnore */
    public function __toString()
    {
        $body = "";

        foreach ($this as $key => $value) {
            $body .= "  $key => $value\n";
        }

        return "<#Request\n$body>";
    }
}

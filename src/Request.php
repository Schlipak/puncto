<?php

namespace Puncto;

include_once 'IRequest.php';
include_once 'Bootstrapable.php';

use \Throwable;

class Request extends Bootstrapable implements IRequest
{
    protected function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{$this->toCamelCase($key)} = $value;
        }
    }

    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);
        foreach ($matches[0] as $match) {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }

        return $result;
    }

    public function getBody()
    {
        $result = [];

        if ($this->requestMethod === "POST") {
            foreach ($_POST as $key => $value) {
                $result[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }

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
        }

        return $result;
    }

    public function accepts($format)
    {
        return $this->httpAccept === $format;
    }

    public function __toString()
    {
        $body = "";

        foreach ($this as $key => $value) {
            $body .= "  $key => $value\n";
        }

        return "<#Request\n$body>";
    }
}

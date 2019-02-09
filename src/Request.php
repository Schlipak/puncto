<?php

namespace Puncto;

use Puncto\Interfaces\IRequest;
use Puncto\RequestBody\ParserFactory;
use \Throwable;

class Request extends Bootstrapable implements IRequest
{
    const TEST_KEY = 'PUNCTO_MOCK_PHP_INPUT';

    private $env;

    public function __construct($env)
    {
        parent::__construct();

        $this->env = $env;
    }

    protected function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{StringHelper::toCamelCase($key)} = $value;
        }
    }

    public function getBody()
    {
        $result = [];

        if (in_array($this->requestMethod, ['POST', 'PUT', 'PATCH'])) {
            foreach ($_POST as $key => $value) {
                $result[$key] = filter_var($_POST[$key], FILTER_SANITIZE_SPECIAL_CHARS);
            }

            try {
                $input = '';

                if ($this->env->PUNCTO_ENV === 'development' && isset($this->env->{self::TEST_KEY})) {
                    $input = $this->env->{self::TEST_KEY};
                } else {
                    // @codeCoverageIgnoreStart
                    $input = file_get_contents('php://input');
                    // @codeCoverageIgnoreEnd
                }

                if (!empty($input)) {
                    $parser = ParserFactory::create($this);
                    $decodedInput = $parser->parse($input);

                    if ($decodedInput) {
                        foreach ($decodedInput as $key => $value) {
                            $result[$key] = $value;

                            if (is_string($value)) {
                                $result[$key] = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                            }
                        }
                    }
                }
            } catch (Throwable $err) {
                Logger::warn("  Unprocessable input: {$err->getMessage()}");
            }
        }

        return $result;
    }

    public function contentTypeFormat()
    {
        $contentType = isset($this->contentType) ? $this->contentType : $this->httpContentType;

        $parts = explode(';', trim($contentType));
        $boundary = null;

        if (isset($parts[1]) && preg_match("/^boundary=.+$/", trim($parts[1]))) {
            $boundary = '--' . explode("boundary=", trim($parts[1]))[1];
        }

        return [
            'format' => trim($parts[0]),
            'boundary' => $boundary,
        ];
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
            ob_start();
            print_r($value);
            $valueString = ob_get_clean();

            $body .= "  $key => $valueString\n";
        }

        return "<#Request\n$body>";
    }
}

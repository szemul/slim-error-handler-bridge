<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Exception;

use Slim\Exception\HttpSpecializedException;
use Szemul\SlimErrorHandlerBridge\Enum\ParameterErrorReason;
use Szemul\SlimErrorHandlerBridge\ParameterError\ParameterErrorCollectingInterface;

class HttpUnprocessableEntityException extends HttpSpecializedException implements ParameterErrorCollectingInterface
{
    /** @var int */
    protected $code = 422;

    /** @var string */
    protected $message = 'Unprocessable entity.';

    protected $title       = '422 Unprocessable entity';
    protected $description = 'The server server understands the content type of the request entity, and the syntax of the request entity is correct, but it was unable to process the contained instructions.';

    /** @var array<string,ParameterErrorReason> */
    protected array $parameterErrors = [];

    /**
     * @return array<string,mixed>|null
     */
    public function __debugInfo(): ?array
    {
        return [
            'code'            => $this->code,
            'message'         => $this->message,
            'title'           => $this->title,
            'description'     => $this->description,
            'parameterErrors' => $this->parameterErrors,
            'file'            => $this->file,
            'line'            => $this->line,
            'request'         => '** Instance of ' . get_class($this->request),
        ];
    }

    public function addParameterError(string $name, ParameterErrorReason $errorReason): static
    {
        $this->parameterErrors[$name] = $errorReason;

        return $this;
    }

    public function getParameterErrors(): array
    {
        return $this->parameterErrors;
    }

    public function hasParameterErrors(): bool
    {
        return !empty($this->parameterErrors);
    }
}

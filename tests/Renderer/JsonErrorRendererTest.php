<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\Renderer;

use Mockery;
use Mockery\MockInterface;
use RuntimeException;
use Slim\Exception\HttpBadRequestException;
use Szemul\SlimErrorHandlerBridge\Enum\ParameterErrorReason;
use Szemul\SlimErrorHandlerBridge\Exception\HttpUnprocessableEntityException;
use Szemul\SlimErrorHandlerBridge\Renderer\JsonErrorRenderer;
use PHPUnit\Framework\TestCase;

class JsonErrorRendererTest extends TestCase
{
    public function testInvokeWithNonHttpException(): void
    {
        $exception = new RuntimeException();
        $expected  = [
            JsonErrorRenderer::RESPONSE_FIELD_ERROR_CODE        => '500 - Internal error',
            JsonErrorRenderer::RESPONSE_FIELD_ERROR_DESCRIPTION => 'Unexpected condition encountered preventing server from fulfilling request.',
        ];

        $result = (new JsonErrorRenderer())($exception, true);

        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testInvokeWithBadRequestException(): void
    {
        /** @var HttpBadRequestException $exception */
        $exception = Mockery::mock(HttpBadRequestException::class)->makePartial();
        $expected  = [
            JsonErrorRenderer::RESPONSE_FIELD_ERROR_CODE        => $exception->getTitle(),
            JsonErrorRenderer::RESPONSE_FIELD_ERROR_DESCRIPTION => $exception->getDescription(),
        ];

        $result = (new JsonErrorRenderer())($exception, true);

        $this->assertEquals($expected, json_decode($result, true));
    }

    public function testInvokeWithUnprocessableEntityException(): void
    {
        /** @var HttpUnprocessableEntityException|MockInterface $exception */
        $exception       = Mockery::mock(HttpUnprocessableEntityException::class)->makePartial();
        $parameterErrors = [
            'test' => ParameterErrorReason::MUST_BE_EMPTY->value,
        ];

        //@phpstan-ignore-next-line
        $exception->shouldReceive('getParameterErrors')
            ->withNoArgs()
            ->andReturn($parameterErrors);

        $expected = [
            JsonErrorRenderer::RESPONSE_FIELD_ERROR_CODE        => $exception->getTitle(),
            JsonErrorRenderer::RESPONSE_FIELD_ERROR_DESCRIPTION => $exception->getDescription(),
            JsonErrorRenderer::RESPONSE_FIELD_PARAMS            => $parameterErrors,
        ];

        $result = (new JsonErrorRenderer())($exception, true);

        $this->assertEquals($expected, json_decode($result, true));
    }
}

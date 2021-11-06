<?php
declare(strict_types=1);

namespace Szemul\SlimErrorHandlerBridge\Test\Request;

use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Szemul\NotSetValue\NotSetValue;
use Szemul\SlimErrorHandlerBridge\Enum\ParameterErrorReason;
use Szemul\SlimErrorHandlerBridge\Enum\RequestValueType;
use Szemul\SlimErrorHandlerBridge\Exception\HttpUnprocessableEntityException;
use Szemul\SlimErrorHandlerBridge\ParameterError\ParameterErrorCollectingInterface;
use Szemul\SlimErrorHandlerBridge\Request\RequestArrayHandler;

class RequestArrayHandlerTest extends TestCase
{
    private const ARRAY = [
        'array'     => [
            'foo' => '123bar',
        ],
        'string'    => 'foo',
        'int'       => 123,
        'float'     => 123.123,
        'bool'      => true,
        'date'      => '2021-01-01T00:00:00Z',
        'dateMicro' => '2021-01-01T00:00:00.000000Z',
    ];

    private const ERROR_KEY_PREFIX = 'test.';

    private ParameterErrorCollectingInterface $errorCollector;
    private RequestArrayHandler               $sut;

    protected function setUp(): void
    {
        parent::setUp();

        // @phpstan-ignore-next-line
        $this->errorCollector = new HttpUnprocessableEntityException(Mockery::mock(ServerRequestInterface::class));

        $this->sut = new RequestArrayHandler(self::ARRAY, $this->errorCollector, self::ERROR_KEY_PREFIX);
    }

    public function testErrorHandlingWithNoErrorHandler_shouldDoNothing(): void
    {
        $sut = new RequestArrayHandler(self::ARRAY, null, '');

        $this->assertNull($sut->getSingleValueFromArray('missing', true, RequestValueType::createString()));
    }

    public function testConvertNotSetValue(): void
    {
        $this->assertSame('string', $this->sut->convertNotSetValue('string'));
        $this->assertNull($this->sut->convertNotSetValue(new NotSetValue()));
        $this->assertSame('string', $this->sut->convertNotSetValue(new NotSetValue(), 'string'));
    }

    public function testGetSingleValueFromArrayWithNoErrors(): void
    {
        $this->assertSame('foo', $this->sut->getSingleValueFromArray('string', true, RequestValueType::createString()));
        $this->assertSame(123, $this->sut->getSingleValueFromArray('int', true, RequestValueType::createInt()));
        $this->assertSame(123.123, $this->sut->getSingleValueFromArray('float', true, RequestValueType::createFloat()));
        $this->assertSame(true, $this->sut->getSingleValueFromArray('bool', true, RequestValueType::createBool()));

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetSingleValueFromArrayWithTypeConversions(): void
    {
        $this->assertSame(0, $this->sut->getSingleValueFromArray('string', true, RequestValueType::createInt()));
        $this->assertSame(0.0, $this->sut->getSingleValueFromArray('string', true, RequestValueType::createFloat()));
        $this->assertSame(true, $this->sut->getSingleValueFromArray('string', true, RequestValueType::createBool()));
        $this->assertSame('123', $this->sut->getSingleValueFromArray('int', true, RequestValueType::createString()));

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetSingleValueFromArrayWithDefaults(): void
    {
        $this->assertSame(
            'foo',
            $this->sut->getSingleValueFromArray(
                'missing',
                false,
                RequestValueType::createString(),
                defaultValue: 'foo',
            ),
        );

        $this->assertNull(
            $this->sut->getSingleValueFromArray('missing', false, RequestValueType::createString(), defaultValue: null),
        );

        $this->assertEquals(
            new NotSetValue(),
            $this->sut->getSingleValueFromArray('missing', false, RequestValueType::createString()),
        );

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetSingleValueWithErrorHandling(): void
    {
        $validation = function (string $value) {
            $this->assertSame($value, self::ARRAY['string']);

            return false;
        };

        $expectedErrors = [
            self::ERROR_KEY_PREFIX . 'missing' => ParameterErrorReason::MISSING,
            self::ERROR_KEY_PREFIX . 'string'  => ParameterErrorReason::INVALID,
        ];

        $this->assertNull($this->sut->getSingleValueFromArray('missing', true, RequestValueType::createString()));
        $this->assertNull($this->sut->getSingleValueFromArray('string', true, RequestValueType::createString(), $validation));

        $this->assertCollectedErrorsMatch($expectedErrors);
    }

    public function testGetArrayValueFromArrayWithNoErrors(): void
    {
        $this->assertSame(
            self::ARRAY['array'],
            $this->sut->getArrayValueFromArray('array', true, RequestValueType::createString()),
        );
        $this->assertSame(
            ['foo' => 123],
            $this->sut->getArrayValueFromArray('array', true, RequestValueType::createInt()),
        );

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetArrayValueFromArrayWithDefaults(): void
    {
        $this->assertSame(
            [],
            $this->sut->getArrayValueFromArray('missing', false, RequestValueType::createString(), defaultValue: []),
        );
        $this->assertSame(
            ['foo' => 'bar'],
            $this->sut->getArrayValueFromArray(
                'missing',
                false,
                RequestValueType::createString(),
                defaultValue: ['foo' => 'bar'],
            ),
        );
        $this->assertEquals(
            new NotSetValue(),
            $this->sut->getArrayValueFromArray('missing', false, RequestValueType::createString()),
        );

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetArrayValueFromArrayWithErrorHandling(): void
    {
        $expectedErrors = [
            self::ERROR_KEY_PREFIX . 'missing'   => ParameterErrorReason::MISSING,
            self::ERROR_KEY_PREFIX . 'string'    => ParameterErrorReason::INVALID,
            self::ERROR_KEY_PREFIX . 'array'     => ParameterErrorReason::INVALID,
            self::ERROR_KEY_PREFIX . 'array.foo' => ParameterErrorReason::INVALID,
        ];

        $validation = function (array $value): bool {
            $this->assertSame(self::ARRAY['array'], $value, 'validation function failed');

            return false;
        };

        $elementValidation = function (string $value): bool {
            $this->assertSame(self::ARRAY['array']['foo'], $value, 'element validation function failed');

            return false;
        };

        $this->assertSame(
            [],
            $this->sut->getArrayValueFromArray('missing', true, RequestValueType::createString()),
        );
        $this->assertSame(
            [],
            $this->sut->getArrayValueFromArray('string', false, RequestValueType::createString()),
        );
        $this->assertSame(
            [],
            $this->sut->getArrayValueFromArray('array', false, RequestValueType::createString(), $validation),
        );
        $this->assertSame(
            [],
            $this->sut->getArrayValueFromArray(
                'array',
                false,
                RequestValueType::createString(),
                elementValidationFunction: $elementValidation,
            ),
        );

        $this->assertCollectedErrorsMatch($expectedErrors);
    }

    public function testGetDateFromArrayWithNoErrors(): void
    {
        $this->assertSame(self::ARRAY['date'], $this->sut->getDateFromArray('date', true)->toIso8601ZuluString());
        $this->assertSame(self::ARRAY['date'], $this->sut->getDateFromArray('date', true, true)->toIso8601ZuluString());
        $this->assertSame(self::ARRAY['dateMicro'], $this->sut->getDateFromArray('dateMicro', true, true)->toIso8601ZuluString('microsecond'));
        $this->assertNull($this->sut->getDateFromArray('missing', false));

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetDateFromArrayWithErrorHandling(): void
    {
        $expectedErrors = [
            self::ERROR_KEY_PREFIX . 'missing'   => ParameterErrorReason::MISSING,
            self::ERROR_KEY_PREFIX . 'dateMicro' => ParameterErrorReason::INVALID,
            self::ERROR_KEY_PREFIX . 'string'    => ParameterErrorReason::INVALID,
        ];

        $this->assertNull($this->sut->getDateFromArray('missing', true));
        $this->assertNull($this->sut->getDateFromArray('dateMicro', false));
        $this->assertNull($this->sut->getDateFromArray('string', false));

        $this->assertCollectedErrorsMatch($expectedErrors);
    }

    public function testGetEnumFromArrayWithNoErrors(): void
    {
        $this->assertSame('foo', $this->sut->getEnumFromArray('string', ['foo', 'bar'], true));
        $this->assertNull($this->sut->getEnumFromArray('missing', ['foo', 'bar'], false));

        $this->assertFalse($this->errorCollector->hasParameterErrors());
    }

    public function testGetEnumFromArrayWithErrorHandling(): void
    {
        $expectedErrors = [
            self::ERROR_KEY_PREFIX . 'missing' => ParameterErrorReason::MISSING,
            self::ERROR_KEY_PREFIX . 'string'  => ParameterErrorReason::INVALID,
        ];

        $this->assertNull($this->sut->getEnumFromArray('missing', ['foo', 'bar'], true));
        $this->assertNull($this->sut->getEnumFromArray('string', ['foobar', 'bar'], true));

        $this->assertCollectedErrorsMatch($expectedErrors);
    }

    public function testValidateDateString(): void
    {
        $this->assertTrue($this->sut->validateDateString('2021-01-01', 'Y-m-d'));
        $this->assertTrue($this->sut->validateDateString('2021-01-01T00:00:00Z', DATE_ATOM));
        $this->assertFalse($this->sut->validateDateString('foo', 'Y-m-d'));
        $this->assertFalse($this->sut->validateDateString('foo', DATE_ATOM));
    }

    /**
     * @param array<string,string> $expected
     */
    private function assertCollectedErrorsMatch(array $expected): void
    {
        $this->assertTrue($this->errorCollector->hasParameterErrors());
        $this->assertEquals($expected, json_decode(json_encode($this->errorCollector->getParameterErrors()), true));
    }
}

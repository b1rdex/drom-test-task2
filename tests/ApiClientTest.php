<?php

declare(strict_types=1);

namespace Acme\Tests;

use Acme\ApiClient;
use Acme\ApiException;
use Acme\CommentFactory;
use BlastCloud\Guzzler\UsesGuzzler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    use UsesGuzzler;

    private ApiClient $sut;

    public function testList(): void
    {
        $comments = [
            ['id' => 1, 'name' => 'name1', 'text' => 'text1'],
            ['id' => 2, 'name' => 'name2', 'text' => 'text2'],
        ];
        $this->guzzler->expects($this->once())
            ->get("/comments")
            ->withoutQuery()
            ->willRespond(new Response(200, [], json_encode([
                'status' => true,
                'data' => $comments,
            ], JSON_THROW_ON_ERROR)));

        $list = $this->sut->list();
        self::assertEquals((new CommentFactory())->fromList($comments), $list);
    }

    public function testAdd(): void
    {
        $formData = ['name' => 'some name', 'text' => 'some text'];
        $this->guzzler->expects($this->once())
            ->post("/comment")
            ->withForm($formData)
            ->withoutQuery()
            ->willRespond(new Response(200, [], json_encode([
                'status' => true,
                'data' => null,
            ], JSON_THROW_ON_ERROR)));

        $this->sut->add($formData['name'], $formData['text']);
    }

    public function testUpdate(): void
    {
        $id = 1;
        $formData = ['name' => 'new name', 'text' => 'new text'];

        $this->guzzler->expects($this->once())
            ->put("/comment/" . $id)
            ->withoutQuery()
            ->withForm($formData)
            ->willRespond(new Response(200, [], json_encode([
                'status' => true,
                'data' => null,
            ], JSON_THROW_ON_ERROR)));

        $this->sut->update($id, $formData['name'], $formData['text']);
    }

    /**
     * @dataProvider exceptionsProvider
     */
    public function testExceptions(callable $callable): void
    {
        $this->expectException(ApiException::class);
        \Closure::bind($callable, $this)();
    }

    public function exceptionsProvider(): iterable
    {
        yield [function (): void {
            $this->guzzler->queueResponse(new Response(500));
            $this->sut->list();
        }];
        yield [function (): void {
            $this->guzzler->queueResponse(new Response(200, [], \json_encode([], JSON_THROW_ON_ERROR)));
            $this->sut->list();
        }];
        yield [function (): void {
            $this->guzzler->queueResponse(new Response(200, [], \json_encode(['status' => false, 'data' => []], JSON_THROW_ON_ERROR)));
            $this->sut->list();
        }];
        yield [function (): void {
            $this->guzzler->queueResponse(new Response(200, [], \json_encode(['status' => false, 'data' => null], JSON_THROW_ON_ERROR)));
            $this->sut->add('z', 'z');
        }];
        yield [function (): void {
            $this->guzzler->queueResponse(new Response(200, [], \json_encode(['status' => false, 'data' => null], JSON_THROW_ON_ERROR)));
            $this->sut->update(1, 'z', 'z');
        }];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = $this->guzzler->getClient([
            RequestOptions::HTTP_ERRORS => false,
        ]);
        $this->sut = new ApiClient($httpClient);
    }
}

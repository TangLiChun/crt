<?php

declare(strict_types=1);

use App\Core\Request;
use App\Core\Response;
use App\Core\Router;

test('Router 可以匹配参数路由', function (): void {
    $router = new Router();
    $router->get('/contacts/{id}', fn(Request $request): Response => Response::json(['id' => $request->param('id')]));

    $response = $router->dispatch(new Request('GET', '/contacts/42'));

    assert_same('json', $response->type);
    assert_contains('"id":"42"', (string) $response->body);
});

test('Router 对未知路由返回 404 视图', function (): void {
    $router = new Router();
    $response = $router->dispatch(new Request('GET', '/missing'));

    assert_same('view', $response->type);
    assert_same(404, $response->status);
});

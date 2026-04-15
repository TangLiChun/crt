<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Services\MarketingPostService;
use App\Domain\Services\PostingRuleService;

final class MarketingPostController
{
    public function __construct(
        private readonly MarketingPostService $posts,
        private readonly PostingRuleService $rules,
        private readonly Csrf $csrf,
        private readonly Session $session,
    ) {
    }

    public function index(Request $request, array $user): Response
    {
        return Response::view('marketing-posts/index', array_merge(
            $this->posts->list($user),
            ['pageTitle' => '营销排期']
        ));
    }

    public function show(Request $request, array $user): Response
    {
        $post = $this->posts->find((int) $request->param('id'), $user);
        if ($post === null) {
            return Response::view('errors/not-found', ['pageTitle' => '排期不存在'], 404);
        }

        return Response::view('marketing-posts/show', [
            'pageTitle' => '排期详情',
            'post' => $post,
            'rules' => $this->rules->all(),
        ]);
    }

    public function store(Request $request, array $user): Response
    {
        if (!$this->csrf->verify((string) $request->input('_token'))) {
            $this->session->flash('error', '排期创建失败，表单令牌无效。');

            return Response::redirect('/marketing-posts');
        }

        try {
            $this->posts->save($request->all(), $user);
            $this->session->flash('success', '营销排期已创建。');
        } catch (\Throwable $exception) {
            $this->session->flash('error', $exception->getMessage());
        }

        return Response::redirect('/marketing-posts');
    }

    public function update(Request $request, array $user): Response
    {
        if (!$this->csrf->verify((string) $request->input('_token'))) {
            $this->session->flash('error', '排期更新失败，表单令牌无效。');

            return Response::redirect('/marketing-posts/' . (int) $request->param('id'));
        }

        try {
            $this->posts->save($request->all(), $user, (int) $request->param('id'));
            $this->session->flash('success', '营销排期已更新。');
        } catch (\Throwable $exception) {
            $this->session->flash('error', $exception->getMessage());
        }

        return Response::redirect('/marketing-posts');
    }
}

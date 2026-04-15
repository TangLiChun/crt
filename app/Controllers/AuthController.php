<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Repositories\UserRepository;

final class AuthController
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly Session $session,
        private readonly Csrf $csrf,
        private readonly array $demoUsers,
    ) {
    }

    public function showLogin(Request $request, ?array $user): Response
    {
        if ($user !== null) {
            return Response::redirect('/');
        }

        return Response::view('auth/login', [
            'pageTitle' => '登录',
            'demoUser' => $this->demoUsers[0] ?? [],
            'demoUsers' => $this->demoUsers,
        ]);
    }

    public function login(Request $request): Response
    {
        if (!$this->csrf->verify((string) $request->input('_token'))) {
            $this->session->flash('error', '表单令牌失效，请刷新后重试。');

            return Response::redirect('/login');
        }

        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');
        $user = $this->users->findByUsername($username);

        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            $this->session->flash('error', '账号或密码错误。');

            return Response::redirect('/login');
        }

        $this->session->put('user_id', (int) $user['id']);
        $this->session->regenerate();
        $this->session->flash('success', '欢迎回来，' . $user['display_name'] . '。');

        return Response::redirect('/');
    }

    public function logout(Request $request): Response
    {
        if ($this->csrf->verify((string) $request->input('_token'))) {
            $this->session->invalidate();
        }

        return Response::redirect('/login');
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Services\MarketingPostService;
use App\Domain\Services\ReminderService;

final class ReminderController
{
    public function __construct(
        private readonly ReminderService $reminders,
        private readonly MarketingPostService $marketingPosts,
        private readonly Csrf $csrf,
        private readonly Session $session,
    ) {
    }

    public function index(Request $request, array $user): Response
    {
        return Response::view('reminders/index', [
            'pageTitle' => '提醒中心',
            'reminders' => $this->reminders->list($user),
            'postAlerts' => $this->marketingPosts->list($user)['alerts'],
        ]);
    }

    public function complete(Request $request, array $user): Response
    {
        if ($this->csrf->verify((string) $request->input('_token'))) {
            $this->reminders->complete((int) $request->param('id'), $user);
            $this->session->flash('success', '提醒已标记完成。');
        }

        return Response::redirect('/reminders');
    }

    public function dismiss(Request $request, array $user): Response
    {
        if ($this->csrf->verify((string) $request->input('_token'))) {
            $this->reminders->dismiss((int) $request->param('id'), $user);
            $this->session->flash('success', '提醒已忽略。');
        }

        return Response::redirect('/reminders');
    }
}

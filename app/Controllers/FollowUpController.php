<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Services\FollowUpService;

final class FollowUpController
{
    public function __construct(
        private readonly FollowUpService $followUps,
        private readonly Csrf $csrf,
        private readonly Session $session,
    ) {
    }

    public function store(Request $request, array $user): Response
    {
        $contactId = (int) $request->param('id');

        if (!$this->csrf->verify((string) $request->input('_token'))) {
            $this->session->flash('error', '跟进提交失败，表单令牌无效。');

            return Response::redirect('/contacts/' . $contactId);
        }

        try {
            $this->followUps->create($contactId, $user, $request->all());
            $this->session->flash('success', '跟进记录已保存。');
        } catch (\Throwable $exception) {
            $this->session->flash('error', $exception->getMessage());
        }

        return Response::redirect('/contacts/' . $contactId);
    }
}

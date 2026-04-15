<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Domain\Services\ContactService;
use App\Domain\Services\FollowUpService;

final class ContactController
{
    private ?FollowUpService $followUps = null;

    public function __construct(
        private readonly ContactService $contacts,
        private readonly Csrf $csrf,
        private readonly Session $session,
    ) {
    }

    public function setFollowUpService(FollowUpService $followUps): void
    {
        $this->followUps = $followUps;
    }

    public function index(Request $request, array $user): Response
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'contact_type' => trim((string) $request->query('contact_type', '')),
            'stage' => trim((string) $request->query('stage', '')),
            'follow_up' => trim((string) $request->query('follow_up', '')),
        ];

        return Response::view('contacts/index', array_merge(
            $this->contacts->list($filters, $user),
            [
                'filters' => $filters,
                'pageTitle' => '客户与潜客',
            ]
        ));
    }

    public function show(Request $request, array $user): Response
    {
        $contact = $this->contacts->find((int) $request->param('id'), $user);
        if ($contact === null) {
            return Response::view('errors/not-found', ['pageTitle' => '客户不存在'], 404);
        }

        $followUps = $this->followUps?->listForContact((int) $contact['id']) ?? [];

        return Response::view('contacts/show', [
            'pageTitle' => '客户详情',
            'contact' => $contact,
            'followUps' => $followUps,
            'activitySummary' => [
                'total' => count($followUps),
                'latest' => $followUps[0]['created_at'] ?? null,
            ],
        ]);
    }

    public function store(Request $request, array $user): Response
    {
        if (!$this->csrf->verify((string) $request->input('_token'))) {
            $this->session->flash('error', '新建客户失败，表单令牌无效。');

            return Response::redirect('/contacts');
        }

        try {
            $this->contacts->save($request->all(), $user);
            $this->session->flash('success', '客户信息已创建。');
        } catch (\Throwable $exception) {
            $this->session->flash('error', $exception->getMessage());
        }

        return Response::redirect('/contacts');
    }

    public function update(Request $request, array $user): Response
    {
        if (!$this->csrf->verify((string) $request->input('_token'))) {
            $this->session->flash('error', '更新客户失败，表单令牌无效。');

            return Response::redirect('/contacts/' . (int) $request->param('id'));
        }

        $contactId = (int) $request->param('id');

        try {
            $this->contacts->save($request->all(), $user, $contactId);
            $this->session->flash('success', '客户信息已更新。');
        } catch (\Throwable $exception) {
            $this->session->flash('error', $exception->getMessage());
        }

        return Response::redirect('/contacts/' . $contactId);
    }
}

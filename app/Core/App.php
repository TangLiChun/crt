<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\ContactController;
use App\Controllers\FollowUpController;
use App\Controllers\KpiController;
use App\Controllers\MarketingPostController;
use App\Controllers\ReminderController;
use App\Controllers\WorkbenchController;
use App\Domain\Repositories\ContactRepository;
use App\Domain\Repositories\FollowUpRepository;
use App\Domain\Repositories\MarketingPostRepository;
use App\Domain\Repositories\PostingRuleRepository;
use App\Domain\Repositories\ReminderRepository;
use App\Domain\Repositories\UserRepository;
use App\Domain\Services\ContactService;
use App\Domain\Services\FollowUpService;
use App\Domain\Services\KpiService;
use App\Domain\Services\MarketingPostService;
use App\Domain\Services\PostingRuleService;
use App\Domain\Services\ReminderService;
use App\Domain\Services\AccessService;
use App\Domain\Services\WorkbenchService;
use RuntimeException;

final class App
{
    private array $appConfig;
    private array $databaseConfig;
    private Session $session;
    private Csrf $csrf;
    private Database $database;
    private Router $router;
    private array $container = [];

    public function __construct()
    {
        $this->appConfig = require config_path('app.php');
        $this->databaseConfig = require config_path('database.php');
        date_default_timezone_set($this->appConfig['timezone'] ?? 'Asia/Shanghai');

        $this->session = new Session((string) ($this->appConfig['session_name'] ?? 'crm_session'));
        $this->session->start();
        $this->csrf = new Csrf($this->session);
        $this->database = new Database($this->databaseConfig);
        $this->database->ensureInitialized();
        $this->router = new Router();

        $this->bootContainer();
        $this->registerRoutes();
    }

    public function csrf(): Csrf
    {
        return $this->csrf;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->appConfig[$key] ?? $default;
    }

    public function user(): ?array
    {
        $userId = $this->session->get('user_id');
        if (!is_int($userId) && !ctype_digit((string) $userId)) {
            return null;
        }

        return $this->container['userRepository']->find((int) $userId);
    }

    public function handle(Request $request): void
    {
        $response = $this->router->dispatch($request);
        $this->send($response, $request);
    }

    private function send(Response $response, Request $request): void
    {
        $isCli = PHP_SAPI === 'cli';

        if (!$isCli) {
            http_response_code($response->status);
        }

        if (!$isCli) {
            foreach ($response->headers as $header => $value) {
                header($header . ': ' . $value);
            }
        }

        if ($response->type === 'redirect' && $response->location !== null) {
            if (!$isCli) {
                header('Location: ' . $response->location);
            }
            return;
        }

        if ($response->type === 'json') {
            echo $response->body;
            return;
        }

        if ($response->type === 'html') {
            echo $response->body;
            return;
        }

        if ($response->type !== 'view' || $response->template === null) {
            throw new RuntimeException('未知响应类型');
        }

        $templateFile = view_path($response->template . '.php');
        if (!file_exists($templateFile)) {
            throw new RuntimeException('视图不存在: ' . $response->template);
        }

        $data = $response->data;
        $data['csrfToken'] = $this->csrf->token();
        $data['authUser'] = $this->user();
        $data['flash'] = $this->session->consumeFlash();
        $data['pageTitle'] = $data['pageTitle'] ?? $this->appConfig['name'];
        $data['currentPath'] = $request->path();

        extract($data, EXTR_SKIP);

        ob_start();
        include $templateFile;
        $content = (string) ob_get_clean();

        include view_path('layouts/app.php');
    }

    private function bootContainer(): void
    {
        $pdo = $this->database->pdo();

        $this->container['userRepository'] = new UserRepository($pdo);
        foreach (($this->appConfig['demo_users'] ?? [$this->appConfig['demo_user']]) as $demoUser) {
            $this->container['userRepository']->ensure($demoUser);
        }
        $this->container['contactRepository'] = new ContactRepository($pdo, (int) $this->appConfig['page_size']);
        $this->container['followUpRepository'] = new FollowUpRepository($pdo);
        $this->container['reminderRepository'] = new ReminderRepository($pdo);
        $this->container['postingRuleRepository'] = new PostingRuleRepository($pdo, $this->appConfig['post_rules']);
        $this->container['marketingPostRepository'] = new MarketingPostRepository($pdo);
        $this->container['accessService'] = new AccessService();

        $this->container['contactService'] = new ContactService(
            $this->container['contactRepository'],
            $this->container['accessService']
        );
        $this->container['postingRuleService'] = new PostingRuleService($this->container['postingRuleRepository']);
        $this->container['reminderService'] = new ReminderService(
            $this->container['reminderRepository'],
            $this->container['contactRepository'],
            $this->container['accessService']
        );
        $this->container['marketingPostService'] = new MarketingPostService(
            $this->container['marketingPostRepository'],
            $this->container['postingRuleRepository'],
            $this->container['reminderRepository'],
            $this->container['accessService']
        );
        $this->container['followUpService'] = new FollowUpService(
            $this->container['followUpRepository'],
            $this->container['contactRepository'],
            $this->container['reminderRepository'],
            $this->container['accessService']
        );
        $this->container['kpiService'] = new KpiService(
            $this->container['contactRepository'],
            $this->container['followUpRepository'],
            $this->container['reminderRepository'],
            $this->container['marketingPostRepository'],
            $this->container['accessService']
        );
        $this->container['workbenchService'] = new WorkbenchService(
            $this->container['contactRepository'],
            $this->container['reminderService'],
            $this->container['marketingPostService'],
            $this->container['kpiService'],
            $this->container['accessService']
        );

        $this->container['authController'] = new AuthController(
            $this->container['userRepository'],
            $this->session,
            $this->csrf,
            $this->appConfig['demo_users'] ?? [$this->appConfig['demo_user']]
        );
        $this->container['workbenchController'] = new WorkbenchController($this->container['workbenchService']);
        $this->container['kpiController'] = new KpiController($this->container['kpiService']);
        $this->container['contactController'] = new ContactController($this->container['contactService'], $this->csrf, $this->session);
        $this->container['contactController']->setFollowUpService($this->container['followUpService']);
        $this->container['followUpController'] = new FollowUpController($this->container['followUpService'], $this->csrf, $this->session);
        $this->container['reminderController'] = new ReminderController(
            $this->container['reminderService'],
            $this->container['marketingPostService'],
            $this->csrf,
            $this->session
        );
        $this->container['marketingPostController'] = new MarketingPostController(
            $this->container['marketingPostService'],
            $this->container['postingRuleService'],
            $this->csrf,
            $this->session
        );
    }

    private function registerRoutes(): void
    {
        $auth = $this->container['authController'];
        $workbench = $this->container['workbenchController'];
        $kpis = $this->container['kpiController'];
        $contacts = $this->container['contactController'];
        $followUps = $this->container['followUpController'];
        $reminders = $this->container['reminderController'];
        $marketingPosts = $this->container['marketingPostController'];

        $this->router->get('/login', fn(Request $request): Response => $auth->showLogin($request, $this->user()));
        $this->router->post('/login', fn(Request $request): Response => $auth->login($request));
        $this->router->post('/logout', fn(Request $request): Response => $auth->logout($request));

        $this->router->get('/health', fn(): Response => Response::json(['status' => 'ok', 'time' => gmdate(DATE_ATOM)]));
        $this->router->get('/api/v1/health', fn(): Response => Response::json(['status' => 'ok', 'time' => gmdate(DATE_ATOM)]));

        $guard = fn(callable $handler): \Closure => function (Request $request) use ($handler): Response {
            if ($this->user() === null) {
                return Response::redirect('/login');
            }

            return $handler($request);
        };

        $this->router->get('/', $guard(fn(Request $request): Response => $workbench->index($request, $this->user() ?? [])));
        $this->router->get('/kpi', $guard(fn(Request $request): Response => $kpis->index($request, $this->user() ?? [])));
        $this->router->get('/contacts', $guard(fn(Request $request): Response => $contacts->index($request, $this->user() ?? [])));
        $this->router->get('/contacts/{id}', $guard(fn(Request $request): Response => $contacts->show($request, $this->user() ?? [])));
        $this->router->post('/contacts', $guard(fn(Request $request): Response => $contacts->store($request, $this->user() ?? [])));
        $this->router->post('/contacts/{id}', $guard(fn(Request $request): Response => $contacts->update($request, $this->user() ?? [])));
        $this->router->post('/contacts/{id}/follow-ups', $guard(fn(Request $request): Response => $followUps->store($request, $this->user() ?? [])));

        $this->router->get('/reminders', $guard(fn(Request $request): Response => $reminders->index($request, $this->user() ?? [])));
        $this->router->post('/reminders/{id}/complete', $guard(fn(Request $request): Response => $reminders->complete($request, $this->user() ?? [])));
        $this->router->post('/reminders/{id}/dismiss', $guard(fn(Request $request): Response => $reminders->dismiss($request, $this->user() ?? [])));

        $this->router->get('/marketing-posts', $guard(fn(Request $request): Response => $marketingPosts->index($request, $this->user() ?? [])));
        $this->router->get('/marketing-posts/{id}', $guard(fn(Request $request): Response => $marketingPosts->show($request, $this->user() ?? [])));
        $this->router->post('/marketing-posts', $guard(fn(Request $request): Response => $marketingPosts->store($request, $this->user() ?? [])));
        $this->router->post('/marketing-posts/{id}', $guard(fn(Request $request): Response => $marketingPosts->update($request, $this->user() ?? [])));
    }
}

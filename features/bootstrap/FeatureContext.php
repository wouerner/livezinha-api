<?php

use Behat\Behat\Context\Context;
use Behat\Hook\BeforeScenario;
use Behat\Hook\AfterScenario;
use Behat\Step\Given;
use Behat\Step\When;
use Behat\Step\Then;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class FeatureContext implements Context
{
    private static Application $app;
    private ?TestResponse $response = null;
    private ?string $bearerToken = null;
    private array $headers = [];
    private ?\App\Models\LiveStream $lastLive = null;

    public function __construct()
    {
        if (isset(self::$app)) {
            return;
        }

        $dbPath = __DIR__ . '/../../storage/framework/testing/behat.sqlite';

        @mkdir(dirname($dbPath), 0755, true);
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'false';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = $dbPath;

        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $dbPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('app.debug', false);

        self::$app = $app;
    }

    private static bool $migrated = false;

    #[BeforeScenario]
    public function setUpDatabase(): void
    {
        $this->response = null;
        $this->bearerToken = null;
        $this->headers = [];
        $this->lastLive = null;

        if (!self::$migrated) {
            $kernel = self::$app->make('Illuminate\Contracts\Console\Kernel');
            $kernel->call('migrate:fresh', ['--force' => true]);
            self::$migrated = true;
        }

        self::$app->make('db')->connection()->beginTransaction();

        // Reset auth state that may have been cached by a previous scenario's requests
        Auth::forgetGuards();
    }

    #[AfterScenario]
    public function tearDownDatabase(): void
    {
        if (self::$migrated) {
            self::$app->make('db')->connection()->rollBack();
        }
    }

    private function json(string $method, string $uri, array $data = []): TestResponse
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];

        if ($this->bearerToken) {
            $server['HTTP_AUTHORIZATION'] = "Bearer {$this->bearerToken}";
        }

        foreach ($this->headers as $key => $value) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $server[$serverKey] = $value;
        }

        $request = Request::create($uri, $method, $data, [], [], $server);

        if (!empty($data)) {
            $request->setJson(new \Symfony\Component\HttpFoundation\ParameterBag($data));
        }

        $kernel = self::$app->make('Illuminate\Contracts\Http\Kernel');

        try {
            $response = $kernel->handle($request);
            $kernel->terminate($request, $response);
        } catch (\Throwable $e) {
            if (method_exists(self::$app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class), 'render')) {
                $response = self::$app->make(\Illuminate\Contracts\Debug\ExceptionHandler::class)->render($request, $e);
            } else {
                $response = new \Illuminate\Http\JsonResponse(
                    ['message' => $e->getMessage()],
                    $e instanceof \Illuminate\Auth\AuthenticationException ? 401 : 500
                );
            }
        }

        return TestResponse::fromBaseResponse($response);
    }

    // --- AUTHENTICATION ---

    #[Given('I am authenticated as an admin')]
    public function iAmAuthenticatedAsAdmin(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->bearerToken = $user->createToken('auth-token')->plainTextToken;
    }

    #[Given('I am not authenticated')]
    public function iAmNotAuthenticated(): void
    {
        $this->bearerToken = null;
    }

    #[Given('there is an admin with email :email and password :password')]
    public function thereIsAnAdminWithEmailAndPassword(string $email, string $password): void
    {
        \App\Models\User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);
    }

    // --- LIVE STREAMS ---

    #[Given('there is an active live stream with title :title')]
    public function thereIsAnActiveLiveStream(string $title): void
    {
        $this->lastLive = \App\Models\LiveStream::factory()->active()->create(['title' => $title]);
    }

    #[Given('there is an active live stream')]
    public function thereIsAnActiveLiveStreamDefault(): void
    {
        $this->lastLive = \App\Models\LiveStream::factory()->active()->create();
    }

    #[Given('there is a scheduled live stream with title :title')]
    public function thereIsAScheduledLiveStream(string $title): void
    {
        $this->lastLive = \App\Models\LiveStream::factory()->create([
            'title' => $title,
            'status' => 'scheduled',
            'scheduled_at' => now()->addHour(),
        ]);
    }

    #[Given('there is a finished live stream with title :title')]
    public function thereIsAFinishedLiveStream(string $title): void
    {
        $this->lastLive = \App\Models\LiveStream::factory()->create([
            'title' => $title,
            'status' => 'finished',
            'scheduled_at' => now()->subHours(2),
        ]);
    }

    #[Given('there is a live with title :title')]
    public function thereIsALiveWithTitle(string $title): void
    {
        $this->lastLive = \App\Models\LiveStream::factory()->create(['title' => $title]);
    }

    // --- QUESTIONS ---

    #[Given('there is a question')]
    public function thereIsAQuestion(): void
    {
        $live = $this->lastLive ?? \App\Models\LiveStream::factory()->active()->create();
        $this->lastLive = $live;
        \App\Models\Question::factory()->create(['live_stream_id' => $live->id]);
    }

    #[Given('there is a question with text :text')]
    public function thereIsAQuestionWithText(string $text): void
    {
        $live = $this->lastLive ?? \App\Models\LiveStream::factory()->active()->create();
        $this->lastLive = $live;
        \App\Models\Question::factory()->create([
            'live_stream_id' => $live->id,
            'question_text' => $text,
        ]);
    }

    #[Given('there is a question with status :status')]
    public function thereIsAQuestionWithStatus(string $status): void
    {
        $live = $this->lastLive ?? \App\Models\LiveStream::factory()->active()->create();
        $this->lastLive = $live;
        \App\Models\Question::factory()->create([
            'live_stream_id' => $live->id,
            'status' => $status,
        ]);
    }

    #[Given('there is a question with status :status and text :text')]
    public function thereIsAQuestionWithStatusAndText(string $status, string $text): void
    {
        $live = $this->lastLive ?? \App\Models\LiveStream::factory()->active()->create();
        $this->lastLive = $live;
        \App\Models\Question::factory()->create([
            'live_stream_id' => $live->id,
            'question_text' => $text,
            'status' => $status,
        ]);
    }

    #[Given('there are :count active questions for the live')]
    public function thereAreActiveQuestionsForTheLive(int $count): void
    {
        if (!$this->lastLive) {
            $this->lastLive = \App\Models\LiveStream::factory()->active()->create();
        }
        for ($i = 0; $i < $count; $i++) {
            \App\Models\Question::factory()->active()->create([
                'live_stream_id' => $this->lastLive->id,
                'displayed_at' => now()->subMinutes(($count - $i) * 5),
            ]);
        }
    }

    #[Given('there are :count active questions, oldest displayed :minutes minutes ago')]
    public function thereAreActiveQuestionsWithDisplayed(int $count, int $minutes): void
    {
        if (!$this->lastLive) {
            $this->lastLive = \App\Models\LiveStream::factory()->active()->create();
        }
        for ($i = 0; $i < $count; $i++) {
            \App\Models\Question::factory()->active()->create([
                'live_stream_id' => $this->lastLive->id,
                'displayed_at' => now()->subMinutes($minutes - ($i * 5)),
            ]);
        }
    }

    // --- REQUESTS ---

    #[When('I request :method :uri')]
    public function iRequest(string $method, string $uri): void
    {
        $this->response = $this->json($method, $uri);
    }

    #[When('I request :method :uri with data:')]
    public function iRequestWithData(string $method, string $uri, \Behat\Gherkin\Node\PyStringNode $json): void
    {
        $data = json_decode($json->getRaw(), true);
        $this->response = $this->json($method, $uri, $data ?? []);
    }

    #[When('I request :method :uri with body:')]
    public function iRequestWithBody(string $method, string $uri, \Behat\Gherkin\Node\PyStringNode $json): void
    {
        $data = json_decode($json->getRaw(), true);
        $this->response = $this->json($method, $uri, $data ?? []);
    }

    // --- ASSERTIONS ---

    #[Then('the response status should be :status')]
    public function theResponseStatusShouldBe(int $status): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $actual = $this->response->getStatusCode();
        if ($actual !== $status) {
            throw new \RuntimeException(sprintf(
                'Expected status %d but got %d. Response: %s',
                $status,
                $actual,
                $this->response->getContent()
            ));
        }
    }

    #[Then('the response should contain :key with value :value')]
    public function theResponseShouldContain(string $key, string $value): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        $actual = data_get($json, $key);
        if ((string) $actual !== $value) {
            throw new \RuntimeException(sprintf(
                'Expected %s to be "%s" but got "%s".',
                $key,
                $value,
                (string) $actual
            ));
        }
    }

    #[Then('the response should contain :key with integer :value')]
    public function theResponseShouldContainInt(string $key, int $value): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        $actual = data_get($json, $key);
        if ($actual !== $value) {
            throw new \RuntimeException(sprintf(
                'Expected %s to be %d but got %s.',
                $key,
                $value,
                json_encode($actual)
            ));
        }
    }

    #[Then('the response should contain :key with boolean true')]
    public function theResponseShouldContainBoolTrue(string $key): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        $actual = data_get($json, $key);
        if ($actual !== true) {
            throw new \RuntimeException(sprintf(
                'Expected %s to be true but got %s.',
                $key,
                json_encode($actual)
            ));
        }
    }

    #[Then('the response should contain :key with boolean false')]
    public function theResponseShouldContainBoolFalse(string $key): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        $actual = data_get($json, $key);
        if ($actual !== false) {
            throw new \RuntimeException(sprintf(
                'Expected %s to be false but got %s.',
                $key,
                json_encode($actual)
            ));
        }
    }

    #[Then('the response should be empty')]
    public function theResponseShouldBeEmpty(): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        if ($json !== []) {
            throw new \RuntimeException(sprintf(
                'Expected empty response but got: %s',
                json_encode($json)
            ));
        }
    }

    #[Then('the response should have :count items')]
    public function theResponseShouldHaveItems(int $count): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        if (!is_array($json) || count($json) !== $count) {
            throw new \RuntimeException(sprintf(
                'Expected %d items but got %d.',
                $count,
                is_array($json) ? count($json) : 0
            ));
        }
    }

    #[Then('the response should have :count items where :key is :value')]
    public function theResponseShouldHaveItemsWhere(int $count, string $key, string $value): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        $filtered = array_filter($json, fn ($item) => ($item[$key] ?? null) == $value);
        if (count($filtered) !== $count) {
            throw new \RuntimeException(sprintf(
                'Expected %d items where %s is "%s" but got %d.',
                $count,
                $key,
                $value,
                count($filtered)
            ));
        }
    }

    #[Then('the response should have field :key')]
    public function theResponseShouldHaveField(string $key): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        if (!array_key_exists($key, $json)) {
            throw new \RuntimeException(sprintf(
                'Expected response to have field "%s". Available fields: %s',
                $key,
                implode(', ', array_keys($json))
            ));
        }
    }

    #[Then('the first item should contain :key with value :value')]
    public function theFirstItemShouldContain(string $key, string $value): void
    {
        if ($this->response === null) {
            throw new \RuntimeException('No response was made');
        }
        $json = $this->response->json();
        if (!is_array($json) || !isset($json[0])) {
            throw new \RuntimeException('Expected response to be a non-empty array.');
        }
        $actual = $json[0][$key] ?? null;
        if ((string) $actual !== $value) {
            throw new \RuntimeException(sprintf(
                'Expected first item\'s %s to be "%s" but got "%s".',
                $key,
                $value,
                (string) $actual
            ));
        }
    }
}

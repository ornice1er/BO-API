<?php

namespace Tests\Unit;

use App\Http\Repositories\RequeteRepository;
use App\Services\LogService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class RequeteControllerCopTest extends TestCase
{
    use WithFaker, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();

        $logMock = $this->createMock(LogService::class);
        $this->app->instance(LogService::class, $logMock);

        $this->repo = $this->createMock(RequeteRepository::class);
        $this->app->instance(RequeteRepository::class, $this->repo);
    }

    /** @test */
    public function index_includes_affectations_with_current_posts()
    {
        $payload = collect([
            [
                'id' => 1,
                'affectations' => [
                    [
                        'id' => 10,
                        'copUp' => ['id' => 100, 'agent' => ['id' => 1000]],
                        'copDown' => ['id' => 101, 'agent' => ['id' => 1001]],
                    ],
                ],
                'affectation' => [
                    'id' => 11,
                    'copUp' => ['id' => 102, 'agent' => ['id' => 1002]],
                    'copDown' => ['id' => 103, 'agent' => ['id' => 1003]],
                ],
            ],
        ]);

        $this->repo->method('getAll')->willReturn($payload);

        $response = $this->getJson('/api/requete');
        $response->assertStatus(200)
            ->assertJsonPath('data.0.affectations.0.copUp.agent.id', 1000)
            ->assertJsonPath('data.0.affectation.copDown.agent.id', 1003);
    }

    /** @test */
    public function show_includes_affectations_with_current_posts()
    {
        $payload = [
            'id' => 2,
            'affectations' => [
                [
                    'id' => 20,
                    'copUp' => ['id' => 200, 'agent' => ['id' => 2000]],
                    'copDown' => ['id' => 201, 'agent' => ['id' => 2001]],
                ],
            ],
            'affectation' => [
                'id' => 21,
                'copUp' => ['id' => 202, 'agent' => ['id' => 2002]],
                'copDown' => ['id' => 203, 'agent' => ['id' => 2003]],
            ],
        ];

        $this->repo->method('get')->willReturn($payload);

        $response = $this->getJson('/api/requete/2');
        $response->assertStatus(200)
            ->assertJsonPath('data.affectations.0.copDown.agent.id', 2001)
            ->assertJsonPath('data.affectation.copUp.agent.id', 2002);
    }
}



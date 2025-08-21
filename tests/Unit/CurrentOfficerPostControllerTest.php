<?php

namespace Tests\Unit;

use App\Http\Repositories\CurrentOfficerPostRepository;
use App\Models\CurrentOfficerPost;
use App\Services\LogService;
use App\Utilities\Common;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CurrentOfficerPostControllerTest extends TestCase
{
    use WithFaker, WithoutMiddleware;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->repository = $this->createMock(CurrentOfficerPostRepository::class);
        $this->app->instance(CurrentOfficerPostRepository::class, $this->repository);

        // Mock LogService to satisfy type-hint and avoid DB writes
        $logMock = $this->createMock(LogService::class);
        $this->app->instance(LogService::class, $logMock);
    }

    /** @test */
    public function it_can_list_all_current_officer_posts()
    {
        $this->repository
            ->method('getAll')
            ->willReturn(collect([(new CurrentOfficerPost())->fill([
                'agent_id' => 1,
                'unite_admin_id' => 1,
                'fonction_id' => 1,
                'statut' => 'active',
            ])]));

        $response = $this->getJson('/api/current-officer-posts');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_show_a_current_officer_post()
    {
        $cop = (new CurrentOfficerPost())->fill([
            'id' => 10,
            'agent_id' => 2,
            'unite_admin_id' => 3,
            'fonction_id' => 4,
            'statut' => 'inactive',
        ]);

        $this->repository
            ->method('get')
            ->willReturn($cop);

        $response = $this->getJson('/api/current-officer-posts/10');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Poste courant trouvé']);
    }

    /** @test */
    public function it_can_create_a_current_officer_post()
    {
        $data = [
            'agent_id' => 5,
            'unite_admin_id' => 6,
            'fonction_id' => 7,
            'statut' => 'active',
        ];

        $this->repository
            ->method('makeStore')
            ->willReturn((new CurrentOfficerPost())->fill($data));

        $response = $this->postJson('/api/current-officer-posts', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function it_can_update_a_current_officer_post()
    {
        $update = [
            'statut' => 'inactive',
        ];

        $this->repository
            ->method('makeUpdate')
            ->willReturn((new CurrentOfficerPost())->fill(array_merge([
                'id' => 12,
                'agent_id' => 5,
                'unite_admin_id' => 6,
                'fonction_id' => 7,
            ], $update)));

        $response = $this->putJson('/api/current-officer-posts/12', $update);

        $response->assertStatus(200)
            ->assertJsonFragment($update);
    }

    /** @test */
    public function it_can_delete_a_current_officer_post()
    {
        $this->repository
            ->method('makeDestroy')
            ->willReturn(true);

        $response = $this->deleteJson('/api/current-officer-posts/15');

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Poste courant supprimé']);
    }

    /** @test */
    public function it_can_change_state_of_a_current_officer_post()
    {
        $this->repository
            ->method('setStatus')
            ->willReturn(true);

        $response = $this->getJson('/api/current-officer-posts/20/state/active');

        $response->assertStatus(200);
    }
}



<?php

namespace Tests\Unit;

use App\Http\Repositories\CountryRepository;
use App\Models\Country;
use App\Utilities\Common;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $countryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->countryRepository = $this->createMock(CountryRepository::class);
        $this->app->instance(CountryRepository::class, $this->countryRepository);
    }

    /** @test */
    public function it_can_list_all_countries()
    {
        // Arrange
        $this->countryRepository
            ->method('getAll')
            ->willReturn(Country::factory()->count(5)->make());

        // Act
        $response = $this->getJson('/api/countries');

        // Assert
        $response->assertStatus(200);
        //  ->assertJsonStructure([
        //     'message',
        //     'data' => [
        //         '*' => ['id', 'name', 'code', 'created_at', 'updated_at']
        //     ]
        //  ])
    }

    /** @test */
    public function it_can_show_a_single_country()
    {
        // Arrange
        $country = Country::factory()->create();

        // Act
        $response = $this->getJson("/api/countries/{$country->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonIsArray(Common::responseArray(
                "Recupération d'un pays",
                data: $country));
    }

    /** @test */
    public function it_can_create_a_country()
    {
        // Arrange
        $data = [
            'name' => $this->faker->country,
            'code' => strtoupper($this->faker->unique()->lexify('???')),
        ];

        $this->countryRepository
            ->method('makeStore')
            ->willReturn((new Country)->fill($data));

        // Act
        $response = $this->postJson('/countries', $data);

        // Assert
        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function it_can_update_a_country()
    {
        // Arrange
        $country = Country::factory()->create();
        $data = [
            'name' => 'Updated Country Name',
            'code' => 'NEW',
        ];

        // Act
        $response = $this->putJson("/countries/{$country->id}", $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment($data);

        $this->assertDatabaseHas('countries', ['id' => $country->id, 'name' => 'Updated Country Name']);
    }

    /** @test */
    public function it_can_delete_a_country()
    {
        // Arrange
        $country = Country::factory()->create();

        // Act
        $response = $this->deleteJson("/countries/{$country->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}

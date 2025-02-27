<?php

namespace Tests\Feature\Student;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\Allocation;
use App\Models\Program;
use App\Models\Student;
use App\Models\Claim;
use App\Models\Institution;
use App\Models\ProgramYear;
use Illuminate\Support\Facades\Event;
use App\Events\ApplicationSubmitted;
use Illuminate\Support\Facades\Cache;
class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable page existence check for Inertia testing
        config(['inertia.testing.ensure_pages_exist' => false]);

        // Create a Student record associated with this user
        $this->student = Student::factory()->create();
        $this->user = $this->student->user;
    }

    public function test_it_stores_a_new_application_and_dispatches_event(): void
    {
        Event::fake();

        $institution = Institution::factory()->create();
        $programYear = ProgramYear::factory()->create();
        $allocation = Allocation::factory()->create([
            'institution_guid'  => $institution->guid,
            'program_year_guid' => $programYear->guid,
        ]);
        $program = Program::factory()->create([
            'institution_guid' => $institution->guid,
        ]);


        // mimic submitted data for the required fields based on ApplicationStoreRequest:
        $storeData = [
            'institution_guid'  => $institution->guid,
            'program_guid'      => $program->guid,
            'student_guid'      => $this->student->guid,
            'agreement_confirmed' => true,
            'registration_confirmed' => true,
            'claim_status'      => 'Submitted',
        ];

        Auth::login($this->user);
        $this->assertAuthenticated();
        $this->actingAs($this->user);
        $this->assertAuthenticatedAs($this->user);
        $response = $this->post(route('student.applications.store'), $storeData);

        // Expect a redirection to the student home route.
        $response->assertRedirect(route('student.home'));

        // Assert that the Claim record is created in the database with the provided data.
        $this->assertDatabaseHas('claims', [
            'institution_guid' => $storeData['institution_guid'],
            'program_guid'     => $storeData['program_guid'],
            'student_guid'     => $storeData['student_guid'],
        ]);

        // Assert that the ApplicationSubmitted event was dispatched.
        Event::assertDispatched(ApplicationSubmitted::class);
    }

    public function test_it_updates_an_existing_application_and_dispatches_event(): void
    {
        Event::fake();

        $institution = Institution::factory()->create();
        $programYear = ProgramYear::factory()->create();
        $allocation = Allocation::factory()->create([
            'institution_guid'  => $institution->guid,
            'program_year_guid' => $programYear->guid,
        ]);
        $program = Program::factory()->create([
            'institution_guid' => $institution->guid,
        ]);

        // Create a Claim record with initial valid data.
        $claim = Claim::factory()->create([
            'institution_guid'  => $institution->guid,
            'program_guid'      => $program->guid,
            'student_guid'      => $this->student->guid,
            'agreement_confirmed' => true,
            'registration_confirmed' => true,
            'claim_status'      => 'Submitted',
        ]);

        $updateData = [
            'id'           => $claim->id,
            'guid'         => $claim->guid,
            'institution_guid'  => $institution->guid,
            'program_guid'      => $program->guid,
            'student_guid'      => $this->student->guid,
            'agreement_confirmed' => true,
            'registration_confirmed' => true,
            'claim_status'      => 'Submitted',
        ];


        Auth::login($this->user);
        $this->assertAuthenticated();
        $this->actingAs($this->user);
        $this->assertAuthenticatedAs($this->user);
        $response = $this->put(route('student.applications.update', $claim->id), $updateData);

        $response->assertRedirect(route('student.home'));
        $this->assertDatabaseHas('claims', [
            'id'           => $claim->id,
            'guid'         => $claim->guid,
            'agreement_confirmed' => true,
            'registration_confirmed' => true,
            'claim_status'      => 'Submitted',
            'institution_guid' => $institution->guid,
            'allocation_guid'  => $allocation->guid,
            'program_guid'     => $program->guid,
            'student_guid'     => $this->student->guid,
        ]);

        Event::assertDispatched(ApplicationSubmitted::class);
    }


    public function test_it_returns_inertia_dashboard_when_student_exists()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('student.home'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Student::Dashboard')
                ->has('results')
                ->where('page', 'applications')
            );
    }

    public function test_it_returns_inertia_dashboard_with_profile_when_student_does_not_exist()
    {
        // Remove the student record to simulate a missing student.
        $this->student->delete();

        // Simulate provider user cache value.
        Cache::put('bcsc_provider_user_' . $this->user->id, json_encode(['name' => 'Provider Name']));

        $this->actingAs($this->user);

        $response = $this->get(route('student.home'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Student::Dashboard')
                ->where('page', 'profile')
                ->has('providerUser')
            );
    }

    public function test_it_fetches_paginated_applications_as_json()
    {
        $this->actingAs($this->user);

        // Create several claims for the student.
        Claim::factory()->count(30)->create(['student_guid' => $this->student->guid]);

        $response = $this->get(route('student.claims.fetchApplications'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'body' => [
                    'data',
                    'current_page',
                    'last_page',
                ],
            ]);
    }

}

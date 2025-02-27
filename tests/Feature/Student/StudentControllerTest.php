<?php

namespace Tests\Feature\Student;

use App\Models\Allocation;
use App\Models\Institution;
use App\Models\Student;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable page existence check for Inertia testing
        config(['inertia.testing.ensure_pages_exist' => false]);

        $this->student = Student::factory()->create();
        $this->user = $this->student->user;
    }

    public function test_index_returns_dashboard_when_student_exists(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('student.home'));

        // Assuming your index method returns an Inertia component.
        // Adjust the expected component alias if needed.
        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Student::Dashboard')
                ->has('results')
            );
    }

    public function test_store_creates_new_student_and_redirects(): void
    {
        $this->actingAs($this->user);


        $studentData = [
            'guid'                => str_replace('-', '', (string) Str::uuid()),
            'first_name' => 'Alice',
            'last_name'  => 'Johnson',
            'dob'        => '1990-05-15',
            'gender'     => 'Female',
            'email'      => 'alice.johnson@example.com',
            'sin'                 => '932069073',
            'city'                => 'Vancouver',
            'zip_code'            => 'V0V0V0',
            'citizenship'         => 'Canadian',
            'grade12_or_over19'   => 'grade12',
            'bc_resident'         => true,
            'info_consent'        => true,
            'duplicative_funding' => true,
            'tax_implications'    => true,
            'lifetime_max'        => true,
            'fed_prov_benefits'   => true,
            'workbc_client'       => true,
            'additional_supports' => true,
            'total_grant'         => 0,
            'excel_guid'          => null,
        ];
        // Post valid student data.
        $response = $this->post(route('student.store'), $studentData);

        $response->assertRedirect(route('student.home'));

        $student = Student::where('sin', $studentData['sin'])->first();

        // Assert that a student record exists in the test database.
        $this->assertDatabaseHas('students', [
            'user_guid'   => $student->user->guid,
            'first_name'  => $student->first_name,
            'last_name'   => $student->last_name,
            'email'       => $student->email,
            'zip_code'    => $student->zip_code,
        ]);
    }

    public function test_update_modifies_existing_student_and_redirects(): void
    {
        $this->actingAs($this->user);

        // Prepare update data. All required fields must be included.
        $updateData = [
            'id'         => $this->student->id,
            'guid'       => $this->student->guid,
            'user_guid'  => $this->user->guid,
            'sin'        => '441182870',
            'first_name' => 'Tevin',
            'last_name'  => 'Cremin',
            'dob'        => '1990-05-15',
            'gender'     => 'Female',
            'email'      => 'darrel82@example.net',
            'city'       => 'Victoria',
            'zip_code'   => 'V8W1L8',
            'citizenship'=> 'Canadian',
            'grade12_or_over19' => 'grade12',
            'bc_resident'=> true,
            'info_consent' => true,
            'duplicative_funding' => true,
            'tax_implications' => true,
            'lifetime_max' => true,
            'fed_prov_benefits' => true,
            'workbc_client' => true,
            'additional_supports' => true,
            'total_grant' => 0,
        ];

        $response = $this->put(route('student.update', $this->student->id), $updateData);

        $response->assertRedirect(route('student.home'));

        // Assert that the student record was updated in the database.
        $this->assertDatabaseHas('students', [
            'id'         => $this->student->id,
            'last_name'  => 'Cremin',
            'email'      => 'darrel82@example.net',
            'sin'        => '441182870',
        ]);
    }


    public function test_it_fetches_institutions_by_guid_or_all_when_none_provided()
    {
        $this->actingAs($this->user);

        $institution = Institution::factory()->create();
        $allocation = Allocation::factory()->create([
            'institution_guid' => $institution->guid,
            'status'           => 'active',
        ]);

        // Fetch a specific institution.
        $response = $this->get(route('student.claims.fetchInstitutions', ['institution' => $institution->guid]));
        $response->assertStatus(200)
            ->assertJsonFragment(['guid' => $institution->guid]);

        // Fetch all institutions.
        $responseAll = $this->get(route('student.claims.fetchInstitutions'));
        $responseAll->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'institutions',
            ]);
    }
}

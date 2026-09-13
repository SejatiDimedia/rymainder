<?php

namespace Tests\Feature;

use App\Domain\Admin\Enums\UserRole;
use App\Domain\Sponsor\Enums\PaymentFrequency;
use App\Domain\Sponsor\Enums\SponsorStatus;
use App\Domain\Sponsor\Models\Sponsor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SponsorImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::SUPER_ADMIN,
        ]);
    }

    public function test_sponsors_index_renders_import_button_and_template_links(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.sponsors.index'));

        $response->assertOk();
        $response->assertSee('Download Template');
        $response->assertSee('Import Excel / CSV');
        $response->assertSee(route('admin.sponsors.import-template', ['format' => 'xlsx']));
        $response->assertSee(route('admin.sponsors.import-template', ['format' => 'csv']));
        $response->assertSee('open-import-modal');
    }

    public function test_user_can_download_excel_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.sponsors.import-template', ['format' => 'xlsx']));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=rymainder_sponsors_import_template.xlsx');
    }

    public function test_user_can_download_csv_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.sponsors.import-template', ['format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=rymainder_sponsors_import_template.csv');
        $this->assertStringContainsString('"name","email","phone"', file_get_contents($response->getFile()->getPathname()));
    }

    public function test_import_sponsors_from_valid_csv_file(): void
    {
        $csvContent = "name,email,phone,orphan_name,amount,frequency,last_donation_date,status,telegram_chat_id,channels,notes\n"
            . "H. Budi Prakoso,budi.prakoso@example.com,081234567890,Fatimah,500000,annual,2026-08-15,active,998877,\"email, whatsapp\",Annual donor\n"
            . "Hj. Siti Aminah,siti.aminah@example.com,+628987654321,Yusuf,300000,6_months,2026-07-01,active,,\"email, whatsapp, telegram\",Semiannual donor\n";

        $file = UploadedFile::fake()->createWithContent('sponsors.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sponsors.import'), [
                'file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect(route('admin.sponsors.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sponsors', [
            'email' => 'budi.prakoso@example.com',
            'name' => 'H. Budi Prakoso',
            'phone' => '+6281234567890',
            'amount' => '500000.00',
            'frequency' => PaymentFrequency::ANNUAL->value,
            'orphan_name' => 'Fatimah',
            'telegram_chat_id' => '998877',
        ]);

        $this->assertDatabaseHas('sponsors', [
            'email' => 'siti.aminah@example.com',
            'name' => 'Hj. Siti Aminah',
            'phone' => '+628987654321',
            'amount' => '300000.00',
            'frequency' => PaymentFrequency::SIX_MONTHS->value,
            'orphan_name' => 'Yusuf',
        ]);
    }

    public function test_import_sponsors_skips_duplicates_when_skip_mode_selected(): void
    {
        $existing = Sponsor::create([
            'name' => 'Original Name',
            'email' => 'existing@example.com',
            'phone' => '+6281111111111',
            'amount' => 100000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-01-01'),
            'status' => SponsorStatus::ACTIVE,
        ]);

        $csvContent = "name,email,phone,orphan_name,amount,frequency,last_donation_date,status\n"
            . "New Donor,new.donor@example.com,081222222222,,200000,annual,2026-08-01,active\n"
            . "Changed Name,existing@example.com,081333333333,,999000,annual,2026-08-01,active\n";

        $file = UploadedFile::fake()->createWithContent('sponsors.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sponsors.import'), [
                'file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect(route('admin.sponsors.index'));
        $response->assertSessionHas('success');

        // New donor created
        $this->assertDatabaseHas('sponsors', [
            'email' => 'new.donor@example.com',
        ]);

        // Existing donor retained original data
        $existing->refresh();
        $this->assertEquals('Original Name', $existing->name);
        $this->assertEquals(100000, (int) $existing->amount);
    }

    public function test_import_sponsors_updates_duplicates_when_update_mode_selected(): void
    {
        $existing = Sponsor::create([
            'name' => 'Original Name',
            'email' => 'existing@example.com',
            'phone' => '+6281111111111',
            'amount' => 100000,
            'frequency' => PaymentFrequency::ANNUAL,
            'last_donation_date' => Carbon::parse('2026-01-01'),
            'status' => SponsorStatus::ACTIVE,
        ]);

        $csvContent = "name,email,phone,orphan_name,amount,frequency,last_donation_date,status\n"
            . "Updated Full Name,existing@example.com,081999888777,Updated Orphan,850000,6_months,2026-08-10,active\n";

        $file = UploadedFile::fake()->createWithContent('sponsors.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sponsors.import'), [
                'file' => $file,
                'duplicate_mode' => 'update',
            ]);

        $response->assertRedirect(route('admin.sponsors.index'));
        $response->assertSessionHas('success');

        $existing->refresh();
        $this->assertEquals('Updated Full Name', $existing->name);
        $this->assertEquals('+6281999888777', $existing->phone);
        $this->assertEquals(850000, (int) $existing->amount);
        $this->assertEquals(PaymentFrequency::SIX_MONTHS, $existing->frequency);
        $this->assertEquals('Updated Orphan', $existing->orphan_name);
    }

    public function test_import_reports_row_validation_errors(): void
    {
        $csvContent = "name,email,phone,amount,frequency,last_donation_date\n"
            . ",missingname@example.com,081234567890,500000,annual,2026-08-15\n"
            . "Valid Name,badphone@example.com,invalid_phone_number,500000,annual,2026-08-15\n"
            . "Invalid Date,invaliddate@example.com,081234567890,500000,annual,not-a-valid-date\n";

        $file = UploadedFile::fake()->createWithContent('sponsors.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sponsors.import'), [
                'file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect(route('admin.sponsors.index'));
        $response->assertSessionHas('import_errors');

        $errors = session('import_errors');
        $this->assertCount(3, $errors);
        $this->assertStringContainsString('Row 2', $errors[0]);
        $this->assertStringContainsString('Row 3', $errors[1]);
        $this->assertStringContainsString('Row 4', $errors[2]);
    }

    public function test_import_allows_future_or_upcoming_donation_date_and_excel_serials(): void
    {
        $csvContent = "name,email,phone,amount,frequency,last_donation_date\n"
            . "Upcoming Donor,upcoming@example.com,081234567890,500000,annual,2026-09-15\n"
            . "Excel Serial Donor,serial@example.com,081234567891,500000,annual,46280\n";

        $file = UploadedFile::fake()->createWithContent('sponsors.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.sponsors.import'), [
                'file' => $file,
                'duplicate_mode' => 'skip',
            ]);

        $response->assertRedirect(route('admin.sponsors.index'));
        $response->assertSessionHas('success');
        $response->assertSessionMissing('import_errors');

        $this->assertDatabaseHas('sponsors', [
            'email' => 'upcoming@example.com',
        ]);
        $upcoming = Sponsor::where('email', 'upcoming@example.com')->first();
        $this->assertNotNull($upcoming);
        $this->assertEquals('2026-09-15', $upcoming->last_donation_date->toDateString());

        $this->assertDatabaseHas('sponsors', [
            'email' => 'serial@example.com',
        ]);
        $serial = Sponsor::where('email', 'serial@example.com')->first();
        $this->assertNotNull($serial);
        $this->assertEquals('2026-09-15', $serial->last_donation_date->toDateString());
    }

    public function test_guest_cannot_import_sponsors(): void
    {
        $response = $this->get(route('admin.sponsors.import-template'));
        $response->assertRedirect(route('login'));

        $file = UploadedFile::fake()->create('sponsors.csv');
        $postResponse = $this->post(route('admin.sponsors.import'), [
            'file' => $file,
            'duplicate_mode' => 'skip',
        ]);
        $postResponse->assertRedirect(route('login'));
    }
}

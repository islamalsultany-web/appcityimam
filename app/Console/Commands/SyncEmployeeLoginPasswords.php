<?php

namespace App\Console\Commands;

use App\Models\AppUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncEmployeeLoginPasswords extends Command
{
    protected $signature = 'users:sync-employee-passwords {--dry-run : Preview without saving}';

    protected $description = 'Set each user password to their employee number (for login with الرقم الوظيفي)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $skipped = 0;

        AppUser::query()
            ->whereNotNull('employee_number')
            ->where('employee_number', '!=', '')
            ->orderBy('id')
            ->each(function (AppUser $user) use ($dryRun, &$updated, &$skipped): void {
                $employeeNumber = trim((string) $user->employee_number);

                if ($employeeNumber === '') {
                    $skipped++;

                    return;
                }

                if ($user->role === 'admin' || $user->hasRole('admin')) {
                    $skipped++;

                    return;
                }

                if ($dryRun) {
                    $this->line("Would update #{$user->id} ({$user->username}) → password = {$employeeNumber}");
                    $updated++;

                    return;
                }

                $user->password = Hash::make($employeeNumber);
                $user->must_change_credentials = true;
                $user->save();
                $updated++;
            });

        $this->info($dryRun
            ? "Preview: {$updated} user(s) would be updated, {$skipped} skipped."
            : "Done: {$updated} password(s) set to employee number, {$skipped} skipped.");

        return self::SUCCESS;
    }
}

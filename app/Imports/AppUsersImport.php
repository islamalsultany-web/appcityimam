<?php

namespace App\Imports;

use App\Models\AppUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class AppUsersImport implements OnEachRow
{
    /** @var array<string,string> */
    private array $hashCache = [];

    public function onRow(Row $row): void
    {
        // Skip header row; import relies on column order to support Arabic/English headers.
        if ($row->getIndex() === 1) {
            return;
        }

        $source = $row->toArray();

        if ($this->isRowEmpty($source)) {
            return;
        }

        $username = $this->clean($source[0] ?? null);
        if ($username === null) {
            $username = 'user_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(4));
        }

        $password = $this->clean($source[1] ?? null) ?? '12345678';
        $passwordConfirmation = $this->clean($source[2] ?? null) ?? $password;

        $role = Str::lower((string) ($this->clean($source[7] ?? null) ?? 'asker'));
        if (! in_array($role, AppUser::ROLE_OPTIONS, true)) {
            $role = 'asker';
        }

        // Cache hashed passwords so identical passwords are only hashed once (bcrypt is slow).
        if (! isset($this->hashCache[$password])) {
            $this->hashCache[$password] = Hash::make((string) $password);
        }

        $payload = [
            'password' => $this->hashCache[$password],
            'password_confirmation' => (string) $passwordConfirmation,
            'employee_number' => $this->clean($source[3] ?? null),
            'badge_number' => $this->clean($source[4] ?? null),
            'division' => $this->clean($source[5] ?? null),
            'unit' => $this->clean($source[6] ?? null),
            'role' => $role,
            'responder_scopes' => in_array($role, ['responder', 'admin'], true) ? ['all'] : [],
        ];

        // updateOrCreate: 2 queries per row max; syncRoles intentionally omitted here
        // because it adds 4+ queries per row. Roles are synced via the seeder or
        // the admin can assign them individually from the permissions page.
        AppUser::query()->updateOrCreate(
            ['username' => $username],
            $payload
        );
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->clean($value) !== null) {
                return false;
            }
        }

        return true;
    }
}

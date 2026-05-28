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

        $employeeNumber = $this->clean($source[3] ?? null);

        $username = $this->clean($source[0] ?? null);
        if ($username === null && $employeeNumber !== null) {
            $username = $employeeNumber;
        }
        if ($username === null) {
            $username = 'user_' . now()->format('YmdHis') . '_' . Str::lower(Str::random(4));
        }

        $password = $this->clean($source[1] ?? null);
        if ($password === null || $password === '') {
            $password = $employeeNumber ?? Str::password(12);
        }

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
            'employee_number' => $employeeNumber,
            'badge_number' => $this->clean($source[4] ?? null),
            'division' => $this->clean($source[5] ?? null),
            'unit' => $this->clean($source[6] ?? null),
            'role' => $role,
            'responder_scopes' => in_array($role, ['responder', 'admin'], true) ? ['all'] : [],
        ];

        // updateOrCreate: 2 queries per row max; syncRoles intentionally omitted here
        // because it adds 4+ queries per row. Roles are synced via the seeder or
        // the admin can assign them individually from the permissions page.
        $user = AppUser::query()->updateOrCreate(
            ['username' => $username],
            $payload
        );

        if (in_array($role, AppUser::ROLE_OPTIONS, true)) {
            $user->syncRoles([$role]);
        }
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

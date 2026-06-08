<?php

namespace App\Imports;

use App\Models\AppUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Spatie\Permission\Models\Role;

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

        $passwordFromFile = $this->clean($source[1] ?? null);

        $role = Str::lower((string) ($this->clean($source[7] ?? null) ?? 'asker'));
        if (! in_array($role, AppUser::ROLE_OPTIONS, true)) {
            $role = 'asker';
        }

        $payload = [
            'employee_number' => $employeeNumber,
            'badge_number' => $this->clean($source[4] ?? null),
            'division' => $this->clean($source[5] ?? null),
            'unit' => $this->clean($source[6] ?? null),
            'role' => $role,
            'responder_scopes' => in_array($role, ['responder', 'admin'], true) ? ['all'] : [],
        ];

        $user = $this->findExistingUser($employeeNumber, $username);

        if ($passwordFromFile !== null && $passwordFromFile !== '') {
            if (! isset($this->hashCache[$passwordFromFile])) {
                $this->hashCache[$passwordFromFile] = Hash::make((string) $passwordFromFile);
            }

            $payload['password'] = $this->hashCache[$passwordFromFile];
        } elseif (! $user) {
            $password = $employeeNumber ?? Str::password(12);

            if (! isset($this->hashCache[$password])) {
                $this->hashCache[$password] = Hash::make((string) $password);
            }

            $payload['password'] = $this->hashCache[$password];
        }

        if ($user) {
            if ($this->shouldRefreshUsernameFromImport($user, $username, $employeeNumber)) {
                $user->username = $this->resolveAvailableUsername($username, $employeeNumber, $user->id);
            }

            $user->fill($payload);
            $user->save();
        } else {
            $user = AppUser::query()->create(array_merge([
                'username' => $this->resolveAvailableUsername($username, $employeeNumber),
            ], $payload));
        }

        if (in_array($role, AppUser::ROLE_OPTIONS, true)) {
            Role::findOrCreate($role, 'web');
            $user->syncRoles([$role]);
        }
    }

    private function findExistingUser(?string $employeeNumber, string $username): ?AppUser
    {
        if ($employeeNumber !== null) {
            $byEmployeeNumber = AppUser::query()->where('employee_number', $employeeNumber)->first();

            if ($byEmployeeNumber) {
                return $byEmployeeNumber;
            }
        }

        return AppUser::query()->where('username', $username)->first();
    }

    private function shouldRefreshUsernameFromImport(AppUser $user, string $importedUsername, ?string $employeeNumber): bool
    {
        $importedUsername = trim($importedUsername);
        $currentUsername = trim((string) $user->username);

        if ($importedUsername === '' || $importedUsername === $currentUsername) {
            return false;
        }

        if ($employeeNumber !== null && $importedUsername === $employeeNumber) {
            return false;
        }

        $storedEmployeeNumber = trim((string) $user->employee_number);

        if ($storedEmployeeNumber !== '' && $currentUsername === $storedEmployeeNumber) {
            return true;
        }

        return $storedEmployeeNumber === '';
    }

    private function resolveAvailableUsername(string $username, ?string $employeeNumber, ?int $ignoreUserId = null): string
    {
        $username = Str::limit(trim($username), 255, '');

        if ($username === '') {
            $username = $employeeNumber ?? ('user_' . Str::lower(Str::random(8)));
        }

        if (! $this->usernameIsTaken($username, $ignoreUserId)) {
            return $username;
        }

        if ($employeeNumber !== null && $employeeNumber !== '') {
            $withEmployeeNumber = Str::limit($username . ' - ' . $employeeNumber, 255, '');

            if (! $this->usernameIsTaken($withEmployeeNumber, $ignoreUserId)) {
                return $withEmployeeNumber;
            }
        }

        do {
            $candidate = Str::limit($username . ' - ' . Str::lower(Str::random(4)), 255, '');
        } while ($this->usernameIsTaken($candidate, $ignoreUserId));

        return $candidate;
    }

    private function usernameIsTaken(string $username, ?int $ignoreUserId = null): bool
    {
        $query = AppUser::query()->where('username', $username);

        if ($ignoreUserId !== null) {
            $query->where('id', '!=', $ignoreUserId);
        }

        return $query->exists();
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

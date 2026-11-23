<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClearanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'department_status',
        'department_remark',
        'library_status',
        'library_remark',
        'store_status',
        'store_remark',
        'finance_status',
        'finance_remark',
        'registrar_status',
        'registrar_remark',
        'overall_status',
        'certificate_serial',
        'verification_code',
        'certificate_status',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function officeSequence(): array
    {
        return [
            'department_head' => ['label' => 'Academic Department', 'status' => 'department_status', 'remark' => 'department_remark'],
            'library'         => ['label' => 'Library', 'status' => 'library_status', 'remark' => 'library_remark'],
            'store'           => ['label' => 'Store', 'status' => 'store_status', 'remark' => 'store_remark'],
            'finance'         => ['label' => 'Finance', 'status' => 'finance_status', 'remark' => 'finance_remark'],
            'registrar'       => ['label' => 'Registrar', 'status' => 'registrar_status', 'remark' => 'registrar_remark'],
        ];
    }

    public function isReadyForOffice(string $role): bool
    {
        $sequence = array_keys(self::officeSequence());
        $index = array_search($role, $sequence, true);
        if ($index === false) {
            return false;
        }

        $map = self::officeSequence();
        if ($this->{$map[$role]['status']} !== 'pending') {
            return false;
        }

        // Enforce defined sequential order: all previous offices must be approved first
        for ($i = 0; $i < $index; $i++) {
            $prevCol = $map[$sequence[$i]]['status'];
            if ($this->{$prevCol} !== 'approved') {
                return false;
            }
        }

        return true;
    }

    public function progressPercentage(): int
    {
        $statuses = [
            $this->department_status,
            $this->library_status,
            $this->store_status,
            $this->finance_status,
            $this->registrar_status,
        ];

        $approvedCount = collect($statuses)->filter(fn ($s) => $s === 'approved')->count();
        return (int) round(($approvedCount / 5) * 100);
    }

    public function recalculateOverallStatus(): void
    {
        $statuses = [
            $this->department_status,
            $this->library_status,
            $this->store_status,
            $this->finance_status,
            $this->registrar_status,
        ];

        if (in_array('rejected', $statuses, true)) {
            $this->overall_status = 'rejected';
        } elseif (collect($statuses)->every(fn ($s) => $s === 'approved')) {
            $this->overall_status = 'approved';
        } else {
            $this->overall_status = 'pending';
        }

        $this->save();
    }
}
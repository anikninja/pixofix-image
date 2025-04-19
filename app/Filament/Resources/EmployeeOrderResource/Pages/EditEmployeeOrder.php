<?php

namespace App\Filament\Resources\EmployeeOrderResource\Pages;

use App\Filament\Resources\EmployeeOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use App\Enums\RolesEnum;
use App\Models\User;
use PhpParser\Node\Expr\Cast\String_;

class EditEmployeeOrder extends EditRecord
{
    protected static string $resource = EmployeeOrderResource::class;
    protected function authorizeAccess(): void
    {
        $user = Auth::user();
        if ($user && $user->hasRole(RolesEnum::Employee)) {
            $employeeId = $user->employee->id ?? null;
            if ($employeeId) {
                // Check if the order belongs to the employee
                $this->record = $this->getRecordModel()::query()
                    ->where('employee_id', $employeeId)
                    ->find($this->record->id);
                if (!$this->record) {
                    abort(403, 'You are not authorized to edit this order.');
                }
                // Check if the order is in a state that allows editing
                if ($this->record->status === 'completed') {
                    abort(403, 'You cannot edit an order that has been completed.');
                } elseif ($this->record->status === 'cancelled') {
                    abort(403, 'You cannot edit an order that has been cancelled.');
                }                
            }
        } else {
            abort(403, 'Employees are not allowed to create or edit orders.');
        }
    }

    protected function getRecordModel(): string
    {
        return $this->record::class;
    }
    protected function getHeaderActions(): array
    {
        return [];
    }
    protected function getFormActions(): array
    {
        return [];
    }
    
}

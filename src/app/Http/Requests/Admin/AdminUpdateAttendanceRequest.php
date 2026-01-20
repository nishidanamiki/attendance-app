<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\StoreStampCorrectionRequest;

class AdminUpdateAttendanceRequest extends StoreStampCorrectionRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['attendance_id'], $rules['work_date']);

        return $rules;
    }
}

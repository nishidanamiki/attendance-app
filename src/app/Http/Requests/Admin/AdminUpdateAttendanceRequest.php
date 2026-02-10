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
        logger()->info('AdminUpdateAttendanceRequest authorize', [
            'auth' => auth()->check(),
            'user_id' => auth()->user()?->id,
            'is_admin' => auth()->user()?->is_admin,
            'path' => request()->path(),
        ]);
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

        unset($rules['attendance_id']);

        $rules['user_id'] = ['required', 'exists:users,id'];

        return $rules;
    }
}

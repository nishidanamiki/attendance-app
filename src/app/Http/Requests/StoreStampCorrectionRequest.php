<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStampCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_id' => ['nullable', 'integer', 'exists:attendances,id'],
            'work_date' => ['required_without:attendance_id', 'date'],
            'clock_in_at' => ['nullable', 'date_format:H:i'],
            'clock_out_at' => ['nullable', 'date_format:H:i'],
            'breaks' => ['array'],
            'breaks.*.id' => ['nullable', 'integer'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'remarks' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'clock_in_at' => $this->nullIfEmpty($this->input('clock_in_at')),
            'clock_out_at' => $this->nullIfEmpty($this->input('clock_out_at')),
        ]);

        $rawBreaks = $this->input('breaks', []);
        if (!is_array($rawBreaks)) {
            $this->merge(['breaks' => []]);
            return;
        }

        $breaks = [];

        foreach ($rawBreaks as $i => $break) {
            if (!is_array($break)) {
                continue;
            }

            $breaks[$i] = [
                'id' => $break['id'] ?? null,
                'start' => $this->nullIfEmpty($break['start'] ?? null),
                'end' => $this->nullIfEmpty($break['end'] ?? null),
            ];
        }

        $this->merge(['breaks' => $breaks]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->toMinutes($this->clock_in_at);
            $clockOut = $this->toMinutes($this->clock_out_at);

            if ($clockIn !== null && $clockOut !== null && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_in_at', '出勤時間もしくは退勤時間が不適切な値です');
            }

            foreach ($this->input('breaks', []) as $i => $break) {
                $start = $this->toMinutes($break['start'] ?? null);
                $end = $this->toMinutes($break['end'] ?? null);

                if ($start === null && $end === null) {
                    continue;
                }

                if (
                    $start !== null &&
                    (
                        ($clockIn !== null && $start < $clockIn) ||
                        ($clockOut !== null && $start > $clockOut)
                    )
                ) {
                    $validator->errors()->add("breaks.$i.start", '休憩時間が不適切な値です');
                }

                if ($end !== null && $clockOut !== null && $end > $clockOut) {
                    $validator->errors()->add("breaks.$i.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }

    private function nullIfEmpty(?string $value): ?string
    {
        return ($value === '' ? null : $value);
    }

    private function toMinutes(?string $time): ?int
    {
        if (!$time) return null;

        [$h, $m] = explode(':', $time);
        return ((int)$h) * 60 + (int)$m;
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'new_start_time' => ['required', 'date_format:H:i'],
            'new_end_time'   => ['required', 'date_format:H:i', 'after:new_start_time'],

            'breaks.*.new_break_start_time' => [
                'nullable',
                'required_with:breaks.*.new_break_end_time',
                'date_format:H:i',
                'after_or_equal:new_start_time',
                'before_or_equal:new_end_time',
            ],
            'breaks.*.new_break_end_time' => [
                'nullable',
                'required_with:breaks.*.new_break_start_time',
                'date_format:H:i',
                'after:breaks.*.new_break_start_time',
                'before_or_equal:new_end_time',
            ],

            'comment' => ['required'],
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        if (
            $this->new_start_time &&
            $this->new_end_time &&
            $this->new_start_time >= $this->new_end_time
        ) {
            $validator->errors()->add(
                'new_start_time',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        foreach ($this->breaks ?? [] as $index => $break) {
            $breakStart = $break['new_break_start_time'] ?? null;
            $breakEnd   = $break['new_break_end_time'] ?? null;

            if (
                $breakStart &&
                (
                    $breakStart < $this->new_start_time ||
                    $breakStart > $this->new_end_time
                )
            ) {
                $validator->errors()->add(
                    "breaks.$index.new_break_start_time",
                    '休憩時間が不適切な値です'
                );
            }

            if (
                $breakEnd &&
                $breakEnd > $this->new_end_time
            ) {
                $validator->errors()->add(
                    "breaks.$index.new_break_start_time",
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
            }

            if (
                $breakStart &&
                $breakEnd &&
                $breakStart >= $breakEnd
            ) {
                $validator->errors()->add(
                    "breaks.$index.new_break_start_time",
                    '休憩時間が不適切な値です'
                );
            }
        }
    });
}

    public function attributes()
    {
        return [
            'breaks.*.new_break_start_time' => '休憩開始時間',
            'breaks.*.new_break_end_time'   => '休憩終了時間',
        ];
    }

    public function messages()
    {
        return [
            'new_start_time.required' => '出勤時間を入力してください',
            'new_start_time.date_format' => '時間は「--:--」形式で入力してください',
            'new_end_time.required' => '退勤時間を入力してください',
            'new_end_time.after'   => '出勤時間もしくは退勤時間が不適切な値です',
            'new_end_time.date_format' => '時間は「--:--」形式で入力してください',

            'breaks.*.new_break_start_time.after_or_equal'
            => '休憩時間が不適切な値です',
            'breaks.*.new_break_start_time.before_or_equal'
            => '休憩時間が不適切な値です',
            'breaks.*.new_break_start_time.date_format' => '時間は「--:--」形式で入力してください',

            'breaks.*.new_break_end_time.after'
            => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.new_break_end_time.before_or_equal'
            => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.new_break_end_time.date_format' => '時間は「--:--」形式で入力してください',

            'comment.required' => '備考を記入してください',
        ];
    }

}

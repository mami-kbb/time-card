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
            'new_start_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'new_end_time'   => ['required', 'after:new_start_time', 'regex:/^\d{2}:\d{2}$/'],

            'breaks.*.new_break_start_time' => [
                'nullable',
                'required_with:breaks.*.new_break_end_time',
                'after_or_equal:new_start_time',
                'before_or_equal:new_end_time',
                'regex:/^\d{2}:\d{2}$/',
            ],
            'breaks.*.new_break_end_time' => [
                'nullable',
                'required_with:breaks.*.new_break_start_time',
                'after:breaks.*.new_break_start_time',
                'before_or_equal:new_end_time',
                'regex:/^\d{2}:\d{2}$/',
            ],

            'comment' => ['required'],
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {

        // ① 出勤・退勤
        if ($this->new_start_time >= $this->new_end_time) {
            $validator->errors()->add(
                'new_start_time',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        foreach ($this->breaks ?? [] as $index => $break) {

            $breakStart = $break['new_break_start_time'] ?? null;
            $breakEnd   = $break['new_break_end_time'] ?? null;

            // ② 休憩開始が勤務時間外
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

            // ③ 休憩終了が退勤より後
            if (
                $breakEnd &&
                $breakEnd > $this->new_end_time
            ) {
                $validator->errors()->add(
                    "breaks.$index.new_break_end_time",
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
            }

            // ⑤ 休憩開始 >= 休憩終了（★追加）
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

    public function messages()
    {
        return [
            'new_start_time.required' => '出勤時間を入力してください',
            'new_start_time.regex' => '時間は「--:--」形式で入力してください',
            'new_end_time.required' => '退勤時間を入力してください',
            'new_end_time.after'   => '出勤時間もしくは退勤時間が不適切な値です',
            'new_end_time.regex' => '時間は「--:--」形式で入力してください',

            'breaks.*.new_break_start_time.after_or_equal'
            => '休憩時間が不適切な値です',
            'breaks.*.new_break_start_time.before_or_equal'
            => '休憩時間が不適切な値です',
            'breaks.*.new_break_start_time.regex' => '時間は「--:--」形式で入力してください',

            'breaks.*.new_break_end_time.after'
            => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.new_break_end_time.before_or_equal'
            => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.new_break_end_time.regex' => '時間は「--:--」形式で入力してください',

            'comment.required' => '備考を記入してください',
        ];
    }

}

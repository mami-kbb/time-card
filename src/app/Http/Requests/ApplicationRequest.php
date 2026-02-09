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
            'new_end_time' => ['required', 'date_format:H:i'],
            'comment' => ['required'],
            'breaks.*.new_break_start_time' => ['nullable', 'date_format:H:i'],
            'breaks.*.new_break_end_time'=> ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->new_start_time >= $this->new_end_time) {
                $validator->errors()->add(
                    'new_start_time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }
            
            foreach ($this->breaks ?? [] as $break) {
                if (
                    ($break['new_break_start_time'] && $break['new_break_start_time'] < $this->new_start_time) ||
                    ($break['new_break_end_time'] && $break['new_break_end_time'] > $this->new_end_time)
                ) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
}

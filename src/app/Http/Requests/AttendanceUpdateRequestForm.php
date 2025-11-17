<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequestForm extends FormRequest
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
            'start_time' => 'required|regex:/^\d{1,2}:\d{2}$/',
            'end_time' => 'required|regex:/^\d{1,2}:\d{2}$/',
            'break_start.*' => 'nullable|regex:/^\d{1,2}:\d{2}$/',
            'break_end.*' => 'nullable|regex:/^\d{1,2}:\d{2}$/',
            'note' => 'required|string',
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');
        $breakStartTimes = $this->input('break_start', []);
        $breakEndTimes = $this->input('break_end', []);

        // 出勤・退勤のタイムスタンプ
        $startTs = $startTime ? strtotime($startTime) : null;
        $endTs = $endTime ? strtotime($endTime) : null;

        // 出勤・退勤チェック
        if ($startTs && $endTs && $startTs > $endTs) {
            $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
        }

        // 休憩チェック
        foreach ($breakStartTimes as $index => $breakStart) {
            $breakStartTs = $breakStart ? strtotime($breakStart) : null;
            $breakEndTs = isset($breakEndTimes[$index]) ? strtotime($breakEndTimes[$index]) : null;

            // 休憩開始時間が出勤時間より前、もしくは退勤時間より後
            if ($breakStartTs && ($breakStartTs < $startTs || $breakStartTs > $endTs)) {
                $validator->errors()->add("break_start.$index", '休憩時間が不適切な値です');
            }

            // 休憩終了時間が退勤時間より後、もしくは開始時間より前
            if ($breakEndTs && ($breakEndTs > $endTs || ($breakStartTs && $breakEndTs < $breakStartTs))) {
                $validator->errors()->add("break_end.$index", '休憩時間もしくは退勤時間が不適切な値です');
            }
        }

    });
}


    public function messages()
    {
        return [
            'note.required' =>'備考を入力してください',
        ];
    }
}

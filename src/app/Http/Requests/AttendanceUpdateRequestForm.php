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
            'start_time' => 'nullable|regex:/^\d{1,2}:\d{2}$/',
            'end_time' => 'nullable|regex:/^\d{1,2}:\d{2}$/',
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

            if (empty($startTime) || empty($endTime)) {
                $validator->errors()->add('time', '出勤時間または退勤時間を入力してください');
            }

            // 出勤・退勤のタイムスタンプ
            $workStartTimestamp = $startTime ? strtotime($startTime) : null;
            $workEndTimestamp   = $endTime ? strtotime($endTime) : null;

            // 出勤時間・退勤時間チェック
            if ($workStartTimestamp && $workEndTimestamp && $workStartTimestamp > $workEndTimestamp) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩チェックのタイムスタンプ
            foreach ($breakStartTimes as $index => $breakStart) {
                $breakStartTimestamp = $breakStart ? strtotime($breakStart) : null;
                $breakEndTimestamp   = isset($breakEndTimes[$index]) ? strtotime($breakEndTimes[$index]) : null;

            // 休憩開始時間が出勤時間より前、もしくは退勤時間より後
                if ($breakStartTimestamp && ($breakStartTimestamp < $workStartTimestamp || $breakStartTimestamp > $workEndTimestamp)) {
                    $validator->errors()->add("break_start.$index", '休憩時間が不適切な値です');
                }

            // 休憩終了時間が退勤時間より後、もしくは開始時間より前
                if ($breakEndTimestamp && ($breakEndTimestamp > $workEndTimestamp || ($breakStartTimestamp && $breakEndTimestamp < $breakStartTimestamp))) {
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

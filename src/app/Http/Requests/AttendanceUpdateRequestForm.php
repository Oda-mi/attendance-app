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
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'break_start.*' => 'nullable',
            'break_end.*' => 'nullable',
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

    /*
    |--------------------------------------------------------------------------
    | 出退勤バリデーション
    |--------------------------------------------------------------------------
    */
            // 出勤・退勤のタイムスタンプ
            $workStartTimestamp = $startTime ? strtotime($startTime) : null;
            $workEndTimestamp   = $endTime ? strtotime($endTime) : null;

            // 出勤時間・退勤時間チェック
            if ($workStartTimestamp && $workEndTimestamp && $workStartTimestamp > $workEndTimestamp) {
                $validator->errors()->add('start_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            //入力必須チェック
            if (empty($startTime) || empty($endTime)) {
                $validator->errors()->add('time', '出勤時間と退勤時間は必ず入力してください');
            }

            //入力形式チェック(HH:MM)
            $hasWorkTimeFormatError = false;

            if ($this->start_time && !preg_match('/^\d{1,2}:\d{2}$/', $this->start_time)) {
                $hasWorkTimeFormatError = true;
            }

            if ($this->end_time && !preg_match('/^\d{1,2}:\d{2}$/', $this->end_time)) {
                $hasWorkTimeFormatError = true;
            }

            if ($hasWorkTimeFormatError) {
                $validator->errors()->add('work_time_format', '出勤・退勤の時間は半角の HH:MM 形式で入力してください');
            }

    /*
    |--------------------------------------------------------------------------
    | 休憩時間バリデーション
    |--------------------------------------------------------------------------
    */
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

            //入力形式チェック(HH:MM)
            foreach ($this->break_start ?? [] as $index => $breakStart) {
                if ($breakStart !== null && $breakStart !== '' && !preg_match('/^\d{1,2}:\d{2}$/', $breakStart)) {
                    $validator->errors()->add("break_start_format.$index", '休憩時間は半角の HH:MM 形式で入力してください');
                }
            }

            foreach ($this->break_end ?? [] as $index => $breakEnd) {
                if ($breakEnd !== null && $breakEnd !== '' && !preg_match('/^\d{1,2}:\d{2}$/', $breakEnd)) {
                    $validator->errors()->add("break_end_format.$index", '休憩時間は半角の HH:MM 形式で入力してください');
                }
            }

            //休憩は入力必須じゃないけど、入力するなら開始・終了両方必須
            foreach (($this->break_start ?? []) as $index => $breakStart) {
                $breakEnd = $this->break_end[$index] ?? null;

                if (($breakStart && !$breakEnd) || (!$breakStart && $breakEnd)) {
                    $validator->errors()->add(
                        "break_start_end.$index",'休憩を入力する場合は、開始時間と終了時間の両方を入力してください'
                    );
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

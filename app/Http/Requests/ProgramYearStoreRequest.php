<?php

namespace App\Http\Requests;

use App\Models\ProgramYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProgramYearStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create', ProgramYear::class);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'guid' => 'required',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'claim_percent' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'last_touch_by_user_guid' => 'required|exists:users,guid',
            'comment' => 'nullable',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'guid' => Str::orderedUuid()->getHex(),
            'last_touch_by_user_guid' => $this->user()->guid,
        ]);
    }

    /**
     * Convert to boolean
     *
     * @return bool
     */
    private function toBoolean($booleable)
    {
        return filter_var($booleable, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}

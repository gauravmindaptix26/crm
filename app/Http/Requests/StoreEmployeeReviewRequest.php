<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreEmployeeReviewRequest extends FormRequest
{
    public function authorize()
    {
        // Allow only Project Managers (adjust this check for your auth/roles)
        $user = $this->user();
        if (! $user) return false;
        return $user->roles->first()->name === 'Project Manager';
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:users,id',
            'review_month' => 'required|date_format:Y-m', // e.g. 2025-08
            'quality_of_work' => 'required|integer|min:1|max:10',
            'communication' => 'required|integer|min:1|max:10',
            'ownership' => 'required|integer|min:1|max:10',
            'team_collaboration' => 'required|integer|min:1|max:10',
            'comments' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'review_month.date_format' => 'Review month format must be YYYY-MM (e.g. 2025-08).',
        ];
    }
}

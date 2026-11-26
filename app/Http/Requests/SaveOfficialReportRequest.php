<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOfficialReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \App\Support\AppAuth::user($this) !== null;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'starts_on' => 'required|date_format:Y-m-d',
            'ends_on' => 'required|date_format:Y-m-d|after_or_equal:starts_on',
            'content' => 'required|array:theme,accent,font,chart,number,prepared_on,author,manager,attachments,sections,metrics',
            'content.theme' => ['required', Rule::in(['heritage', 'modern', 'minimal'])],
            'content.accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'content.font' => ['required', Rule::in(['cairo', 'serif', 'sans'])],
            'content.chart' => ['required', Rule::in(['bar', 'doughnut', 'none'])],
            'content.number' => 'nullable|string|max:100',
            'content.prepared_on' => 'required|date_format:Y-m-d',
            'content.author' => 'required|string|max:255',
            'content.manager' => 'nullable|string|max:255',
            'content.attachments' => 'nullable|string|max:4000',
            'content.sections' => 'required|array|min:1|max:20',
            'content.sections.*' => 'required|array:title,body,enabled,columns,rows',
            'content.sections.*.title' => 'required|string|max:255',
            'content.sections.*.body' => 'nullable|string|max:12000',
            'content.sections.*.enabled' => 'required|boolean',
            'content.sections.*.columns' => 'present|array|max:6',
            'content.sections.*.columns.*' => 'required|string|max:100',
            'content.sections.*.rows' => 'present|array|max:100',
            'content.sections.*.rows.*' => 'array|max:6',
            'content.sections.*.rows.*.*' => 'nullable|string|max:1000',
            'content.metrics' => 'present|array|max:12',
            'content.metrics.*' => 'required|array:label,value,unit',
            'content.metrics.*.label' => 'required|string|max:100',
            'content.metrics.*.value' => 'required|numeric|min:0|max:1000000000000',
            'content.metrics.*.unit' => 'nullable|string|max:50',
        ];
    }
}

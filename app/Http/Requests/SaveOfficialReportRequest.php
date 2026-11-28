<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOfficialReportRequest extends FormRequest
{
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $charts = $this->input('content.charts', []);
            if (! is_array($charts)) {
                return;
            }
            foreach ($charts as $i => $chart) {
                if (is_array($chart) && is_array($chart['labels'] ?? null) && is_array($chart['values'] ?? null)
                    && count($chart['labels']) !== count($chart['values'])) {
                    $validator->errors()->add('content.charts.'.$i.'.values', 'يجب أن يقابل كل عنوان في الرسم قيمة واحدة.');
                }
            }
        });
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'starts_on' => 'required|date_format:Y-m-d',
            'ends_on' => 'required|date_format:Y-m-d|after_or_equal:starts_on',
            'content' => 'required|array:theme,accent,font,chart,number,prepared_on,author,manager,attachments,sections,metrics,charts',
            'content.charts' => 'sometimes|array',
            'content.charts.*' => 'required|array:title,type,unit,labels,values',
            'content.charts.*.title' => 'required|string|max:255',
            'content.charts.*.type' => 'required|in:bar,doughnut',
            'content.charts.*.unit' => 'required|string|max:50',
            'content.charts.*.labels' => 'required|array|min:1',
            'content.charts.*.labels.*' => 'required|string|max:100',
            'content.charts.*.values' => 'required|array|min:1',
            'content.charts.*.values.*' => 'required|numeric|min:0|max:1000000000000',
            'content.theme' => ['required', Rule::in(['heritage', 'modern', 'minimal'])],
            'content.accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'content.font' => ['required', Rule::in(['cairo', 'serif', 'sans'])],
            'content.chart' => ['required', Rule::in(['bar', 'doughnut', 'none'])],
            'content.number' => 'nullable|string|max:100',
            'content.prepared_on' => 'required|date_format:Y-m-d',
            'content.author' => 'required|string|max:255',
            'content.manager' => 'nullable|string|max:255',
            'content.attachments' => 'nullable|string|max:4000',
            'content.sections' => 'required|array|min:1',
            'content.sections.*' => 'required|array:title,body,enabled,columns,rows',
            'content.sections.*.title' => 'required|string|max:255',
            'content.sections.*.body' => 'nullable|string',
            'content.sections.*.enabled' => 'required|boolean',
            'content.sections.*.columns' => 'present|array',
            'content.sections.*.columns.*' => 'required|string|max:100',
            'content.sections.*.rows' => 'present|array',
            'content.sections.*.rows.*' => 'array',
            'content.sections.*.rows.*.*' => 'nullable|string',
            'content.metrics' => 'present|array',
            'content.metrics.*' => 'required|array:label,value,unit',
            'content.metrics.*.label' => 'required|string|max:100',
            'content.metrics.*.value' => 'required|numeric|min:0|max:1000000000000',
            'content.metrics.*.unit' => 'nullable|string|max:50',
        ];
    }
}

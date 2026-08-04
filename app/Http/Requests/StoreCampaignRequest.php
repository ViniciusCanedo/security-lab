<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'article_id' => ['nullable', 'exists:articles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da campanha é obrigatório.',
            'subject.required' => 'O assunto do e-mail é obrigatório.',
            'content.required' => 'O conteúdo da campanha é obrigatório.',
            'article_id.exists' => 'O artigo informado não existe.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'cover_image_url' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', Rule::enum(ArticleStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título do artigo é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode exceder 255 caracteres.',
            'content.required' => 'O conteúdo do artigo é obrigatório.',
            'summary.max' => 'O resumo não pode exceder 1000 caracteres.',
            'cover_image_url.url' => 'A URL da imagem de capa deve ser válida.',
            'status.enum' => 'O status informado é inválido.',
        ];
    }
}

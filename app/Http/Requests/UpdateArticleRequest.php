<?php

namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'cover_image_url' => ['nullable', 'url', 'max:255'],
            'status' => ['sometimes', Rule::enum(ArticleStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título do artigo não pode ser vazio.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode exceder 255 caracteres.',
            'content.required' => 'O conteúdo do artigo não pode ser vazio.',
            'summary.max' => 'O resumo não pode exceder 1000 caracteres.',
            'cover_image_url.url' => 'A URL da imagem de capa deve ser válida.',
            'status.enum' => 'O status informado é inválido.',
        ];
    }
}

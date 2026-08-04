<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Novo Comentário</title>
</head>
<body>
    <h1>Novo comentário recebido!</h1>
    <p>Olá, o seu artigo "{{ $comment->article->title }}" recebeu um novo comentário de {{ $comment->user->name }}:</p>
    <blockquote>
        {{ $comment->content }}
    </blockquote>
</body>
</html>

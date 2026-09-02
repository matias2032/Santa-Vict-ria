# Centro Médico Santa Victória — Landing Page

## Estrutura
```
santa_victoria/
├── index.php                  → página inicial (hero dinâmico + serviços)
├── sobre.php                  → Sobre Nós
├── servicos.php                → lista completa de serviços (tabela tratamentos)
├── galeria.php                 → galeria de fotos
├── contacto.php                → formulário de agendamento
├── processar_agendamento.php   → grava o agendamento e regista os e-mails
├── config/
│   └── db.php                  → ligação PDO à base de dados
├── includes/
│   ├── header.php               → widget de cabeçalho (reutilizado em todas as páginas)
│   └── footer.php               → widget de rodapé (reutilizado em todas as páginas)
└── assets/
    ├── css/style.css
    ├── js/main.js
    └── images/
        ├── logo.png
        └── galeria/             → coloca aqui as fotos do hero e da galeria
```

## Como usar

1. **Base de dados**: cria a base `santa_victoria` com o schema que já tens (as 3 tabelas:
   `tratamentos`, `agendamentos`, `email_logs`).
2. **Credenciais**: edita `config/db.php` com o utilizador/password do teu MySQL.
3. **Fotos do hero/galeria**: coloca as imagens (.jpg/.jpeg/.png/.webp) dentro de
   `assets/images/galeria/`. O hero da página inicial e a página de Galeria
   leem essa pasta automaticamente — não precisas de mexer no código PHP.
4. **Logótipo**: já está em `assets/images/logo.png` (o que enviaste).
5. **Serviços**: adiciona linhas na tabela `tratamentos` (com `ativo = 1`) e elas
   aparecem automaticamente na página inicial (6 mais recentes) e em `servicos.php` (todas).
6. **Agendamentos**: o formulário em `contacto.php` grava em `agendamentos` e tenta
   enviar dois e-mails (confirmação ao cliente + notificação à clínica) através da
   função `mail()` do PHP — cada tentativa é registada em `email_logs`. Se o teu
   servidor não tiver SMTP configurado, os e-mails podem falhar, mas o agendamento
   fica sempre gravado na base de dados.

## Widgets reutilizáveis

`includes/header.php` e `includes/footer.php` são incluídos em todas as páginas
(`index.php`, `sobre.php`, `servicos.php`, `galeria.php`, `contacto.php`). Para criar
uma nova página, basta seguir o mesmo padrão:

```php
<?php
$tituloPagina = 'Título da Página';
require_once __DIR__ . '/includes/header.php';
?>

<!-- o teu conteúdo aqui -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
```

## Paleta de cores (extraída do logótipo)

| Nome            | Hex       |
|-----------------|-----------|
| Azul profundo   | `#1c2a6d` |
| Azul médio      | `#2f3e9e` |
| Roxo            | `#7238d6` |
| Magenta         | `#c22bb3` |
| Ciano (acento)  | `#2ec4f1` |

Tipografia: **Fraunces** (títulos) + **Manrope** (texto), via Google Fonts.

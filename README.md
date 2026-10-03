# The ClassicPress Theme

Tema minimalista para ClassicPress / WordPress. Feito para ser iterado.

Desenvolvido com a ajuda do DeepSeek.

## Estrutura

- `style.css` — cabeçalho do tema + estilos (grid com sidebar esquerda sticky).
- `functions.php` — setup, menus e enfileiramento do CSS.
- `header.php` — abre `.site` e o `.site-header`.
- `footer.php` — fecha `.site` e chama `wp_footer()`.
- `sidebar.php` — categorias como `.category-link` (com estado `.active`) + link "Ver todas".
- `index.php` — listagem principal.
- `single.php` — post único (com `.autor` e `.post-footer-tags`).
- `page.php` — páginas estáticas.
- `archive.php` — categorias, tags, datas, autor.
- `search.php` — resultados de busca.
- `404.php` — página de erro.
- `comments.php` — área de comentários.
- `searchform.php` — formulário de busca.

## Layout

Grid CSS com quatro áreas: header, sidebar (180px, sticky), main, footer.
Em telas menores que 768px, tudo vira uma coluna única e as categorias
viram uma linha de pílulas horizontais.

## Paleta

- Texto principal: `#27262b`
- Texto secundário: `#6e6e73`
- Acento (links, botões, tags): `#7253ed` (hover `#5a3ec8`)
- Bordas: `#eef0f2`
- Fundo suave: `#f7f7f9`

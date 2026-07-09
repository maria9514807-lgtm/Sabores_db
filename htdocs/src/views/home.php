<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sabores</title>
    <link rel="stylesheet" href="css/styles.css">
    <!-- <meta http-equiv="refresh" content="5"> -->
</head>

<script>
    function enviarAcao(url) { // FUNÇÃO Exclusiva para trazer novas PÁGINAS HTML para dentro do main (ex: formulário de adicionar receita)
        fetch(url).then(response => {
            if (response.status === 401) { // Se o servidor respondeu 401 (Não autorizado), abre o modal de login automaticamente!
                console.log('Acesso negado: Abrindo modal de login.');
                document.getElementById('viewLogin').showModal();
                return null; // Cancela os próximos passos do then
            }
            
            if (!response.ok) {
                throw new Error('Erro no servidor.');
            }
            
            // Verifica o tipo de resposta (se é JSON ou HTML de uma nova página)
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return response.json();
            } else {
                return response.text(); // Se for o HTML da página add_receita
            }
        })
        .then(dados => {
            if (!dados) return;

            // Se recebeu uma string HTML (no caso de chamar a view de adicionar receita)
            if (typeof dados === 'string') {
                // Substitui o conteúdo do painel principal pelo formulário recebido
                document.querySelector('main').innerHTML = dados;
            } else if (dados.sucesso) {
                console.log(dados.mensagem);
                // Aqui você atualiza a cor do botão ou contador visualmente
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
        });
    }

    // FUNÇÃO B: Exclusiva para a ação assíncrona de CURTIR (Altera apenas o botão sem recarregar o main)
    function curtirReceita(idReceita, botaoElemento) {
        const dadosForm = new FormData();
        dadosForm.append('recipe_id', idReceita); // Prepara o ID para o $_POST['recipe_id'] do Controller

        fetch('index.php?page=acao&acao=curtir', {
            method: 'POST',
            body: dadosForm
        })
        .then(response => {
            if (response.status === 401) {
                document.getElementById('viewLogin').showModal(); // Abre o login se não estiver logado
                return null;
            }
            if (!response.ok) throw new Error('Erro no servidor.');
            return response.json(); // Espera o JSON estruturado do PHP
        })
        .then(dados => {
            if (!dados || !dados.sucesso) return;

            // Liga ou desliga a classe CSS 'ativo' (para pintar o coração de vermelho)
            if (dados.curtido) {
                botaoElemento.classList.add('ativo');
            } else {
                botaoElemento.classList.remove('ativo');
            }

            // Atualiza o contador de texto interno deste botão específico
            const contadorSpan = botaoElemento.querySelector('.count');
            if (contadorSpan) {
                contadorSpan.textContent = dados.novas_curtidas;
            }
        })
        .catch(error => console.error('Erro ao curtir receita:', error));
    }
</script>

<body>
    <div id="fundo">
        <div id="perfil"> <!-- Onde ficarão os elementos do perfil sempre visiveis -->
            <div class="logo">
                <img id="logo_img" src="img/sabores.png" alt="Logo da emrpesa sabores">
                <label id="nome_empresa">Sabores</label>
            </div>
            <div id="totais">
                <div>Total de receitas: <?= htmlspecialchars($totalReceitas) ?></div>
                <div>Total de porções: <?= htmlspecialchars($totalPorcoes) ?></div>
            </div>
            <div id="meio_perfil">
                <button id="add_receita" onclick="enviarAcao('index.php?page=acao&acao=add_receita')"> Adicionar Receita </button>
            </div>
            <footer id="rodape_perfil">
                <label> Sabores </label>
                <div id="icones">
                    <a href="https://instagram.com.br" class="icone">
                        <img src="img/icones/instagram.svg" alt="logo do instagram">
                    </a>
                    <a href="https://x.com.br" class="icone">
                        <img src="img/icones/twitter.svg" alt="logo do X">
                    </a>
                    <a href="https://tiktok.com.br" class="icone">
                        <img src="img/icones/tiktok.svg" alt="logo do tiktok">
                    </a>
                </div>
                <label>Copyright-2026</label>
            </footer>
        </div>
        <main> <!-- Elementos do painel principal -->
            <header class="header_">
                <div class="m"></div>
                <button id="login" onclick="<?= $acaoBotaoLogin ?>"><?= htmlspecialchars($textoBotaoLogin) ?></button>
            </header>
            <div id="div_filtro">
                <div class="filtros" >Entrada</div>
                <div class="filtros" >Prato Principal</div>
                <div class="filtros" >Sobremesa</div>
            </div>
            <section id="display_" >
                <?php if (count($receitas) > 0): ?>
                    <?php foreach ($receitas as $receita): ?>

                        <div class="recipe_card">

                            <div class="recipe_header">

                                <div class="user_info">
                                    <img class="user_avatar" 
                                        src="img/imagens_perfil/<?= !empty($receita['imagem']) ? htmlspecialchars($receita['imagem']) : 'avatar-padrao.png' ?>" 
                                        alt="Foto de <?= htmlspecialchars($receita['nome_usuario']) ?>">
                                    <span class="user_name"><?= htmlspecialchars($receita['nome_usuario']) ?></span>
                                </div>

                                <h2 class="recipe_title" ><?= htmlspecialchars($receita['titulo_receita']) ?></h2>
                                <span class="recipe_tipe"><?= htmlspecialchars($receita['tipo_receita']) ?></span>
                                <span class="recipe_date"><?= htmlspecialchars($receita['criado_em']) ?></span>
                            </div>

                            <div class="recipe_body">
                                <div class="meta_info">
                                    <div class="meta_item">
                                        <span class="meta_label">Custo total:</span>
                                        <span class="meta_value">R$ <?= htmlspecialchars($receita['custo_total']) ?></span>
                                    </div>
                                    <div class="meta_item">
                                        <span class="meta_label">Tempo de preparo:</span>
                                        <span class="meta_value"><?= htmlspecialchars($receita['tempo_preparo_minutos']) ?> min</span>
                                    </div>
                                    <div class="meta_item">
                                        <span class="meta_label">Rendimento:</span>
                                        <span class="meta_value"><?= htmlspecialchars($receita['rendimento_porcoes']) ?> porções</span>
                                    </div>
                                </div>
                            </div>

                            <div class="recipe_footer">
                                <div class="interacoes">
                                    <button class="btn_action curtir_bt <?= !empty($receita['usuario_ja_curtiu']) ? 'ativo' : '' ?>" 
                                            aria-label="Curtir receita" 
                                            onclick="curtirReceita(<?= $receita['id'] ?>, this)">
                                        <img class="icon" src="img/icones/coracao.svg" alt="">
                                        <span class="count"><?= htmlspecialchars($receita['curtidas']) ?></span>
                                    </button>

                                    <button class="btn_action comentar_bt" aria-label="Ver comentários" onclick="enviarAcao('index.php?page=acao&acao=comentar<?= htmlspecialchars($receita['id']) ?>')">
                                        <img class="icon" src="img/icones/comentario.svg" alt="">
                                        <span class="count"><?= htmlspecialchars($receita['comentarios']) ?></span>
                                    </button>
                                </div>

                                <span class="recipe_id">ID: #<?= htmlspecialchars($receita['id']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhuma receita encontrada.</p>
                <?php endif; ?>
            </section>
            <footer id="rodape_main">
                    <button class="navegation_bt" >Anterior</button>
                    <button class="navegation_bt" >1</button>
                    <button class="navegation_bt" >2</button>
                    <button class="navegation_bt" >3</button>
                    <button class="navegation_bt" >Próximo</button>
            </footer>
        </main>
    </div>

    <?php require_once '../src/views/partials/login.html'; ?>
</body>
</html>



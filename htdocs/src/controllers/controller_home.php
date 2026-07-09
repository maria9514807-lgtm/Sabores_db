<?php // Dispatcher (Despachante)
    class HomeController {
        public function gerenciarAcao($codigoAcao) { // Esse é o método único que todos os botões vão chamar

            $usuarioLogado = isset($_SESSION['usuario_id']);

            if ($codigoAcao === 'exibir_home' || $codigoAcao === null) { // Se for para exibir a home ou se não houver ação, entra direto (sem exigir login)
                $this->acaoExibirHome($usuarioLogado);
                return;
            }

            // BARREIRA DE SEGURANÇA: Qualquer outra ação a partir daqui EXIGE login obrigatório.
            if (!$usuarioLogado) { // Se não estiver logado, responde estritamente com JSON 401 para o JavaScript tratar na tela.
                http_response_code(401); // Define o status como Não Autorizado
                header('Content-Type: application/json');
                echo json_encode([
                    'sucesso' => false, 
                    'erro' => 'Sessao_expirada',
                    'mensagem' => 'Você precisa estar logado para executar essa ação.'
                ]);
                exit; // Interrompe totalmente a execução
            }   

            // O Despachante (Dispatcher)
            switch ($codigoAcao) {
                case 'add_receita':
                    $this->acaoAdicionarReceita();
                    break;
                    
                case 'curtir':
                    $this->acaoCurtir();
                    break;
                    
                case 'comentar':
                    $this->acaoComentar();
                    break;
                    
                case 'exibir_home':
                default:
                    $this->acaoExibirHome($usuarioLogado); // Caso venha uma ação inválida/estranha por engano, joga para a home por segurança
                    break;
            }
        }

        // MÉTODOS PRIVADOS (A lógica de cada botão)
        private function acaoExibirHome($usuarioLogado) {
            // Instancia e roda a lógica do Feed.
            $controller = new controladoraFeed();
            $dadosFeed = $controller->exibirFeed();
            $porcoes = $controller->exibirPorcoes();

            // Extrai as variáveis para que fiquem limpas dentro da view 
            // (cria $receitas, $paginaAtual, $totalPaginas, $inicio, $fim)
            extract($dadosFeed);
            extract($porcoes);

            if ($usuarioLogado) {
                $textoBotaoLogin = 'Logout';
                $acaoBotaoLogin  = "window.location.href='index.php?page=logout'";
            } else {
                $textoBotaoLogin = 'Login';
                $acaoBotaoLogin  = "document.getElementById('viewLogin').showModal()";
            }
            // =========================================================================

            require_once '../src/views/home.php';
        }

        private function acaoAdicionarReceita() {
            require_once '../src/views/addRecipe.php';
        }

        private function acaoCurtir() {
            $idReceita = $_POST['recipe_id'] ?? null;
            $idUsuario = $_SESSION['usuario_id'] ?? null;

            header('Content-Type: application/json');

            if (!$idReceita || !$idUsuario) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Parâmetros inválidos ou ausentes.']);
                return;
            }

            // Chama o método estático direto do seu Model (que já usa a função getConnection())
            $resultado = recipe::toggleCurtida($idUsuario, $idReceita);
            
            // Se houver falha interna no banco de dados, define o código HTTP 500
            if (!$resultado['sucesso']) {
                http_response_code(500);
            }

            echo json_encode($resultado);
        }

        private function acaoComentar() {
            // Lógica do banco...
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true, 'mensagem' => 'Comentado!']);
        }
    }
    
    require_once '../src/models/model_r.php';

    class controladoraFeed{
        public function exibirFeed(){
            $limitepagina = 4;

            // Captura a página atual da URL (?page=X)
            $paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($paginaAtual < 1) $paginaAtual = 1;

            // Cálculo do OFFSET para o banco de dados
            $offset = ($paginaAtual - 1) * $limitepagina;

            // Busca os dados usando o Model
            $totalReceitas = recipe::getTotal_nf();
            $totalPaginas = ceil($totalReceitas / $limitepagina); // Calcula o número total de páginas cecessárias
            $receitas = recipe::listarReceitas_nf($limitepagina, $offset);

            // Lógica para renderizar exatamente 3 números no rodapé
            $inicioItem = max(1, $paginaAtual - 1);
            $fimItem = min($totalPaginas, $inicioItem + 2);

            if($fimItem - $inicioItem < 2 && $inicioItem > 1){
                $inicioItem = max(1, $fimItem - 2);
            }

            // Retorna um array com tudo que a VIEW vai precisar para desenhar a tela
            return[
                'receitas'      => $receitas,
                'paginaAtual'   => $paginaAtual,
                'totalPaginas'  => $totalPaginas,
                'inicio'        => $inicioItem,
                'fim'           => $fimItem,
                'totalReceitas' => $totalReceitas
            ];
        }

        public function exibirPorcoes(){
            $totalPorcoes = recipe::getTotalPorcoes_nf();
            
            // Retorna um array com tudo que a VIEW vai precisar para desenhar a tela
            return[
                'totalPorcoes' => $totalPorcoes
            ];
        }
    }
?>
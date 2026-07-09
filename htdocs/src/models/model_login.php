<?php
    require_once '../config/database.php';
    
    class userAccess{
        public function login() {
            // 1. Captura o corpo da requisição JSON enviado pelo JavaScript
            $dados_recebidos = file_get_contents('php://input');
            $input = json_decode($dados_recebidos, true) ?? [];

            // Agora pegamos os dados de dentro do array do JSON decodificado
            $nome = $input['nome'] ?? '';
            $senha = $input['senha'] ?? '';

            // Define que a resposta do PHP sempre será um JSON
            header('Content-Type: application/json');

            // Validação de campos vazios
            if (empty($nome) || empty($senha)) {
                http_response_code(400); // Bad Request (Requisição inválida)
                echo json_encode(['erro' => 'Preencha todos os campos!']);
                exit();
            }

            $db = getConnection();

            // Busca o usuário pelo nome
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE nome_usuario = :nome");
            $stmt->execute([':nome' => $nome]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && $senha === $usuario['senha']) {
                // Garante que a sessão está ativa antes de salvar os dados
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                if (ob_get_length()){
                    ob_clean(); // LIMPA qualquer HTML ou espaço invisível gerado antes deste ponto
                }
                header('Content-Type: application/json; charset=utf-8'); // Define explicitamente que a resposta é um JSON puro
                http_response_code(200); // Retorna Status 200 (OK)
                echo json_encode([
                    'sucesso' => true,
                    'url' => 'index.php?page=home' 
                ]);
                exit();
                
            } else {
                if (ob_get_length()){
                    ob_clean(); // LIMPA também no bloco de erro para garantir JSON limpo aqui
                }

                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401); // Define o status 401 (Não autorizado) para o JS capturar
                echo json_encode(['erro' => 'Usuário ou senha inválidos!']);
                exit();
            }
        }
    }
?>
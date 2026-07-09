<?php
    function getConnection(){
        // Configurações do banco de dados
        $host = 'localhost';
        $port = '3306';
        $dbname = 'saboresdb'; // Substitua pelo nome do banco que você criou no phpMyAdmin
        $username = 'root';
        $password = ''; // No XAMPP, a senha padrão é vazia

        try {
            // Cria a conexão usando PDO
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
            
            // Configura o PDO para lançar exceções em caso de erro
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $conn;

        }catch(PDOException $exception){
            // Se der erro ele para a execução e mostra o erro
            die("Erro de conexão: " . $exception->getMessage());
        }
    }
?>
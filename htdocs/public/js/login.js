const campoNome = document.getElementById('nome');
const campoSenha = document.getElementById('senha');
const msg_output = document.getElementById('mensagem');

function fazer_login(event){
    event.preventDefault(); // IMPEDE a página de recarregar e cancelar o fetch

    let formularioValido = true; // Variável de controle para saber se o formulário está válido

    if(campoNome.value.trim() === "") { // trim remove os espaços em branco das extremidades
        campoNome.classList.add('erro_borda'); // Adiciona a borda vermelha
        formularioValido = false;
    } else {
        campoNome.classList.remove('erro_borda'); // Remove se estiver preenchido
    }

    if (campoSenha.value.trim() === "") {
        campoSenha.classList.add('erro_borda'); // Adiciona a borda vermelha
        formularioValido = false;
    } else {
        campoSenha.classList.remove('erro_borda'); // Remove se estiver preenchido
    }

    if (!formularioValido) { // Exibe a mensagem na div baseado na validação
        msg_output.textContent = "Nome do usuário ou senha obrigatório!";
        msg_output.className = "msg_erro"; // Aplica cor vermelha no texto
        console.error("Nome do usuário ou senha obrigatório!");
    } else {
        logar_db(campoNome.value.trim(), campoSenha.value.trim());
    }
}

function logar_db(nome, senha) {
    fetch("index.php?page=logar", {// Rota para acessar a ação de logar
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ nome: nome, senha: senha })// Enviando apenas nome e senha recebidos por parâmetro
    })
    .then(response => {
        if (response.ok) { // Se deu certo (200), aí sim lemos o JSON com a URL de redirecionamento
            return response.json().then(dados => {
                console.log("Login realizado com sucesso!");
                // AJUSTE AQUI: Se tiver URL redireciona, se não tiver recarrega a página atual
                if (dados.url) {
                    window.location.href = dados.url; 
                } else {
                    window.location.reload(); 
                }
            });
        }
        else if (response.status === 401) {//Se o servidor respondeu 401 (Senha errada)
            campoNome.classList.add('erro_borda');
            campoSenha.classList.add('erro_borda');
            msg_output.textContent = "Nome de usuário ou senha incorreta!";
            msg_output.className = "msg_erro";
        }else { // Se o servidor respondeu 400 (Campos vazios) ou outro erro
            msg_output.textContent = "Erro na validação dos dados.";
            msg_output.className = "msg_erro";
            console.log("Erro no servidor. Código: " + response.status);
        }
    })
    .catch(error => {
        console.error('Erro de rede ou falha catastrófica: ', error);
    });
}

document.addEventListener("DOMContentLoaded", () => { // Fecha o modal se clicar fora dele.
    const modalLogin = document.getElementById('viewLogin');

    modalLogin.addEventListener('click', (event) => {
        // Pega as coordenadas exatas da "caixa" do modal
        const r = modalLogin.getBoundingClientRect();
        
        // Verifica se o clique ocorreu DO LADO DE FORA das 4 bordas do modal
        const clicouFora = (
            event.clientX < r.left ||
            event.clientX > r.right ||
            event.clientY < r.top ||
            event.clientY > r.bottom
        );

        // Só fecha se o clique foi realmente na área do backdrop (fora do quadrado)
        if (clicouFora) {
            modalLogin.close();
        }
    });
});
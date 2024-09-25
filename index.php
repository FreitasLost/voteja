<?php
// Definir o cabeçalho para o formato JSON se a requisição for AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    // Recebe os dados JSON
    $data = json_decode(file_get_contents('php://input'), true);

    // Verifica se os dados necessários estão presentes
    if (isset($data['candidato']) && isset($data['partido']) && isset($data['eleitor_nome']) && isset($data['eleitor_id'])) {
        // Formata os dados para serem salvos
        $voto = sprintf(
            "Candidato: %s, Partido: %s, Eleitor: %s, ID: %s\n",
            $data['candidato'],
            $data['partido'],
            $data['eleitor_nome'],
            $data['eleitor_id']
        );

        // Salva o voto no arquivo votos.txt
        file_put_contents('votos.txt', $voto, FILE_APPEND);

        // Retorna uma resposta de sucesso
        echo json_encode(['success' => true]);
        exit; // Para evitar a execução do código HTML abaixo
    } else {
        // Retorna uma resposta de erro
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit; // Para evitar a execução do código HTML abaixo
    }
}

// Função para ler os votos do arquivo e contar quantos votos cada candidato recebeu
function getVoteCounts() {
    $voteCounts = [
        "Fabiano" => 0,
        "Thuani Veterinaria" => 0,
        "Toyota" => 0,
    ];

    $lines = file('votos.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (preg_match('/Candidato: (.*?), Partido: (.*?), Eleitor: (.*?), ID: (.*)/', $line, $matches)) {
            $candidate = trim($matches[1]);
            if (array_key_exists($candidate, $voteCounts)) {
                $voteCounts[$candidate]++;
            }
        }
    }

    return $voteCounts;
}

// Obtém a contagem de votos ao carregar a página
$voteCounts = getVoteCounts();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eleições Democráticas - Votação com Validação</title>
    <style>
        /* Seu CSS aqui */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            color: #333;
        }
        header {
            background-color: #1a5f7a;
            color: white;
            text-align: center;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1, h2 {
            margin-bottom: 0.5rem;
        }
        .voting-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .candidate {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .candidate:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .candidate img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid #1a5f7a;
        }
        button, input[type="submit"] {
            background-color: #1a5f7a;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: bold;
            margin-top: 1rem;
        }
        button:hover, input[type="submit"]:hover {
            background-color: #2c7da0;
        }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        .results {
            margin-top: 3rem;
        }
        .progress-bar {
            height: 24px;
            background-color: #e6e6e6;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 0.75rem;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.2);
        }
        .progress {
            height: 100%;
            background-color: #1a5f7a;
            transition: width 0.5s ease-in-out;
            display: flex;
            align-items: center;
            padding-left: 10px;
            color: white;
            font-weight: bold;
        }
        #message {
            text-align: center;
            margin-top: 1.5rem;
            font-weight: bold;
            color: #1a5f7a;
            padding: 1rem;
            background-color: #e6f3f8;
            border-radius: 8px;
        }
        .voter-form {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .voter-form input[type="text"] {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .hidden {
            display: none;
        }
        /* Restante do CSS... */
    </style>
</head>
<body>
    <header>
        <h1>Eleições Democráticas</h1>
        <p>Exerça seu direito de voto - Uma voz, um voto!</p>
    </header>
    <main>
        <h2>Eleição Para Prefeito de Novo Horizonte</h2>
        <p>Escolha o candidato que melhor representa seus ideais e visão para o futuro da nossa cidade.</p>
        
        <div class="voter-form" id="voterForm">
            <h3>Validação do Eleitor</h3>
            <form onsubmit="return validateVoter(event)">
                <input type="text" id="voterName" placeholder="Digite seu nome completo" required>
                <input type="text" id="voterID" placeholder="Digite seu número de telefone" required>
                <input type="submit" value="Validar e Prosseguir">
            </form>
        </div>

        <div class="voting-container hidden" id="votingContainer">
            <!-- Candidatos serão inseridos aqui via JavaScript -->
        </div>
        <div id="message"></div>
        <div class="results hidden" id="results">
            <h3>Resultados Parciais:</h3>
            <div id="resultsBars"></div>
        </div>
    </main>

    <script>
        const candidates = [
            {name: "Fabiano", party: "PL", image: "fabiano.jpeg"},
            {name: "Thuani Veterinaria", party: "PT", image: "thuani.jpeg"},
            {name: "Toyota", party: "Republicanos", image: "toyota.jpeg"}
        ];
        let votes = new Array(candidates.length).fill(0);
        let hasVoted = false;
        let validatedVoter = null;

        // Preenche os votos com os dados obtidos do PHP
        const initialVotes = <?php echo json_encode(array_values($voteCounts)); ?>;
        votes = initialVotes;

        function createCandidates() {
            const container = document.getElementById('votingContainer');
            candidates.forEach((candidate, index) => {
                const candidateElement = document.createElement('div');
                candidateElement.className = 'candidate';
                candidateElement.innerHTML = `
                    <img src="${candidate.image}" alt="${candidate.name}">
                    <h3>${candidate.name}</h3>
                    <p>${candidate.party}</p>
                    <button onclick="vote(${index})">Votar</button>
                `;
                container.appendChild(candidateElement);
            });
        }

        function validateVoter(event) {
            event.preventDefault();
            const voterName = document.getElementById('voterName').value;
            const voterID = document.getElementById('voterID').value;
            
            if (voterName.length > 5 && voterID.length > 8) {
                validatedVoter = { name: voterName, id: voterID };
                document.getElementById('voterForm').classList.add('hidden');
                document.getElementById('votingContainer').classList.remove('hidden');
                document.getElementById('results').classList.remove('hidden');
                showMessage(`Bem-vindo, ${voterName}! Você pode agora votar.`);
                return true;
            } else {
                showMessage("Por favor, insira um nome válido (mais de 5 caracteres) e um número de telefone válido.");
                return false;
            }
        }

        function vote(index) {
            if (!hasVoted && validatedVoter) {
                votes[index]++;
                hasVoted = true;
                updateResults();
                disableVoting();
                showMessage(`Obrigado por votar em ${candidates[index].name}, ${validatedVoter.name}! Seu voto foi registrado.`);

                // Envia o voto para o servidor
                fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        candidato: candidates[index].name,
                        partido: candidates[index].party,
                        eleitor_nome: validatedVoter.name,
                        eleitor_id: validatedVoter.id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage("Voto registrado com sucesso!");
                    } else {
                        showMessage("Erro ao registrar o voto.");
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showMessage("Erro ao comunicar com o servidor.");
                });
            }
        }

        function disableVoting() {
            const buttons = document.querySelectorAll('.candidate button');
            buttons.forEach(button => {
                button.disabled = true;
            });
        }

        function showMessage(message) {
            const messageElement = document.getElementById('message');
            messageElement.textContent = message;
        }

        function updateResults() {
            const resultsBars = document.getElementById('resultsBars');
            resultsBars.innerHTML = '';
            votes.forEach((voteCount, index) => {
                const totalVotes = votes.reduce((acc, vote) => acc + vote, 0);
                const percentage = ((voteCount / totalVotes) * 100).toFixed(1);
                resultsBars.innerHTML += `
                    <div>
                        <h4>${candidates[index].name} (${candidates[index].party}): ${voteCount} votos (${percentage}%)</h4>
                        <div class="progress-bar">
                            <div class="progress" style="width: ${percentage}%;">${percentage}%</div>
                        </div>
                    </div>
                `;
            });
        }

        // Inicia a página com os candidatos
        createCandidates();
        updateResults(); // Atualiza a exibição dos resultados iniciais
    </script>
</body>
</html>

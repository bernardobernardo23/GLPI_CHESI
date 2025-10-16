<?php
session_start();
require_once '../db_connection.php';

// Verifica login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_setor = $_SESSION['usuario_setor'] ?? '';
$usuario_admin = ($usuario_setor === 'TI');

// ------------------------------------
// 1. FILTRO PADRÃO E RECEBIMENTO DE DADOS
// ------------------------------------
// MUDANÇA AQUI: Define 'Pendentes' como o agrupamento de 'Aberto' e 'Em andamento' por padrão.
$status_filtro = $_GET['status'] ?? 'Pendentes'; // Valor padrão: 'Pendentes'
$nome_filtro = $_GET['nome_autor'] ?? ''; 

$where = "";
$params = [];

// Filtro por STATUS
if ($status_filtro === 'todos') {
    // Não adiciona cláusula WHERE para status
} elseif ($status_filtro === 'Pendentes') {
    // Se o padrão é 'Pendentes', filtramos por dois status
    $where .= " AND c.status IN ('Aberto', 'Em andamento')";
} else {
    // Para 'Aberto', 'Em andamento' (se vier específico da URL) ou 'Fechado'
    $where .= " AND c.status = ?";
    $params[] = $status_filtro; // Adiciona o status específico como parâmetro
}

// Filtro por NOME DO AUTOR (Apenas para administradores verem todos)
if ($nome_filtro !== '' && $usuario_admin) {
    // Busca nomes que contenham a string de pesquisa
    $where .= " AND u.nome LIKE ?";
    $params[] = '%' . $nome_filtro . '%';
}

// ------------------------------------
// 2. MONTAGEM DA CONSULTA SQL
// ------------------------------------
if ($usuario_admin) {
    // Admin vê todos os chamados (sujeito aos filtros)
    $sql = "
        SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor
        FROM chamados c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE 1=1 $where
        ORDER BY c.dt_abertura DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
} else {
    // Usuário comum vê apenas seus próprios chamados
    $sql = "
        SELECT c.*, u.nome AS autor_nome, u.setor AS autor_setor
        FROM chamados c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.autor_id = ? $where
        ORDER BY c.dt_abertura DESC
    ";
    // O ID do usuário deve ser o primeiro parâmetro, antes dos filtros
    $params = array_merge([$usuario_id], $params);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

$chamados = $stmt->fetchAll();

// Função para retornar a classe de cor do status (mantida a original)
function getStatusClass($status) {
    switch ($status) {
        case 'Aberto':
            return 'bg-red-500 text-white';
        case 'Em andamento':
            return 'bg-yellow-500 text-gray-900';
        case 'Fechado':
            return 'bg-green-600 text-white';
        default:
            return 'bg-gray-400 text-white';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Lista de Chamados</title>
</head>
<body class="bg-gray-100 min-h-screen flex">

<?php include '../areaLateral.php'; ?>

<main class="flex-1 ml-64 p-8">
    <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-xl">
        <h1 class="text-3xl font-extrabold text-gray-800 mb-6">Meus Chamados</h1>

        <form method="get" class="mb-6 bg-gray-50 p-4 rounded-lg flex flex-wrap gap-6 items-end border border-gray-200">
            
            <div>
                <label for="status-filtro" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status-filtro" name="status" class="border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500 w-48">
                    <option value="todos" <?= $status_filtro==='todos'?'selected':'' ?>>Todos</option>
                    <option value="Pendentes" <?= $status_filtro==='Pendentes'?'selected':'' ?>>Pendentes</option>
                    <option value="Aberto" <?= $status_filtro==='Aberto'?'selected':'' ?>>Aberto</option>
                    <option value="Em andamento" <?= $status_filtro==='Em andamento'?'selected':'' ?>>Em andamento</option>
                    <option value="Fechado" <?= $status_filtro==='Fechado'?'selected':'' ?>>Fechados</option>
                </select>
            </div>

            <?php if ($usuario_admin): ?>
            <div>
                <label for="nome-autor" class="block text-sm font-medium text-gray-700 mb-1">Autor do Chamado</label>
                <input type="text" id="nome-autor" name="nome_autor" value="<?= htmlspecialchars($nome_filtro) ?>" 
                        placeholder="Pesquisar por nome..." class="border border-gray-300 p-2 rounded-md focus:ring-blue-500 focus:border-blue-500 w-64">
            </div>
            <?php endif; ?>

            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                Aplicar Filtros
            </button>
            <?php if ($status_filtro !== 'Pendentes' || $nome_filtro !== ''): ?>
            <a href="listarChamados.php" class="text-gray-500 hover:text-gray-700 text-sm py-2">Limpar Filtros</a>
            <?php endif; ?>
        </form>
        

        <?php if (count($chamados) > 0): ?>
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Tipo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Título</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r"><?= $usuario_admin ? 'Autor' : 'Setor' ?></th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r">Abertura</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($chamados as $c): ?>
                    <tr class="hover:bg-blue-50 transition duration-100">
                        <td class="px-4 py-3 whitespace-nowrap text-center text-gray-700 font-medium border-r"><?= $c['id'] ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600 border-r"><?= ucfirst($c['tipo']) ?></td>
                        <td class="px-4 py-3 text-gray-800 font-medium border-r"><?= htmlspecialchars($c['titulo']) ?></td>
                        
                        <td class="px-4 py-3 whitespace-nowrap border-r">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold <?= getStatusClass($c['status']) ?>">
                                <?= $c['status'] ?>
                            </span>
                        </td>
                        
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600 border-r">
                            <?= $usuario_admin ? htmlspecialchars($c['autor_nome']) . " (" . htmlspecialchars($c['autor_setor']) . ")" : htmlspecialchars($c['autor_setor']) ?>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 border-r"><?= date('d/m/Y H:i', strtotime($c['dt_abertura'])) ?></td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <a href="detalhesChamado.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold transition">Ver Detalhes</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-lg text-gray-500 p-4 border border-gray-300 rounded-lg bg-gray-50">Nenhum chamado encontrado com os filtros aplicados.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
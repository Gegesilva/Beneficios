<?php
header('Content-type: text/html; charset=ISO-8895-1');
include "../Config/config.php";
include "../Config/database.php";
include "../app/models/models.php";
ini_set('max_input_vars', 3000);
error_reporting(0); // Desativa a exibição de todos os tipos de erros
ini_set('display_errors', '0'); // Garante que erros não sejam exibidos no navegador

$DataIni = $_POST['DataIni'];
$DataFim = $_POST['DataFim'];

if (!isset($DataIni) || !isset($DataFim)) {
  $DataIni = date('2025-01-01');
  $DataFim = date('Y-m-t');
} else {
  if (empty($DataIni)) {
    $DataIni = date('2025-01-01');
  }
  if (empty($DataFim)) {
    $DataFim = date('Y-m-t');
  }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../public/CSS/index.css">
  <title>DATABIT</title>
</head>

<body>

  <div class="header">
    <div class="logo">
      <img src="img/logo.jpg" alt="logo">
    </div>
    <div class="header-topo">
      <div class="titulo">Benefícios - Data base</div>
    </div>


    <div class="filtros">
      <form method="POST">
        <!-- <label for="year">Data: </label> -->
        <input type="date" class="filtro" name="DataIni" onchange="this.form.submit()"
          value="<?= htmlspecialchars($DataIni) ?>">

        <input type="date" class="filtro" name="DataFim" onchange="this.form.submit()"
          value="<?= htmlspecialchars($DataFim) ?>">

        <!-- <label for="filtroBeneficio">Beneficios: </label> -->
        <select id="filtroBeneficio" class="filtroCliente" onchange="filtrarPorBeneficio()">
          <option value="">-- Beneficios --</option>
        </select>
        <!-- <label for="filtroCliente">Cliente: </label> -->
        <select id="filtroCliente" class="filtroCliente" onchange="filtrarPorCliente()">
          <option value="">-- Clientes --</option>
        </select>
      </form>
    </div>

  </div>

  </div>

  <div class="month-grid">
    <div class="month-card">
      <table>
        <thead>
          <tr>
            <th class="titulo-col-tab" onclick="ordenarTabela(0)">Clente <i class="fa fa-sort" aria-hidden="true"></i>
            </th>
            <th class="titulo-col-tab" onclick="ordenarTabela(1)">Grupo <i class="fa fa-sort" aria-hidden="true"></i>
            </th>
            <th class="titulo-col-tab" onclick="ordenarTabela(2)">Tipo Benefício <i class="fa fa-sort"
                aria-hidden="true"></i>
            </th>
            <th class="titulo-col-tab" onclick="ordenarTabela(3)">Valor Inicial <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(4)">Valor concedido <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(5)">Valor Utilizado <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(6)">Valor Expirado <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(7)">Valor Final <i class="fa fa-sort"
                aria-hidden="true"></i>
            </th>
            <th>
              <button class="btn-xls-detal" onclick="exportarExcel()"></button>
            </th>
          </tr>
        </thead>
        <?php
        $sql = "SELECT 
                    v.CODCLI,
                    MAX(b.CLIENTE) AS CLIENTE,
                    MAX(b.GRUPO_ECONOMICO) AS GRUPO_ECONOMICO,
                    v.TIPO_BENEFICIO,
                    MAX(g.TB01107_CODIGO) AS COD_GRUPO,

                    ISNULL(v.TotValorFinalAnt, 0) AS VALOR_INICIAL,

                    ISNULL(SUM(a.VALOR_CONCEDIDO), 0) AS VALOR_CONCEDIDO,
                    ISNULL(SUM(a.VALOR_UTILIZADO), 0) AS VALOR_UTILIZADO,
                    ISNULL(SUM(a.VALOR_EXPIRADO), 0) AS VALOR_EXPIRADO,

                    ISNULL(v.TotValorFinalAnt, 0)
                      + ISNULL(SUM(a.VALOR_CONCEDIDO), 0)
                      - ISNULL(SUM(a.VALOR_UTILIZADO), 0)
                      - ISNULL(SUM(a.VALOR_EXPIRADO), 0) AS VALOR_FINAL_CALCULADO

                FROM (
                    SELECT 
                        CODCLI,
                        TIPO_BENEFICIO,
                        SUM(VALOR_FINAL) AS TotValorFinalAnt
                    FROM Beneficios
                    WHERE DATA < '$DataIni' -- $DataIni
                    GROUP BY CODCLI, TIPO_BENEFICIO
                ) v
                -- LEFT JOIN com as movimentações no período (pode não existir)
                LEFT JOIN Beneficios a 
                  ON a.CODCLI = v.CODCLI AND a.TIPO_BENEFICIO = v.TIPO_BENEFICIO
                  AND a.DATA BETWEEN '$DataIni' AND '$DataFim' -- $DataIni AND $DataFim

                -- Dados auxiliares
                LEFT JOIN Beneficios b ON b.CODCLI = v.CODCLI AND b.TIPO_BENEFICIO = v.TIPO_BENEFICIO
                LEFT JOIN TB01107 g ON g.TB01107_NOME = b.GRUPO_ECONOMICO

                --WHERE v.CODCLI = '00001209' -- Se quiser filtrar

                GROUP BY 
                    v.CODCLI,
                    v.TIPO_BENEFICIO,
                    v.TotValorFinalAnt

                ";

        $stmt = sqlsrv_prepare($conn, $sql, []);
        sqlsrv_execute($stmt);
        ?>
        <tbody>
          <?php
          function formatarMoeda($valor)
          {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
          }

          $tabela = "";
          $totaisPorClienteTipo = [];

          while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $cliente = htmlspecialchars($row['CLIENTE']);
            $grupoEconomico = htmlspecialchars($row['GRUPO_ECONOMICO']);
            $tipoBeneficio = htmlspecialchars($row['TIPO_BENEFICIO']);
            $codCli = htmlspecialchars($row['CODCLI'], ENT_QUOTES);
            $codGrupo = htmlspecialchars($row['COD_GRUPO'], ENT_QUOTES);

            $valorInicial = (float) $row['VALOR_INICIAL'];
            $valorConcedido = (float) $row['VALOR_CONCEDIDO'];
            $valorUtilizado = (float) $row['VALOR_UTILIZADO'];
            $valorExpirado = (float) $row['VALOR_EXPIRADO'];
            $valorFinal = (float) $row['VALOR_FINAL_CALCULADO'];

            // Chave única por cliente + tipo de benefício
            $chave = $cliente . '|' . $tipoBeneficio;

            if (!isset($totaisPorClienteTipo[$chave])) {
              $totaisPorClienteTipo[$chave] = [
                'CLIENTE' => $cliente,
                'GRUPO_ECONOMICO' => $grupoEconomico,
                'TIPO_BENEFICIO' => $tipoBeneficio,
                'CODCLI' => $codCli,
                'COD_GRUPO' => $codGrupo,
                'VALOR_INICIAL' => 0,
                'VALOR_CONCEDIDO' => 0,
                'VALOR_UTILIZADO' => 0,
                'VALOR_EXPIRADO' => 0,
                'VALOR_FINAL' => 0
              ];
            }

            $totaisPorClienteTipo[$chave]['VALOR_INICIAL'] += $valorInicial;
            $totaisPorClienteTipo[$chave]['VALOR_CONCEDIDO'] += $valorConcedido;
            $totaisPorClienteTipo[$chave]['VALOR_UTILIZADO'] += $valorUtilizado;
            $totaisPorClienteTipo[$chave]['VALOR_EXPIRADO'] += $valorExpirado;
            $totaisPorClienteTipo[$chave]['VALOR_FINAL'] += $valorFinal;
          }

          foreach ($totaisPorClienteTipo as $row) {
            $cliente = $row['CLIENTE'];
            $grupoEconomico = $row['GRUPO_ECONOMICO'];
            $tipoBeneficio = $row['TIPO_BENEFICIO'];
            $codCli = $row['CODCLI'];
            $codGrupo = $row['COD_GRUPO'];

            $valorInicial = $row['VALOR_INICIAL'];
            $valorConcedido = $row['VALOR_CONCEDIDO'];
            $valorUtilizado = $row['VALOR_UTILIZADO'];
            $valorExpirado = $row['VALOR_EXPIRADO'];
            $valorfinal = $row['VALOR_FINAL'];

            $tabela .= "<tr class='linha-click2'>";
            $tabela .= "<td>{$cliente}</td>";
            $tabela .= "<td>{$grupoEconomico}</td>";
            $tabela .= "<td>{$tipoBeneficio}</td>";
            $tabela .= "<td>" . formatarMoeda($valorInicial) . "</td>";
            $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('{$codCli}', '{$DataIni}', '{$DataFim}', 'C')\">" . formatarMoeda($valorConcedido) . "</td>";
            $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('{$codGrupo}', '{$DataIni}', '{$DataFim}', 'U')\">" . formatarMoeda($valorUtilizado) . "</td>";
            $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('{$codCli}', '{$DataIni}', '{$DataFim}', 'E')\">" . formatarMoeda($valorExpirado) . "</td>";
            $tabela .= "<td>" . formatarMoeda($valorfinal) . "</td>";
            $tabela .= "<td></td>";
            $tabela .= "</tr>";
          }

          echo $tabela;

          ?>

        </tbody>
        <tfoot>
          <tr style="font-size: 0.75rem; font-weight: normal; color: #555;">
            <th colspan="3" style="text-align: right;">Totais:</th>
            <th id="totalValorInicial"></th>
            <th id="totalValorConcedido"></th>
            <th id="totalValorUtilizado"></th>
            <th id="totalValorExpirado"></th>
            <th id="totalValorFinal"></th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
  <script src="JS/script.js" charset="utf-8"></script>

  <form id="detalForm" method="post" action="../app/views/detal.php" style="display: none;">
    <input type="hidden" name="cliente" id="cliente">
    <input type="hidden" name="dataIni" id="dataIni">
    <input type="hidden" name="dataFim" id="dataFim">
    <input type="hidden" name="tipoValor" id="tipoValor">
  </form>

</body>

</html>
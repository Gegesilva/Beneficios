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

if (empty($DataIni) && empty($DataFim)) {
  $DataIni = date('Y-01-01');
  $DataFim = date('Y-m-t');
} else {
  if (empty($DataIni)) {
    $DataIni = date('Y-01-01');
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

    <div class="titulo">
      Benefícios - Data base
    </div>

    <div class="filtros">
      <form method="POST">
        <label for="year">Data: </label>
        <input type="date" class="filtro" min="<?= date('Y') . '-01-01' ?>" name="DataFim" onchange="this.form.submit()"
          value="<?= htmlspecialchars($DataFim) ?>">

        <label for="filtroCliente">Cliente: </label>
        <select id="filtroCliente" class="filtroCliente" onchange="filtrarPorCliente()">
          <option value="">-- Todos os clientes --</option>
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
            <th class="titulo-col-tab" onclick="ordenarTabela(2)">Valor Inicial <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(3)">Valor concedido <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(4)">Valor Utilizado <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(5)">Valor Expirado <i class="fa fa-sort"
                aria-hidden="true"></i></th>
            <th class="titulo-col-tab" onclick="ordenarTabela(6)">Valor Final <i class="fa fa-sort"
                aria-hidden="true"></i>
            </th>
            <th>
              <button class="btn-xls-detal" onclick="exportarExcel()"></button>
            </th>
          </tr>
        </thead>
        <?php
        $sql = "WITH Concedido AS (
                              SELECT 
                                  TB02278_CODCLI AS CODCLI,
                                  TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
                                  A.TB01008_NOME AS CLIENTE,
                                  ISNULL(TB02091_DATANOTA, TB02278_DATA) AS DATA,
                                  SUM(TB02278_VLRBENEF) AS VALOR_CONCEDIDO
                              FROM VW02310
                              LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278_CODCLI
                              LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                              LEFT JOIN TB02021 ON TB02021_CODIGO = TB02278_NUMVENDA
                              LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021_NTFISC
                              GROUP BY TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, ISNULL(TB02091_DATANOTA, TB02278_DATA)
                          ),
                          Utilizado AS (
                              SELECT 
                                  TB02278.TB02278_CODCLI AS CODCLI,
                                  TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
                                  A.TB01008_NOME AS CLIENTE,
                                  TB02091_DATA AS DATA,
                                  SUM(VLRDESCBENEF) AS VALOR_UTILIZADO
                              FROM VW02311
                              LEFT JOIN TB02278 ON TB02278_CODIGO = BENEFICIO
                              LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278.TB02278_CODCLI
                              LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                              LEFT JOIN TB01074 ON TB01074_CODIGO = TB02278_CLASSIFICACAO
                              LEFT JOIN TB02091 ON TB02091_NTFISC = NTFISC AND TB02091_CODEMP = CODEMP
                              GROUP BY TB02278.TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, TB02091_DATA
                          ),
                          Expirado AS (
                              SELECT 
                                  vw02310.TB02278_CODCLI AS CODCLI,
                                  TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
                                  A.TB01008_NOME AS CLIENTE,
                                  ISNULL(TB02091_DATANOTA, vw02310.TB02278_DATA) AS DATA,
                                  SUM(vw02310.TB02278_VLRREST) AS VALOR_EXPIRADO
                              FROM VW02310
                              LEFT JOIN TB02278 AS B ON B.TB02278_CODIGO = vw02310.TB02278_CODIGO
                              LEFT JOIN TB01008 AS A ON TB01008_CODIGO = vw02310.TB02278_CODCLI
                              LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                              LEFT JOIN TB02021 ON TB02021_CODIGO = vw02310.TB02278_NUMVENDA
                              LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021.TB02021_NTFISC
                              WHERE vw02310.TB02278_SITUACAO = 'I'
                              GROUP BY vw02310.TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, ISNULL(TB02091_DATANOTA, vw02310.TB02278_DATA)
                          )

                          SELECT 
                              COALESCE(c.CODCLI, u.CODCLI, e.CODCLI) AS CODCLI,
                              COALESCE(c.GRUPO_ECONOMICO, u.GRUPO_ECONOMICO, e.GRUPO_ECONOMICO) AS GRUPO_ECONOMICO,
                              COALESCE(c.CLIENTE, u.CLIENTE, e.CLIENTE) AS CLIENTE,
                              COALESCE(c.DATA, u.DATA, e.DATA) AS DATA,
                              0 AS VALOR_INICIAL, 
                              ISNULL(c.VALOR_CONCEDIDO, 0) AS VALOR_CONCEDIDO,
                              ISNULL(u.VALOR_UTILIZADO, 0) AS VALOR_UTILIZADO,
                              ISNULL(e.VALOR_EXPIRADO, 0) AS VALOR_EXPIRADO,
                              0 + ISNULL(c.VALOR_CONCEDIDO, 0) - ISNULL(u.VALOR_UTILIZADO, 0) - ISNULL(e.VALOR_EXPIRADO, 0) AS VALOR_FINAL
                          FROM Concedido c
                          FULL JOIN Utilizado u  ON c.CODCLI = u.CODCLI AND c.GRUPO_ECONOMICO = u.GRUPO_ECONOMICO AND c.DATA = u.DATA
                          FULL JOIN Expirado e ON COALESCE(c.CODCLI, u.CODCLI) = e.CODCLI 
                              AND COALESCE(c.GRUPO_ECONOMICO, u.GRUPO_ECONOMICO) = e.GRUPO_ECONOMICO 
                              AND COALESCE(c.DATA, u.DATA, e.DATA) = e.DATA

                          WHERE CAST(c.DATA AS DATE) BETWEEN '$DataIni' AND '$DataFim'

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

          while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $valorInicial = (float) $row['VALOR_INICIAL'];
            $valorConcedido = (float) $row['VALOR_CONCEDIDO'];
            $valorUtilizado = (float) $row['VALOR_UTILIZADO'];
            $valorExpirado = (float) $row['VALOR_EXPIRADO'];
            $valorfinal = (float) $row['VALOR_FINAL'];


            $tabela .= "<tr class='linha-click2'>";
            $tabela .= "<td>$row[CLIENTE]</td>";
            $tabela .= "<td>$row[GRUPO_ECONOMICO]</td>";
            $tabela .= "<td>" . formatarMoeda($valorInicial) . "</td>";
            $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('$row[CODCLI]', '$DataIni', '$DataFim', 'C')\">" . formatarMoeda($valorConcedido) . "</td>";
            $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('$row[CODCLI]', '$DataIni', '$DataFim', 'U')\">" . formatarMoeda($valorUtilizado) . "</td>";
            $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('$row[CODCLI]', '$DataIni', '$DataFim', 'E')\">" . formatarMoeda($valorExpirado) . "</td>";
            $tabela .= "<td>" . formatarMoeda($valorfinal) . "</td>";
            $tabela .= "<td></td>";
            $tabela .= "</tr>";
          }
          print ($tabela);
          ?>

        </tbody>
        <!-- <tfoot>
          <tr style="font-size: 0.75rem; font-weight: normal; color: #555;">
            <th colspan="8" style="text-align: right;">Totais:</th>
            <th></th>
            <th></th>
            <th></th>
          </tr>
        </tfoot> -->
      </table>
    </div>
  </div>
  <script src="JS/script.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <form id="detalForm" method="post" action="../app/views/detal.php" style="display: none;">
    <input type="hidden" name="cliente" id="cliente">
    <input type="hidden" name="dataIni" id="dataIni">
    <input type="hidden" name="dataFim" id="dataFim">
    <input type="hidden" name="tipoValor" id="tipoValor">
  </form>

</body>

</html>
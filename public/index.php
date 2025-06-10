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

if (!isset($DataFim)) {
  $DataIni = date('2021-01-01');
  $DataFim = date('Y-m-t');
} else {
  if (empty($DataIni)) {
    $DataIni = date('2021-01-01');
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
        <input type="date" class="filtro" min="<?= date('Y') . '-01-01' ?>" name="DataFim" onchange="this.form.submit()"
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
        $sql = "WITH Concedido AS (
                              SELECT 
                                  TB02278_CODCLI AS CODCLI,
                                  TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
                                  A.TB01008_NOME AS CLIENTE,
                                  CAST(TB02278_DATA AS DATE) AS DATA,
                                  SUM(TB02278_VLRBENEF) AS VALOR_CONCEDIDO,
								  TB01074_NOME Ben
                              FROM VW02310
                              LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278_CODCLI
                              LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                              LEFT JOIN TB02021 ON TB02021_CODIGO = TB02278_NUMVENDA
                              LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021_NTFISC AND TB02091_CODEMP = TB02021_CODEMP
                              GROUP BY TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, TB02278_DATA, TB01074_NOME
                          ),
                          Utilizado AS (
                              SELECT 
                                  TB02278.TB02278_CODCLI AS CODCLI,
                                  TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
                                  A.TB01008_NOME AS CLIENTE,
                                  CAST(ISNULL(TB02091_DATA, TB02278_DTCAD) AS DATE) AS DATA,
                                  SUM(VLRDESCBENEF) AS VALOR_UTILIZADO,
								  TB01074_NOME Ben
                              FROM VW02311
                              LEFT JOIN TB02278 ON TB02278_CODIGO = BENEFICIO
                              LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278.TB02278_CODCLI
                              LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                              LEFT JOIN TB01074 ON TB01074_CODIGO = TB02278_CLASSIFICACAO
                              LEFT JOIN TB02091 ON TB02091_NTFISC = NTFISC AND TB02091_CODEMP = CODEMP
                              GROUP BY TB02278.TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, TB02091_DATA, TB02278_DTCAD, TB01074_NOME
                          ),
                          Expirado AS (
                              SELECT 
                                  vw02310.TB02278_CODCLI AS CODCLI,
                                  TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
                                  A.TB01008_NOME AS CLIENTE,
                                  SUM(vw02310.TB02278_VLRREST) AS VALOR_EXPIRADO,
                                  CONVERT(date, '01/' + vw02310.TB02278_MES, 103) DATA,
                                  TB01074_NOME Ben
                              FROM VW02310
                              LEFT JOIN TB02278 AS B ON B.TB02278_CODIGO = vw02310.TB02278_CODIGO
                              LEFT JOIN TB01008 AS A ON TB01008_CODIGO = vw02310.TB02278_CODCLI
                              LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                              LEFT JOIN TB02021 ON TB02021_CODIGO = vw02310.TB02278_NUMVENDA
                              LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021.TB02021_NTFISC
                              WHERE vw02310.TB02278_SITUACAO = 'I'
                              GROUP BY vw02310.TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, vw02310.TB02278_MES, TB01074_NOME
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
                              0 + ISNULL(c.VALOR_CONCEDIDO, 0) - ISNULL(u.VALOR_UTILIZADO, 0) - ISNULL(e.VALOR_EXPIRADO, 0) AS VALOR_FINAL,
                              TB01107_CODIGO CODGRUPO,
							                COALESCE(c.Ben, u.Ben, e.Ben)  TIPO_BENEFICIO
                          FROM Concedido c
                          FULL JOIN Utilizado u  ON c.CODCLI = u.CODCLI AND c.GRUPO_ECONOMICO = u.GRUPO_ECONOMICO AND c.DATA = u.DATA
                          FULL JOIN Expirado e ON COALESCE(c.CODCLI, u.CODCLI) = e.CODCLI 
                              AND COALESCE(c.GRUPO_ECONOMICO, u.GRUPO_ECONOMICO) = e.GRUPO_ECONOMICO 
                              AND COALESCE(c.DATA, u.DATA, e.DATA) = e.DATA
                          LEFT JOIN TB01008 AS A ON TB01008_CODIGO = COALESCE(c.CODCLI, u.CODCLI, e.CODCLI)
                          LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO

                          WHERE COALESCE(c.DATA, e.DATA, u.DATA) BETWEEN '$DataIni' AND '$DataFim'
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
            $chave = $row['CLIENTE']/*  . '|' . $row['GRUPO_ECONOMICO'] */ ;

            if (!isset($agrupados[$chave])) {
              $agrupados[$chave] = [
                'CLIENTE' => $row['CLIENTE'],
                'GRUPO_ECONOMICO' => $row['GRUPO_ECONOMICO'],
                'TIPO_BENEFICIO' => $row['TIPO_BENEFICIO'],
                'COD_GRUPO' => $row['CODGRUPO'],
                'CODCLI' => $row['CODCLI'],
                'VALOR_INICIAL' => 0,
                'VALOR_CONCEDIDO' => 0,
                'VALOR_UTILIZADO' => 0,
                'VALOR_EXPIRADO' => 0,
                'VALOR_FINAL' => 0,
              ];
            }

            $agrupados[$chave]['VALOR_INICIAL'] += (float) $row['VALOR_INICIAL'];
            $agrupados[$chave]['VALOR_CONCEDIDO'] += (float) $row['VALOR_CONCEDIDO'];
            $agrupados[$chave]['VALOR_UTILIZADO'] += (float) $row['VALOR_UTILIZADO'];
            $agrupados[$chave]['VALOR_EXPIRADO'] += (float) $row['VALOR_EXPIRADO'];
            $agrupados[$chave]['VALOR_FINAL'] += (float) $row['VALOR_FINAL'];
            $agrupados[$chave]['CODGRUPO'] += (float) $row['COD_GRUPO'];
          }

          // Monta a tabela HTML
          $tabela = "";

          foreach ($agrupados as $row) {
            $valorInicial = $row['VALOR_INICIAL'];
            $valorConcedido = $row['VALOR_CONCEDIDO'];
            $valorUtilizado = $row['VALOR_UTILIZADO'];
            $valorExpirado = $row['VALOR_EXPIRADO'];
            $valorfinal = $row['VALOR_FINAL'];

            $totalValorInicial += $valorInicial;
            $totalValorConcedido += $valorConcedido;
            $totalValorUtlizado += $valorUtilizado;
            $totalValorExpirado += $valorExpirado;
            $totalValorFinal += $valorfinal;

            $cliente = htmlspecialchars($row['CLIENTE']);
            $grupoEconomico = htmlspecialchars($row['GRUPO_ECONOMICO']);
            $tipoBeneficio = htmlspecialchars($row['TIPO_BENEFICIO']);
            $codCli = htmlspecialchars($row['CODCLI'], ENT_QUOTES);
            $codGrupo = htmlspecialchars($row['COD_GRUPO'], ENT_QUOTES);

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
            <th><?= formatarMoeda($totalValorInicial) ?></th>
            <th><?= formatarMoeda($totalValorConcedido) ?></th>
            <th><?= formatarMoeda($totalValorUtlizado) ?></th>
            <th><?= formatarMoeda($totalValorExpirado) ?></th>
            <th><?= formatarMoeda($totalValorFinal) ?></th>
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
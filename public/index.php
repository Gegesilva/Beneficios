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
        $sql = "SELECT DISTINCT
                    a.CODCLI AS CODCLI_ORIGINAL,
                    a.GRUPO_ECONOMICO AS GRUPO_ECONOMICO,
                    a.CLIENTE,
                    a.TIPO_BENEFICIO,
                    g.TB01107_CODIGO CODGRUPO,
                      ISNULL(ini.VALOR_INI, 0) AS VALOR_INICIAL,
                      ISNULL(conc.VALOR_CONCEDIDO, 0) AS VALOR_CONCEDIDO,
                      ISNULL(util.VALOR_UTILIZADO, 0) AS VALOR_UTILIZADO,
                      ISNULL(expir.VALOR_EXPIRADO, 0) AS VALOR_EXPIRADO,
                    ISNULL(ini.VALOR_INI, 0) + ISNULL(conc.VALOR_CONCEDIDO, 0) - ISNULL(util.VALOR_UTILIZADO, 0) - ISNULL(expir.VALOR_EXPIRADO, 0) AS VALOR_FINAL_CALCULADO
                  FROM 
                      Beneficios a
                    LEFT JOIN TB01107 g ON g.TB01107_NOME = a.GRUPO_ECONOMICO

                  OUTER APPLY (
                      SELECT SUM(VALOR_FINAL) AS VALOR_INI
                      FROM Beneficios b
                      WHERE b.CODCLI = a.CODCLI 
                        AND b.TIPO_BENEFICIO = a.TIPO_BENEFICIO
                        AND b.DATA <= '$DataIni'
                  ) ini

                  OUTER APPLY (
                      SELECT SUM(VALOR_CONCEDIDO) AS VALOR_CONCEDIDO
                      FROM Beneficios b
                      WHERE b.CODCLI = a.CODCLI 
                        AND b.TIPO_BENEFICIO = a.TIPO_BENEFICIO
                        AND b.DATA BETWEEN '$DataIni' AND '$DataFim'
                  ) conc

                  OUTER APPLY (
                      SELECT SUM(VALOR_UTILIZADO) AS VALOR_UTILIZADO
                      FROM Beneficios b
                      WHERE b.CODCLI = a.CODCLI 
                        AND b.TIPO_BENEFICIO = a.TIPO_BENEFICIO
                        AND b.DATA BETWEEN '$DataIni' AND '$DataFim'
                  ) util

                  OUTER APPLY (
                      SELECT SUM(VALOR_EXPIRADO) AS VALOR_EXPIRADO
                      FROM Beneficios b
                      WHERE b.CODCLI = a.CODCLI 
                        AND b.TIPO_BENEFICIO = a.TIPO_BENEFICIO
                        AND b.DATA BETWEEN '$DataIni' AND '$DataFim'
                  ) expir

                    --WHERE a.CODCLI = '00000185'
                ";


$stmt = sqlsrv_prepare($conn, $sql);
sqlsrv_execute($stmt);

function formatarMoeda($valor)
{
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

$tabela = "";

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $valorInicial = $row['VALOR_INICIAL'];
    $valorConcedido = $row['VALOR_CONCEDIDO'];
    $valorUtilizado = $row['VALOR_UTILIZADO'];
    $valorExpirado = $row['VALOR_EXPIRADO'];
    $valorFinal = $row['VALOR_FINAL_CALCULADO'];

    $cliente = htmlspecialchars($row['CLIENTE']);
    $grupoEconomico = htmlspecialchars($row['GRUPO_ECONOMICO']);
    $tipoBeneficio = htmlspecialchars($row['TIPO_BENEFICIO']);
    $codCli = htmlspecialchars($row['CODCLI_ORIGINAL'], ENT_QUOTES);
    $codGrupo = htmlspecialchars($row['CODGRUPO'], ENT_QUOTES);

    $tabela .= "<tr class='linha-click2'>";
    $tabela .= "<td>{$cliente}</td>";
    $tabela .= "<td>{$grupoEconomico}</td>";
    $tabela .= "<td>{$tipoBeneficio}</td>";
    $tabela .= "<td>" . formatarMoeda($valorInicial) . "</td>";
    $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('{$codCli}', '{$DataIni}', '{$DataFim}', '{$tipoBeneficio}', 'C')\">" . formatarMoeda($valorConcedido) . "</td>";
    $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('{$codCli}', '{$DataIni}', '{$DataFim}', '{$tipoBeneficio}', 'U')\">" . formatarMoeda($valorUtilizado) . "</td>";
    $tabela .= "<td class='linha-click' style='cursor:pointer' onclick=\"enviarDetalhes('{$codCli}', '{$DataIni}', '{$DataFim}', '{$tipoBeneficio}', 'E')\">" . formatarMoeda($valorExpirado) . "</td>";
    $tabela .= "<td>" . formatarMoeda($valorFinal) . "</td>";
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
    <input type="hidden" name="beneficio" id="beneficio">
    <input type="hidden" name="tipoValor" id="tipoValor">
  </form>

</body>

</html>
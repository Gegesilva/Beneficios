<?php
header('Content-type: text/html; charset=ISO-8895-1');
include "../../Config/config.php";
include "../../Config/database.php";


$cliente = (string) $_POST["cliente"];
$dataIni = date('Y-m-d', strtotime($_POST["dataIni"]));
$dataFim = date('Y-m-d', strtotime($_POST["dataFim"]));
$tipoValor = (string) $_POST["tipoValor"];
$sql = "";

/* Verifica o tipo do valor e seleciona a query correta */
switch ($tipoValor) {
    case $tipoValor == 'C':
        $sql = "SELECT
                tb02278_codigo as CODIGO,
                TB02278_SITUACAO AS SITUACAO,
                TB02278_CINTERNO AS CODIGO_INTERNO,
                TB02278_NUMVENDA as VENDA_ORIGEM_BENEFICIO,
                TB02021_NTFISC NUM_NOTA,
                FORMAT(ISNULL (TB02091_DATANOTA, TB02278_DATA), 'dd/MM/yyyy')  AS DATA_NOTA,
                FORMAT(TB02278_DATA, 'dd/MM/yyyy') AS DATA_BENEFICIO,
                TB01074_NOME AS TIPO_BENEFICIO,
                TB02278_NOME AS HISTORICO,
                TB02278_CODCLI AS CODCLI,
                A.TB01008_NOME AS CLIENTE,
                TB02278_MES AS MES,
                FORMAT(TB02278_DTVALIDADE, 'dd/MM/yyy') AS VALIDADE,
                TB02278_VLRBENEF AS VALOR_BENEFICIO,
                TB02278_VLRUTILIZADO AS VALOR_UTILIZADO,
                TB02278_VLRREST AS VALOR_RESTANTE,
                TB02278_MARCANOME AS MARCA,
                TB01107_NOME AS GRUPO_ECONOMICO


                from VW02310

                LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278_CODCLI
                LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                LEFT JOIN TB02021 ON TB02021_CODIGO = TB02278_NUMVENDA
                LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021_NTFISC

                WHERE TB02278_CODCLI = ?
                AND CAST(ISNULL(TB02091_DATANOTA, TB02278_DATA) AS DATE) BETWEEN ? AND ?
                --AND TB01074_NOME in (@classificacao)
                ";
        break;
    case $tipoValor == 'U':
        $sql = "select
BENEFICIO AS CODBENEFICIO,
TB02278_CINTERNO AS CODIGO_INTERNO,
TB02278_SITUACAO AS SITUACAO,
TB02278_TIPOBENEF AS TIPO,
TB01074_NOME AS CLASSIFICACAO,
NUMORC AS ORCAMENTO,
VENDA AS PEDIDO,
NTFISC AS NOTAFISCAL,
TB02091_DATA AS DATA_NOTA,
VLRNOTA AS VALORNOTA,
CODEMP AS EMPRESA,
DATA AS DATAVINCULO,
NOMECLI AS CLIENTE_PEDIDO,
TB01008_NOME AS CLIENTE_BENEFICIO,
TB01107_NOME AS GRUPO,
TB02278_VLRBENEF AS VLR_BENEFICIO,
VLRDESCBENEF AS VALOR_UTILIZADO


from VW02311

LEFT JOIN TB02278 ON TB02278_CODIGO = BENEFICIO
LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278_CODCLI
LEFT JOIN TB01107 ON TB01107_CODIGO = TB01008_GRUPO
LEFT JOIN TB01074 ON TB01074_CODIGO = TB02278_CLASSIFICACAO
LEFT JOIN TB02091 ON TB02091_NTFISC = NTFISC AND TB02091_CODEMP = CODEMP

WHERE TB02091_DATA BETWEEN @DATAI AND @DATAF
and TB01074_CODIGO in (@classificacao)";
        break;
    case $tipoValor == 'E':
        $sql = "";
        break;
}

$nomesMeses = [
    1 => 'janeiro',
    2 => 'fevereiro',
    3 => 'março',
    4 => 'abril',
    5 => 'maio',
    6 => 'junho',
    7 => 'julho',
    8 => 'agosto',
    9 => 'setembro',
    10 => 'outubro',
    11 => 'novembro',
    12 => 'dezembro'
  ];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../public/CSS/detal.css">
    <title>TINSEI</title>
</head>

<body>
    <div class="month-grid">
        <!-- Exemplo de um mês (repita para os outros) -->
        <div class="month-card">
            <!-- <div class="month-title"> -  de - Periodo:   </div> -->
            <table>
                <thead>
                    <tr>
                        <th class="titulo-col-tab" onclick="ordenarTabela(0)">Codigo <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(1)">Situação <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(2)">Codigo interno <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(3)">Origem beneficio <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(4)">Nota <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(5)">Data Nota <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(6)">Data Beneficio <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(7)">Historico <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(8)">Cliente <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(9)">Mês <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(10)">Validade <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th>
                            <button class="btn-xls-detal" onclick="exportarExcel()"></button>
                            <button class="btn-voltar-detal" onclick="voltar()"></button>
                        </th>
                    </tr>
                </thead>
                <?php
                

                $stmt = sqlsrv_prepare($conn, $sql, [$cliente, $dataIni, $dataFim]);    
                sqlsrv_execute($stmt);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                ?>
                <tbody>
                    <?php
                    function formatarMoeda($valor)
                    {
                        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
                    }
                    $totalBeneficio = 0;
                    $totalRest = 0;

                    $tabela = "";

                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        $vlrBeneficio = (float) $row['VALOR_BENEFICIO'];
                        $vlrRest = (float) $row['VALOR_RESTANTE'];

                        $totalBeneficio += $vlrBeneficio;
                        $totalRest += $vlrRest; 

                        $tabela .= "<tr>";
                        $tabela .= "<td>$row[CODIGO]</td>";
                        $tabela .= "<td>$row[SITUACAO]</td>";
                        $tabela .= "<td>$row[CODIGO_INTERNO]</td>";
                        $tabela .= "<td>$row[NUM_NOTA]</td>";
                        $tabela .= "<td>$row[DATA_NOTA]</td>";
                        $tabela .= "<td>$row[DATA_BENEFICIO]</td>";
                        $tabela .= "<td>$row[TIPO_BENEFICIO]</td>";
                        $tabela .= "<td>$row[HISTORICO]</td>";
                        $tabela .= "<td>$row[CLIENTE]</td>";
                        $tabela .= "<td>$row[MES]</td>";
                        $tabela .= "<td>$row[VALIDADE]</td>";
                        $tabela .= "<td>" . formatarMoeda($vlrBeneficio) . "</td>";
                        $tabela .= "<td>" . formatarMoeda($vlrRest) . "</td>";
                        $tabela .= "<td>$row[MARCA]</td>";
                        $tabela .= "<td>$row[GRUPO_ECONOMICO]</td>";
                        $tabela .= "<td></td>";
                        $tabela .= "</tr>";
                    }
                    print ($tabela);
                    ?>

                </tbody>
                <tfoot>
                    <tr style="font-size: 0.75rem; font-weight: normal; color: #555;">
                        <th colspan="8" style="text-align: right;">Totais:</th>
                        <th><?= formatarMoeda($totalBeneficio) ?></th>
                        <th><?= formatarMoeda($totalRest) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script src="../../public/JS/script.js" charset="utf-8"></script>
</body>

</html>
<?php
header('Content-type: text/html; charset=ISO-8895-1');
include "../../Config/config.php";
include "../../Config/database.php";


$cliente = (string) $_POST["cliente"];
$dataIni = date('Y-m-d', strtotime($_POST["dataIni"]));
$dataFim = date('Y-m-d', strtotime($_POST["dataFim"]));
$beneficio = (string) $_POST["beneficio"];
$tipoValor = (string) $_POST["tipoValor"];



if (isset($DataFim)) {
    /* Se a data final não for informada, define o período como o ano atual */
    /* $DataIni = date('Y-m-d', strtotime('-1 year', strtotime($DataFim))) */
    $DataIni = date('Y-01-01');
    $DataFim = date('Y-m-d');
}
$sql = "";
// print_r($cliente);
/* Verifica o tipo do valor e seleciona a query correta */
switch ($tipoValor) {
    case $tipoValor == 'C':
        $sql = "SELECT 
                    tb02278_codigo as CODIGO,
                    TB02278_SITUACAO AS SITUACAO,
                    TB02278_CINTERNO AS CODIGO_INTERNO,
                    TB02278_NUMVENDA as VENDA_ORIGEM_BENEFICIO,
                    TB02021_NTFISC NUM_NOTA,
                    FORMAT(TB02278_DATA, 'dd/MM/yyyy') AS DATA_BENEFICIO,
                    TB01074_NOME AS TIPO_BENEFICIO,
                    TB02278_NOME AS HISTORICO,
                    A.TB01008_NOME AS CLIENTE,
                    TB02278_MES AS MES,
                    FORMAT(TB02278_DTVALIDADE, 'dd/MM/yyy') AS VALIDADE,
                    TB02278_VLRBENEF AS VALOR,
                    TB02278_MARCANOME AS MARCA,
                    TB01107_NOME AS GRUPO_ECONOMICO
                FROM VW02310
                LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278_CODCLI
                LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                LEFT JOIN TB02021 ON TB02021_CODIGO = TB02278_NUMVENDA
                WHERE TB02278_CODCLI = ?
                AND CAST(TB02278_DATA AS DATE) BETWEEN ? AND ?
                AND TB01074_NOME = ?
                ";
        break;
    case $tipoValor == 'U':
        $sql = "SELECT 
                    tb02278_codigo as CODIGO,
                    TB02278_SITUACAO AS SITUACAO,
                    TB02278_CINTERNO AS CODIGO_INTERNO,
                    TB02278_NUMVENDA as VENDA_ORIGEM_BENEFICIO,
                    TB02021_NTFISC NUM_NOTA,
                    FORMAT(TB02278_DATA, 'dd/MM/yyyy') AS DATA_BENEFICIO,
                    TB01074_NOME AS TIPO_BENEFICIO,
                    TB02278_CODCLI AS CODCLI,
                    A.TB01008_NOME AS CLIENTE,
                    TB02278_MES AS MES,
                    FORMAT(TB02278_DTVALIDADE, 'dd/MM/yyy') AS VALIDADE,
                    VLRDESCBENEF AS VALOR,
                    TB02278_MARCANOME AS MARCA,
                    TB01107_NOME AS GRUPO_ECONOMICO
                FROM VW02311
                LEFT JOIN TB02278 ON TB02278_CODIGO = BENEFICIO
                LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278.TB02278_CODCLI
                LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
                LEFT JOIN TB01074 ON TB01074_CODIGO = TB02278_CLASSIFICACAO
                LEFT JOIN TB02021 ON TB02021_CODIGO = VENDA  
                WHERE venda is not null AND TB02278.TB02278_CODCLI = ?
                AND CAST(TB02021_DATA AS DATE) BETWEEN ? AND ?";
        break;
    case $tipoValor == 'E':
        $sql = "SELECT 
                    TITULO,
                    TIPO,
                    TB01007_NOME EMPRESA,
                    FORMAT(DATA, 'dd/MM/yyyy') DATA,
                    FORMAT(DATA_VENCIMENTO, 'dd/MM/yyyy') DATA_VENCIMENTO,
                    NOTAFISCAL,
                    VALOR
                FROM SALDO_CLIENTES 
                LEFT JOIN TB01007 ON TB01007_CODIGO = empresa
                WHERE CODCLI = ?
                AND DATA BETWEEN ? AND ?
                $filtroEmp
                AND TIPO = 'ACRESCIMO'";
        break;
}

/* Pega nome de cliente */
$clienteNome = "SELECT TB01008_NOME FROM TB01008 WHERE TB01008_CODIGO = ?";
$stmtCliente = sqlsrv_prepare($conn, $clienteNome, [$cliente]);
if (sqlsrv_execute($stmtCliente) === false) {
    die(print_r(sqlsrv_errors(), true));
}
$rowCliente = sqlsrv_fetch_array($stmtCliente, SQLSRV_FETCH_ASSOC);
$clienteNome = isset($rowCliente['TB01008_NOME']) ? $rowCliente['TB01008_NOME'] : 'Cliente não encontrado';
if ($stmtCliente === false) {
    die(print_r(sqlsrv_errors(), true));
}

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
            <div class="month-title"> Cliente: <?= $clienteNome ?> </div>
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
                        <th class="titulo-col-tab" onclick="ordenarTabela(6)">Data Beneficio <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(7)">Tipo Beneficio <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <!-- <th class="titulo-col-tab" onclick="ordenarTabela(8)">Historico <i class="fa fa-sort"
                                aria-hidden="true"></i></th> -->
                        <th class="titulo-col-tab" onclick="ordenarTabela(9)">Cliente <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(13)">Grupo <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(10)">Mês <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(11)">Validade <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(12)">Marca <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(14)">Valor <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th>

                            <button class="btn-xls-detal" onclick="exportarExcel()"></button>
                            <button id="btn-voltar" class="btn-voltar-detal" onclick="voltar()"></button>
                        </th>
                    </tr>
                </thead>
                <?php


                $stmt = sqlsrv_prepare($conn, $sql, [$cliente, $dataIni, $dataFim, $beneficio]);
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
                    $totalVlr = 0;

                    $tabela = "";

                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        $vlr = (float) $row['VALOR'];

                        $totalVlr += $vlr;

                        $tabela .= "<tr>";
                        $tabela .= "<td>$row[CODIGO]</td>";
                        $tabela .= "<td>$row[SITUACAO]</td>";
                        $tabela .= "<td>$row[CODIGO_INTERNO]</td>";
                        $tabela .= "<td>$row[VENDA_ORIGEM_BENEFICIO]</td>";
                        $tabela .= "<td>$row[NUM_NOTA]</td>";
                        $tabela .= "<td>$row[DATA_BENEFICIO]</td>";
                        $tabela .= "<td>$row[TIPO_BENEFICIO]</td>";
                        //$tabela .= "<td>$row[HISTORICO]</td>";
                        $tabela .= "<td>$row[CLIENTE]</td>";
                        $tabela .= "<td>$row[GRUPO_ECONOMICO]</td>";
                        $tabela .= "<td>$row[MES]</td>";
                        $tabela .= "<td>$row[VALIDADE]</td>";
                        $tabela .= "<td>$row[MARCA]</td>";
                        $tabela .= "<td>" . formatarMoeda($vlr) . "</td>";
                        $tabela .= "<td></td>";
                        $tabela .= "</tr>";
                    }
                    print ($tabela);

                    ?>

                </tbody>
                <tfoot>
                    <tr style="font-size: 0.75rem; font-weight: normal; color: #555;">
                        <th colspan="12" style="text-align: right;">Totais:</th>
                        <th><?= formatarMoeda($totalVlr) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script src="../../public/JS/script.js" charset="utf-8"></script>
</body>

</html>
<?php
header('Content-type: text/html; charset=ISO-8895-1');
include "../../Config/config.php";
include "../../Config/database.php";


$cliente = (string) $_POST["cliente"];
$dataIni = $_POST["dataIni"];
$dataFim = $_POST["dataFim"];
$tipoValor = (STRING) $_POST["tipoValor"];

$filtroSql = "";
$tipoContas = "";

switch ($tipoValor) {
    case $tipoValor == 'C':
        $Sql = "";
        $tipoContas = "Todos os títulos";
        break;
    case $tipoValor == 'U':
        $Sql = "";
        $tipoContas = "Títulos recebidos";
        break;
    case $tipoValor == 'E':
        $Sql = "";
        $tipoContas = "Títulos em aberto";
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
            <div class="month-title"><?= $tipoContas ?> - <?= $nomesMeses[$mes] ?> de <?= $ano ?> - Periodo: <?= $periodo ?>  </div>
            <table>
                <thead>
                    <tr>
                        <th class="titulo-col-tab" onclick="ordenarTabela(0)">Título <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(1)">Empresa <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(2)">Data Emissão <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(3)">Vencimento <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(4)">Venc Original <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(5)">Data Baixa <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(6)">Dif Dias <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(7)">Cliente <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(8)">Valor Título <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(9)">Valor Pago <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th class="titulo-col-tab" onclick="ordenarTabela(10)">Tipo Documento <i class="fa fa-sort"
                                aria-hidden="true"></i></th>
                        <th>
                            <button class="btn-xls-detal" onclick="exportarExcel()"></button>
                            <button class="btn-voltar-detal" onclick="voltar()"></button>
                        </th>
                    </tr>
                </thead>
                <?php
                $sql = "SELECT 
                            Func.TB04010_CODIGO,
                            Func.TB04010_CODEMP,
                            FORMAT(Func.TB04010_DTCAD, 'dd/MM/yyyy') TB04010_DTCAD,
                            FORMAT(Tab.TB04010_DTVENC,'dd/MM/yyyy') TB04010_DTVENC,
                            FORMAT(Func.TB04010_DTVENCORIGINAL, 'dd/MM/yyyy') TB04010_DTVENCORIGINAL,
                            ISNULL(DATEDIFF(D, Func.TB04010_DTVENCORIGINAL, Func.TB04011_DTBAIXA), 0) DIEFERECA_DIAS,
                            ISNULL(FORMAT(Func.TB04011_DTBAIXA, 'dd/MM/yyyy'), 'Aberto') TB04011_DTBAIXA,
                            NOMECLIENTE,
                            Func.TB04010_VLRTITULO,
                            Func.TB04010_VLRPAGO,
						    TipoDoc.TB04003_NOME
                        FROM FTVENCIDO_DETAL_2($ano, $mes, '$periodo') as Func
                        LEFT JOIN TB04010 Tab ON Tab.TB04010_CODIGO = Func.TB04010_CODIGO AND Tab.TB04010_CODCLI = Func.TB04010_CODCLI
                        LEFT JOIN TB04003 TipoDoc ON TipoDoc.TB04003_CODIGO = Tab.TB04010_TIPDOC

                        $filtroSql
                    ";

                $stmt = sqlsrv_query($conn, $sql);
                ?>
                <tbody>
                    <?php
                    function formatarMoeda($valor)
                    {
                        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
                    }
                    $totalTitulo = 0;
                    $totalPago = 0;

                    $tabela = "";

                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        $titulo = (float) $row['TB04010_VLRTITULO'];
                        $pago = (float) $row['TB04010_VLRPAGO'];

                        $totalTitulo += $titulo;
                        $totalPago += $pago;

                        $tabela .= "<tr>";
                        $tabela .= "<td>$row[TB04010_CODIGO]</td>";
                        $tabela .= "<td>$row[TB04010_CODEMP]</td>";
                        $tabela .= "<td>$row[TB04010_DTCAD]</td>";
                        $tabela .= "<td>$row[TB04010_DTVENC]</td>";
                        $tabela .= "<td>$row[TB04010_DTVENCORIGINAL]</td>";
                        $tabela .= "<td>$row[TB04011_DTBAIXA]</td>";
                        $tabela .= "<td>$row[DIEFERECA_DIAS]</td>";
                        $tabela .= "<td>$row[NOMECLIENTE]</td>";
                        $tabela .= "<td>" . formatarMoeda($titulo) . "</td>";
                        $tabela .= "<td>" . formatarMoeda($pago) . "</td>";
                        $tabela .= "<td>$row[TB04003_NOME]</td>";
                        $tabela .= "<td></td>";
                        $tabela .= "</tr>";
                    }
                    print ($tabela);
                    ?>

                </tbody>
                <tfoot>
                    <tr style="font-size: 0.75rem; font-weight: normal; color: #555;">
                        <th colspan="8" style="text-align: right;">Totais:</th>
                        <th><?= formatarMoeda($totalTitulo) ?></th>
                        <th><?= formatarMoeda($totalPago) ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <script src="../../public/JS/script.js" charset="utf-8"></script>
</body>

</html>
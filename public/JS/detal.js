 // Ordenação das Colunas
 function ordenarTabela(colIndex) {
     const table = document.querySelector("table");
     const tbody = table.tBodies[0];
     const rows = Array.from(tbody.querySelectorAll("tr"));

     const asc = !table.dataset.sortAsc || table.dataset.sortAsc === "false";
     table.dataset.sortAsc = asc;

     rows.sort((a, b) => {
         const cellA = a.children[colIndex].innerText.trim();
         const cellB = b.children[colIndex].innerText.trim();

         // Remove "R$", espaços e pontos dos milhares, substitui vírgula por ponto
         const normalize = str =>
             parseFloat(
                 str.replace(/R\$\s?/g, "") // remove R$ e espaço
                 .replace(/\./g, "") // remove pontos dos milhares
                 .replace(",", ".") // troca vírgula decimal por ponto
             );

         const numA = normalize(cellA);
         const numB = normalize(cellB);

         if (!isNaN(numA) && !isNaN(numB)) {
             return asc ? numA - numB : numB - numA;
         }

         return asc ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
     });

     tbody.innerHTML = "";
     rows.forEach(row => tbody.appendChild(row));
 }

 //exportar xls
 function exportarExcel() {
     // Seleciona a tabela original
     const tabelaOriginal = document.querySelector("table");

     // Clona a tabela inteira
     const tabelaClone = tabelaOriginal.cloneNode(true);

     // Remove as linhas que estão com display: none
     const linhas = tabelaClone.querySelectorAll("tr");
     linhas.forEach(linha => {
         if (getComputedStyle(linha).display === "none") {
             linha.remove();
         }
     });

     // Pega o HTML da tabela limpa
     const html = tabelaClone.outerHTML;

     // Cria o arquivo Excel
     const blob = new Blob(["\ufeff" + html], {
         type: "application/vnd.ms-excel"
     });
     const url = URL.createObjectURL(blob);

     // Cria link para download
     const a = document.createElement("a");
     a.href = url;
     a.download = "Detalhamento_inadimplencia.xls";
     document.body.appendChild(a);
     a.click();
     document.body.removeChild(a);
 }


 function voltar() {
     history.back();
 }


 // Preenche a lista de beneficios dinamicamente
 document.addEventListener('DOMContentLoaded', () => {
     const selectBen = document.getElementById('filtroBeneficio');
     const linhasBen = document.querySelectorAll('.linha');
     const beneficios = new Set();

     linhasBen.forEach(linha => {
         const nome = linha.cells[7].textContent.trim();
         beneficios.add(nome);
     });

     Array.from(beneficios).sort().forEach(nome => {
         const option = document.createElement('option');
         option.value = nome;
         option.textContent = nome;
         selectBen.appendChild(option);
     });
 });



 // Filtra as linhas com base no Beneficio selecionado
 function filtrarPorBeneficio() {
     const filtro = document.getElementById('filtroBeneficio').value;
     const linhasBen = document.querySelectorAll('.linha');

     linhasBen.forEach(linha => {
         const nome = linha.cells[7].textContent.trim();
         linha.style.display = (!filtro || nome === filtro) ? '' : 'none';
     });

     atualizarTotais();
 }


 // Adiciona um evento para filtrar por beneficio
 function salvarFiltro() {
     const filtro = {
         nome: document.getElementById('filtroBeneficio').value,
     };

     localStorage.setItem('filtroBeneficio', JSON.stringify(filtro));
 }

 function carregarFiltro() {
     const filtroSalvo = localStorage.getItem('filtroBeneficio');
     if (filtroSalvo) {
         const filtro = JSON.parse(filtroSalvo);
         document.getElementById('filtroBeneficio').value = filtro.nome;
     }

 }


 // Carrega o filtro ao carregar a página
 carregarFiltro();

 // Adiciona um evento para salvar o filtro
 document.getElementById('filtroBeneficio').addEventListener('change', salvarFiltro);



 //somar valores
 function formatarMoeda(valor) {
     return valor.toLocaleString('pt-BR', {
         style: 'currency',
         currency: 'BRL'
     });
 }

 function parseValor(texto) {
    if (!texto) return 0;
  
    // Remove tudo que não for número, vírgula, ponto ou sinal de menos
    const limpo = texto.replace(/[^\d,-]/g, '')
                       .replace(/\.(?=\d{3})/g, '') // remove pontos de milhar
                       .replace(',', '.');
  
    const numero = parseFloat(limpo);
    return isNaN(numero) ? 0 : numero;
  }

 function atualizarTotais() {
     const linhas = document.querySelectorAll("table tbody tr");
     let totalBeneficio = 0;
     let totalUtilizado = 0;
     let totalRestante = 0;

     linhas.forEach(linha => {
         if (linha.style.display === "none") return;

         const colunas = linha.querySelectorAll("td");
         totalBeneficio += parseValor(colunas[12]?.textContent);
         totalUtilizado += parseValor(colunas[13]?.textContent);
         totalRestante  += parseValor(colunas[14]?.textContent);

     });

     const elBen = document.getElementById("totalBeneficio");
     const elUtil = document.getElementById("totalUtilizado");
     const elRest = document.getElementById("totalRestante");

     if (elBen) elBen.textContent = formatarMoeda(totalBeneficio);
     if (elUtil) elUtil.textContent = formatarMoeda(totalUtilizado);
     if (elRest) elRest.textContent = formatarMoeda(totalRestante);
 }
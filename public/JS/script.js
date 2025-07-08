  function enviarDetalhes(cliente, dataIni, dataFim, beneficio, tipoValor) {
    document.getElementById('cliente').value = cliente;
    document.getElementById('dataIni').value = dataIni;
    document.getElementById('dataFim').value = dataFim;
    document.getElementById('beneficio').value = beneficio;
    document.getElementById('tipoValor').value = tipoValor;
    document.getElementById('detalForm').submit();
  }



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
    let table = document.querySelector("table");
    let html = table.outerHTML;

    let blob = new Blob(["\ufeff" + html], {
      type: "application/vnd.ms-excel"
    });
    let url = URL.createObjectURL(blob);

    let a = document.createElement("a");
    a.href = url;
    a.download = "Beneficios.xls";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

  }

  function voltar() {
    history.back();
  }




  // Preenche a lista de clientes dinamicamente
  document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('filtroCliente');
    const linhas = document.querySelectorAll('.linha-click2');
    const clientes = new Set();

    linhas.forEach(linha => {
      const nome = linha.cells[0].textContent.trim();
      clientes.add(nome);
    });

    Array.from(clientes).sort().forEach(nome => {
      const option = document.createElement('option');
      option.value = nome;
      option.textContent = nome;
      select.appendChild(option);
    });
  });

  // Filtra as linhas com base no cliente selecionado
  function filtrarPorCliente() {
    const filtro = document.getElementById('filtroCliente').value;
    const linhas = document.querySelectorAll('.linha-click2');

    linhas.forEach(linha => {
      const nome = linha.cells[0].textContent.trim();
      linha.style.display = (!filtro || nome === filtro) ? '' : 'none';
    });

    atualizarTotais();
  }


  function salvarFiltro() {
    const filtro = {
      nome: document.getElementById('filtroCliente').value,
    };

    localStorage.setItem('filtroCliente', JSON.stringify(filtro));
  }

  function carregarFiltro() {
    const filtroSalvo = localStorage.getItem('filtroCliente');
    if (filtroSalvo) {
      const filtro = JSON.parse(filtroSalvo);
      document.getElementById('filtroCliente').value = filtro.nome;

      filtrarPorCliente(); // Aplica o filtro ao carregar a página
    }

  }


  // Carrega o filtro ao carregar a página
  carregarFiltro();

  // Adiciona um evento para salvar o filtro
  document.getElementById('filtroCliente').addEventListener('change', salvarFiltro);






  // Preenche a lista de beneficios dinamicamente
  document.addEventListener('DOMContentLoaded', () => {
    const selectBen = document.getElementById('filtroBeneficio');
    const linhasBen = document.querySelectorAll('.linha-click2');
    const beneficios = new Set();

    linhasBen.forEach(linha => {
      const nome = linha.cells[2].textContent.trim();
      beneficios.add(nome);
    });

    Array.from(beneficios).sort().forEach(nome => {
      const option = document.createElement('option');
      option.value = nome;
      option.textContent = nome;
      selectBen.appendChild(option);
    });
  });

  // Filtra as linhas com base no cliente selecionado
  function filtrarPorBeneficio() {
    const filtro = document.getElementById('filtroBeneficio').value;
    const linhasBen = document.querySelectorAll('.linha-click2');

    linhasBen.forEach(linha => {
      const nome = linha.cells[2].textContent.trim();
      linha.style.display = (!filtro || nome === filtro) ? '' : 'none';
    });
    atualizarFiltroCliente();
    atualizarTotais();
  }


  // Atualiza a lista de clientes no filtro quando as linhas são filtradas
  function atualizarFiltroCliente() {
    const clienteSelect = document.getElementById('filtroCliente');
    const linhas = document.querySelectorAll('.linha-click2');

    const clientesVisiveis = new Set();

    linhas.forEach(linha => {
      if (linha.style.display !== 'none') {
        const cliente = linha.cells[0].textContent.trim();
        clientesVisiveis.add(cliente);
      }
    });

    const valorSelecionado = clienteSelect.value;

    clienteSelect.innerHTML = '<option value="">-- Clientes --</option>';
    Array.from(clientesVisiveis).sort().forEach(cliente => {
      const option = document.createElement('option');
      option.value = cliente;
      option.textContent = cliente;
      clienteSelect.appendChild(option);
    });

    // Se o cliente selecionado anteriormente ainda existir, manter selecionado
    if (clientesVisiveis.has(valorSelecionado)) {
      clienteSelect.value = valorSelecionado;
    }
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

      filtrarPorCliente(); // Aplica o filtro ao carregar a página
    }

  }


  // Carrega o filtro ao carregar a página
  carregarFiltro();

  // Adiciona um evento para salvar o filtro
  document.getElementById('filtroBeneficio').addEventListener('change', salvarFiltro);


//somar valores
  function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
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
  
    let totalInicial = 0, totalConcedido = 0, totalUtilizado = 0, totalExpirado = 0, totalFinal = 0;
  
    linhas.forEach(linha => {
      if (linha.style.display === "none") return; // ignora linhas filtradas
  
      const celulas = linha.querySelectorAll("td");
  
      totalInicial   += parseValor(celulas[3]?.textContent);
      totalConcedido += parseValor(celulas[4]?.textContent);
      totalUtilizado += parseValor(celulas[5]?.textContent);
      totalExpirado  += parseValor(celulas[6]?.textContent);
      totalFinal     += parseValor(celulas[7]?.textContent);
    });
  
    document.getElementById("totalValorInicial").textContent = formatarMoeda(totalInicial);
    document.getElementById("totalValorConcedido").textContent = formatarMoeda(totalConcedido);
    document.getElementById("totalValorUtilizado").textContent = formatarMoeda(totalUtilizado);
    document.getElementById("totalValorExpirado").textContent = formatarMoeda(totalExpirado);
    document.getElementById("totalValorFinal").textContent = formatarMoeda(totalFinal);
  }
  

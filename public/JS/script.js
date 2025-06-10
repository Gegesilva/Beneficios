  function enviarDetalhes(cliente, dataIni, dataFim, tipoValor) {
    document.getElementById('cliente').value = cliente;
    document.getElementById('dataIni').value = dataIni;
    document.getElementById('dataFim').value = dataFim;
    document.getElementById('tipoValor').value = tipoValor;
    document.getElementById('detalForm').submit();
  }

 


  /* Grafico */
  document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('lineChart').getContext('2d');
    /* define o tamanho da font do grafico geral */
    Chart.defaults.font.size = 20;

    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: chartData.labels,
        datasets: [{
            label: '0 a 30 dias',
            data: chartData.datasets.perc0a30,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.3
          },
          {
            label: '0 a 90 dias',
            data: chartData.datasets.perc0a90,
            borderColor: 'rgba(255, 159, 64, 1)',
            backgroundColor: 'rgba(255, 159, 64, 0.2)',
            tension: 0.3
          },
          {
            label: '0 a 365 dias',
            data: chartData.datasets.perc0a365,
            borderColor: 'rgba(153, 102, 255, 1)',
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            tension: 0.3
          },
          {
            label: 'Global',
            data: chartData.datasets.percall,
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.3
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => value + '%'
            }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: ctx => ctx.dataset.label + ': ' + ctx.raw + '%'
            },
            titleFont: {
              weight: 'bold',
              size: '20px'
            },
            footerFont: {
              weight: 'bold',
              size: '10px'
            }
          },
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 60,
              font: {
                size: 20
              }
            }
          },
          title: {
            display: true,
            text: 'Percentual de inadimplência por mês.',
            font: {
              weight: 'bold',
              size: '25px'
            }
          }
        },
        elements: {
          point: {
            radius: 5
          }
        }
      }
    });
  });


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
             .replace(/\./g, "")     // remove pontos dos milhares
             .replace(",", ".")      // troca vírgula decimal por ponto
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
    a.download = "Detalhamneto inadimplencia.xls";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

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
  

  function voltar() {
    history.back();
  }





  
  // Preenche a lista de beneficios dinamicamente
  document.addEventListener('DOMContentLoaded', () => {
    const selectBen = document.getElementById('filtroBeneficio');
    const linhasBen = document.querySelectorAll('.linha-click2');
    const clientes = new Set();

    linhasBen.forEach(linha => {
      const nome = linha.cells[2].textContent.trim();
      clientes.add(nome);
    });

    Array.from(clientes).sort().forEach(nome => {
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
  }


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
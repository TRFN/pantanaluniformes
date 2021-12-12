var main_fn = () => {
    $("#resumo").html("");
    let html = "",
        conteudos = {},
        meses = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
        formato = { minimumFractionDigits: 2, style: "currency", currency: "BRL" };
    for (let item of {all-data}) {
        item["o-data"] = new Date(item["o-data"]);

        let ano = item["o-data"].getFullYear(),
            mes = item["o-data"].getMonth();

        typeof conteudos[ano] == "undefined" && (conteudos[ano] = {});
        typeof conteudos[ano][meses[mes]] == "undefined" && (conteudos[ano][meses[mes]] = []);

        conteudos[ano][meses[mes]].push(item);
    }
    for (let i in conteudos) {
        html = "<br><br><h2>Ano " + i + "</h2><hr><div class='row'>";
        valortotal = [0, 0];
        entradas = [];
        saidas = [];
        for (let j of meses) {
            if (typeof conteudos[i][j] !== "undefined") {
                html += "<div class='col-lg-3 offset-lg-1 col-sm-6 col-xs-12 mb-4' style='color: #fff; background: #0298; padding: 16px;'><h5>Mês de " + j + "</h5>";
                let valor = [0, 0];
                for (let k in conteudos[i][j]) {
                    typeof conteudos[i][j][k].valor != "undefined" && (valor[conteudos[i][j][k].tipo == "Venda" ? 0 : 1] += conteudos[i][j][k].valor);
                }
                html += "<br><strong>Entrada:&nbsp;</strong>" + valor[0].toLocaleString("pt-BR", formato);
                html += "<br><strong>Saída:&nbsp;</strong>" + valor[1].toLocaleString("pt-BR", formato);
                html += "<br><strong>Balanço:&nbsp;</strong>" + (valor[0] - valor[1]).toLocaleString("pt-BR", formato);
                valortotal[0] += valor[0];
                valortotal[1] += valor[1];

                entradas.push({ name: j, y: valor[0] });
                saidas.push({ name: j, y: valor[1] });
                html += "</div>";
            }
        }
        html +=
            "</div><div class='row'><div class='col-lg-3 offset-lg-1 col-12 text-center' style='color: #fff; background: #282a; padding: 16px;'><h4>Total de Entradas</h4><br><h2>" +
            valortotal[0].toLocaleString("pt-BR", formato) +
            "</h2></div>";
        html += "<div class='col-lg-3 offset-lg-1 col-12 text-center' style='color: #fff; background: #a22b; padding: 16px;'><h4>Total de Saídas</h4><br><h2>" + valortotal[1].toLocaleString("pt-BR", formato) + "</h2></div>";
        html += "<div class='col-lg-3 offset-lg-1 col-12 text-center' style='color: #fff; background: #22ab; padding: 16px;'><h4>Balanço</h4><br><h2>" + (valortotal[0] - valortotal[1]).toLocaleString("pt-BR", formato) + "</h2></div>";

        html += `<div class="col-12"><br><br><br></div><figure class="highcharts-figure col-9 offset-2 my-4 d-none d-md-inline-block">
			    <div id="container-${i}"></div>
			</figure>`;

        html += "</div>";

        $("#resumo").append(html);

        Highcharts.chart(`container-${i}`, {
            chart: {
                scrollablePlotArea: {
                    minWidth: 700,
                },
            },

            title: {
                text: "Indices do ano de " + i,
            },

            subtitle: {
                text: "",
            },

            xAxis: {
                tickInterval: 50,
                tickWidth: 0,
                gridLineWidth: 1,
                labels: {
                    align: "left",
                    x: 3,
                    y: -3,
                },
            },

            yAxis: [
                {
                    title: {
                        text: null,
                    },
                    labels: {
                        align: "left",
                        x: 3,
                        y: 16,
                        format: "{value:.,0f}",
                    },
                    showFirstLabel: false,
                },
                {
                    linkedTo: 0,
                    gridLineWidth: 0,
                    opposite: true,
                    title: {
                        text: null,
                    },
                    labels: {
                        align: "right",
                        x: -3,
                        y: 16,
                        format: "{value:.,0f}",
                    },
                    showFirstLabel: false,
                },
            ],

            legend: {
                align: "left",
                verticalAlign: "top",
                borderWidth: 0,
            },

            tooltip: {
                shared: true,
                crosshairs: true,
            },

            plotOptions: {
                series: {
                    cursor: "pointer",
                    className: "popup-on-click",
                    marker: {
                        lineWidth: 1,
                    },
                    animation: 0,
                },
            },

            series: [
                {
                    name: "Entrada",
                    data: entradas,
                    lineWidth: 4,
                    marker: {
                        radius: 4,
                    },
                    zones: [
                        {
                            value: 400,
                            color: "#f7a35c",
                        },
                        {
                            value: 1500,
                            color: "#7cb5ec",
                        },
                        {
                            color: "#90ed7d",
                        },
                    ],
                },
                {
                    name: "Saída",
                    data: saidas,
                    lineWidth: 4,
                    marker: {
                        radius: 4,
                    },
                    zones: [
                        {
                            value: 100,
                            color: "#555",
                        },
                        {
                            value: 500,
                            color: "#a85",
                        },
                        {
                            color: "#aa1111",
                        },
                    ],
                },
            ],
        });
    }
};

LWDKExec(main_fn);

# Este diretorio contem os arquivos relativos a framework

-> Relacao de diretorios <-

1. Controls  - Sao metodos que podem ser pre-configurados no sistema. Possibilitam o carregamento de plugins e a reutilizacao de codigo.
2. Database  - Contem os arquivos de banco de dados.
3. Engine    - Contem os arquivos principais da frameword, incluindo classes internas de operacao e estrutura.
4. Languages - Pasta que contem traducoes (para sites/sistemas multi-lingua). (em breve)
5. Layouts   - Pasta que contem os Layouts HTML do sistema, que podem ser aplicados aos templates.
6. Models    - Contem tanto modelos HTML (similares aos Layouts, porem reutilizaveis em varios lugares e varias vezes) quanto modelos PHP ( para formularios, no momento ).
7. Plugins   - Pasta que contem plugins de terceiros que podem ser carregados em controles. Observacao: Os plugins com namespaces ainda sao incompativeis.
8. Sections  - Seriam as denominadas "paginas/secoes" de um site/sistema. Nela contem a relacao de paginas/funcoes relativas a um bloco do sistema. Sao esquematizadas via traits e auxiliam na organizacao
9. Templates - Sao divididos em duas partes:
    A. (UI) - HTML: Template(s) HTML do sistema/site geral.
    B. (UX) - PHP : Interface de funcionamento geral da aplicacao.

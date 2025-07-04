WITH Concedido AS (
    SELECT 
        TB02278_CODCLI AS CODCLI,
        TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
        A.TB01008_NOME AS CLIENTE,
        ISNULL(TB02091_DATANOTA, TB02278_DATA) AS DATA,
        SUM(TB02278_VLRBENEF) AS VALOR_CONCEDIDO
    FROM VW02310
    LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278_CODCLI
    LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
    LEFT JOIN TB02021 ON TB02021_CODIGO = TB02278_NUMVENDA
    LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021_NTFISC
    GROUP BY TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME, ISNULL(TB02091_DATANOTA, TB02278_DATA)
),
Utilizado AS (
    SELECT 
        TB02278.TB02278_CODCLI AS CODCLI,
        TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
        A.TB01008_NOME AS CLIENTE,
        --TB02091_DATA AS DATA,
        SUM(VLRDESCBENEF) AS VALOR_UTILIZADO
    FROM VW02311
    LEFT JOIN TB02278 ON TB02278_CODIGO = BENEFICIO
    LEFT JOIN TB01008 AS A ON TB01008_CODIGO = TB02278.TB02278_CODCLI
    LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
    LEFT JOIN TB01074 ON TB01074_CODIGO = TB02278_CLASSIFICACAO
    LEFT JOIN TB02091 ON TB02091_NTFISC = NTFISC AND TB02091_CODEMP = CODEMP
    GROUP BY TB02278.TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME--, TB02091_DATA
),
Expirado AS (
    SELECT 
        vw02310.TB02278_CODCLI AS CODCLI,
        TB01107.TB01107_NOME AS GRUPO_ECONOMICO,
        A.TB01008_NOME AS CLIENTE,
        --ISNULL(TB02091_DATANOTA, vw02310.TB02278_DATA) AS DATA,
        SUM(vw02310.TB02278_VLRREST) AS VALOR_EXPIRADO
    FROM VW02310
    LEFT JOIN TB02278 AS B ON B.TB02278_CODIGO = vw02310.TB02278_CODIGO
    LEFT JOIN TB01008 AS A ON TB01008_CODIGO = vw02310.TB02278_CODCLI
    LEFT JOIN TB01107 ON TB01107_CODIGO = A.TB01008_GRUPO
    LEFT JOIN TB02021 ON TB02021_CODIGO = vw02310.TB02278_NUMVENDA
    LEFT JOIN TB02091 ON TB02091_NTFISC = TB02021.TB02021_NTFISC
    WHERE vw02310.TB02278_SITUACAO = 'I'
    GROUP BY vw02310.TB02278_CODCLI, TB01107.TB01107_NOME, A.TB01008_NOME--, ISNULL(TB02091_DATANOTA, vw02310.TB02278_DATA)
)

SELECT 
    COALESCE(c.CODCLI, u.CODCLI, e.CODCLI) AS CODCLI,
    COALESCE(c.GRUPO_ECONOMICO, u.GRUPO_ECONOMICO, e.GRUPO_ECONOMICO) AS GRUPO_ECONOMICO,
    COALESCE(c.CLIENTE, u.CLIENTE, e.CLIENTE) AS CLIENTE,
    --CAST(COALESCE(c.DATA, u.DATA, e.DATA) AS DATE) AS DATA,
    0 AS VALOR_INICIAL, 
    ISNULL(c.VALOR_CONCEDIDO, 0) AS VALOR_CONCEDIDO,
    ISNULL(u.VALOR_UTILIZADO, 0) AS VALOR_UTILIZADO,
    ISNULL(e.VALOR_EXPIRADO, 0) AS VALOR_EXPIRADO,
    0 + ISNULL(c.VALOR_CONCEDIDO, 0) - ISNULL(u.VALOR_UTILIZADO, 0) - ISNULL(e.VALOR_EXPIRADO, 0) AS VALOR_FINAL
FROM Concedido c
FULL  JOIN Utilizado u 
    ON c.CODCLI = u.CODCLI AND c.GRUPO_ECONOMICO = u.GRUPO_ECONOMICO --AND c.DATA = u.DATA
FULL  JOIN Expirado e 
    ON COALESCE(c.CODCLI, u.CODCLI) = e.CODCLI 
    AND COALESCE(c.GRUPO_ECONOMICO, u.GRUPO_ECONOMICO) = e.GRUPO_ECONOMICO 
    --AND COALESCE(c.DATA, u.DATA) = e.DATA

	WHERE --CAST(c.DATA AS DATE) BETWEEN '2025-01-01' AND '2025-05-27'
	 C.CODCLI = '00003635'
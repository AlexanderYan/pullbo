<?php
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["arquivo_csv"])) {
    $csvFile = $_FILES["arquivo_csv"]["tmp_name"];
    if (is_uploaded_file($csvFile)) {
        $csvData = file_get_contents($csvFile);
        $csvDataUtf8 = iconv("ISO-8859-1", "UTF-8", $csvData); // Converte para UTF-8

        $mapeamento_colunas = [
            'Histórico' => 8,
            'Data Abertura' => 9,
            'OP' => 12,
            'DESCRIÇÃO HISTORICO' => 6,
            'RETORNO' => 17,
            'TME' => 23,
            'H FIN.' => 18,
            'OPERADOR' => 19,
            'SERVIÇO' => 22,
            'CUMPR?' => 16,
            'VT' => 20,
            'PROC' => 15,
            'Contrato + Nome' => 11,
            'TEC' => 31,
            'Caminho_Rede' => 27,
            'Bairro' => 30,
            'Alerta' => 33,
            'Cidade' => 2,
            'Nom_Logradouro' => 29,
            'Assunto Histórico' => 7,
            'RESOLUTA' => 28,
            'UF' => 3,
            'Carteira' => 1,
            'contrato' => 5,
            'TESTE' => 0,
            'NOME' => 4,
        ];

        $linhas = explode("\n", $csvDataUtf8);

        echo '<table>' . PHP_EOL;

        foreach ($linhas as $linha) {
            $dados = str_getcsv(trim($linha), ';');

            if (count($dados) >= 32) {
                if ($primeira_linha) {
                    $primeira_linha = false;
                    
                } else {
                    $dados[23] = '=SE(INDIRETO(SUBSTITUIR("E"&LIN();",";";"))="";"";INDIRETO(SUBSTITUIR("E"&LIN();",";";"))-INDIRETO(SUBSTITUIR("B"&LIN();",";";")))';
                    $dados[16] = '=SE(OU(INDIRETO("H"&LIN())="Anulada"); "Anulada"; SE(OU(INDIRETO("G"&LIN())<>""); "S"; SE(OU(INDIRETO("A"&LIN())<>""); "N"; "")))';
                    $dados[20] = '=SE(CONT.SE(INDIRETO("D"&LIN()); "*ABERTO VT*") = 1; "S"; SE(CONT.SE(INDIRETO("D"&LIN()); "*ABERTA VT*") = 1; "S"; SE(CONT.SE(INDIRETO("D"&LIN()); "*GERADA VT*") = 1; "S"; SE(CONT.SE(INDIRETO("D"&LIN()); "*GERADO VT*") = 1; "S"; "N"))))';
                    $dados[15] = 'S';
                    $dados[31] = 'FTTH / HFC / PACPON / RÁDIO';
                    $dados[32] = '';
                    $dados[28] = '';
                    $dados[3] = '=PROCV(INDIRETO("R"&LIN());Operadores!U:W;2;0)';
                    $dados[1] = '=PROCV(INDIRETO("R"&LIN());Operadores!U:W;3;0)';
                    $dados[11] = '=PROCV(INDIRETO("y"&LIN());Operadores!N:O;2;0)';
                    $dados[6] = $dados[5] . ' ' . $dados[4] . ' ' .  ' | ' . $dados[7];

                   // Combinar a coluna "Data Abertura" e "Hora Abertura" no formato "dd/mm/yyyy hh:mm:ss.ffffff"
$dataAberturaOriginal = $dados[9];
$horaAberturaOriginal = $dados[10];

// Remover as barras duplas no início da data, se existirem
$dataAberturaOriginal = preg_replace('/^\/\//', '', $dataAberturaOriginal);

// Dividir a data e a hora
list($data, $horaCompleta) = explode(" ", "$dataAberturaOriginal $horaAberturaOriginal");

// Extrair apenas a hora e os minutos (hh:mm:ss)
$horaMinutoSegundo = substr($horaCompleta, 0, 8);

// Combinar a data no formato "dd/mm/yyyy" com a hora, os minutos e os segundos "hh:mm:ss"
$dataHoraFormatada = $data . " " . $horaMinutoSegundo;

$dados[9] = $dataHoraFormatada;



                    echo '<tr>';
                    foreach ($mapeamento_colunas as $indice) {
                        $valor = $dados[$indice];
                        echo '<td>' . htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') . '</td>';
                    }
                    echo '</tr>' . PHP_EOL;
                }
            }
        }

        echo '</table>' . PHP_EOL;





$estatisticas_cidade = [];

        $linhas = explode("\n", $csvDataUtf8);

        foreach ($linhas as $linha) {
            $dados = str_getcsv(trim($linha), ';');

            if (count($dados) >= 32) {
                $cidade = $dados[$mapeamento_colunas['Cidade']];

                if (!empty($cidade)) {
                    // Verifique se a cidade já existe nas estatísticas
                    if (isset($estatisticas_cidade[$cidade])) {
                        $estatisticas_cidade[$cidade]++;
                    } else {
                        $estatisticas_cidade[$cidade] = 1;
                    }
                }
            }
        }

        // Salve as estatísticas em um arquivo temporário (opcional)
        $tempFileName = tempnam(sys_get_temp_dir(), 'estatisticas_cidade_');
        file_put_contents($tempFileName, json_encode($estatisticas_cidade));

        // Redirecione o usuário para a página de estatísticas
        header("Location: estatisticas.php?file=" . urlencode($tempFileName));
        exit();





    } else {
        echo "Erro ao carregar o arquivo CSV.";
    }
} else {
    echo "Por favor, selecione um arquivo CSV para enviar.";
}
?>

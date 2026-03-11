<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Orçamento #{{ str_pad($orcamento->id, 4, '0', STR_PAD_LEFT) }} - IdealTech</title>
    <style>
        @page {
            margin: 40px 50px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
        
        /* Cabeçalho */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .header-logo {
            width: 50%;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 250px;
        }
        .header-info {
            width: 50%;
            text-align: right;
            font-size: 12px;
            color: #555;
            vertical-align: middle;
            line-height: 1.4;
        }
        .header-info strong {
            color: #004c99;
            font-size: 14px;
        }

        /* Título Centralizado */
        .doc-title-container {
            width: 100%;
            margin-bottom: 25px;
            text-align: center;
        }
        .doc-title {
            font-size: 16px; /* Tamanho reduzido */
            font-weight: bold;
            color: #000; /* Cor preta */
            text-transform: uppercase;
        }

        /* Dados do Cliente Neutros */
        .cliente-info {
            margin-bottom: 30px;
            line-height: 1.6;
        }

        /* Tabela de Itens (Estilo Clean) */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            text-align: left;
            padding: 8px 5px;
            border-bottom: 2px solid #004c99;
            color: #004c99;
            font-size: 13px;
        }
        .items-table td {
            padding: 10px 5px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .items-table .item-desc strong {
            display: block;
            color: #333;
        }
        .items-table .item-desc small {
            color: #666;
            font-size: 12px;
        }

        /* Totais e Condições */
        .totals-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .total-highlight {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        
        .condicoes-box {
            margin-top: 20px;
            line-height: 1.6;
        }

        /* Observações e Assinatura */
        .observations {
            margin-top: 40px;
            font-size: 13px;
            color: #555;
        }
        .signature {
            margin-top: 60px;
            text-align: center;
            page-break-inside: avoid;
        }

        /* AJUSTE PARA O EDITOR DE TEXTO (QUILL) */
        /* Remove o espaçamento gigante que o PDF dá por padrão nas tags <p> */
        .condicoes-box p, .observations p {
            margin: 0 0 5px 0;
            padding: 0;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ storage_path('app/public/img/img_orcamento.png') }}" alt="IdealTech">
            </td>
            <td class="header-info">
                <strong>IdealTech Soluções em Informática LTDA</strong><br>
                CNPJ: 07.955.432/0001-03<br>
                Rua General Gomes Carneiro, 436<br>
                Edifício Marcelo - Sala 02 - Centro<br>
                idealtech@idealtechcomputadores.com.br<br>
                Whatsapp: (54) 99187-7218<br>
                {{ \Carbon\Carbon::parse($orcamento->data_emissao)->locale('pt-BR')->translatedFormat('l, d \d\e F \d\e Y') }}
            </td>
        </tr>
    </table>

    <div class="doc-title-container">
        <div class="doc-title">
            Orçamento 
        </div>
    </div>

    <div class="cliente-info">
        <strong>Cliente:</strong> {{ $orcamento->nome_cliente }}<br>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="{{ $orcamento->mostrar_valores_individuais ? '65%' : '90%' }}">Descrição do Item</th>
                <th width="10%" style="text-align: center;">Qtd</th>
                @if($orcamento->mostrar_valores_individuais)
                <th width="25%" style="text-align: right;">Valor</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($orcamento->itens as $item)
            <tr>
                <td class="item-desc">
                    <strong>• {{ $item->descricao }} <small>{!! nl2br(e($item->detalhes)) !!}</small></strong>
{{--                     @if($item->detalhes)
                        <small>{!! nl2br(e($item->detalhes)) !!}</small>
                    @endif --}}
                </td>
                <td style="text-align: center;">{{ rtrim(rtrim(number_format($item->quantidade, 2, ',', ''), '0'), ',') }}</td>
                
                @if($orcamento->mostrar_valores_individuais)
                <td style="text-align: right;">R$ {{ number_format($item->valor_total, 2, ',', '.') }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        @if($orcamento->desconto > 0 || $orcamento->frete_acrescimos > 0)
            <div style="text-align: right; color: #666; font-size: 13px; margin-bottom: 10px;">
                {{-- Subtotal: R$ {{ number_format($orcamento->subtotal, 2, ',', '.') }}<br> --}}
                @if($orcamento->desconto > 0)
                    Desconto: - R$ {{ number_format($orcamento->desconto, 2, ',', '.') }}<br>
                @endif
{{--                 @if($orcamento->frete_acrescimos > 0)
                    Acréscimos: + R$ {{ number_format($orcamento->frete_acrescimos, 2, ',', '.') }}<br>
                @endif --}}
            </div>
        @endif

        <div class="total-highlight" style="text-align: right;">
            Valor total: R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}
        </div>

        <div class="condicoes-box">
            @if($orcamento->condicoes_pagamento)
                {!! $orcamento->condicoes_pagamento !!}
            @else
                Consulte formas de pagamento
            @endif
        </div>
    </div>

    <div class="observations">
        <strong>Observações:</strong><br>
        @if($orcamento->observacoes)
            {!! $orcamento->observacoes !!}<br><br>
        @endif
        
        @if($orcamento->data_validade)
            Proposta válida até {{ $orcamento->data_validade->format('d/m/Y') }}
        @else
            Proposta válida por 5 dias
        @endif
    </div>

    <div class="signature">
        <p>Atenciosamente,</p>
        <strong>IdealTech Soluções em Informática.</strong>
    </div>

</body>
</html>
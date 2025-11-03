<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            @page {
                margin: 0.5cm;
            }
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.3;
            color: #111827;
            max-width: 14cm;
            margin: 0 auto;
            padding: 20px;
            background: white;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            padding-bottom: 15px;
            margin-bottom: 15px;
            border-bottom: 1px solid #E5E7EB;
        }
        
        .brand-logo {
            width: 40px;
            height: 40px;
            margin: 0 auto 8px;
        }
        
        .brand-logo img {
            width: 100%;
            height: auto;
        }
        
        .empresa-nome {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin: 0;
            line-height: 1.2;
        }
        
        .comprovante-title {
            text-align: center;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #374151;
            text-transform: uppercase;
        }
        
        .detalhes-box {
            background: #F9FAFB;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #E5E7EB;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            padding: 4px 0;
        }
        
        .info-label {
            font-weight: 500;
            color: #6B7280;
            min-width: 80px;
            font-size: 11px;
        }
        
        .info-value {
            font-weight: 400;
            color: #111827;
            text-align: right;
            font-size: 11px;
        }
        
        .valor-destaque {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
        }
        
        .assinaturas {
            margin-top: 30px;
            display: flex;
            justify-content: center;
        }
        
        .assinatura {
            text-align: center;
            width: 45%;
        }
        
        .linha-assinatura {
            border-top: 1px solid #111827;
            margin: 30px 0 5px;
        }
        
        .assinatura-label {
            font-size: 10px;
            color: #6B7280;
            margin: 3px 0;
        }
        
        .assinatura-nome {
            font-size: 10px;
            color: #111827;
            margin: 0;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #9CA3AF;
        }
        
        .btn-print {
            background: #0077FF;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 15px;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
        }
        
        .btn-close {
            background: #6B7280;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 15px;
            font-family: 'Inter', sans-serif;
            font-size: 11px;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <!-- Botões de ação (não aparecem na impressão) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
        <button class="btn-close" onclick="window.close()">
            <i class="fas fa-times"></i> Fechar
        </button>
    </div>

    <!-- Cabeçalho -->
    <div class="header">
        <!-- Logo da empresa -->
        <div class="brand-logo">
            @if(file_exists(public_path('storage/img/logo2.png')))
                <img src="{{ asset('storage/img/logo2.png') }}" alt="Logo">
            @else
                <div style="height: 40px; display: flex; align-items: center; justify-content: center; background: #F3F4F6; border-radius: 6px;">
                    <span style="color: #6B7280; font-weight: 600; font-size: 10px;">LOGO</span>
                </div>
            @endif
        </div>
        
        <h1 class="empresa-nome">IdealTech Soluções em Informática</h1>
    </div>

    <!-- Título -->
    <div class="comprovante-title">
        Comprovante de Pagamento
    </div>

    <!-- Detalhes -->
    <div class="detalhes-box">
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value">{{ $cliente->nome }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Serviço:</span>
            <span class="info-value">{{ $servico->descricao }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Parcela:</span>
            <span class="info-value">{{ $parcela->numero_parcela }}/{{ $parcela->total_parcelas }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Valor:</span>
            <span class="info-value valor-destaque">R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Pagamento:</span>
            <span class="info-value">{{ now()->format('d/m/Y') }}</span>
        </div>
    </div>

    <!-- Assinaturas -->
    <div class="assinaturas">
        <div class="assinatura">
            <div class="linha-assinatura"></div>
            <p class="assinatura-label">Idealtech Soluções em Informática</p>
        </div>

    </div>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        window.onload = function() {
            window.print();
        };
        
        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>
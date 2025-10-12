<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Serviços - IdealTech</title>
    <style>
        @page { margin: 20px; }
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        /* Header */
        .header { 
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .company-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin-right: 20px;
        }
        .company-details {
            flex: 1;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin: 0;
        }
        .company-slogan {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }
        .report-info {
            text-align: right;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        .report-date {
            color: #666;
            margin: 5px 0;
        }
        
        /* Filtros */
        .filters {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .filter-item {
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            color: #495057;
        }
        
        /* Insights Cards */
        .insights {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .insight-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            background: white;
        }
        .insight-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
            margin: 5px 0;
        }
        .insight-label {
            font-size: 11px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .insight-icon {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        /* Tabela */
        .table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        .table th { 
            background-color: #2c5aa0;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e3d6f;
            font-size: 11px;
        }
        .table td { 
            border: 1px solid #dee2e6; 
            padding: 8px;
            vertical-align: top;
            font-size: 11px;
        }
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* Badges */
        .badge { 
            padding: 4px 8px; 
            border-radius: 12px; 
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        .bg-success { 
            background-color: #d4edda; 
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .bg-warning { 
            background-color: #fff3cd; 
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .bg-danger { 
            background-color: #f8d7da; 
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .bg-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .bg-secondary {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
        
        /* Alinhamentos */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        /* Observações */
        .observations {
            margin-top: 5px;
            font-size: 10px;
            color: #6c757d;
            font-style: italic;
        }
        
        /* Progresso */
        .progress-container {
            width: 100%;
            background-color: #e9ecef;
            border-radius: 10px;
            margin: 2px 0;
        }
        .progress-bar {
            height: 6px;
            border-radius: 10px;
            background-color: #28a745;
            text-align: center;
            color: white;
            font-size: 8px;
            line-height: 6px;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
        
        /* Quebra de página */
        .page-break {
            page-break-before: always;
        }
        
        /* Resumo */
        .summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-total {
            border-top: 2px solid #2c5aa0;
            padding-top: 8px;
            margin-top: 8px;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        <div class="company-info">
            <img src="{{ storage_path('app/public/img/logo2.png') }}" class="logo" alt="IdealTech">
            <div class="company-details">
                <h1 class="company-name">IdealTech Soluções em Informática</h1>
            </div>
            <div class="report-info">
                <h2 class="report-title">Relatório de Serviços</h2>
                <p class="report-date">Emitido em: {{ now()->format('d/m/Y H:i') }}</p>
                <!-- ADICIONE AQUI O PERÍODO -->
                @if(request('data_inicial') && request('data_final'))
                <p class="report-date">
                    Período: {{ \Carbon\Carbon::parse(request('data_inicial'))->format('d/m/Y') }} 
                    a {{ \Carbon\Carbon::parse(request('data_final'))->format('d/m/Y') }}
                </p>
                @endif
            </div>
        </div>
    </div>
    <!-- Filtros Aplicados -->
    @if(request()->anyFilled(['search', 'status', 'tipo_pagamento']))
    <div class="filters">
        <h3 style="margin: 0 0 10px 0; color: #2c5aa0;">Filtros Aplicados:</h3>
        @if(request('search'))
        <div class="filter-item">
            <span class="filter-label">Busca:</span> "{{ request('search') }}"
        </div>
        @endif
        @if(request('status'))
        <div class="filter-item">
            <span class="filter-label">Status:</span> {{ ucfirst(request('status')) }}
        </div>
        @endif
        @if(request('tipo_pagamento'))
        <div class="filter-item">
            <span class="filter-label">Tipo de Pagamento:</span> 
            {{ request('tipo_pagamento') == 'avista' ? 'À Vista' : 'Parcelado' }}
        </div>
        @endif
    </div>
    @endif


    <!-- Tabela de Serviços -->
    <table class="table">
        <thead>
            <tr>
                <th width="12%">Cliente</th>
                <th width="18%">Serviço</th>
                <th width="8%">Valor Total</th>
                <th width="8%">Valor Pago</th>
                <th width="8%">Valor Pendente</th>
                <th width="10%">Tipo</th>
                <th width="10%">Status</th>
                <th width="10%">Data Serviço</th>
                <th width="8%">Progresso</th>
            </tr>
        </thead>
        <tbody>
            @foreach($servicos as $servico)
            @php
                // Calcular valores pagos e pendentes para cada serviço
                if ($servico->tipo_pagamento == 'avista') {
                    $valorPago = $servico->status_pagamento == 'pago' ? $servico->valor : 0;
                    $valorPendente = $servico->status_pagamento == 'pago' ? 0 : $servico->valor;
                } else {
                    $valorPago = $servico->parcelasServico->where('status', 'paga')->sum('valor_parcela');
                    $valorPendente = $servico->parcelasServico->where('status', 'pendente')->sum('valor_parcela');
                }
                
                $parcelasPagas = $servico->parcelasServico->where('status', 'paga')->count();
                $totalParcelas = $servico->parcelasServico->count();
                $progresso = $totalParcelas > 0 ? ($parcelasPagas / $totalParcelas) * 100 : 0;
            @endphp
            <tr>
                <td>
                    <strong>{{ $servico->cliente->nome }}</strong>
                </td>
                <td>
                    {{ $servico->descricao }}
                </td>
                <td class="text-right text-bold">
                    R$ {{ number_format($servico->valor, 2, ',', '.') }}
                </td>
                <td class="text-right text-success text-bold">
                    R$ {{ number_format($valorPago, 2, ',', '.') }}
                </td>
                <td class="text-right text-danger text-bold">
                    R$ {{ number_format($valorPendente, 2, ',', '.') }}
                </td>
                <td class="text-center">
                    <span class="badge {{ $servico->tipo_pagamento == 'avista' ? 'bg-info' : 'bg-secondary' }}">
                        {{ $servico->tipo_pagamento == 'avista' ? 'À Vista' : 'Parcelado' }}
                        @if($servico->tipo_pagamento == 'parcelado')
                            ({{ $servico->parcelas }}x)
                        @endif
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge 
                        @if($servico->status_pagamento == 'pago') bg-success
                        @elseif($servico->status_pagamento == 'nao_pago') bg-danger
                        @else bg-warning @endif">
                        {{ ucfirst($servico->status_pagamento) }}
                    </span>
                </td>
                <td class="text-center">
                    {{ $servico->data_servico->format('d/m/Y') }}
                </td>
                <td class="text-center">
                    @if($servico->tipo_pagamento == 'parcelado' && $servico->parcelas > 1)
                        <small>{{ $parcelasPagas }}/{{ $totalParcelas }}</small>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: {{ $progresso }}%;">
                                {{ $progresso > 20 ? (int)$progresso . '%' : '' }}
                            </div>
                        </div>
                    @else
                        <span>-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumo Final -->
    @if(isset($insights) && count($insights) > 0)
    <div class="summary">
        <h3 style="margin: 0 0 15px 0; color: #2c5aa0;">Resumo Financeiro</h3>
        <div class="summary-item">
            <span>Total de Serviços:</span>
            <span class="text-bold">{{ number_format($insights['total_servicos']) }}</span>
        </div>
        <div class="summary-item">
            <span>Valor Total dos Serviços:</span>
            <span class="text-bold">R$ {{ number_format($insights['valor_total'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-item">
            <span>Total Pago:</span>
            <span class="text-bold text-success">R$ {{ number_format($insights['total_pago'], 2, ',', '.') }}</span>
        </div> 
        <div class="summary-item">
            <span>Total Devedor:</span>
            <span class="text-bold text-danger">R$ {{ number_format($insights['total_devedor'], 2, ',', '.') }}</span>
        </div>
        <div class="summary-item summary-total">
            <span>Saldo do Período:</span>
            <span class="text-bold">R$ {{ number_format($insights['valor_mes_atual'], 2, ',', '.') }}</span>
        </div>
    </div>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        <p>
            IdealTech Soluções em Informática 
        </p>
        <p>
            Relatório gerado automaticamente pelo sistema • 
            Página 1 de 1 • 
            {{ $servicos->count() }} registros listados
        </p>
    </div>
</body>
</html>
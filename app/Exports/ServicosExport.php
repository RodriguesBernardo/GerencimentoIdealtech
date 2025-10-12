<?php

namespace App\Exports;

use App\Models\Servico;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ServicosExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Servico::with('cliente');

        // Aplicar filtro de data
        $dataInicial = $this->request->data_inicial ?? now()->startOfMonth()->format('Y-m-d');
        $dataFinal = $this->request->data_final ?? now()->endOfMonth()->format('Y-m-d');
        $query->whereBetween('data_servico', [$dataInicial, $dataFinal]);

        // Filtros básicos
        if ($this->request->search) {
            $query->whereHas('cliente', function($q) {
                $q->where('nome', 'like', "%{$this->request->search}%");
            });
        }

        if ($this->request->status) {
            $query->where('status_pagamento', $this->request->status);
        }

        if ($this->request->tipo_pagamento) {
            $query->where('tipo_pagamento', $this->request->tipo_pagamento);
        }

        return $query->latest('data_servico')->get()->map(function($servico) {
            return [
                'Cliente' => $servico->cliente->nome,
                'Serviço' => $servico->descricao,
                'Valor' => 'R$ ' . number_format($servico->valor, 2, ',', '.'),
                'Tipo Pagamento' => $servico->tipo_pagamento == 'avista' ? 'À Vista' : 'Parcelado',
                'Status' => ucfirst($servico->status_pagamento),
                'Data Serviço' => $servico->data_servico->format('d/m/Y'),
                'Parcelas' => $servico->tipo_pagamento == 'parcelado' ? $servico->parcelas . 'x' : '1x',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Cliente',
            'Serviço', 
            'Valor',
            'Tipo Pagamento',
            'Status',
            'Data Serviço',
            'Parcelas'
        ];
    }
}
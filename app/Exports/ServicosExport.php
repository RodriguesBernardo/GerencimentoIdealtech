<?php

namespace App\Exports;

use App\Models\Servico;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServicosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Servico::with(['cliente', 'parcelasServico']);

        // Aplicar filtros
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

        // ORDENAÇÃO GARANTIDA
        return $query->orderBy('data_servico', 'DESC')
                    ->orderBy('created_at', 'DESC')
                    ->get();
    }

    public function headings(): array
    {
        return [
            'Data',
            'Cliente',
            'Descrição',
            'Valor Total',
            'Status',
            'Tipo Pagamento',
            'Parcelas'
        ];
    }

    public function map($servico): array
    {
        return [
            $servico->data_servico->format('d/m/Y'),
            $servico->cliente->nome,
            $servico->descricao,
            'R$ ' . number_format($servico->valor, 2, ',', '.'),
            ucfirst($servico->status_pagamento),
            $servico->tipo_pagamento == 'avista' ? 'À Vista' : 'Parcelado',
            $servico->parcelas
        ];
    }
}
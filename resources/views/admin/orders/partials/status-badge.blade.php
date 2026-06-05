@if($status === 'paid')
    <span class="badge text-bg-success">Payee</span>
@elseif($status === 'pending')
    <span class="badge text-bg-warning">En attente</span>
@elseif($status === 'failed')
    <span class="badge text-bg-danger">Echouee</span>
@elseif($status === 'cancelled')
    <span class="badge text-bg-secondary">Annulee</span>
@elseif($status === 'refunded')
    <span class="badge text-bg-info">Remboursee</span>
@else
    <span class="badge text-bg-light">{{ $status }}</span>
@endif

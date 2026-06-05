@if($status === 'successful')
    <span class="badge text-bg-success">Reussi</span>
@elseif($status === 'pending')
    <span class="badge text-bg-warning">En attente</span>
@elseif($status === 'failed')
    <span class="badge text-bg-danger">Echoue</span>
@elseif($status === 'cancelled')
    <span class="badge text-bg-secondary">Annule</span>
@elseif($status === 'refunded')
    <span class="badge text-bg-info">Rembourse</span>
@else
    <span class="badge text-bg-light">{{ $status }}</span>
@endif

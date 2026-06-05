@if($status === 'requested')
    <span class="badge text-bg-warning">Demandee</span>
@elseif($status === 'approved')
    <span class="badge text-bg-primary">Validee</span>
@elseif($status === 'rejected')
    <span class="badge text-bg-danger">Refusee</span>
@elseif($status === 'processed')
    <span class="badge text-bg-success">Traitee</span>
@else
    <span class="badge text-bg-light">{{ $status }}</span>
@endif

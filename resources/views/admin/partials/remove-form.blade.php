{{-- Collapsible "remove with a reason" form. Needs $action; optional $label, $placeholder --}}
<details class="remove-box">
    <summary class="btn btn-danger btn-sm">
        <i class="fa-regular fa-trash-can" aria-hidden="true"></i> {{ $label ?? 'Remove post' }}
    </summary>
    <form method="POST" action="{{ $action }}" class="remove-form">
        @csrf
        <label class="sr-only" for="reason-{{ md5($action) }}">Reason for removal</label>
        <input type="text" id="reason-{{ md5($action) }}" name="reason" class="input" maxlength="200" required
               placeholder="{{ $placeholder ?? 'Reason (saved in the audit log)' }}">
        <button type="submit" class="btn btn-danger btn-sm">Confirm</button>
    </form>
</details>

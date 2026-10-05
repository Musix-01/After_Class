@if (session('status'))
    <div class="toast" role="status">
        <i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert" role="alert">
        <strong>That didn't go through.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

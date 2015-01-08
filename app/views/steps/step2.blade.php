@section('content')
	<h1>Step 2</h1>

	<p>
		So far, so good.
	</p>

	<p>
		Next, we'll have to log in on Tumblr.
	</p>
@stop

@section('buttons')
	<p>
		<a class="pure-button pure-button-primary" href="{{ $next }}">Log in on Tumblr</a>
	</p>
@endsection

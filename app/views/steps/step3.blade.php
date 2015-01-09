@section('content')
	<h1>Step 3</h1>

	<p>
		<strong>Which blog do you want to copy posts from?</strong>
	</p>

	<p>
		<?php var_dump($blogs); ?>
	</p>
@stop

@section('buttons')
	<p>
		<a class="pure-button pure-button-primary" href="{{ $next }}">Log in on Tumblr</a>
	</p>
@endsection

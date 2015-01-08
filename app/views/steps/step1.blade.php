@section('content')
	<h1>Step 1</h1>

	<p>
		Please click the button below to log in on Blogger, and to given this site
		permission to read your blog.
	</p>
@stop

@section('buttons')
	<p>
		<a class="pure-button pure-button-primary" href="{{ $next }}">Log in on Blogger</a>
	</p>
@endsection

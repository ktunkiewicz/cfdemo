<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>cfdemo</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
        <style>
            body {
                padding-top: 50px;
                padding-bottom: 20px;
            }
        </style>
        <!--[if lt IE 9]>
            <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" 
aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Demo project</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
	    <form class="navbar-form navbar-left" role="search">
	        <div class="form-group">
        	    <select class="form-control" placeholder="Choose channel" id="channel">
        		<option val="EUR_GBP">EUR_GBP</option>
        	    </select>
                </div>
            </form>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>
    <div class="container">
      <div class="row">
        <div class="col-md-12" id="output">
        </div>
      </div>
      <hr>
      <footer>
        <p>Kamil Tunkiewicz &lt;kamil.tunkiewicz@gmail.com&gt;</p>
      </footer>
    </div>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>
	<script>
	    var conn = new ab.Session('ws://tunkiewicz.net:8081',
	        function() {
	            conn.subscribe($("#channel").val(), function(topic, data) {
			$("#output").html('<h2>'+topic+'</h2><pre>'+JSON.stringify(data).replace(/,/g,",\n").replace(/\{/g,"{\n").replace(/\}/g,"}\n")+'</pre>');
	            });
	        },
	        function() {
	            console.warn('WebSocket connection closed');
	        },
	        {'skipSubprotocolCheck': true}
	    );
	</script>
    </body>
</html>

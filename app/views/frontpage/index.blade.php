<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>cfdemo</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="http://bootswatch.com/superhero/bootstrap.min.css">
        <link rel="stylesheet" href="/assets/styles.css">
        <!--[if lt IE 9]>
            <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#">Demo project</a>
        </div>
        <form class="navbar-form pull-right" role="search">
              <div class="form-group">
                  <select class="form-control" placeholder="Choose channel" id="channel">
                    @foreach($variants as $v)
                      <option val="{{ $v }}">{{ $v }}</option>
                    @endforeach
                  </select>
              </div>
        </form>
      </div>
    </nav>
    <div style="height:100%">
        <div class="row mapRow">
            <div class="col-md-12" id="mapContainer">
                <div class="tooltip"></div>
            </div>
            <div id="overview">
        	<h3>Global data</h3>
        	<dl class="dl-horizontal">
        	    <dt>Min. rate:</dt><dd><span id="min"></span></dd>
        	    <dt>Max. rate:</dt><dd><span id="max"></span></dd>
        	    <dt>Avg. rate:</dt><dd><span id="avg"></span></dd>
        	    <dt>Avg. sale:</dt><dd><span id="avgSell"></span></dd>
        	    <dt>Total sale:</dt><dd><span id="totalSell"></span></dd>
        	    <dt>Count:</dt><dd><span id="count"></span></dd>
        	</dl>
            </div>
        </div>
        <footer>
          <p>Kamil Tunkiewicz &lt;kamil.tunkiewicz@gmail.com&gt;</p>
        </footer>
    </div>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script src="http://autobahn.s3.amazonaws.com/js/autobahn.js"></script>
        <script src="http://d3js.org/d3.v3.min.js"></script>
        <script src="http://d3js.org/topojson.v1.min.js"></script>
        <script>
            var live_data = {}
	    
	    function updateOverview(){
		$('#min').html(live_data.min);
		$('#max').html(live_data.max);
		$('#avg').html(live_data.avg);
		$('#avgSell').html(live_data.avgSell);
		$('#totalSell').html(live_data.totalSell);
		$('#count').html(live_data.count);
	    }   
	             
            function updateColors() {
        	var c = live_data.countries
        	var max = 0;
		for(code in c) {
		    var obj = $('svg .country[code='+code+']');
		    if (obj.length) {
			if (c[code].count==0) {
			    var actual = parseInt(obj.css('fill').split(',')[1].trim());
			    actual = actual - 20;
			    if (actual<0) { actual=0; }
			    obj.css('fill','rgb('+actual+','+actual+','+actual+')');
			} else {
			    if (c[code].count > max) { max = c[code].count; }
		    	    var color = c[code].count / (max / 255);
			    obj.css('fill','rgb('+color+','+color+','+color+')');
			}
		    }
		}
            }
            
            var current_subs = null;
	    var conn = new ab.Session('ws://tunkiewicz.net:8081',
	        function() {
	    	    subscribe();
	        },
	        function() {
	            console.warn('WebSocket connection closed');
	        },
	        {'skipSubprotocolCheck': true}
	    );

	    $('#channel').change(function(){
	      console.log('Unsubscribing',curr_sub);
	      if (curr_sub!==null) {
    	    	  conn.unsubscribe(curr_sub);
		  live_data = {};
	          subscribe();
    	      } else {
    	        subscribe();
    	      }
	    });
	
	    var curr_sub = null;
	    function subscribe() {
		curr_sub = null;
	        conn.subscribe($("#channel").val(), function(topic, data) {
	    	    curr_sub = topic;
		    live_data = data;
		});   
	    }
	    
            var map;
            var debouncer;
            var zoom = d3.behavior.zoom().scaleExtent([1, 9]).on("zoom", moveMap);
            var graticule = d3.geo.graticule();
            var tooltip = $("#mapContainer .tooltip");
            var projection, path, height, width;
            var codes = { '4':'AF', '248':'AX', '8':'AL', '12':'DZ', '16':'AS', '20':'AD', '24':'AO', '660':'AI', '10':'AQ', '28':'AG', '32':'AR', '51':'AM', '533':'AW', '36':'AU', '40':'AT', '31':'AZ', '44':'BS', '48':'BH', '50':'BD', '52':'BB', '112':'BY', '56':'BE', '84':'BZ', '204':'BJ', '60':'BM', '64':'BT', '68':'BO', '70':'BA', '72':'BW', '74':'BV', '76':'BR', '86':'IO', '96':'BN', '100':'BG', '854':'BF', '108':'BI', '116':'KH', '120':'CM', '124':'CA', '132':'CV', '136':'KY', '140':'CF', '148':'TD', '152':'CL', '156':'CN', '162':'CX', '166':'CC', '170':'CO', '174':'KM', '178':'CG', '180':'CD', '184':'CK', '188':'CR', '384':'CI', '191':'HR', '192':'CU', '196':'CY', '203':'CZ', '208':'DK', '262':'DJ', '212':'DM', '214':'DO', '218':'EC', '818':'EG', '222':'SV', '226':'GQ', '232':'ER', '233':'EE', '231':'ET', '238':'FK', '234':'FO', '242':'FJ', '246':'FI', '250':'FR', '254':'GF', '258':'PF', '260':'TF', '266':'GA', '270':'GM', '268':'GE', '276':'DE', '288':'GH', '292':'GI', '300':'GR', '304':'GL', '308':'GD', '312':'GP', '316':'GU', '320':'GT', '831':'GG', '324':'GN', '624':'GW', '328':'GY', '332':'HT', '334':'HM', '336':'VA', '340':'HN', '344':'HK', '348':'HU', '352':'IS', '356':'IN', '360':'ID', '364':'IR', '368':'IQ', '372':'IE', '833':'IM', '376':'IL', '380':'IT', '388':'JM', '392':'JP', '832':'JE', '400':'JO', '398':'KZ', '404':'KE', '296':'KI', '408':'KP', '410':'KR', '414':'KW', '417':'KG', '418':'LA', '428':'LV', '422':'LB', '426':'LS', '430':'LR', '434':'LY', '438':'LI', '440':'LT', '442':'LU', '446':'MO', '807':'MK', '450':'MG', '454':'MW', '458':'MY', '462':'MV', '466':'ML', '470':'MT', '584':'MH', '474':'MQ', '478':'MR', '480':'MU', '175':'YT', '484':'MX', '583':'FM', '498':'MD', '492':'MC', '496':'MN', '499':'ME', '500':'MS', '504':'MA', '508':'MZ', '104':'MM', '516':'NA', '520':'NR', '524':'NP', '528':'NL', '530':'AN', '540':'NC', '554':'NZ', '558':'NI', '562':'NE', '566':'NG', '570':'NU', '574':'NF', '580':'MP', '578':'NO', '512':'OM', '586':'PK', '585':'PW', '275':'PS', '591':'PA', '598':'PG', '600':'PY', '604':'PE', '608':'PH', '612':'PN', '616':'PL', '620':'PT', '630':'PR', '634':'QA', '638':'RE', '642':'RO', '643':'RU', '646':'RW', '652':'BL', '654':'SH', '659':'KN', '662':'LC', '663':'MF', '666':'PM', '670':'VC', '882':'WS', '674':'SM', '678':'ST', '682':'SA', '686':'SN', '688':'RS', '690':'SC', '694':'SL', '702':'SG', '703':'SK', '705':'SI', '90':'SB', '706':'SO', '710':'ZA', '239':'GS', '724':'ES', '144':'LK', '736':'SD', '740':'SR', '744':'SJ', '748':'SZ', '752':'SE', '756':'CH', '760':'SY', '158':'TW', '762':'TJ', '834':'TZ', '764':'TH', '626':'TL', '768':'TG', '772':'TK', '776':'TO', '780':'TT', '788':'TN', '792':'TR', '795':'TM', '796':'TC', '798':'TV', '800':'UG', '804':'UA', '784':'AE', '826':'GB', '840':'US', '581':'UM', '858':'UY', '860':'UZ', '548':'VU', '862':'VE', '704':'VN', '92':'VG', '850':'VI', '876':'WF', '732':'EH', '887':'YE', '894':'ZM', 716:'ZW' };            
            var names = { '4':'Afghanistan', '248':'&Aring;land Islands', '8':'Albania', '12':'Algeria', '16':'American Samoa', '20':'Andorra', '24':'Angola', '660':'Anguilla', '10':'Antarctica', '28':'Antigua and Barbuda', '32':'Argentina', '51':'Armenia', '533':'Aruba', '36':'Australia', '40':'Austria', '31':'Azerbaijan', '44':'Bahamas', '48':'Bahrain', '50':'Bangladesh', '52':'Barbados', '112':'Belarus', '56':'Belgium', '84':'Belize', '204':'Benin', '60':'Bermuda', '64':'Bhutan', '68':'Bolivia, Plurinational State of', '70':'Bosnia and Herzegovina', '72':'Botswana', '74':'Bouvet Island', '76':'Brazil', '86':'British Indian Ocean Territory', '96':'Brunei Darussalam', '100':'Bulgaria', '854':'Burkina Faso', '108':'Burundi', '116':'Cambodia', '120':'Cameroon', '124':'Canada', '132':'Cape Verde', '136':'Cayman Islands', '140':'Central African Republic', '148':'Chad', '152':'Chile', '156':'China', '162':'Christmas Island', '166':'Cocos (Keeling) Islands', '170':'Colombia', '174':'Comoros', '178':'Congo', '180':'Congo, the Democratic Republic of the', '184':'Cook Islands', '188':'Costa Rica', '384':'C&ocirc;te d\'Ivoire', '191':'Croatia', '192':'Cuba', '196':'Cyprus', '203':'Czech Republic', '208':'Denmark', '262':'Djibouti', '212':'Dominica', '214':'Dominican Republic', '218':'Ecuador', '818':'Egypt', '222':'El Salvador', '226':'Equatorial Guinea', '232':'Eritrea', '233':'Estonia', '231':'Ethiopia', '238':'Falkland Islands (Malvinas)', '234':'Faroe Islands', '242':'Fiji', '246':'Finland', '250':'France', '254':'French Guiana', '258':'French Polynesia', '260':'French Southern Territories', '266':'Gabon', '270':'Gambia', '268':'Georgia', '276':'Germany', '288':'Ghana', '292':'Gibraltar', '300':'Greece', '304':'Greenland', '308':'Grenada', '312':'Guadeloupe', '316':'Guam', '320':'Guatemala', '831':'Guernsey', '324':'Guinea', '624':'Guinea-Bissau', '328':'Guyana', '332':'Haiti', '334':'Heard Island and McDonald Islands', '336':'Holy See (Vatican City State)', '340':'Honduras', '344':'Hong Kong', '348':'Hungary', '352':'Iceland', '356':'India', '360':'Indonesia', '364':'Iran, Islamic Republic of', '368':'Iraq', '372':'Ireland', '833':'Isle of Man', '376':'Israel', '380':'Italy', '388':'Jamaica', '392':'Japan', '832':'Jersey', '400':'Jordan', '398':'Kazakhstan', '404':'Kenya', '296':'Kiribati', '408':'Korea, Democratic People\'s Republic of', '410':'Korea, Republic of', '414':'Kuwait', '417':'Kyrgyzstan', '418':'Lao People\'s Democratic Republic', '428':'Latvia', '422':'Lebanon', '426':'Lesotho', '430':'Liberia', '434':'Libyan Arab Jamahiriya', '438':'Liechtenstein', '440':'Lithuania', '442':'Luxembourg', '446':'Macao', '807':'Macedonia, the former Yugoslav Republic of', '450':'Madagascar', '454':'Malawi', '458':'Malaysia', '462':'Maldives', '466':'Mali', '470':'Malta', '584':'Marshall Islands', '474':'Martinique', '478':'Mauritania', '480':'Mauritius', '175':'Mayotte', '484':'Mexico', '583':'Micronesia, Federated States of', '498':'Moldova, Republic of', '492':'Monaco', '496':'Mongolia', '499':'Montenegro', '500':'Montserrat', '504':'Morocco', '508':'Mozambique', '104':'Myanmar', '516':'Namibia', '520':'Nauru', '524':'Nepal', '528':'Netherlands', '530':'Netherlands Antilles', '540':'New Caledonia', '554':'New Zealand', '558':'Nicaragua', '562':'Niger', '566':'Nigeria', '570':'Niue', '574':'Norfolk Island', '580':'Northern Mariana Islands', '578':'Norway', '512':'Oman', '586':'Pakistan', '585':'Palau', '275':'Palestinian Territory, Occupied', '591':'Panama', '598':'Papua New Guinea', '600':'Paraguay', '604':'Peru', '608':'Philippines', '612':'Pitcairn', '616':'Poland', '620':'Portugal', '630':'Puerto Rico', '634':'Qatar', '638':'R&eacute;union', '642':'Romania', '643':'Russian Federation', '646':'Rwanda', '652':'Saint Barth&eacute;lemy', '654':'Saint Helena, Ascension and Tristan da Cunha', '659':'Saint Kitts and Nevis', '662':'Saint Lucia', '663':'Saint Martin (French part)', '666':'Saint Pierre and Miquelon', '670':'Saint Vincent and the Grenadines', '882':'Samoa', '674':'San Marino', '678':'Sao Tome and Principe', '682':'Saudi Arabia', '686':'Senegal', '688':'Serbia', '690':'Seychelles', '694':'Sierra Leone', '702':'Singapore', '703':'Slovakia', '705':'Slovenia', '90':'Solomon Islands', '706':'Somalia', '710':'South Africa', '239':'South Georgia and the South Sandwich Islands', '724':'Spain', '144':'Sri Lanka', '736':'Sudan', '740':'Suriname', '744':'Svalbard and Jan Mayen', '748':'Swaziland', '752':'Sweden', '756':'Switzerland', '760':'Syrian Arab Republic', '158':'Taiwan, Province of China', '762':'Tajikistan', '834':'Tanzania, United Republic of', '764':'Thailand', '626':'Timor-Leste', '768':'Togo', '772':'Tokelau', '776':'Tonga', '780':'Trinidad and Tobago', '788':'Tunisia', '792':'Turkey', '795':'Turkmenistan', '796':'Turks and Caicos Islands', '798':'Tuvalu', '800':'Uganda', '804':'Ukraine', '784':'United Arab Emirates', '826':'United Kingdom', '840':'United States', '581':'United States Minor Outlying Islands', '858':'Uruguay', '860':'Uzbekistan', '548':'Vanuatu', '862':'Venezuela, Bolivarian Republic of', '704':'Viet Nam', '92':'Virgin Islands, British', '850':'Virgin Islands, U.S.', '876':'Wallis and Futuna', '732':'Western Sahara', '887':'Yemen', '894':'Zambia', 716:'Zimbabwe' };
	            
            function drawMap() {
        	width = $('#mapContainer').width();
                height = $('#mapContainer').height();
                projection = d3.geo.mercator().translate([(width/2), ((height*1.25)/2)]).scale( width/2/Math.PI);
                path = d3.geo.path().projection(projection);

                map = d3.select("#mapContainer").append("svg")
                    //.attr("preserveAspectRatio", "xMinYMin meet")
                    //.attr("viewBox", "0 0 600 400")
                    .attr("width",width)
                    .attr("height",height)
                    //.classed("svg-content-responsive", true)
                    .call(zoom)
                    .append("g");
                g = map.append("g");
                
            }
            
            d3.json("assets/world-50m.json", function(error, world) {
                if (error) return console.error(error);
		
                map.append("path")
                    .datum(graticule)
                    .attr("class", "graticule")
                    .attr("d", path);

                g.append("path")
                    .datum({type: "LineString", coordinates: [[-180, 0], [-90, 0], [0, 0], [90, 0], [180, 0]]})
                    .attr("class", "equator")
                    .attr("d", path);

                var topo = topojson.feature(world, world.objects.countries).features;
                var country = g.selectAll(".country").data(topo);

                country.enter().insert("path")
                    .attr("class", "country")
                    .attr("d", path)
                    .attr("id", function(d,i) { return d.id; })
                    .attr("code", function(d,i) { return codes[d.id]; })
                    .attr("name", function(d,i) { return names[d.id]; })
	

                //tooltips
                $('svg .country')
                    .on("mousemove", function(d,i) {
                      if (!tooltipTimeout) {
                        tooltipTimeout = window.setTimeout(function(){},500);
    	            	c = $(d.currentTarget);
                	var data = live_data.countries[c.attr('code')];
                	if (data!==undefined) {
                            $(tooltip)
                                .css('left',mouseX)
                                .css('top',mouseY)
                                .html('<b>'+c.attr('name')+'</b><dl class="dl-horizontal"><dt>Min. rate:</dt><dd>'+data.min+'</dd><dt>Max. rate:</dt><dd>'+data.max+'</dd><dt>Avg. rate:</dt><dd>'+data.avg+'</dd><dt>Avg. sale:</dt><dd>'+data.avgSell+'</dd><dt>Total sale:</dt><dd>'+data.totalSell+'</dd><dt>Count:</dt><dd>'+data.count+'</dd></dl><br>')
                                .css('opacity',1)
                                .show();
                        }
                      } else {
                            $(tooltip)
                                .css('left',mouseX)
                                .css('top',mouseY);
                      }
                    })
                    .on("mouseout",  function(d,i) {
                        $(tooltip).hide();
                        window.clearTimeout(tooltipTimeout);
                        tooltipTimeout = null;
                    }); 
                
                window.setInterval(function(){
            	    updateColors();
            	    updateOverview();
                },500);
            });            
            
            var tooltipTimeout = null;

            function repaintMap() {
        	width = $('#mapContainer').width();
                height = $('#mapContainer').height();
                $('svg').attr('width',width).attr('height',height);
            }            

            function moveMap() {

              var t = d3.event.translate;
              var s = d3.event.scale; 
              zscale = s;
              var h = height/4;


              t[0] = Math.min(
                (width/height)  * (s - 1), 
                Math.max( width * (1 - s), t[0] )
              );

              t[1] = Math.min(
                h * (s - 1) + h * s, 
                Math.max(height  * (1 - s) - h * s, t[1])
              );

              zoom.translate(t);
              g.attr("transform", "translate(" + t + ")scale(" + s + ")");

              //adjust the country hover stroke width based on zoom level
              d3.selectAll(".country").style("stroke-width", 1.5 / s);

            }

            drawMap();

	    var mouseX, mouseY;
	    $(document).mousemove(function(e) {
		mouseX = e.pageX;
	        mouseY = e.pageY;
    	    }).mouseover();

            $(window).resize(function() {
                if(debouncer) { window.clearTimeout(debouncer); }
                debouncer = window.setTimeout(function() {
                    console.log('resizing');
                    repaintMap();
                }, 300);
            });

	</script>
    </body>
</html>

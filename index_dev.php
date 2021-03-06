<?PHP

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/radiator.lib.php';

// $hour = date('H');
// $day = date('D');
// if ($hour >= 19  && $day == 'Mon'){
// 	header('location: wallpaper.php');
// 	die();
// }

include("calendars.inc.php");

// $fof = json_decode(file_get_contents('etc/fof.json'));

// echo '<pre>';
// var_dump($fof);
// echo '</pre>';

?><html>
<head>

<script src='https://api.mapbox.com/mapbox-gl-js/v0.54.0/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v0.54.0/mapbox-gl.css' rel='stylesheet' />

<link href="https://fonts.googleapis.com/css?family=Oxygen" rel="stylesheet">
<link href="static/style.css" rel="stylesheet">
<style type="text/css">

div {

}
<?PHP
	$template = 'a.cal-%2$s { background-color: %1$s; }'."\n";
	foreach($google_calendars as $name => $data){
		printf($template, $data['color'], $name);
	}
?>
</style>



<link rel='stylesheet' href='node_modules/fullcalendar/dist/fullcalendar.css' />
<script src='node_modules/jquery/dist/jquery.min.js'></script>
<script src='node_modules/moment/min/moment.min.js'></script>
<script src='node_modules/fullcalendar/dist/fullcalendar.js'></script>

</head>

<body>

<div id='calendar'></div>
<!--
<div id="rightbar">
	<h2>Network in</h2>
	<img src="https://treacle.mine.nu/bandwidthd/Total-1-R.png">
	<h2>Network out</h2>
	<img src="https://treacle.mine.nu/bandwidthd/Total-1-S.png">
</div>
 -->
<div id="bottom">
	<iframe style="display: inline-block; float: left;" id="forecast_embed" type="text/html" frameborder="0" height="245px" width="650px"
	src="//forecast.io/embed/#lat=51.768&lon=-1.2&name=Hope:&units=si&font-face-name=Oxygen&font-face-url=<?PHP echo urlencode("https://fonts.gstatic.com/s/oxygen/v5/78wGxsHfFBzG7bRkpfRnCQ.woff2"); ?>"
	> </iframe>


	<div id="datetime">
			<div id="time">88:88</div>
			<div id="date">DDD MMM dd YYYY</div>

	</div>

	<div style="display: inline-block;" id="calendarkey">
		<ul>
<?PHP
$template = '			<li style="color: %s">%s &ndash; &#x2588;</li>';
foreach($google_calendars as $name => $data){
	printf($template, $data['color'], $data['name']);
}
?>
		</ul>
	</div>
	<div id='map' style='width: 400px; height: 245px; display: inline-block'></div>

</div>



<canvas id="countdown" width="50" height="50"></canvas>

<script type="text/javascript">

var currentMarkers=[];

var handleError = function(error){
	console.log('---')
	console.log(error)
}

map = null;

calendars = <?PHP echo fullcal_json($google_calendars) ?>


updateMap = function(data, textresult, jsXDR){
	console.log(data)
	console.log(textresult)
	console.log(map);

	if (currentMarkers!==null) {
	    for (var i = currentMarkers.length - 1; i >= 0; i--) {
	      currentMarkers[i].remove();
	    }
	}

	var llb = new mapboxgl.LngLatBounds();

	var i = 0;
	var len = data.length;
	for (; i < len; i++) {
	  item = data[i]

	  if (item.id == "MTE5MDgzMzc~"){
	  	console.log(item);
	  	console.log(item.longitude);

	  	var aq = new mapboxgl.LngLat(item.location.longitude, item.location.latitude);
	  	console.log('Found Aq at ',item.location.longitude, item.location.latitude)
	    llb.extend(aq)

	    // create a HTML element for each feature
	    var aqmark = document.createElement('div');
	    aqmark.className = 'marker';
	    // make a marker for each feature and add to the map
	    oneMarker = new mapboxgl.Marker(aqmark)
	      .setLngLat(aq)
	      .addTo(map);
	    currentMarkers.push(oneMarker);
	  }

	  if (item.id == "MTAxNzI2NDM5Mg~~"){
	  	var fyr = new mapboxgl.LngLat(item.location.longitude, item.location.latitude);
	  	console.log('Found Fyr at ',item.location.longitude, item.location.latitude)
	    llb.extend(fyr)

	    // create a HTML element for each feature
	    var fyrmark = document.createElement('div');
	    fyrmark.className = 'marker';
	    // make a marker for each feature and add to the map
	    oneMarker = new mapboxgl.Marker(fyrmark)
	      .setLngLat(fyr)
	      .addTo(map);
	    currentMarkers.push(oneMarker);
	  }
	}

	console.log(llb);

	map.fitBounds(llb, { padding: 60, maxZoom: 16  } )

}

$(function() {

  // page is now ready, initialize the calendar...

  $('#calendar').fullCalendar({
  	height: 600,
  	width: 1400,
    eventSources: calendars,
    error: handleError
  }
	  )


	mapboxgl.accessToken = 'pk.eyJ1IjoiYXF1YXJpb24iLCJhIjoiQzRoeUpwZyJ9.gIhABGtR7UMR-LZUJGRW0A';
	map = new mapboxgl.Map({
	container: 'map',
	style: 'mapbox://styles/mapbox/streets-v11'
	});


  	$.get('data/fof.json',updateMap);

});

window.setInterval( function(){
	document.location.reload(true);
} , 1000 * 60 * 60);

window.setInterval( function(){
	$.get('data/fof.json',updateMap);
} , 1000 * 300 );

window.setInterval( function(){
	now = new Date();
	mins = now.getMinutes();
	hours = now.getHours();
	if (mins < 10){
		mins = "0" + mins;
	}
	if (hours < 10){
		hours = "0" + hours;
	}
	time = hours + ":" + mins;
	$('#time').html(time);
	$('#date').html(now.toDateString());
} , 1000);


circle = {
	x : 0,
	y : 0,
	radius : false,
	curPerc : 0,
	counterClockwise : false,
	circ : Math.PI * 2,
	quart : Math.PI / 2,

	drawCircle : function(id){
		canvas = document.getElementById(id);
		context = canvas.getContext('2d');
		circle.x = canvas.width / 2;
		circle.y = canvas.height / 2;
		circle.radius = 10;
		 context.lineWidth = 3;
		circle.endPercent = 85;
		circle.curPerc = 0;

		 context.strokeStyle = '#ad2323';
		 context.shadowOffsetX = 0;
		 context.shadowOffsetY = 0;
		 context.shadowBlur = 4;
		 context.shadowColor = '#656565';

		 circle.animate(0, id)
	},


	animate : function(current, id) {
		canvas = document.getElementById(id);
		context = canvas.getContext('2d');


	     context.clearRect(0, 0, canvas.width, canvas.height);
	     context.beginPath();
	     context.arc(circle.x, circle.y, circle.radius, -(circle.quart), ((circle.circ) * current) - circle.quart, false);
	     context.stroke();

	 }

}

circle.drawCircle('countdown');

var percent = 0;

seconds = 900

window.setInterval( function(){
	//.fullCalendar( ‘refetchEvents’ )
	// $('#number').html(parseInt($('#number').html())+1);
	if(percent <= 1.02){
		circle.animate(percent, 'countdown');
		percent += .01;
		// console.log(percent);
	} else {
		console.log('Refreshing');
		$('#calendar').fullCalendar( 'refetchEvents' );
		percent = 0;
	}
} , 9000); // basically seconds, 1800 = 30 minutes



</script>
</body>
</html>

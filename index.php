<?php
	require('passwords.php');

	$passwords = fetch_passwords();

	$persons = array_keys($passwords);

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      	if (isset($_POST['person'])) {
        	$person = $_POST['person'];  
        }
      	
      	if (!empty($_POST['password'])) {
          	if ($_POST['password'] != $passwords[$person]) {
                die("Password is incorrect");
            }

            $date_array = explode('-', $_POST['date']);
            if (count($date_array) == 3) {
                if (!checkdate($date_array[1], $date_array[0], $date_array[2])) {
                    die("Entered date is invalid");
				}
            } else {
                die("Entered date is invalid");
            }

            $year = date('Y', strtotime($_POST['date']));
            $month = date('m', strtotime($_POST['date']));
            $day = date('d', strtotime($_POST['date']));

            if (!is_numeric($_POST['value'])) {
                die("Entered value is invalid");
            }

            $myfile = fopen($person .".txt", "a") or die("Unable to open database!");
            $txt = "[Date.UTC(". $year .",(". $month ."-1),". $day ."), ". $_POST['value'] ."],\n";
            fwrite($myfile, $txt);
            fclose($myfile);
        }
	}

	$series = array();
	foreach ($persons as $person) {
      	$myfile = fopen($person .".txt", "r") or die("Unable to open database!");
        $db = fread($myfile, filesize($person .".txt"));
        fclose($myfile);
      
     	$series[$person] = $db; 
    }

	
?>
<!doctype html>
<html lang="nl" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Lukas en zijn sinus">
    <title>Lukas' sinus</title>

	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="192x192" href="/android-chrome-192x192.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">

    <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/cover/">
	<script src="https://code.highcharts.com/highcharts.js"></script>
  	<!-- Bootstrap core CSS -->
	<link href="/assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>
    <!-- Custom styles for this template -->
	<link href="cover.css" rel="stylesheet">
</head>

<body class="d-flex h-100 text-center text-white bg-dark">
	<div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
		<header class="mb-auto">
			<div>
				<h3 class="float-md-start mb-0"><?php foreach($persons as $person) { if ($persons[sizeof($persons)-1] == $person) { echo 'en '. ucwords($person); } else { echo ucwords($person) .', '; } } ?> hun sinus</h3>
			</div>
		</header>

		<main class="px-3">
            <p class="lead">De laatste status van <?php foreach($persons as $person) { if ($persons[sizeof($persons)-1] == $person) { echo 'en '. ucwords($person); } else { echo ucwords($person) .', '; } } ?> hun sinus a.k.a. liefdesleven wordt nauwlettend bijgehouden. Zodra er nieuws is, zullen ze een nieuwe waarde toevoegen. Zal de grafiek de vorm van een sinus aanhouden?</p>
			<p class="text-muted">Vroeger stond hier alleen de sinus van Lukas, vandaar lukassinus.xyz :)</p>
			<div id="container" style="width:100%; height:500px;"></div>
		</main>

		<footer class="mt-auto text-white-50">
			<p>
				<form method="post" action="" id="form">
                  	<select name="person">
                        <option value="lukas"<?php if($person == "lukas") { echo ' selected'; } ?>>Lukas</option>
                        <option value="tom"<?php if($person == "tom") { echo ' selected'; } ?>>Tom</option>
                        <option value="niels"<?php if($person == "niels") { echo ' selected'; } ?>>Niels</option>
                    </select><br /><br />
					<input type="password" name="password" placeholder="Password" /><br />
					<input type="text" name="date" placeholder="d-m-Y" /> 
					<input type="number" name="value" placeholder="Value" /><br /><br />
					<button type="submit" name="btnSubmit">Submit</button>
				</form>
			</p>
		</footer>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const chart = Highcharts.chart('container', {
              	credits: {
					enabled: false
				},
				chart: {
					type: 'spline',
					backgroundColor: null,
				},
                legend: {
                    itemStyle: {
                        color: '#fff',
                        fontWeight: 'normal'
                    }
                },
				title: {
					text: ''
				},
				xAxis: {
					type: 'datetime',
					dateTimeLabelFormats: {
						day: '%d-%m-%Y'
					},
					title: {
						text: 'Datum'
					},
					gridLineWidth: 1,
                    labels: {
                        style: {
                            color: '#fff'
                        }
                    }
				},
				yAxis: {
					title: {
						text: 'Success rate'
					},
                  	max: 100,
                  	min: 0,
                    labels: {
                        style: {
                            color: '#fff'
                        }
                    }
				},
				series: [{
					name: 'Liefdesleven Lukas',
					data: [<?php echo $series["lukas"]; ?>],
				}, {
                  	name: 'Liefdesleven Tom',
				  	data: [<?php echo $series["tom"]; ?>],
                  	color: '#ff0000'
                }, {
                  	name: 'Liefdesleven Niels',
				  	data: [<?php echo $series["niels"]; ?>],
                  	color: '#ffff00'
                }]
			});
		});
	</script>
</body>
</html>

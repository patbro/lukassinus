<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	require 'PHPMailer/src/Exception.php';
	require 'PHPMailer/src/PHPMailer.php';
	require('passwords.php');

	$passwords = fetch_passwords();

	$persons = array_keys($passwords);

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newsletter'])) {
		if (!empty($_POST['name']) && !empty($_POST['email'])) {
			$myfile = fopen("newsletter.txt", "a") or die("Unable to open newsletter database!");
            $txt = $_POST['email'] .",". $_POST['name'] ."\n";
            fwrite($myfile, $txt);
            fclose($myfile);
			die("Subscribed!");
		}
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form'])) {
      	if (isset($_POST['person'])) {
        	$update_person = $_POST['person'];  
        }
      	
      	if (!empty($_POST['password'])) {
          	if ($_POST['password'] != $passwords[$update_person]) {
                die("COMPUTER SAYS NO! Password is incorrect");
            }

            $date_array = explode('-', $_POST['date']);
            if (count($date_array) == 3) {
                if (!checkdate($date_array[1], $date_array[0], $date_array[2])) {
                    die("Entered date is invalid (format should be: d-m-Y)");
				}
            } else {
                die("Entered date is invalid (format should be: d-m-Y)");
            }

            $year = date('Y', strtotime($_POST['date']));
            if ($year < "2022") {
                die("The year you entered is bullshit!");
            }

            $month = date('m', strtotime($_POST['date']));
            $day = date('d', strtotime($_POST['date']));

            if (!is_numeric($_POST['value'])) {
                die("Entered value is not numeric");
            }

            if ($_POST['value'] < 0 || $_POST['value'] > 100) {
                die("Impossibru! Value should be between 0 and 100");
            }

            // Require user to input the day they started dating, if no values were added in the past
            if (filesize($update_person .".txt") == 0 && $_POST['value'] != 0) {
                die("Not too fast, son! There is no initial start date known yet. You have to add a value of 0 (zero) on the day you started dating first. Then you can add any value you like");
            }

            $myfile = fopen($update_person .".txt", "a") or die("Unable to open database!");
            $txt = "[Date.UTC(". $year .",(". $month ."-1),". $day ."), ". $_POST['value'] ."],\n";
            fwrite($myfile, $txt);
            fclose($myfile);

			$mail = new PHPMailer();
			$mail->setFrom('lukassinus@vanbroeckhuijsenvof.nl', 'Lukas Sinus');

			$myfile = fopen("newsletter.txt", "r") or die("Unable to open newsletter database");
			// Output one line until end-of-file
			while(!feof($myfile)) {
  				$pieces = explode(",", fgets($myfile));
				$mail->addBcc($pieces[0], $pieces[1]);
			}
			fclose($myfile);

			$mail->CharSet = PHPMailer::CHARSET_UTF8;
			$mail->Subject = 'LUKAS SINUS ALARMMMMM!!';
			$mail->Body = ucwords($update_person) ." heeft een nieuwe waarde toegevoegd op www.lukassinus.xyz \n\nGa snel naar de website om te checken hoe dit liefdesverhaal afgelopen is!";

			//send the message, check for errors
			if (!$mail->send()) {
				die("Mailer error: ". $mail->ErrorInfo);
			} else {
				die("Updated! Newsletter has been sent");
			}
		}
	}

	$series = array();
	foreach ($persons as $person) {
      	$myfile = fopen($person .".txt", "r") or die("Unable to open database for ". $person);
        $db = fread($myfile, filesize($person .".txt"));
        fclose($myfile);

        // If the database is empty, fill it with a zero for today, otherwise the chart malfunctions
        if (empty($db)) {
            $db = "[Date.UTC(". date('Y') .",". date('m') ."-1,". date('d') ."), 0],";
        }
      
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
				<h3 class="float-md-start mb-0"><?php foreach($persons as $person) { if (!empty($passwords[$person])) { if ($persons[sizeof($persons)-1] == $person) { echo 'en '. ucwords($person); } else { echo ucwords($person) .', '; } } } ?> hun sinus</h3>
			</div>
		</header>

		<main class="px-3">
            <p class="lead">De laatste status van <?php foreach($persons as $person) { if (!empty($passwords[$person])) { if ($persons[sizeof($persons)-1] == $person) { echo 'en '. ucwords($person); } else { echo ucwords($person) .', '; } } } ?> hun sinus a.k.a. liefdesleven wordt nauwlettend bijgehouden. Zodra er nieuws is, zullen ze een nieuwe waarde toevoegen. Zal de grafiek de vorm van een sinus aanhouden?</p>
			<p class="text-muted">Vroeger stond hier alleen de sinus van Lukas, vandaar lukassinus.xyz :)</p>

			<p>Meld je hieronder aan voor de nieuwsbrief.<br /><u>Vul je echte naam in</u> om te voorkomen dat de email in de spam terecht komt.</p>

			<form method="post" action="" id="newsletter">
				<input type="text" name="name" placeholder="Voornaam Achternaam" /><br />
				<input type="email" name="email" placeholder="lukas@sinus.nl" /><br />
				<button type="submit" name="newsletter">Aanmelden</button><br />
			</form>

			<div id="container" style="width:100%; height:500px;"></div>
		</main>

		<footer class="mt-auto text-white-50">
			<p>
				<form method="post" action="" id="form">
                  	<select name="person">
					  	<?php 
							foreach($persons as $person) { 
								if (!empty($passwords[$person])) { 
									echo '<option value="'. $person .'"';
									if (isset($update_person) && $update_person == $person) {
										echo ' selected';
									}
									echo '>'. ucwords($person) .'</option>';
								}
							}
						?>
                    </select><br /><br />
					<input type="password" name="password" placeholder="Password" /><br />
					<input type="text" name="date" placeholder="d-m-Y" /> 
					<input type="number" name="value" placeholder="Value" /><br /><br />
					<button type="submit" name="form">Submit</button>
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
					name: 'Carlo (Lukas)',
					data: [<?php echo $series["carlo"]; ?>],
					visible: false
				}, {
					name: 'Scharrel (Lukas)',
					data: [<?php echo $series["lukas"]; ?>],
					color: '#0000FF'
				}, {
					name: 'Fleur (Tom)',
					data: [<?php echo $series["tom"]; ?>],
					color: '#ff0000'
				}, {
					name: 'Vera (Niels)',
					data: [<?php echo $series["niels"]; ?>],
					color: '#ffff00'
				}, {
					name: 'Scharrel (Stef)',
					data: [<?php echo $series["stef"]; ?>],
					color: '#00ff00'
				}, {
					name: 'Hanna (Jan-Felix)',
					data: [<?php echo $series["jan-Felix"]; ?>],
					color: '#7b32a8'
				}, {
					name: 'Scharrel (Stijn)',
					data: [<?php echo $series["stijn"]; ?>],
					color: '#ffa500'
				}, {
					name: 'Jara (Jose)', 
					data: [<?php echo $series["jose"]; ?>], 
					color: '#a500ff'
                }]
			});
		});
	</script>
</body>
</html>

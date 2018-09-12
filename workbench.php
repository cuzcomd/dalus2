<?php require('php/session.php'); ?>
<!DOCTYPE html>
<!-- This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version. -->
<html lang="de">
<head>
	<meta charset="UTF-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Webanwendung zur Ausbreitungsabschätzung von Schadstoffen in der Atmosphäre für Feuerwehren.">
	<title>DALUS</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-editable.css" rel="stylesheet"/>
	<link href="css/jasny-bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
	<link rel="apple-touch-icon" sizes="180x180" href="/dalus/images/favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/dalus/images/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/dalus/images/favicon/favicon-16x16.png">
	<link rel="manifest" href="/dalus/images/favicon/manifest.json">
	<link rel="mask-icon" href="/dalus/images/favicon/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="/dalus/images/favicon/favicon.ico">
	<meta name="msapplication-config" content="/dalus/images/favicon/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
	<script src="js/jquery.min.js"></script>
	<link rel="stylesheet" href="css/jquery.dataTables.min.css">
	<link rel="stylesheet" href="css/datetimepicker.css">
	<link rel="stylesheet" href="css/alertify/alertify.core.css">
	<link rel="stylesheet" href="css/alertify/alertify.bootstrap.css">
	<link rel="stylesheet" href="css/toastr.min.css"> <!-- CSS für Script zum dynamischen Anzeigen von Statusmeldungen -->
	<link rel="stylesheet" href="css/bootstrap-colorpicker.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/dataTables.cellEdit.js"></script>
	<script src="js/OSM.js"></script>
	<script src="js/users.js"></script>
	<script src="js/objects.js"></script>
	<script src="js/messkataster.js"></script>
	<script src="js/messtrupps.js"></script>
	<script src="js/init.js"></script>
	<<script src = "js/bootstrap-colorpicker.min.js"></script> <!-- Geocoding von Messpunkten -->

	<script> // Initialfunktion
	OWMAPIkey = "";
	GoogleAPIkey = "";
	benutzer = []; //Initialisierung
	optionen = []; //Initialisierung
	userAL = ""; //Initialisierung
	userID = 0; //Initialisierung
	prj_id = 0; //Initialisierung
	maxRowID = 0; //Initialisierung
	messpunktNummer = 1; //Initialisierung
	objectNummer = 1; // Initialisierung
	metCounter = 1; // Initialisierung
	activeObject = null; // Initialisierung
	activeProjectName = "Unbekanntes Projekt";  //Initialisierung
	ursprungKoordinaten = ""; //Initialisierung
	loadOptions(); // Allgemeine Optionen der DALUS-Installation laden
	loadUser(); // Daten des angemeldeten Benutzers laden
	updateProjects(); //Verfügbare Projekte aktualisieren
	updateSharedProjects(); //Verfügbare geteilte Projekte aktualisieren
	isSharedWith(); //Aktualisieren, mit wem das Projekt geteilt wird
	updateAllUsers() //Aktulisiert alle verfügbaren Benutzer
	objectArray = []; //Array für temporär erzeugte Objekte
	deleteArray = []; // Array für temporär gelöschte Objekte
	markerArray =[]; // Array für temporär erzeugte Marker
	messtruppArray = []; // Array für Messtrupps
	var selectedShape; //Initialisierung für aktuell markiertes Geometrieobjekt
	
	function loadOptions(){ //Aktualisiert die Punkte im Messkataster
	var data = [];
	data = $(this).serialize() + "&" + $.param(data);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/options.php",
		data: {"action": "loadOptions"},
		success: function(data) {
			OWMAPIkey = data.opt_OWMAPI;
			cityName = data.opt_city;
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax
}//Ende Funktion updateKataster
	function initMap() { // Erzeugung der Karte
		loadOSMLayer(); // OSM Kartenbilder laden
		infoWindow = new google.maps.InfoWindow({
			maxWidth: 350
		}); //Globale Initialisierung des Infowindows
		startDrawingManager(map); //Google DrawingManager laden
		dataTables(); // Lädt die Optionen der datatables
		updateKataster(userID, dataTable); // Lädt die Messpunkte
		updateMesstrupps(userID, dataTable3); // Lädt die Messtrupps

		document.getElementById('calcMET').addEventListener('click', function() { // Beim Klick auf "Zeichnen" MET-Modell erzeugen
			generateMET(map);
		});
		
		document.getElementById('switchMesskataster').addEventListener('click', function() {// Messkataster ein-/ausblenden
			loadFixpoints($(this));
		});

		document.getElementById('switchMesskatasterMobile').addEventListener('click', function() {// Messkataster ein-/ausblenden
			loadFixpoints($(this));
		});

		<?php
			if ($accessLevel == 'admin')
			{
				include_once('php/acl/admin/GPSlistener.php'); //Listener für GPS-Logging laden
			}
		?>

		document.getElementById('saveProject').addEventListener('click', function() { // Beim Klick auf "Speichern", aktuelle Änderungen speichern
			saveProjectStatus();	
		});

		document.getElementById('deleteProject').addEventListener('click', function() { // Beim Klick auf "Löschen", aktuelles Projekt löschen
			deleteProject();	
		});

		document.getElementById('startSearch').addEventListener('click', function(){
			var adresse = $('#pac-input').val();
			if (adresse){
				new google.maps.Geocoder().geocode( { 'address': adresse}, function(results, status) {
					if (status == 'OK') {
						map.setCenter(results[0].geometry.location);
						var marker = new google.maps.Marker({
							map: map,
							position: results[0].geometry.location
						});
					} 
					else {
						alert('Geocode was not successful for the following reason: ' + status);
					}
				});
				return;
			}
		}); //Ende eventlistener

		var input = /** @type {!HTMLInputElement} */(document.getElementById('pac-input'));
		var autocomplete = new google.maps.places.Autocomplete(input);
		autocomplete.bindTo('bounds', map);
		autocomplete.addListener('place_changed', function() {
			var place = autocomplete.getPlace();
			if (!place.geometry) {
				new google.maps.Geocoder().geocode( { 'address': place.name}, function(results, status) {
					if (status == 'OK') {
						map.setCenter(results[0].geometry.location);
						var marker = new google.maps.Marker({
							map: map,
							position: results[0].geometry.location
						});
					} 
					else {
						alert('Geocode was not successful for the following reason: ' + status);
					}
				});
			return;
			}

			var marker = new google.maps.Marker({
				map: map,
				position: place.geometry.location
			});
			// If the place has a geometry, then present it on a map.
			if (place.geometry.viewport) {
				map.fitBounds(place.geometry.viewport);
			} 
			else {
				map.setCenter(place.geometry.location);
				map.setZoom(17);  // Why 17? Because it looks good.
			}

			var address = '';
			if (place.address_components) {
				address = [
				(place.address_components[0] && place.address_components[0].short_name || ''),
				(place.address_components[1] && place.address_components[1].short_name || ''),
				(place.address_components[2] && place.address_components[2].short_name || '')
				].join(' ');
			}
		}); // Ende addlistener

		$('.dropdown.keep-open').on({ //Verhindert das Zuklappen der Menüpunkte
			"shown.bs.dropdown": function() { this.closable = false; },
			"click":             function() { this.closable = true; },
			"hide.bs.dropdown":  function() { return this.closable; }
		});

		$('.dropdown.stay .dropdown-menu ').on({
			"click":function(e){
				e.stopPropagation();
			}
		});
	}//Ende Funktion initMap
	</script> <!-- Initialfunktion -->
	<script src="js/module.js"></script>
	<script src="js/googleDrawingManager.js"></script>
</head>
<body>
	<div class="modal fade" id="modal_license" tabindex="-1" role="dialog" aria-labelledby="License">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title text-center"><img src="images/dalus_logo.svg" width="250px"></h4>
				</div>
				<div class="modal-body">
					Das Projekt "Digitale Ausbreitungsabschätzung Luftgetragener Schadstoffe" (DALUS) dient zur Darstellung von Ausbreitungsabschätzungen luftgetragener Schadstoffemissionen und der Dokumentation von Messeinsätzen im Rahmen der operativen Gefahrenwehr.<br/><hr /><br/>
					<div class="panel panel-default">
						<div class="panel-heading text-center">DALUS<br>Copyright <i class="fa fa-copyright" aria-hidden="true"></i> 2017  Marco Trott</div>
						<div class="panel-body">
						   	This program is free software: you can redistribute it and/or modify
						    it under the terms of the GNU General Public License as published by
						    the Free Software Foundation, either version 3 of the License, or
						    (at your option) any later version.<br><br>

						    This program is distributed in the hope that it will be useful,
						    but WITHOUT ANY WARRANTY; without even the implied warranty of
						    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
						    GNU General Public License for more details.<br><br>

						    You should have received a copy of the GNU General Public License
						    along with this program.  If not, see <a href="https://www.gnu.org/licenses/" target="_blank" rel="noopener">https://www.gnu.org/licenses/</a>.<br/><br/>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row">
						<div class="col-xs-4 text-center"><a href="CHANGELOG.md" target="_blank" rel="noopener">Version: 1.6.0</a></div>
						<div class="col-xs-4"><a href="https://github.com/cuzcomd/DALUS" target="_blank" rel="noopener"><i class="fa fa-github" aria-hidden="true"></i> GitHub Repository</a></div>
						<div class="col-xs-4"><a href="mailto:kontakt@cuzcomd.de">kontakt@cuzcomd.de</a></div>
					</div>
				</div>
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div> <!-- Ende modal fade -->

	<div class="modal fade" id="modalMET" tabindex="-1" role="dialog" aria-labelledby="MET Ausbreitungsmodell">
		<div class="modalMET modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">MET Ausbreitungsmodell</h4>
				</div>
				<div class="modal-body container">
					<div id="geocoder row">
						<form id="input-form-met" class="form-horizontal" role="form">
							<div class="form-group" data-toggle="tooltip" title="Freisetzungsort">
								<label class="control-label col-xs-12 col-sm-4" for="addresse">Scha&shy;dens&shy;ort</label>
								<div class="col-xs-12 col-sm-5">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-home"></i></span>
										<input id="addresse" type="textbox" value="Alt Diesdorf 4, Magdeburg" class="form-control">
									</div>
								</div>
								<div class="col-xs-12 col-sm-3 geocoderButtons">
									<button type="button" class="btn btn-default" id="geocode" data-toggle="tooltip" title="MET Freisetzungsort manuell festlegen" onclick="setCoord()"><i class="fa fa-crosshairs"></i> Wählen</button>
								</div>
							</div>
	
							<div class="form-group" data-toggle="tooltip" title="Ausbreitungswinkel">
								<label class="control-label col-xs-12 col-sm-4" for="winkel">Aus&shy;brei&shy;tungs&shy;winkel</label>
								<div class="col-xs-9 col-sm-2">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-arrows-h"></i></span>
										<select id="winkel" name="winkel" class="form-control">
											<option value="45" label="45&deg;">45&deg;</option>
											<option value="60" label="60&deg;" selected>60&deg;</option>
											<option value="90" label="90&deg;">90&deg;</option>
											<option value="360" label="360&deg;">360&deg;</option>
										</select>
									</div>
								</div>
								<div id="ausbreitungsklasse" class="col-xs-3 col-sm-3"></div>
								<div class="col-xs-12 col-sm-3">
									<button type="button" class="btn btn-default" id="setWinkel" data-toggle="tooltip" title="Ausbreitungswinkel bestimmen" onclick="$('#modal_winkel').modal('show');"><i class="fa fa-calculator"></i> Ermitteln</button>
								</div>
							</div>
							<div class="form-group" data-toggle="tooltip" title="Windrichtung">
								<label class="control-label col-xs-12 col-sm-4" for="windrichtung">Wind&shy;richtung</label>
								<div class="col-xs-12 col-sm-5">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-location-arrow"></i></span>
										<input id="windrichtung" type="number" value="280" class="form-control" onchange="document.getElementById('arrow').style.transform = 'rotate('+(this.value-90)+'deg)';">
										<span class="input-group-addon">&deg;</span>
									</div>
								</div>
							</div>
	
							<div class="form-group" data-toggle="tooltip" title="Gefährdung für Personen im Gebäude">
								<label class="control-label col-xs-12 col-sm-4" for="distanz 1">Gefährdung für Personen im Gebäude</label>
								<div class="col-xs-12 col-sm-5">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-exclamation"></i> <i class="fa fa-home"></i></span>
										<input id="distanz1" type="number" value="600" class="form-control">
										<span class="input-group-addon">m</span>
									</div>
								</div>
							</div>
								
							<div class="form-group" data-toggle="tooltip" title="Gefährdung für Personen im Freien">
								<label class="control-label col-xs-12 col-sm-4" for="distanz 1">Gefährdung für Personen im Freien</label>
								<div class="col-xs-12 col-sm-5">
									<div class="input-group">
										<span class="input-group-addon"><i class="fa fa-exclamation"></i> <i class="fa fa-street-view"></i></span>
										<input id="distanz2" type="number" value="1300"  class="form-control">
										<span class="input-group-addon">m</span>
									</div>
								</div>
							</div>
						</form>
						<br>
						<div class="geocoderButtons text-center">
							<button type="button" class="btn btn-primary" id="calcMET" data-toggle="tooltip" title="MET-Freisetzungsort aus Adressfeld lesen" ><i class="fa fa-pencil-square-o"></i> Zeichnen</button>
						</div>
					</div> <!-- Ende Geocoder -->
				</div><!-- Ende modal-body -->
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div><!-- Ende modalMET -->
<div class="modal fade" id="modal_winkel" tabindex="-1" role="dialog" aria-labelledby="Winkel bestimmen">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title text-center">Ausbreitungsklasse bestimmen</h4>
				</div>
				<div class="modal-body">
					<form id="form_winkelrechner" class="form-horizontal">
						<div class="form-group">
							<label for="nebel" class="col-xs-4 form-control-label">Nebel</label>
							<div class="col-xs-8">
								<select id="nebel" name="nebel" class="form-control">
									<option value="true" label="Ja">Ja</option>
									<option value="false" label="Nein">Nein</option>
								</select>
							</div>
						</div>

						<div class="form-group">	
							<label for="windgeschwindigkeit" class="col-xs-4 form-control-label">Wind&shy;ge&shy;schwin&shy;dig&shy;keit</label>
							<div class="col-xs-8">
								<select id="windgeschwindigkeit" name="windgeschwindigkeit" class="form-control">
									<option value="high" label="gr&ouml;&szlig;er 5 m/s (18 km/h)">gr&ouml;&szlig;er 5 m/s (18 km/h)</option>
									<option value="medium" label="zwischen 1 m/s (4 km/h) und 5 m/s (18 km/h)">zwischen 1 m/s (4 km/h) und 5 m/s (18 km/h)</option>
									<option value="low" label="kleiner 1 m/s (4 km/h)">kleiner 1 m/s (4 km/h)</option>
								</select>
							</div>
						</div>

						<div class="form-group">	
							<label for="himmel" class="col-xs-4 form-control-label">Bedeckter Himmel</label>
							<div class="col-xs-8">
								<select id="himmel" name="himmel" class="form-control">
									<option value="true" label="mehr als 50 %">mehr als 50 %</option>
									<option value="false" label="weniger als 50 %">weniger als 50 %</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label for="tageszeit" class="col-xs-4 form-control-label">Tageszeit</label>
							<div class="col-xs-8">
								<select id="tageszeit" name="tageszeit" class="form-control">
									<option value="day" label="Tag">Tag</option>
									<option value="night" label="Nacht">Nacht</option>
								</select>
							</div>
						</div>
					
						<div class="form-group">
							<label for="monat" class="col-xs-4 form-control-label">Monat</label>
							<div class="col-xs-8">
								<select id="monat" name="monat" class="form-control">
									<option value="om" label="Oktober - M&auml;rz">Oktober - M&auml;rz</option>
									<option value="as" label="April - September">April - September</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label for="brand" class="col-xs-4 form-control-label">Brand</label>
							<div class="col-xs-8">
								<select id="brand" name="brand" class="form-control">
									<option value="true" label="Ja">Ja</option>
									<option value="false" label="Nein">Nein</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="intensiverbrand" class="col-xs-4 form-control-label">Intensiver Brand</label>
							<div class="col-xs-8" id="intensiverbrand">
								<label class="radio-inline"><input type="radio" id="intens_brand_ja" name="intens_brand" value="ja">Ja</label>
								<label class="radio-inline"><input type="radio" id="intens_brand_nein" name="intens_brand" value="nein" checked>Nein</label>
							</div>
						</div>
						<div class="form-group">
							<label for="tiefkalt" class="col-xs-4 form-control-label">Tiefkaltes Gas</label>
							<div class="col-xs-8" id ="tiefkalt">
								<label class="radio-inline"><input type="radio" id="tiefkalt_ja" name="tiefkalt" value="ja">Ja</label>
								<label class="radio-inline"><input type="radio" id="tiefkalt_nein" name="tiefkalt" value="nein" checked>Nein</label>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<div class="text-center">
						<button type="button" class="btn btn-default" onclick="getMETweather();">Wetterdaten laden</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Schließen" onclick="computeAngle();">Übernehmen</button>
					</div>
				</div>
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div> <!-- Ende modal fade -->
	
	<div class="modal fade" id="modalOptions" tabindex="-1" role="dialog" aria-labelledby="Optionen">
		<div class="modalOptions modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Optionen </h4>
				</div>
				<div class="modal-body">
					<div id="adminWrapper" class="row">
						<div id="adminPanel" class="col-xs-3">
							<ul class="nav nav-pills nav-stacked">
								<?php
									include_once('php/acl/user/optionsPanel.php'); //Optionen für ACL "user" laden
									if ($accessLevel == 'admin')
									{
										include_once('php/acl/admin/optionsPanel.php'); //Optionen für ACL "admin" laden
									}
								?>
							</ul>
						</div> <!-- Ende adminPanel -->
						<div id="adminContent" class="col-xs-9">
							<div class="tab-content">
								<?php
									include_once('php/acl/user/optionsContent.php'); //Optionen für ACL "user" laden
									if ($accessLevel == 'admin')
									{
										include_once('php/acl/admin/optionsContent.php'); //Optionen für ACL "admin" laden
									}
								?>
							</div> <!-- Ende tab-content -->
						</div> <!-- Ende adminContent -->
					</div> <!-- Ende adminWrapper -->
				</div><!-- Ende modal-body -->
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div><!-- Ende modalOptions -->

	<div class="modal fade" id="modal_new_project" tabindex="-1" role="dialog" aria-labelledby="Neues Projekt">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Neues Projekt erstellen</h4>
				</div>
				<div class="modal-body">
					<form action='' method='POST' class='ajax_create_project' role='form'>
						<input type='hidden' name='username' class="activeUserID" value=''>
						<div class="form-group">
							<label for="projekt_titel_new" class="col-form-label">Projekttitel</label>
							<input class="form-control" type="text" placeholder="Projekttitel" id="projekt_titel_new" name="projekttitel" required>
						</div>
						<div class="form-group">
							<label for="newProjektShared" class="col-form-label">Freigeben für</label>
							<select multiple class="form-control listOfAllUsersExceptMe" type="text" id="newProjektShared" name="shared[]" size="10">
							</select>
						</div>
						<div class="text-center">
							<button type='submit' class='btn btn-primary' onclick="$('#modal_new_project').modal('hide')"><span class='fa fa-check-square-o'></span> Projekt anlegen</button>
						</div>
					</form>
				</div><!-- Ende modal-body -->
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div><!-- Ende Modal_new_project -->

	<div class="modal fade" id="modal_open_project" tabindex="-1" role="dialog" aria-labelledby="Projekt öffnen">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Projekt öffnen</h4>
				</div>
				<div class="modal-body">
					<h5>Meine Projekte </h5>
					<form action='' class="ajax_load_project" method='POST' role='form'>
						<div class="form-group">
							<select class="form-control" type="text" id="projectOpen" name="project_open"  size="10">
							</select>
						</div>
						<div class="text-center">
							<button type='submit' class='btn btn-primary' onclick="$('#modal_open_project').modal('hide')"><span class='fa fa-check-square-o'></span> Projekt öffnen</button>
						</div>
					</form>
					<h5>Für mich freigegebene Projekte</h5>
					<form action='' class="ajax_load_project" method='POST' role='form'>
						<div class="form-group">
							<select class="form-control" id="projectOpenShared" name="project_open"  size="10">
							</select>
						</div>
						<div class="text-center">
							<button type='submit' class='btn btn-primary' onclick="$('#modal_open_project').modal('hide')"><span class='fa fa-check-square-o'></span> Projekt öffnen</button>
						</div>
					</form>
				</div><!-- Ende modal-body -->
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div><!-- Ende Modal_open_project -->

	<div class="modal fade" id="modal_edit_project" tabindex="-1" role="dialog" aria-labelledby="Projekt ändern">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Schließen"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Projekt ändern</h4>
				</div>
				<div class="modal-body">
					<form action='' method='POST' class='ajax_edit_project' role='form'>
						<input type='hidden' class='activeUserID' name='current_user_id' value=''>
						<input type='hidden' class='activeProjectID' name='current_project_id' value='0'>
						<div class="form-group">
							<label for="projekt_titel" class="col-form-label">Projekttitel</label>
							<input class="form-control activeProjectName" id="projekt_titel" type="text" placeholder="Projekttitel" name="projekttitel" value="" required>
						</div>
						<div class="form-group">
							<label for="projektShared" class="col-form-label">Freigeben für</label>
							<select multiple class="form-control" type="text" id="projektShared" name="shared[]"  size="10">
								<!-- Hier erscheinen die Benutzernamen, für die das Projekt freigegeben wurde -->
							</select>
						</div>
						<div class="text-center">
							<button type='submit' class='btn btn-primary' onclick="$('#modal_edit_project').modal('hide')"><span class='fa fa-check-square-o'></span> Änderung Speichern</button>
						</div>
					</form>
				</div><!-- Ende modal-body -->
			</div><!-- Ende modal-content -->
		</div><!-- Ende modal-dialog -->
	</div><!-- Ende Modal_edit_project -->

	<nav id="myNavmenu" class="navmenu navmenu-default navmenu-fixed-left offcanvas-sm" role="navigation">
		<div class="userInformation">
			<span class="fa fa-user-circle" aria-hidden="true"></span>
			<span id="activeUser">&nbsp; Kein Benutzer aktiv</span>
		</div>
		<div class="projectInformation">
			<span class="fa fa-folder-open" aria-hidden="true"></span>
			<span id="activeProject">&nbsp; Kein Projekt geöffnet</span>
		</div>
		<div class="input-group searchbar">
			<label for="pac-input" class="sr-only">Ort suchen</label>
			<input id="pac-input" class="form-control" type="text" placeholder="Ort suchen ...">
			<span id = "startSearch" class="input-group-addon" role="button" title="Suche starten" ><i class="fa fa-search"></i></span>
		</div>
		<div class="werkzeuge hidden-sm hidden-xs">
			<ul class="nav nav-pills nav-werkzeuge">
				<li class="setHand" data-toggle="tooltip" data-placement="bottom" title="Auswahl" role="button"><a data-toggle="tab"><i class="fa fa-mouse-pointer"></i></a></li>
				<li class="setMarkWhite" data-toggle="tooltip" data-placement="bottom" title="Messpunkt" role="button"><a data-toggle="tab"><i class="fa fa-flag-o"></i></a></li>
				<li class="setComment" data-toggle="tooltip" data-placement="bottom" title="Kommentar" role="button"><a data-toggle="tab"><i class="fa fa-commenting-o"></i></a></li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#" title="WerkzeugeToggle"><i class="fa fa-pencil"></i>
					<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="setCirc" data-toggle="tooltip" data-placement="bottom" title="Kreis zeichnen" role="button"><a data-toggle="tab"><i class="fa fa-circle-thin"></i> Kreis</a></li>
						<li class="setPoly" data-toggle="tooltip" data-placement="bottom" title="Polygon zeichnen" role="button"><a data-toggle="tab"><i class="fa fa-bookmark-o"></i> Polygon</a></li>
						<li class="setPath" data-toggle="tooltip" data-placement="bottom" title="Pfad zeichnen" role="button"><a data-toggle="tab"><i class="fa fa-pencil"></i> Pfad</a></li>
					</ul>
				</li>
				<li class="deleteActiveObject" data-toggle="tooltip" data-placement="bottom" title="Objekt löschen" role="button"><a data-toggle="tab"><i class="fa fa-trash"></i></a></li>
				<li id = "switchMesskataster" data-click-state="0" role="button"><a><i class="fa fa-thumb-tack icon-inactive" aria-hidden="true"></i></a></li>
			</ul>
		</div> <!-- Ende Werkzeuge -->
		<div class="weatherapp container-fluid">
			<div class="weathercity"> Wetterdaten werden geladen ...</div>
			<div class="row">
				<div class="temp col-xs-3"></div>
				<div class="wind-speed col-xs-3"></div>
				<div class="wind-direction col-xs-3"></div>
				<div class="clouds col-xs-3"></div>
			</div>
		</div> <!-- Ende Wetterinformationen -->
		<ul class="nav navmenu-nav">
			<li class="dropdown stay keep-open open" id ="project_options" role="presentation" data-toggle="tooltip" data-placement="bottom" title="Projekt">
				<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="ProjektToggle"><i class="fa fa-bars" aria-hidden="true" id="ProjektToggle"></i> Projekt
				<span class="caret"></span></a>
				<ul class="dropdown-menu navmenu-nav">
					<li id="newProject" role="button" onclick="toggleNav('#modal_new_project')" ><a><i class="fa fa-pencil-square-o"></i> Neues Projekt</a></li>
					<li id="openProject" role="button" onclick="toggleNav('#modal_open_project')"><a><i class="fa fa-folder-open-o"></i> Projekt öffnen</a></li>
					<li id="editProject" role="button" onclick="toggleNav('#modal_edit_project')" ><a><i class="fa fa-pencil-square-o"></i> Projekt ändern</a></li>
					<li id="saveProject" role="button"><a><i class="fa fa-floppy-o" aria-hidden="true"></i> Projekt speichern</a></li>
					<li id="deleteProject" role="button" ><a><i class="fa fa-trash-o" aria-hidden="true"></i> Projekt löschen</a></li>
					<li id="exportKML" onclick="toKML()" ><a id="download-link" href="data:;base64," download><i class="fa fa-floppy-o" aria-hidden="true"></i> kml-Datei exportieren</a></li>	
				</ul>
			</li>
			<?php
				if ($accessLevel == 'admin') // Extras nur für Admins einblenden
				{
					echo '
					<li class="dropdown stay keep-open" id ="parameter" role="presentation" data-toggle="tooltip" data-placement="bottom" title="Extras">
						<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="ExtrasToggle"><i class="fa fa-eye-slash" aria-hidden="true"></i> Extras
						<span class="caret"></span></a>
						<ul class="dropdown-menu navmenu-nav">
							<li id = "switchGPS" data-click-state="0" role="button"><a><i class="fa fa-toggle-off" aria-hidden="true"></i> GPS Tracking</a></li>;
						</ul>
					</li>';
				}
			?>
			<li class="dropdown stay keep-open open" id ="modelle" role="presentation" data-toggle="tooltip" data-placement="bottom" title="Ausbreitungsmodelle">
				<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="ModelleToggle"><i class="fa fa-location-arrow" aria-hidden="true"></i> Ausbreitungsmodelle
				<span class="caret"></span></a>
				<ul class="dropdown-menu navmenu-nav">
					<li id ="switch_winkel" role="button" onclick="toggleNav('#modalMET')"><a><i class="fa fa-location-arrow"></i> MET</a></li>
				</ul>
			</li>
			</ul>
	
		<div class="moduleWrapper">
			<div id = "module1" class="module gpsLegende">
				<h5><b>GPS Tracking</b></h5>
				<div>
					<form id="gpsLoadedCars" action="" class="form" role="form">
					<!-- Vom Benutzer gespeicherte Fahrzeuge -->
					</form>
				</div>
				<br><br>
				<div>
					<form action="" class="form"  role="form">
						<div class="form-group">
							<label for="startTrack" class="control-label">Von</label>
							<div class="input-group date form_datetime" id="startTrack" placeholder = "2017-07-17 10:30">
								<input class="form-control" size="16" type="text" value="" id="startTrackInput">
								<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
								<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
							</div>
							<input type="hidden" id="dtp_input1" value="" />
						</div>
						<div class="form-group">
							<label for="endTrack" class="control-label">Bis</label>
							<div class="input-group date form_datetime" id="endTrack" placeholder = "2017-07-17 10:30">
								<input class="form-control" size="16" type="text" value="" id="endTrackInput">
								<span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
								<span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
							</div>
							<input type="hidden" id="dtp_input2" value="" />
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="nav sidebar-footer">
			<a href='php/logout' data-toggle="tooltip" data-placement="bottom" title="Abmelden"><span class="fa fa-power-off" aria-hidden="true"></span></a>
			<a onclick="toggleNav('#modal_license')" data-toggle="tooltip" data-placement="bottom" title="Informationen über Dalus"><span class="fa fa-info-circle" aria-hidden="true"></span></a>
			<a onclick="toggleNav('#modalOptions')" data-toggle="tooltip" data-placement="bottom" title="Optionen"><span class="fa fa-cogs"></span></a>
			<a onclick="printMap()" data-toggle="tooltip" data-placement="bottom" title="Ansicht drucken"><span class="fa fa-print" aria-hidden="true"></span></a></li>
		</div>
	</nav>
	<div class="navbar navbar-default navbar-fixed-top hidden-md hidden-lg text-center">
		<span class="werkzeuge-top">
			<ul class="nav nav-pills nav-werkzeuge">
				<li class="setHand" data-toggle="tooltip" data-placement="bottom" title="Auswahl" role="button"><a data-toggle="tab"><i class="fa fa-mouse-pointer"></i></a></li>
				<li class="setMarkWhite" data-toggle="tooltip" data-placement="bottom" title="Messpunkt" role="button"><a data-toggle="tab"><i class="fa fa-flag-o"></i></a></li>
				<li class="setComment" data-toggle="tooltip" data-placement="bottom" title="Kommentar" role="button"><a data-toggle="tab"><i class="fa fa-commenting-o"></i></a></li>
				<li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="# title="WerkzeugeMobilToggle"><i class="fa fa-pencil"></i>
					<span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li class="setCirc" data-toggle="tooltip" data-placement="bottom" title="Kreis zeichnen" role="button"><a data-toggle="tab"><i class="fa fa-circle-thin"></i> Kreis</a></li>
						<li class="setPoly" data-toggle="tooltip" data-placement="bottom" title="Polygon zeichnen" role="button"><a data-toggle="tab"><i class="fa fa-bookmark-o"></i> Polygon</a></li>
						<li class="setPath" data-toggle="tooltip" data-placement="bottom" title="Pfad zeichnen" role="button"><a data-toggle="tab"><i class="fa fa-pencil"></i> Pfad</a></li>
					</ul>
				</li>
				<li class="deleteActiveObject" data-toggle="tooltip" data-placement="bottom" title="Objekt löschen" role="button"><a data-toggle="tab"><i class="fa fa-trash"></i></a></li>
				<li id = "switchMesskatasterMobile" data-click-state="0" role="button"><a><i class="fa fa-thumb-tack" aria-hidden="true"></i></a></li>
			</ul>
		</span> <!-- Ende Werkzeuge -->
		<button type="button" class="navbar-toggle" data-toggle="offcanvas" data-target="#myNavmenu" data-canvas="body">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
	</div>
	<div id="map"></div>  <!-- Google-Karte -->
	<div id="modul-Kompass" class="modul-Kompass"><img src="images/arrow.png" alt="Windrose" id="arrow"/></div>
	<textarea id="kmlString"></textarea>  <!-- unsichbares Textfeld  als Zwischenspeicher für kml-Export-->
	<script src = "https://maps.googleapis.com/maps/api/js?libraries=geometry,drawing,places&callback=initMap" async defer></script> <!-- GooleAPI laden. Hier muss der API-Schlüssel eingetragen werden. -->
	<script src = "js/bootstrap.min.js"></script> <!-- Bootstrap.js laden -->
	<script src = "js/bootstrap-editable.min.js"></script>  <!-- Script mit Funktionen zur direkten Bearbeitung des Inhalts von DOM-Elementen  -->
	<script src = "js/jasny-bootstrap.min.js"></script>  <!-- Script mit Funktionen für das off-canvas Menü  -->
	<script src = "js/html2canvas.min.js" defer></script>  <!-- Script zum erzuegen eines Screenshots der google-Karte  -->
	<script src = "js/usng.min.js" defer></script> <!-- Script für Umwandlung von Geokoordinaten in UTM-Ref Koordinaten -->
	<script src = "js/MET.js" defer></script> <!-- Adresse des MET-Modells durch Eingabemaske oder manuelle Festlegung bestimmen -->
	<script src = "js/datetimepicker.js"></script> <!-- Script zur Anzeige eines Datumsfeldes  -->
	<script src = "js/datetimepicker.de.js" defer></script> <!--  Deutsche Übersetzung -->
	<script src = "js/datetimepickerOptions.js" defer></script>  <!-- Script mit Optionen für die Anzeige des Datumsfeldes  -->
	<script src = "js/project.js" defer></script> <!--  Script mit Funktionen zur Projektverwaltung-->
	<script src = "js/helpers.js" defer></script> <!-- Script mit Hilfsfunktionen  -->
	<script src = "js/xmlwriter.js" defer></script> <!-- Script zum erzeugen einer kml-Datei -->
	<script src = "js/exportKml.js" defer></script> <!-- Script zum Export der Geometriedaten als kml-Datei -->
	<script src = "js/alertify.min.js" defer></script> <!-- Script zur Anzeige von Popupbenachrichtigungen -->
	<script src = "js/toastr.min.js" defer></script> <!-- Script zum dynamischen Anzeigen von Statusmeldungen -->
	<script src = "js/geocoder.js" defer></script> <!-- Geocoding von Messpunkten -->
	<script src = "js/openweathermap.js" defer></script> <!-- Geocoding von Messpunkten -->
</body>
</html>
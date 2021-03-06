	
function updateProjects(){ //Aktualisiert die Liste der Projekte, die für den angemeldeten Benutzer sichtbar sind
	$('#projectOpen').children('option').remove();// Leert die Liste aller verfügbaren Optionen
	var data = {"action": "updateProjects"};
	data = $(this).serialize() + "&" + $.param(data);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/projects.php",
		data: data,
		success: function(data) {
			$.each(data, function (key, value) {
			 	$('#projectOpen') // Fügt eine neue Option hinzu
			 	.append($('<option></option>') 
			 	.attr('value', value.prj_name)
			 	.text(value.prj_name));
			});
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax
}//Ende Funktion updateProjects

function updateSharedProjects(){ //Aktualisiert die Liste der Projekte, die für den angemeldeten Benutzer sichtbar sind
	$('#projectOpenShared').children('option').remove(); // Leert die Liste aller verfügbaren Optionen
	var data = {"action": "updateSharedProjects"};
	data = $(this).serialize() + "&" + $.param(data);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/projects.php",
		data: data,
		success: function(data) {
			$.each(data, function (key, value) {
				$('#projectOpenShared')// Fügt eine neue Option hinzu
				.append($('<option></option>') 
			 	.attr('value', value.prj_name)
			 	.text(value.prj_name));
			});
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax
}//Ende Funktion updateSharedProjects

function isSharedWith(){ //Aktualisiert die Liste der Projekte, die für den angemeldeten Benutzer sichtbar sind
	$('#projektShared').children('option').remove();// Leert die Liste aller verfügbaren Optionen
	var data = {"action": "isSharedWith", "projectID": prj_id};
	data = $(this).serialize() + "&" + $.param(data);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/projects.php",
		data: data,
		success: function(data) {
			$.each(data, function (key, value) {
				if (value.shared == "yes") {
					$('#projektShared')// Fügt eine neue Option hinzu
				 	.append($('<option selected selected="selected"></option>') 
				 	.attr('value', value.id)
				 	.text(value.username));
				}
				else{
					$('#projektShared')
					 .append($('<option></option>') 
					 .attr('value', value.id)
					 .text(value.username));
					}
			});//Ende each()
		},//Ende success
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax
}//Ende Funktion isSharedWith

function updateMesstruppsMarker(){ //Aktualisiert die Punkte im Messkataster
	$('#markerMesstrupp').children('option').remove();
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/options.php",
		data: {"action": "loadMesstrupps", "UID": ''},
		success: function(data) {
			var obj = JSON.parse(data['0']);
			$.each(obj, function (key, value) {
				if(activeObject != null && value.Abkürzung != activeObject.obj_messtrupp) // Überprüft, ob der Messtrupp bereits ausgewählt wurde und setzt ihn dann als aktiv
				{
				 	$('#markerMesstrupp') // Fügt eine neue Option hinzu
				 	.append($('<option></option>') 
				 	.attr('value', value.Abkürzung)
				 	.text(value.Bezeichnung));
				 }
				 else if(activeObject != null && value.Abkürzung == activeObject.obj_messtrupp)
				 {
				 	$('#markerMesstrupp') // Fügt eine neue Option hinzu
				 	.append($('<option selected selected="selected"></option>') 
				 	.attr('value', value.Abkürzung)
				 	.text(value.Bezeichnung));
				 }
			});
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax
}//Ende Funktion updateMesstruppsMarker

function myCallbackFunction (updatedCell, updatedRow, oldValue) { //Callback für das Editieren der Messkatasterzellen
	    }

function dataTables(){
	dataTable = $('#kataster').DataTable({
		paging: false,
		scrollY: 400,
		 "order": [[ 1, "asc" ]],
		 "columnDefs": [ {
      "targets": 7,
      "orderable": false
    } ]
	}).draw();

	dataTable.MakeCellsEditable({
		"onUpdate": myCallbackFunction,
    	"columns": [1,2,3,4,5,6]
	});

	dataTable2 = $('#katasterGlobal').DataTable({
		paging: false,
		scrollY: 400,
		scrollX: false,
		 "order": [[ 1, "asc" ]],
		 "columnDefs": [ {
      "targets": 7,
      "orderable": false
    } ]
	}).draw();

	dataTable2.MakeCellsEditable({
		"onUpdate": myCallbackFunction,
    	"columns": [1,2,3,4,5,6]
	});

	dataTable3 = $('#messtrupps').DataTable({
		paging: false,
		scrollY: 400,
		scrollX: false,
		 "order": [[ 0, "asc" ]],
		 "columnDefs": [ {
      "targets": 4,
      "orderable": false
    } ]
	}).draw();

	dataTable3.MakeCellsEditable({
		"onUpdate": myCallbackFunction,
    	"columns": [1,2,3]
	});

	dataTable4 = $('#messtruppsGlobal').DataTable({
		paging: false,
		scrollY: 400,
		scrollX: false,
		 "order": [[ 0, "asc" ]],
		 "columnDefs": [ {
      "targets": 4,
      "orderable": false
    } ]
	}).draw();

	dataTable4.MakeCellsEditable({
		"onUpdate": myCallbackFunction,
    	"columns": [1,2,3]
	});
}
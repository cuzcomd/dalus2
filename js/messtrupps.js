function updateMesstrupps(UID, tableName, tableID){ //Aktualisiert die Messtrupps
	tableName.clear(); // Leert die Liste aller verfügbaren Optionen
	var data = [];
	data = $(this).serialize() + "&" + $.param(data);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/options.php",
		data: {"action": "loadMesstrupps", "UID": UID},
		success: function(data) {
			var obj = JSON.parse(data[0]);
			messtruppArray =[]; // leert den bisherigen Array
			$.each(obj, function (key, value) {
				messtruppArray.push({'abkürzung': value.Abkürzung, 'farbe': value.Farbe}); //fügt die ID und Farbe aller Werte in den Array ein
				});
			$.each(obj.slice(1), function (key, value) { //nicht den Standardwert anzeigen
				tableName.row.add([value.ID, value.Abkürzung, value.Bezeichnung, "<div id = 'cp"+tableID+value.ID+"' class='input-group'><input type='text' class='form-control input-lg value='"+value.Farbe+"'><span class='input-group-append'><span class='input-group-text colorpicker-input-addon'><i></i></span></span></div>","<div role='button' class='btn btn-default btn-del-row'><span class='fa fa-trash'></span></div>"]).draw();
				$('#cp'+tableID+value.ID).colorpicker({color:value.Farbe, format:'hex'});
				});
				

			maxRowIDMesstrupp = Math.max.apply(Math,obj.map(function(o){return o.ID;})); //Liest die größte gespeicherte ID aus

			$('.btn-del-row').click(function(){
			 	tableName.row($(this).parents('tr')).remove().draw();
			});
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax
}//Ende Funktion updateKataster

function saveMesstrupps(UID, tableName, tableID){ //Speichert die Messtrupps
	var data = [];
	var headers = [];
	var currentID = "0";
	data.push({"ID":"0","Abkürzung":"","Bezeichnung":"Keine Zuordnung","Farbe":"#000"}) // Standardwert schreiben
	$(tableName+'> thead th').not(':last').each(function(index, item) {
    	headers[index] = $(item).text();
	});
	$(tableName+'> tbody tr').has('td').each(function() {
        var arrayItem = {};
        $('td', $(this)).not(':last').each(function(index, item) {
        	if (headers[index] == "ID")
        	{
        		currentID =$(item).html(); 
        	}
        	if (headers[index] == "Farbe")
        	{
        		let val = $("#cp"+tableID+currentID).colorpicker('getValue');
        		arrayItem[headers[index]] = val;
        	}
        	else
        	{
            	arrayItem[headers[index]] = $(item).html();
        	}
   		});
    	data.push(arrayItem);
	});
	data = JSON.stringify(data);
	$.ajax({
		type: "POST",
		dataType: "json",
		url: "php/options.php",
		data: {"action": "saveMesstrupps", "data": data, "UID": UID},
		success: function(data) {
			if (data == 'noAdmin')
			{
				alertify.error('Du verfügst nicht über die benötigten Rechte.');
			}
			else if (data == "successGlobal")
			{
				alertify.success('Globale Konfiguration überschrieben.');
				updateMesstruppsMarker() //Aktualisiert die Anzeige direkt nach einer Änderung
			}
			else if (data == "successLocal")
			{
				alertify.success('Konfiguration überschrieben.');
				updateMesstruppsMarker() //Aktualisiert die Anzeige direkt nach einer Änderung
			}
			else
			{
				alertify.error('Es ist ein Fehler aufgetreten.');
			}
		},
		error: function(xhr, desc, err) {
			console.log(xhr);
			console.log("Details: " + desc + "\nError:" + err);
		}
	});//Ende Ajax 
}//Ende Funktion saveKataster

function addRowMesstrupps(tableName, tableNameString, tableID){ //Fügt eine neue Zeile in die Tabelle der Messtrupps ein
	maxRowIDMesstrupp += 1;
	tableName.row.add([ maxRowIDMesstrupp, '','',"<div id = 'cp"+tableID+maxRowIDMesstrupp+"' class='input-group'><input type='text' class='form-control input-lg value='#000000'><span class='input-group-append'><span class='input-group-text colorpicker-input-addon'><i></i></span></span></div>","<div role='button' class='btn btn-default' onclick='"+tableNameString+".row($(this).parents(\"tr\")).remove().draw();'><span class='fa fa-trash'></span></div>"]).draw();
	$('#cp'+tableID+maxRowIDMesstrupp).colorpicker({color:'#000000', format:'hex'});
}


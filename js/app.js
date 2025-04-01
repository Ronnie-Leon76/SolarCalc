$('document').ready(function(){

	var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	// Test
	// var fileurl = 'http://localhost:8082/solarflo/';

	// Live
	var fileurl = 'https://solarcalc.davisandshirtliff.com/';

	// Max Irradiation, Max Flow Rate and Max Head
	var max_irradiation_hrs = 6.5;
	var max_flow_rate = 2.8;
	var max_head = 165;

	// Create Account
	$('.create-btn').on('click', function(e){
		e.preventDefault();
		var entity_types = ['text', 'select-one', 'password', 'checkbox', 'email'];
		var errors = [];
		$('.create form').find(':input').each(function(){
			
			// Check if status type is in the above array
			if($.inArray($(this)[0].type, entity_types) !== -1){
				$(this).removeClass('error-found');
				if($(this)[0].type !== 'checkbox'){
					if($(this).val() === ''){
						errors.push(1);
						$(this).addClass('error-found');
					}
					if($(this)[0].type === 'email'){
						var email_status = re.test(String($(this).val()).toLowerCase());
						if(email_status == false){
							errors.push(1);
							$(this).addClass('error-found');
						}
					}
				} else {
					var check_checked = $(this).is(':checked');
					if(check_checked == false){
						errors.push(1);
						$('.terms-and-conditions').addClass('error-found-text');
					} else {						
						$('.terms-and-conditions').removeClass('error-found-text');
					}
				}				
			}
		});

		if(errors.length == 0){
			$('.create-btn').html('CREATING ACCOUNT <i class="fas fa-cog fa-spin"></i>');
			var createdata = $('.create form').serializeArray();
			$.ajax({
				url: 'data?action=register',
        data: createdata,
        method: 'POST',
        dataType: 'JSON',
        success: function(data, status, xhr){

        	console.log(data);
        	$('.create-btn').html(data.text);

        	if(data.status == 1){
        		$('.create form').find(':input').prop('disabled', true);
        		window.location = 'index?int=1';
        	}
        	
        },
        complete : function(xhr, status){
          console.log(xhr);
        },
        error : function(status){
          console.log(status);
        }
			});
		} else {
			$(this).html('CORRECT ERRORS & TRY AGAIN <i class="fas fa-exclamation-triangle"></i>');
		}
	});

	$('.login-btn').on('click', function(e){
		
		e.preventDefault();
		var errors = [];
		$('.login-form').find(':input').each(function(){
			$(this).removeClass('error-found');
			if($(this).val() === ''){
				errors.push(1);
				$(this).addClass('error-found');
			}
			if($(this)[0].type === 'email'){
				var email_status = re.test(String($(this).val()).toLowerCase());
				if(email_status == false){
					errors.push(1);
					$(this).addClass('error-found');
				}
			}
		});

		console.log(errors);

		if(errors.length == 0){

			$(this).html('SIGNING / LOGGING IN <i class="fas fa-cog fa-spin"></i>');
			var logindata = $('.login-form').serializeArray();
			$.ajax({
				url: 'data?action=login',
				data: logindata,
				method: 'POST',
				dataType: 'JSON',
				success: function(data, status, xhr){
					console.log(data);
					$('.login-btn').html(data.text);
					if(data.status == 1){
						$('.login-form form').find(':input').prop('disabled', true);
						window.location = 'index';
					}
				},
				complete : function(xhr, status, data){
					console.log(xhr);
				},
				error : function(status){
					console.log(status);
				}
			});

		} else {
			$(this).html('CORRECT ERRORS & TRY AGAIN <i class="fas fa-exclamation-triangle"></i>');
		}

	});

	// Sign Out
	$('.log-out').on('click', function(e){
		e.preventDefault();
		$.ajax({
			url: 'data?action=logout',
			success: function(data, status, xhr){
				window.location = 'login';
			},
			complete : function(xhr, status){
				console.log(xhr);
			},
			error : function(status){
				console.log(status);
			}
		});
	});

	// Get Location List
	$('input[name=\"location_name\"]').on('keyup', function(){

		var country_code = $('select[name=\"country\"]').val();
		// $('.result-area .nav-tabs #two-tab').html('Irradiation');
		var location_name = $(this).val();
		$('select[name=\"country\"]').removeClass('error-found');
		if(location_name == ''){
			$('.dropdown-locations .dropdown-menu').html('');
			$('.dropdown-locations .dropdown-menu').css('display', 'none');
		}
		$('input[name=\"location_id\"]').prop('value', '');
		$('input[name=\"location_code\"]').prop('value', '');
		$('input[name=\"latitude_place\"]').prop('value', '');
		$('input[name=\"longitude_place\"]').prop('value', '');
		if(country_code != '' && location_name != ''){
			$.ajax({
				url : 'data?action=getlocations',
				data : {
					country_code : country_code,
					location_name : location_name
				},
				// async : false,
				method : 'POST',
				dataType : 'JSON',
				success: function(data, status, xhr){
					if(data.length > 0){
						$('.dropdown-locations .dropdown-menu').css('display', 'unset');
					} else {
						$('.dropdown-locations .dropdown-menu').css('display', 'none');
					}
					var locationoptions = '';
					for(var j = 0; j < data.length; j++){
						locationoptions += '<a class="dropdown-item" href="#" data-location="' +data[j].location+ '">' +data[j].location_details+ '</a>';
					}
					$('.dropdown-locations .dropdown-menu').html(locationoptions);
				},
				complete: function(xhr, status){
                	// console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		} else {
			$('select[name=\"country\"]').addClass('error-found');
		}
	});

	// Get the details of the location on Click
	$('.dropdown-locations .dropdown-menu').on('click', 'a', function(){

		var location_details = $(this).attr('data-location');
		$('.dropdown-locations .dropdown-menu').hide();
		$('.dropdown-locations .dropdown-menu').html('');
		// Get various Parameters
		var location_information = location_details.split('|');
		// Populate Values
		$('input[name=\"location_id\"]').prop('value', location_information[0]);
		$('input[name=\"location_code\"]').prop('value', location_information[2]);
		$('input[name=\"location_name\"]').prop('value', location_information[1]);
		$('input[name=\"latitude_place\"]').prop('value', location_information[3]);
		$('input[name=\"longitude_place\"]').prop('value', location_information[4]);
		// Check if latitude and longitude are provided
		var latitude_place = location_information[3];
		var longitude_place = location_information[4];
		if(latitude_place == '' && longitude_place == ''){
			// Get Coordinates
			// Run Geodecoding from Google
			$('.get-location').text('Getting Location....');
			var location_form = $('.location-form').serializeArray();
			$.ajax({
				url : 'data?action=getcoordinatesgeodecoding',
				data : {
					location_information : location_form
				},
				method : 'POST',
				dataType : 'JSON',
				success: function(data, status, xhr){
					console.log(data);
					if(data.status == 0){
						$('.get-location').text(data.text);
					} else {
						$('input[name=\"latitude_place\"]').prop('value', data.lat);
						$('input[name=\"longitude_place\"]').prop('value', data.lng);
						$('.get-location').text('Get / Search Location');
						$('.latitude-display').text(data.lat);
						$('.longitude-display').text(data.lng);
					}
					
				},
				complete: function(xhr, status){
                	console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}

	});

	$('.get-location').on('click', function(e){

		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.location-form').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});

		var location_name = $('input[name=\"location_name\"]').val();
		var latitude_place = $('input[name=\"latitude_place\"]').val();
		var longitude_place = $('input[name=\"longitude_place\"]').val();
		var location_id = $('input[name=\"location_id\"]').val();
		if(errors.length == 2 && latitude_place == '' && longitude_place == ''){

			$('.get-location').text('Getting Location....');
			var location_form = $('.location-form').serializeArray();
			$.ajax({
				url : 'data?action=getcoordinatesgeodecoding',
				data : {
					location_information : location_form
				},
				method : 'POST',
				dataType : 'JSON',
				success: function(data, status, xhr){

					console.log(data);
					if(data.status == 0){
						$('.get-location').text(data.text);
					} else {
						$('input[name=\"latitude_place\"]').prop('value', data.lat);
						$('input[name=\"longitude_place\"]').prop('value', data.lng);

						$('input[name=\"latitude_place\"], input[name=\"longitude_place\"]').removeClass('error-found');
						$('.get-location').text('Get / Search Location');
						$('.latitude-display').text(data.lat);
						$('.longitude-display').text(data.lng);
					}
					
				},
				complete: function(xhr, status){
                	console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});

		} else if(errors.length == 0){		

			var mapcenter = {
				lat: parseFloat(latitude_place),
				lng: parseFloat(longitude_place)
			};

			displaymap(mapcenter, location_name);

			var location_form = $('.location-form').serializeArray();

			$.ajax({
				url : 'data?action=setcoordinates',
				data : {
					location_information : location_form
				},
				method : 'POST',
				dataType : 'JSON',
				success: function(data, status, xhr){

					$('input[name=\"location_id\"]').prop('value', data.location_id);
					$('input[name=\"location_code\"]').prop('value', data.location_information.location_code);
					$('.get-location').text('Coordinates Set');
					$('.confirm-location').removeClass('d-none');
					
				},
				complete: function(xhr, status){
                	// console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});

		}

	});

	// On Pasting Coordinates i.e. Latitude and Longitude

	$('input[name=\"longitude_place\"]').on('focusout', function(){
		
		var latitude_place = $('input[name=\"latitude_place\"]').val();
		var longitude_place = $(this).val();

		if(latitude_place != '' && longitude_place != ''){
			console.log(latitude_place);
			console.log(longitude_place);

			// Trigger Get Location details
			getcoordinatedetails(latitude_place, longitude_place);

		}

	});

	// Handle Motor Cable Length
	$('input[name=\"cable_length\"]').on('keypress keyup change focus', function(){
		var cable_length = $(this).val();
		if(cable_length != ''){
			$('.motor-cable').text('(' +cable_length+ 'm)');
			$('.motor-cable-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.4');
		} else {
			$('.motor-cable').text('');
			$('.motor-cable-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.2');
		}
		$('.motor-cable-name').addClass('highlight-name');
	});

	$('input[name=\"cable_length\"]').on('focusout', function(){
		$('.motor-cable-name').removeClass('highlight-name');
	});

	// Sizing Options
	$('input[name=\"sizing_option\"]').on('change', function(){		
		var ischecked = this.checked;
		console.log(ischecked);		
		if(ischecked == true){
			// Hide TDH and Show the Other fields
			$(this).prop('value', 2);
			$('.system-head-name').text('Static Head');
			$('.pipe-length-name').text('Pipe Length');
			$('.total-dynamic-head').addClass('d-none');
			$('.total-dynamic-head input[name=\"total_dynamic_head\"]').prop('required', false);
			$('.sizing-option-field').each(function(){
				$(this).removeClass('d-none');
				$(this).find(':input').prop('required', true);
			});
		} else {
			$(this).prop('value', 1);
			$('.system-head-name').text('Total Dynamic Head');
			$('.pipe-length-name').text('');
			$('.total-dynamic-head').removeClass('d-none');
			$('.total-dynamic-head input[name=\"total_dynamic_head\"]').prop('required', true);
			$('.sizing-option-field').each(function(){
				$(this).addClass('d-none');
				$(this).find(':input').prop('required', false);
			});
		}
	});

	// Handle Pipe Length
	$('input[name=\"pipe_length\"]').on('keypress keyup change focus', function(){
		var pipe_length = $(this).val();
		if(pipe_length != ''){
			$('.pipe-length').text('(' +pipe_length+ 'm)');
			$('.pipe-length-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.7');
		} else {
			$('.pipe-length').text('');
			$('.pipe-length-line').attr('style', 'stroke:rgb(100, 100, 100); stroke-width:0.7');
		}
		$('.pipe-length-name').addClass('highlight-name');
	});

	$('input[name=\"pipe_length\"]').on('focusout', function(){
		$('.pipe-length-name').removeClass('highlight-name');
	});

	// Handle Change in Min Panel Uplift
	$('input[name=\"min_panel_uplift\"]').on('change', function(){

		var min_panel_uplift = $(this).val();
		$(this).parent().parent().find('label span').text(min_panel_uplift);
		var max_panel_uplift_input = $('input[name=\"max_panel_uplift\"]');
		var max_panel_uplift = max_panel_uplift_input.val();
		if(min_panel_uplift > max_panel_uplift){
			max_panel_uplift_input.prop('value', min_panel_uplift);
			max_panel_uplift_input.parent().parent().find('label span').text(min_panel_uplift);
		}
		// saveuplift_details();

	});

	// Handle Change in Max Panel Uplift
	$('input[name=\"max_panel_uplift\"]').on('change', function(){

		var max_panel_uplift = $(this).val();
		$(this).parent().parent().find('label span').text(max_panel_uplift);
		var min_panel_uplift_input = $('input[name=\"min_panel_uplift\"]');
		var min_panel_uplift = min_panel_uplift_input.val();
		if(max_panel_uplift < min_panel_uplift){
			min_panel_uplift_input.prop('value', max_panel_uplift);
			min_panel_uplift_input.parent().parent().find('label span').text(max_panel_uplift);
		}
		// saveuplift_details();

	});

	// Set Custom Panel Uplift
	$('input[name=\"custom_panel_uplift\"]').on('change', function(){

		var ischecked = this.checked;
		var min_panel_uplift_input = $('.minpaneluplift').attr('data-minpaneluplift');
		var max_panel_uplift_input = $('.maxpaneluplift').attr('data-maxpaneluplift');

		if(ischecked){
			$('.panel-uplift').removeClass('d-none');
		} else {
			$('input[name=\"min_panel_uplift\"]').prop('value', min_panel_uplift_input);
			$('input[name=\"min_panel_uplift\"]').parent().parent().find('label span').text(min_panel_uplift_input);
			$('input[name=\"max_panel_uplift\"]').prop('value', max_panel_uplift_input);
			$('input[name=\"max_panel_uplift\"]').parent().parent().find('label span').text(max_panel_uplift_input);
			$('.panel-uplift').addClass('d-none');
		}

	});

	// Show / Hide Borehole & Site Conditions
	$('input[name=\"add_site_borehole_conditions\"]').on('change', function(){
		var ischecked = this.checked;
		if(ischecked == true){
			$('.borehole-conditions, .borehole-conditions-table').removeClass('d-none');
		} else {
			$('.borehole-conditions, .borehole-conditions-table').addClass('d-none');
		}
	});

	// Handle System Head
	$('input[name=\"pipe_head\"]').on('keypress keyup change focus', function(){
		var pipe_head = $(this).val();
		if(pipe_head != ''){
			$('.system-head').text('(' +pipe_head+ 'm)');
			$('.system-head-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.2');
			$('.system-head-polygon').attr('style', 'fill:red');
		} else {
			$('.system-head').text('');
			$('.system-head-line').attr('style', 'stroke:rgb(0, 0, 0); stroke-width:0.2');
			$('.system-head-polygon').attr('style', 'fill:black');
		}
		$('.system-head-name').addClass('highlight-name');
	});

	// Elevation
	$('input[name=\"elevation\"]').on('keypress keyup change focus', function(){
		var water_level = $('input[name=\"water_level\"]').val();
		var elevation = $(this).val();
		if(elevation != ''){
			$('.elevation-height').text('(' +elevation+ 'm)');
			$('.elevation-height-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.2');
			$('.elevation-height-polygon').attr('style', 'fill:red');
			if(water_level != ''){
				var static_head = parseFloat(water_level) + parseFloat(elevation);
				$('input[name=\"pipe_head\"]').prop('value', static_head);
				$('.system-head').text('(' +static_head+ 'm)');
			}
		} else {
			$('.elevation-height').text('');
			$('.elevation-height-line').attr('style', 'stroke:rgb(0, 0, 0); stroke-width:0.2');
			$('.elevation-height-polygon').attr('style', 'fill:black');
		}
		$('.elevation-height-name').addClass('highlight-name');
	});

	$('input[name=\"elevation\"]').on('focusout', function(){
		$('.elevation-height-name').removeClass('highlight-name');
	});

	// Pumping Water Level
	$('input[name=\"water_level\"]').on('keypress keyup change focus', function(){
		var water_level = $(this).val();
		var elevation = $('input[name=\"elevation\"]').val();
		if(water_level != ''){
			$('.water-level').text('(' +water_level+ 'm)');
			$('.water-level-line').attr('style', 'stroke:rgb(255, 255, 255); stroke-width:0.2');
			$('.water-level-polygon').attr('style', 'fill:white');
			if(elevation != ''){
				var static_head = parseFloat(water_level) + parseFloat(elevation);
				$('input[name=\"pipe_head\"]').prop('value', static_head);
				$('.system-head').text('(' +static_head+ 'm)');
			}
		} else {
			$('.water-level').text('');
			$('.water-level-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.2');
			$('.water-level-polygon').attr('style', 'fill:red');
		}
		$('.water-level-name').addClass('highlight-name');
	});

	$('input[name=\"water_level\"]').on('focusout', function(){
		$('.water-level-name').removeClass('highlight-name');
		$('.water-level-polygon').attr('style', 'fill: white');
		$('.water-level-line').attr('style', 'stroke:rgb(255, 255, 255); stroke-width:0.2');
	});

	// Total Dynamic Head
	$('input[name=\"total_dynamic_head\"]').on('keypress keyup change focus', function(){
		var total_dynamic_head = $(this).val();
		if(total_dynamic_head != ''){
			$('.system-head').text('(' +total_dynamic_head+ 'm)');
			$('.system-head-line').attr('style', 'stroke:rgb(255, 0, 0); stroke-width:0.2');
			$('.system-head-polygon').attr('style', 'fill:red');
		} else {
			$('.system-head').text('');
			$('.system-head-line').attr('style', 'stroke:rgb(0, 0, 0); stroke-width:0.2');
			$('.system-head-polygon').attr('style', 'fill:black');
		}
		$('.system-head-name').addClass('highlight-name');
	});

	$('input[name=\"output_option\"]').on('change', function(){

		if($(this).is(':checked')){
			var output_option = $(this).val();
			if(output_option === '1'){
				$('.output-option-text').text('day');
			} else {
				$('.output-option-text').text('hr');
			}
			$('input[name=\"pump_option_output\"]').trigger('change');
		}
	});

	$('input[name=\"pump_option_output\"]').on('change keypress keyup', function(){

		var pump_option_output = $(this).val();
		// Get Irradiation Data
		var irradiation_string = $('input[name=\"average_irradiation\"]').val();

		if(isNaN(pump_option_output) === false && pump_option_output != '' && irradiation_string != ''){
			var output_option = $('input[name=\"output_option\"]:checked').val();
			var sizing_for = $('select[name=\"sizing_for\"]').val();
			var irradiation = irradiation_string.split('|');

			if(sizing_for == '0'){
				var irradiation_sizing = irradiation[12];
			} else if(sizing_for == '1') {
				var irradiation_sizing = Math.max.apply(Math, irradiation);
			} else if(sizing_for == '2') {
				var irradiation_sizing = Math.min.apply(Math, irradiation);
			}

			if(output_option == '1'){
				var pump_output = pump_option_output;
				var pump_output_display = pump_option_output / irradiation_sizing;
				var pump_output_display = pump_output_display.toFixed(2);
				$('input[name=\"pump_output\"]').prop('value', pump_output);
				$('.expected-daily-output').html(' - ' +pump_output_display+ 'm³/hr');
			} else if(output_option == '2') {
				var pump_output = pump_option_output * irradiation_sizing;
				var pump_output = pump_output.toFixed(2);
				$('.expected-daily-output').html(' - ' +pump_output+ 'm³/day');
				$('input[name=\"pump_output\"]').prop('value', pump_output);
			}

		}

	});

	$('select[name=\"sizing_for\"]').on('change', function(){
		$('input[name=\"pump_option_output\"]').trigger('change');
	});

	$('select[name=\"average_irradiation\"]').on('change', function(){
		$('input[name=\"pump_option_output\"]').trigger('change');
	});

	$('.calculate-sizing-surface').on('click', function(e){

		var sizing = $('.sizing-form').serializeArray();
		// console.log(sizing);
		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.sizing').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});

		var country_name = $('.sizing-form input[name=\"country\"]').val();
		var location_name = $('.sizing-form input[name=\"location_name\"]').val();
		var latitude_info = $('.sizing-form input[name=\"latitude_info\"]').val();
		var longitude_info = $('.sizing-form input[name=\"longitude_info\"]').val();
		var average_irradiation = $('.sizing-form input[name=\"average_irradiation\"]').val();

		var motor_type = $('.sizing-form input[name=\"phase\"]:checked').val();
		var hybrid_type = $('.sizing-form input[name=\"hybrid\"]:checked').val();

		if(country_name == '' || country_name == ''  || country_name == ''  || country_name == ''  || country_name == '' ){
			errors.push(1);
			$('.get-gps').addClass('error-found');
		} else {
			$('.get-gps').removeClass('error-found');
		}

		if(motor_type == '1' && hybrid_type == '2'){
			$('.phase-error').tooltip('show');
			errors.push(1);
		} else {
			$('.phase-error').tooltip('hide');
		}

		if(errors.length == 0){

			var sizing_irradiation_str = $('.sizing-form input[name=\"average_irradiation\"]').val();
			var daily_irradiation_str = $('.sizing-form input[name=\"daily_irradiation\"]').val();
			var sizing_for = $('.sizing-form select[name=\"sizing_for\"]').val();

			var sizing_irradiation = sizing_irradiation_str.split('|').map(Number);
			var daily_irradiation = daily_irradiation_str.split('|').map(Number);
			console.log(sizing_irradiation);
			console.log(daily_irradiation);

			if(sizing_for == '0'){
				var solar_output = sizing_irradiation[12];
			} else if(sizing_for == '1'){
				var solar_output = Math.max.apply(Math, sizing_irradiation);
			} else if(sizing_for == '2'){
				var solar_output = Math.min.apply(Math, sizing_irradiation);
			}

			var pump_output = parseFloat($('.sizing-form input[name=\"pump_output\"]').val());
			var flow_rate = pump_output / solar_output;

			var delivery_output = [flow_rate * sizing_irradiation[0], flow_rate * sizing_irradiation[1], flow_rate * sizing_irradiation[2], flow_rate * sizing_irradiation[3], flow_rate * sizing_irradiation[4], flow_rate * sizing_irradiation[5], flow_rate * sizing_irradiation[6], flow_rate * sizing_irradiation[7], flow_rate * sizing_irradiation[8], flow_rate * sizing_irradiation[9], flow_rate * sizing_irradiation[10], flow_rate * sizing_irradiation[11], flow_rate * sizing_irradiation[12]];

			var daily_irradiation_total = 0;
			for(var h = 0; h < daily_irradiation.length; h++){
				daily_irradiation_total = daily_irradiation_total + daily_irradiation[h];
			}

			var daily_output = [];
			for(var h = 0; h < daily_irradiation.length; h++){
				var hourly_output = pump_output * daily_irradiation[h] / daily_irradiation_total;
				daily_output.push(parseFloat(hourly_output.toFixed(2)));
			}

			$('input[name=\"daily_output\"]').attr('value', daily_output.join('|'));
			var location_details = $('.sizing-form input[name=\"location_details\"]').val();
			var sizing_option = $('.sizing-form input[name=\"sizing_option\"]').val();

			console.log(sizing_option);
			if(sizing_option == '2'){

				var flowRateUnitSelectedArray = [1/3.6, 1/60, 1, 1/15.852, 1/13.2];
				var diameterUnitSelectedArray = [1, 25.4];
				var pipeLengthUnitSelectedArray = [1, 0.305];
				var pipeMaterialSelected = [150, 150, 150, 120, 120, 105, 80];

				var nConQ = 1/3.6;
				var nConD = 25.4;
				var nConL = 1;

				var pipe_material = $('.sizing-form select[name=\"pipe_material\"]').val();
				var nHazen = pipeMaterialSelected[parseInt(pipe_material)];

				var nQ = flow_rate * nConQ * 15.852;
				var inner_diameter = $('.sizing-form input[name=\"inner_diameter\"]').val();
				var nD = parseFloat(inner_diameter) * nConD / 25.4;

				var nL = $('.sizing-form input[name=\"pipe_length\"]').val();
				var flMetres = 0.002083 * Math.pow(100/nHazen, 1.85) * Math.pow(nQ, 1.85) / Math.pow(nD, 4.8655) * parseFloat(nL) * nConL;

				var residual_head = 5.00;
				var static_head = $('.sizing-form input[name=\"static_head\"]').val();
				var tdh = parseFloat(static_head) + parseFloat(residual_head) + flMetres;
				$('.sizing-form input[name=\"total_dynamic_head\"]').prop('value', tdh);

			} else {
				var tdh = parseFloat($('.sizing-form input[name=\"total_dynamic_head\"]').val());
			}
			var cable = $('.sizing-form input[name=\"cable_length\"]').val();

			$.ajax({
				url : 'data?action=surface&tdh=' +tdh+ '&flow_rate=' +flow_rate+ '&cable=' +cable,
				data : sizing,
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.calculate-sizing-surface').html('Sizing Pumps <i class="fas fa-asterisk fa-spin"></i>');
				},
				success: function(result, status, xhr){
					console.log(result);
					// Open Output Tab
					$('.result-area .nav-tabs #three-tab').trigger('click');
					$('.calculate-sizing-surface').html('Start / Calculate <i class="fas fa-arrow-right"></i>');
					var resulthtml = '';
					if(result.length > 0){
						resulthtml += '<table class="table table-striped table-bordered table-hover table-sm"><thead><tr><th scope="col" style="width:7%">Pump</th><th scope="col" style="width:5%">Curve</th><th scope="col" style="width:5%">Motor (kW)</th><th scope="col" style="width:12%">Inverter</th><th scope="col" style="width:8%">Solar array power (kW)</th><th scope="col" style="width:5%">Total Peak Voltage</th><th scope="col" style="width:10%">Module Arrangement</th><th scope="col" style="width:7%">Panel Model</th><th scope="col" style="width:5%">Cable</th><th scope="col" style="width:5%">Pump Outlet (")</th><th scope="col" style="width:6%">Suitability</th><th scope="col" style="width:7%">Pump Efficiency</th><th scope="col" style="width:7%">Report</th></tr><tr></tr></thead><tbody>';
						for(var g = 0; g < result.length; g++){
							if(result[g].inverter == 1){
								resulthtml += '<tr><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td rowspan="' +result[g].string_options_count+ '" class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="ac"><i class="fas fa-chart-line"></i></td><td rowspan="' +result[g].string_options_count+ '">' +result[g].motor_kw+ '</td><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].inverter_id+ '" data-productname="' +result[g].inverter_model_name+ '">' +result[g].inverter_model+ '</td><td>' +result[g].string_options[0].total_power+ '</td><td>' +result[g].string_options[0].total_peak_voltage+ '</td><td>' +result[g].string_options[0].number_of_panels_per_string+ ' x ' +result[g].string_options[0].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[0].panel_id+ '" data-productname="' +result[g].string_options[0].panel_model+ '">' +result[g].string_options[0].panel_model+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].cable.cable_name+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].pipe_outlet+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].curve_head.appropriate+ '%</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].efficiency+ '%</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '0" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[0].panel_model+ '|' +result[g].string_options[0].panel_id+ '|' +result[g].string_options[0].number_of_panels_per_string+ '|' +result[g].string_options[0].strings+ '|' +result[g].cable.cable_name+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].cable.cable_name+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[0].short_circuit_current+ '|' +result[g].string_options[0].open_circuit_voltage+ '|' +result[g].equipment_id+ '|surface|' +result[g].tdh+ '|' +result[g].flow_rate+ '|' +result[g].system_details.flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								for(var j = 1; j < result[g].string_options.length; j++){
									resulthtml += '<tr><td>' +result[g].string_options[j].total_power+ '</td><td>' +result[g].string_options[j].total_peak_voltage+ '</td><td>' +result[g].string_options[j].number_of_panels_per_string+ ' x ' +result[g].string_options[j].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[j].panel_id+ '" data-productname="' +result[g].string_options[j].panel_model+ '">' +result[g].string_options[j].panel_model+ '</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '' +[j]+ '" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[j].panel_model+ '|' +result[g].string_options[j].panel_id+ '|' +result[g].string_options[j].number_of_panels_per_string+ '|' +result[g].string_options[j].strings+ '|' +result[g].cable.cable_name+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].cable.cable_name+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[j].short_circuit_current+ '|' +result[g].string_options[j].open_circuit_voltage+ '|' +result[g].equipment_id+ '|surface|' +result[g].tdh+ '|' +result[g].flow_rate+ '|' +result[g].system_details.flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								}
							} else {
								resulthtml += '<tr><td class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="ac"><i class="fas fa-chart-line"></i></td><td>' +result[g].motor_kw+ '</td><td >' +result[g].inverter_model+ '</td><td>--</td><td>--</td><td>--</td><td>--</td><td>' +result[g].cable.cable_name+ '</td><td>' +result[g].pipe_outlet+ '</td><td>-</td><td>-</td><td>--</td></tr>';
							}
						}
						resulthtml += '</tbody></table>';

						$('input[name=\"average_output\"]').attr('value', parseFloat(delivery_output[0].toFixed(2))+ '|' +parseFloat(delivery_output[1].toFixed(2))+ '|' +parseFloat(delivery_output[2].toFixed(2))+ '|' +parseFloat(delivery_output[3].toFixed(2))+ '|' +parseFloat(delivery_output[4].toFixed(2))+ '|' +parseFloat(delivery_output[5].toFixed(2))+ '|' +parseFloat(delivery_output[6].toFixed(2))+ '|' +parseFloat(delivery_output[7].toFixed(2))+ '|' +parseFloat(delivery_output[8].toFixed(2))+ '|' +parseFloat(delivery_output[9].toFixed(2))+ '|' +parseFloat(delivery_output[10].toFixed(2))+ '|' +parseFloat(delivery_output[11].toFixed(2))+ '|' +parseFloat(delivery_output[12].toFixed(2)));

    				$('.output-details').html(sizing[14]['value']+ ' m³/day');
    				var pipe_materials = ['HDPE', 'LDPE', 'uPVC', 'Rubber Lined', 'New Steel', 'New Steel', 'Medium Steel', 'Corroded Steel'];
    				$('.pipe-details').text(pipe_materials[sizing[13]['value']]);
    				$('.pipe-length-details').text(sizing[8]['value']+ 'm');
    				$('.total-dynamic-details').text((Math.round(tdh * 100) / 100)+ 'm');
    				$('.output-average-month').html(parseFloat(delivery_output[12].toFixed(2))+ ' m³');

					} else {
						resulthtml += '<p class="no-results">No pump meets your search criteria<p>';
					}
					$('.output-results').html(resulthtml);
				},
				complete: function(xhr, status){
          console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});

		}

	});

	// Calculate Sizing for AC Pumps
	$('.calculate-sizing').on('click', function(e){

		var sizing = $('.sizing-form').serializeArray();
		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.sizing').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});

		var country_name = $('.sizing-form input[name=\"country\"]').val();
		var location_name = $('.sizing-form input[name=\"location_name\"]').val();
		var latitude_info = $('.sizing-form input[name=\"latitude_info\"]').val();
		var longitude_info = $('.sizing-form input[name=\"longitude_info\"]').val();
		var average_irradiation = $('.sizing-form input[name=\"average_irradiation\"]').val();

		if(country_name == '' || country_name == ''  || country_name == ''  || country_name == ''  || country_name == '' ){
			errors.push(1);
			$('.get-gps').addClass('error-found');
		} else {
			$('.get-gps').removeClass('error-found');
		}

		if(sizing[20].value == '1' && sizing[21].value == '2'){
			$('.phase-error').tooltip('show');
			errors.push(1);
		} else {
			$('.phase-error').tooltip('hide');
		}

		if(errors.length == 0){

			var sizing_irradiation = sizing[4]['value'].split('|');
			if(sizing[17]['value'] == '0'){
				var solar_output = sizing_irradiation[12];
			} else if(sizing[17]['value'] == '1'){
				var solar_output = Math.max.apply(Math, sizing_irradiation);
			} else if(sizing[17]['value'] == '2'){
				var solar_output = Math.min.apply(Math, sizing_irradiation);
			}
			
			var flow_rate = sizing[16]['value'] / solar_output;
			var delivery_output = [flow_rate * sizing_irradiation[0], flow_rate * sizing_irradiation[1], flow_rate * sizing_irradiation[2], flow_rate * sizing_irradiation[3], flow_rate * sizing_irradiation[4], flow_rate * sizing_irradiation[5], flow_rate * sizing_irradiation[6], flow_rate * sizing_irradiation[7], flow_rate * sizing_irradiation[8], flow_rate * sizing_irradiation[9], flow_rate * sizing_irradiation[10], flow_rate * sizing_irradiation[11], flow_rate * sizing_irradiation[12]];

			var location_details = sizing[18]['value'];
			var flowRateUnitSelectedArray = [1/3.6, 1/60, 1, 1/15.852, 1/13.2];
			var diameterUnitSelectedArray = [1, 25.4];
			var pipeLengthUnitSelectedArray = [1, 0.305];
			var pipeMaterialSelected = [150, 150, 150, 120, 120, 105, 80];
			var nConQ = 1/3.6;
			var nConD = 25.4;
			var nConL = 1;
			var nHazen = pipeMaterialSelected[sizing[13]['value']];
			var nQ = flow_rate * nConQ * 15.852;
			var nD = sizing[11]['value'] * nConD / 25.4;
			var nL = sizing[10]['value'];
			var flMetres = 0.002083 * Math.pow(100/nHazen, 1.85) * Math.pow(nQ, 1.85) / Math.pow(nD, 4.8655) * nL * nConL;
			var residual_head = 5.00;
			var tdh = parseFloat(sizing[7]['value']) + parseFloat(residual_head) + flMetres;
			var cable = sizing[5].value;
			console.log(tdh);
			console.log(flow_rate);
			console.log(cable);
			
			$.ajax({
				url : 'data?action=sizing&tdh=' +tdh+ '&flow_rate=' +flow_rate+ '&cable=' +cable,
				data : sizing,
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.calculate-sizing').html('Sizing Pumps <i class="fas fa-asterisk fa-spin"></i>');
				},
				success: function(result, status, xhr){
					console.log(result);
					// Open Output Tab
					$('.result-area .nav-tabs #three-tab').trigger('click');
					$('.calculate-sizing').html('Start / Calculate <i class="fas fa-arrow-right"></i>');
					var resulthtml = '';
					if(result.length > 0){
						resulthtml += '<table class="table table-striped table-bordered table-hover table-sm"><thead><tr><th scope="col" style="width:7%">Pump</th><th scope="col" style="width:5%">Curve</th><th scope="col" style="width:5%">Motor (kW)</th><th scope="col" style="width:12%">Inverter</th><th scope="col" style="width:8%">Solar array power (kW)</th><th scope="col" style="width:5%">Total Peak Voltage</th><th scope="col" style="width:10%">Module Arrangement</th><th scope="col" style="width:7%">Panel Model</th><th scope="col" style="width:5%">Cable</th><th scope="col" style="width:5%">Pump Outlet (")</th><th scope="col" style="width:6%">Suitability</th><th scope="col" style="width:7%">Pump Efficiency</th><th scope="col" style="width:7%">Report</th></tr><tr></tr></thead><tbody>';
						for(var g = 0; g < result.length; g++){
							if(result[g].inverter == 1){
								resulthtml += '<tr><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td rowspan="' +result[g].string_options_count+ '" class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="ac"><i class="fas fa-chart-line"></i></td><td rowspan="' +result[g].string_options_count+ '">' +result[g].motor_kw+ '</td><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].inverter_id+ '" data-productname="' +result[g].inverter_model_name+ '">' +result[g].inverter_model+ '</td><td>' +result[g].string_options[0].total_power+ '</td><td>' +result[g].string_options[0].total_peak_voltage+ '</td><td>' +result[g].string_options[0].number_of_panels_per_string+ ' x ' +result[g].string_options[0].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[0].panel_id+ '" data-productname="' +result[g].string_options[0].panel_model+ '">' +result[g].string_options[0].panel_model+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].cable.cable_name+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].pipe_outlet+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].curve_head.appropriate+ '%</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].efficiency+ '%</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '0" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[0].panel_model+ '|' +result[g].string_options[0].panel_id+ '|' +result[g].string_options[0].number_of_panels_per_string+ '|' +result[g].string_options[0].strings+ '|' +result[g].cable.cable_name+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].cable.cable_name+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[0].short_circuit_current+ '|' +result[g].string_options[0].open_circuit_voltage+ '|' +result[g].equipment_id+ '|ac|' +result[g].tdh+ '|' +result[g].flow_rate+ '|' +result[g].system_details.flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								for(var j = 1; j < result[g].string_options.length; j++){
									resulthtml += '<tr><td>' +result[g].string_options[j].total_power+ '</td><td>' +result[g].string_options[j].total_peak_voltage+ '</td><td>' +result[g].string_options[j].number_of_panels_per_string+ ' x ' +result[g].string_options[j].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[j].panel_id+ '" data-productname="' +result[g].string_options[j].panel_model+ '">' +result[g].string_options[j].panel_model+ '</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '' +[j]+ '" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[j].panel_model+ '|' +result[g].string_options[j].panel_id+ '|' +result[g].string_options[j].number_of_panels_per_string+ '|' +result[g].string_options[j].strings+ '|' +result[g].cable.cable_name+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].cable.cable_name+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[j].short_circuit_current+ '|' +result[g].string_options[j].open_circuit_voltage+ '|' +result[g].equipment_id+ '|ac|' +result[g].tdh+ '|' +result[g].flow_rate+ '|' +result[g].system_details.flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								}
							} else {
								resulthtml += '<tr><td class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="ac"><i class="fas fa-chart-line"></i></td><td>' +result[g].motor_kw+ '</td><td >' +result[g].inverter_model+ '</td><td>--</td><td>--</td><td>--</td><td>--</td><td>' +result[g].cable.cable_name+ '</td><td>' +result[g].pipe_outlet+ '</td><td>-</td><td>-</td><td>--</td></tr>';
							}
						}
						resulthtml += '</tbody></table>';						// 
						$('input[name=\"average_output\"]').attr('value', parseFloat(delivery_output[0].toFixed(2))+ '|' +parseFloat(delivery_output[1].toFixed(2))+ '|' +parseFloat(delivery_output[2].toFixed(2))+ '|' +parseFloat(delivery_output[3].toFixed(2))+ '|' +parseFloat(delivery_output[4].toFixed(2))+ '|' +parseFloat(delivery_output[5].toFixed(2))+ '|' +parseFloat(delivery_output[6].toFixed(2))+ '|' +parseFloat(delivery_output[7].toFixed(2))+ '|' +parseFloat(delivery_output[8].toFixed(2))+ '|' +parseFloat(delivery_output[9].toFixed(2))+ '|' +parseFloat(delivery_output[10].toFixed(2))+ '|' +parseFloat(delivery_output[11].toFixed(2))+ '|' +parseFloat(delivery_output[12].toFixed(2)));		
        				$('.output-details').html(sizing[16]['value']+ ' m³');
        				var pipe_materials = ['HDPE', 'LDPE', 'uPVC', 'Rubber Lined', 'New Steel', 'New Steel', 'Medium Steel', 'Corroded Steel'];
        				$('.pipe-details').text(pipe_materials[sizing[13]['value']]);
        				$('.pipe-length-details').text(nL+ 'm');
        				$('.total-dynamic-details').text((Math.round(tdh * 100) / 100)+ 'm');
        				$('.output-average-month').html(parseFloat(delivery_output[12].toFixed(2))+ ' m³');
					} else {
						resulthtml += '<p class="no-results">No pump meets your search criteria<p>';
					}
					$('.output-results').html(resulthtml);
				},
				complete: function(xhr, status){
                	console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}
	});

	// Simple AC Sizing
	$('.calculate-sizing-simple').on('click', function(e){

		var sizing = $('.sizing-form').serializeArray();
		console.log(sizing);
		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.sizing').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});
		var country_name = $('.sizing-form input[name=\"country\"]').val();
		var location_name = $('.sizing-form input[name=\"location_name\"]').val();
		var latitude_info = $('.sizing-form input[name=\"latitude_info\"]').val();
		var longitude_info = $('.sizing-form input[name=\"longitude_info\"]').val();
		var average_irradiation = $('.sizing-form input[name=\"average_irradiation\"]').val();

		if(country_name == '' || country_name == ''  || country_name == ''  || country_name == ''  || country_name == '' ){
			errors.push(1);
			$('.get-gps').addClass('error-found');
		} else {
			$('.get-gps').removeClass('error-found');
		}

		// if(sizing[20].value == '1' && sizing[21].value == '2'){
		// 	$('.phase-error').tooltip('show');
		// 	errors.push(1);
		// } else {
		// 	$('.phase-error').tooltip('hide');
		// }

		console.log(sizing);

		if(errors.length == 0){

			var sizing_irradiation = sizing[4]['value'].split('|');
			if(sizing[17]['value'] == '0'){
				var solar_output = sizing_irradiation[12];
			} else if(sizing[17]['value'] == '1'){
				var solar_output = Math.max.apply(Math, sizing_irradiation);
			} else if(sizing[17]['value'] == '2'){
				var solar_output = Math.min.apply(Math, sizing_irradiation);
			}
			
			var flow_rate = sizing[16]['value'] / solar_output;
			
			var delivery_output = [flow_rate * sizing_irradiation[0], flow_rate * sizing_irradiation[1], flow_rate * sizing_irradiation[2], flow_rate * sizing_irradiation[3], flow_rate * sizing_irradiation[4], flow_rate * sizing_irradiation[5], flow_rate * sizing_irradiation[6], flow_rate * sizing_irradiation[7], flow_rate * sizing_irradiation[8], flow_rate * sizing_irradiation[9], flow_rate * sizing_irradiation[10], flow_rate * sizing_irradiation[11], flow_rate * sizing_irradiation[12]];

			var location_details = sizing[18]['value'];
			var flowRateUnitSelectedArray = [1/3.6, 1/60, 1, 1/15.852, 1/13.2];
			var diameterUnitSelectedArray = [1, 25.4];
			var pipeLengthUnitSelectedArray = [1, 0.305];
			var pipeMaterialSelected = [150, 150, 150, 120, 120, 105, 80];
			var nConQ = 1/3.6;
			var nConD = 25.4;
			var nConL = 1;
			var nHazen = pipeMaterialSelected[sizing[13]['value']];
			var nQ = flow_rate * nConQ * 15.852;
			var nD = sizing[11]['value'] * nConD / 25.4;
			var nL = sizing[10]['value'];
			var flMetres = 0.002083 * Math.pow(100/nHazen, 1.85) * Math.pow(nQ, 1.85) / Math.pow(nD, 4.8655) * nL * nConL;
			var residual_head = 5.00;
			// var tdh = parseFloat(sizing[7]['value']) + parseFloat(residual_head) + flMetres;
			var tdh = parseFloat(sizing[7]['value']);
			var cable = sizing[5].value;
			console.log(tdh);
			console.log(flow_rate);
			console.log(cable);
			
			$.ajax({
				url : 'data?action=sizing&tdh=' +tdh+ '&flow_rate=' +flow_rate+ '&cable=' +cable,
				data : sizing,
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.calculate-sizing-simple').html('Sizing Pumps <i class="fas fa-asterisk fa-spin"></i>');
				},
				success: function(result, status, xhr){
					console.log(result);
					// Open Output Tab
					$('.result-area .nav-tabs #three-tab').trigger('click');
					$('.calculate-sizing-simple').html('Start / Calculate <i class="fas fa-arrow-right"></i>');
					var resulthtml = '';
					if(result.length > 0){
						resulthtml += '<table class="table table-striped table-bordered table-hover table-sm"><thead><tr><th scope="col" style="width:7%">Pump</th><th scope="col" style="width:5%">Curve</th><th scope="col" style="width:5%">Motor (kW)</th><th scope="col" style="width:12%">Inverter</th><th scope="col" style="width:8%">Solar array power (kW)</th><th scope="col" style="width:5%">Total Peak Voltage</th><th scope="col" style="width:10%">Module Arrangement</th><th scope="col" style="width:7%">Panel Model</th><th scope="col" style="width:5%">Cable</th><th scope="col" style="width:5%">Pump Outlet (")</th><th scope="col" style="width:6%">Suitability</th><th scope="col" style="width:7%">Pump Efficiency</th><th scope="col" style="width:7%">Report</th></tr><tr></tr></thead><tbody>';
						for(var g = 0; g < result.length; g++){
							if(result[g].inverter == 1){
								resulthtml += '<tr><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td rowspan="' +result[g].string_options_count+ '" class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="ac"><i class="fas fa-chart-line"></i></td><td rowspan="' +result[g].string_options_count+ '">' +result[g].motor_kw+ '</td><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].inverter_id+ '" data-productname="' +result[g].inverter_model_name+ '">' +result[g].inverter_model+ '</td><td>' +result[g].string_options[0].total_power+ '</td><td>' +result[g].string_options[0].total_peak_voltage+ '</td><td>' +result[g].string_options[0].number_of_panels_per_string+ ' x ' +result[g].string_options[0].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[0].panel_id+ '" data-productname="' +result[g].string_options[0].panel_model+ '">' +result[g].string_options[0].panel_model+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].cable.cable_name+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].pipe_outlet+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].curve_head.appropriate+ '%</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].efficiency+ '%</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '0" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[0].panel_model+ '|' +result[g].string_options[0].panel_id+ '|' +result[g].string_options[0].number_of_panels_per_string+ '|' +result[g].string_options[0].strings+ '|' +result[g].cable.cable_name+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].cable.cable_name+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[0].short_circuit_current+ '|' +result[g].string_options[0].open_circuit_voltage+ '|' +result[g].equipment_id+ '|ac|' +result[g].tdh+ '|' +result[g].flow_rate+ '|' +result[g].system_details.flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								for(var j = 1; j < result[g].string_options.length; j++){
									resulthtml += '<tr><td>' +result[g].string_options[j].total_power+ '</td><td>' +result[g].string_options[j].total_peak_voltage+ '</td><td>' +result[g].string_options[j].number_of_panels_per_string+ ' x ' +result[g].string_options[j].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[j].panel_id+ '" data-productname="' +result[g].string_options[j].panel_model+ '">' +result[g].string_options[j].panel_model+ '</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '' +[j]+ '" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[j].panel_model+ '|' +result[g].string_options[j].panel_id+ '|' +result[g].string_options[j].number_of_panels_per_string+ '|' +result[g].string_options[j].strings+ '|' +result[g].cable.cable_name+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].cable.cable_name+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[j].short_circuit_current+ '|' +result[g].string_options[j].open_circuit_voltage+ '|' +result[g].equipment_id+ '|ac|' +result[g].tdh+ '|' +result[g].flow_rate+ '|' +result[g].system_details.flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								}
							} else {
								resulthtml += '<tr><td class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="ac"><i class="fas fa-chart-line"></i></td><td>' +result[g].motor_kw+ '</td><td >' +result[g].inverter_model+ '</td><td>--</td><td>--</td><td>--</td><td>--</td><td>' +result[g].cable.cable_name+ '</td><td>' +result[g].pipe_outlet+ '</td><td>-</td><td>-</td><td>--</td></tr>';
							}
						}
						resulthtml += '</tbody></table>';						// 
						$('input[name=\"average_output\"]').attr('value', parseFloat(delivery_output[0].toFixed(2))+ '|' +parseFloat(delivery_output[1].toFixed(2))+ '|' +parseFloat(delivery_output[2].toFixed(2))+ '|' +parseFloat(delivery_output[3].toFixed(2))+ '|' +parseFloat(delivery_output[4].toFixed(2))+ '|' +parseFloat(delivery_output[5].toFixed(2))+ '|' +parseFloat(delivery_output[6].toFixed(2))+ '|' +parseFloat(delivery_output[7].toFixed(2))+ '|' +parseFloat(delivery_output[8].toFixed(2))+ '|' +parseFloat(delivery_output[9].toFixed(2))+ '|' +parseFloat(delivery_output[10].toFixed(2))+ '|' +parseFloat(delivery_output[11].toFixed(2))+ '|' +parseFloat(delivery_output[12].toFixed(2)));		
        				$('.output-details').html(sizing[16]['value']+ ' m³');
        				var pipe_materials = ['HDPE', 'LDPE', 'uPVC', 'Rubber Lined', 'New Steel', 'New Steel', 'Medium Steel', 'Corroded Steel'];
        				$('.pipe-details').text(pipe_materials[sizing[13]['value']]);
        				$('.pipe-length-details').text(nL+ 'm');
        				$('.total-dynamic-details').text((Math.round(tdh * 100) / 100)+ 'm');
        				$('.output-average-month').html(parseFloat(delivery_output[12].toFixed(2))+ ' m³');
					} else {
						resulthtml += '<p class="no-results">No pump meets your search criteria<p>';
					}
					$('.output-results').html(resulthtml);
				},
				complete: function(xhr, status){
                	console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}
	});

	// Calculate Sizing for Solarization
	$('.calculate-sizing-solarization').on('click', function(e){
		var sizing = $('.sizing-form').serializeArray();
		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.sizing').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});
		var country_name = $('.sizing-form input[name=\"country\"]').val();
		var location_name = $('.sizing-form input[name=\"location_name\"]').val();
		var latitude_info = $('.sizing-form input[name=\"latitude_info\"]').val();
		var longitude_info = $('.sizing-form input[name=\"longitude_info\"]').val();
		var average_irradiation = $('.sizing-form input[name=\"average_irradiation\"]').val();
		if(country_name == '' || country_name == ''  || country_name == ''  || country_name == ''  || country_name == '' ){
			errors.push(1);
			$('.get-gps').addClass('error-found');
		} else {
			$('.get-gps').removeClass('error-found');
		}
		if(sizing[10].value == '2' && sizing[5].value == '1'){
			$('.phase-error').tooltip('show');
			errors.push(1);
		} else {
			$('.phase-error').tooltip('hide');
		}
		if(errors.length == 0){
			$.ajax({
				url : 'data?action=solarization',
				data : sizing,
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.calculate-sizing-solarization').html('Sizing Pumps <i class="fas fa-asterisk fa-spin"></i>');
				},
				success: function(result, status, xhr){
					console.log(result);
					// // Open Output Tab
					$('.result-area .nav-tabs #three-tab').trigger('click');
					$('.calculate-sizing-solarization').html('Start / Calculate <i class="fas fa-arrow-right"></i>');
					var resulthtml = '';
					resulthtml += '<table class="table table-striped table-bordered table-hover table-sm"><thead><tr><th scope="col" style="width:20%">Pump</th><th scope="col" style="width:10%">Motor (kW)</th><th scope="col" style="width:15%">Inverter</th><th scope="col" style="width:10%">Solar array power (kW)</th><th scope="col" style="width:10%">Total Peak Voltage</th><th scope="col" style="width:15%">Module Arrangement</th><th scope="col" style="width:15%">Panel Model</th><th scope="col" style="width:10%">Report</th></tr><tr></tr></thead><tbody>';
					if(result.inverter == 1){
						resulthtml += '<tr><td rowspan="' +result.string_options_count+ '">' +result.equipment_model+ '</td><td rowspan="' +result.string_options_count+ '">' +result.motor_kw+ '</td><td rowspan="' +result.string_options_count+ '" class="product" data-product="' +result.inverter_id+ '" data-productname="' +result.inverter_model_name+ '">' +result.inverter_model+ '</td><td>' +result.string_options[0].total_power+ '</td><td>' +result.string_options[0].max_voltage+ '</td><td>' +result.string_options[0].number_of_panels_per_string+ ' x ' +result.string_options[0].strings+ ' string(s)</td><td class="product" data-product="' +result.string_options[0].panel_id+ '" data-productname="' +result.string_options[0].panel_model+ '">' +result.string_options[0].panel_model+ '</td><td><button class="btn btn-secondary btn-sm view-report-solarization view-report0" data-product="' +result.equipment_model+ '|' +result.product_id+ '|' +result.inverter_model_name+ '|' +result.inverter_id+ '|' +result.string_options[0].panel_model+ '|' +result.string_options[0].panel_id+ '|' +result.string_options[0].number_of_panels_per_string+ '|' +result.string_options[0].strings+ '|solarization|-|-|-|' +result.pipe_outlet+ '|' +result.string_options[0].short_circuit_current+ '|' +result.string_options[0].open_circuit_voltage+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
						for(var j = 1; j < result.string_options.length; j++){
							resulthtml += '<tr><td>' +result.string_options[j].total_power+ '</td><td>' +result.string_options[j].max_voltage+ '</td><td>' +result.string_options[j].number_of_panels_per_string+ ' x ' +result.string_options[j].strings+ ' string(s)</td><td class="product" data-product="' +result.string_options[j].panel_id+ '" data-productname="' +result.string_options[j].panel_model+ '">' +result.string_options[j].panel_model+ '</td><td><button class="btn btn-secondary btn-sm view-report-solarization view-report' +[j]+ '" data-product="' +result.equipment_model+ '|' +result.product_id+ '|' +result.inverter_model_name+ '|' +result.inverter_id+ '|' +result.string_options[j].panel_model+ '|' +result.string_options[j].panel_id+ '|' +result.string_options[j].number_of_panels_per_string+ '|' +result.string_options[j].strings+ '|solarization|-|-|-|' +result.pipe_outlet+ '|' +result.string_options[j].short_circuit_current+ '|' +result.string_options[j].open_circuit_voltage+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
						}
					} else {
						resulthtml += '<tr><td class="product" data-product="' +result.product_id+ '" data-productname="' +result.equipment_model+ '">' +result.equipment_model+ '</td><td>' +result.motor_kw+ '</td><td >' +result.inverter_model+ '</td><td>--</td><td>--</td><td>--</td><td>' +result.cable.cable_name+ '</td><td>' +result.pipe_outlet+ '</td><td>-</td><td>-</td><td>--</td></tr>';
					}
					resulthtml += '</tbody></table>';
					$('input[name=\"average_output\"]').attr('value', '');    				
    				$('.output-details').html('');
    				var pipe_materials = ['HDPE', 'LDPE', 'uPVC', 'Rubber Lined', 'New Steel', 'New Steel', 'Medium Steel', 'Corroded Steel'];
    				$('.pipe-details').text('');
    				$('.pipe-length-details').text('m');
    				$('.total-dynamic-details').text('');
    				$('.output-average-month').html('');
					$('.output-results').html(resulthtml);
				},
				complete: function(xhr, status){
                	console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}
	});
	// Calculate Sizing for DC Pumps
	$('.calculate-sizing-dc').on('click', function(e){
		var sizing = $('.sizing-form').serializeArray();
		console.log(sizing);
		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.sizing').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});
		var country_name = $('.sizing-form input[name=\"country\"]').val();
		var location_name = $('.sizing-form input[name=\"location_name\"]').val();
		var latitude_info = $('.sizing-form input[name=\"latitude_info\"]').val();
		var longitude_info = $('.sizing-form input[name=\"longitude_info\"]').val();
		var average_irradiation = $('.sizing-form input[name=\"average_irradiation\"]').val();
		if(country_name == '' || country_name == ''  || country_name == ''  || country_name == ''  || country_name == '' ){
			errors.push(1);
			$('.get-gps').addClass('error-found');
		} else {
			$('.get-gps').removeClass('error-found');
		}

		// Limit Maximum Static Head
		if(sizing[7].value != ''){
			if(parseFloat(sizing[7].value) >= max_head){
				$('input[name=\"' +sizing[7].name+ '\"]').addClass('error-found');
				$('.static-head-tooltip').tooltip('show');
				errors.push(1);
			} else {
				$('input[name=\"' +sizing[7].name+ '\"]').removeClass('error-found');
				$('.static-head-tooltip').tooltip('hide');
			}
		}
		// Limit Maximum Flow per Day
		if(sizing[14].value != ''){
			if(parseFloat(sizing[14].value) >= max_irradiation_hrs * max_flow_rate){
				$('input[name=\"' +sizing[14].name+ '\"]').addClass('error-found');
				$('.output-tooltip').tooltip('show');
				errors.push(1);
			} else {
				$('input[name=\"' +sizing[14].name+ '\"]').removeClass('error-found');
				$('.output-tooltip').tooltip('hide');
			}
		}
		if(errors.length == 0){			
			var sizing_irradiation = sizing[4]['value'].split('|');
			if(sizing[15]['value'] == '0'){
				var solar_output = sizing_irradiation[12];
			} else if(sizing[15]['value'] == '1'){
				var solar_output = Math.max.apply(Math, sizing_irradiation);
			} else if(sizing[15]['value'] == '2'){
				var solar_output = Math.min.apply(Math, sizing_irradiation);
			}			
			var flow_rate = sizing[14]['value'] / solar_output;
			var delivery_output = [flow_rate * sizing_irradiation[0], flow_rate * sizing_irradiation[1], flow_rate * sizing_irradiation[2], flow_rate * sizing_irradiation[3], flow_rate * sizing_irradiation[4], flow_rate * sizing_irradiation[5], flow_rate * sizing_irradiation[6], flow_rate * sizing_irradiation[7], flow_rate * sizing_irradiation[8], flow_rate * sizing_irradiation[9], flow_rate * sizing_irradiation[10], flow_rate * sizing_irradiation[11], flow_rate * sizing_irradiation[12]];
			var location_details = sizing[16]['value'];
			var flowRateUnitSelectedArray = [1/3.6, 1/60, 1, 1/15.852, 1/13.2];
			var diameterUnitSelectedArray = [1, 25.4];
			var pipeLengthUnitSelectedArray = [1, 0.305];
			var pipeMaterialSelected = [150, 150, 150, 120, 120, 105, 80];
			var nConQ = 1/3.6;
			var nConD = 25.4;
			var nConL = 1;
			var nHazen = pipeMaterialSelected[sizing[11]['value']];
			var nQ = flow_rate * nConQ * 15.852;
			var nD = sizing[9]['value'] * nConD / 25.4;
			var nL = sizing[8]['value'];
			var flMetres = 0.002083 * Math.pow(100/nHazen, 1.85) * Math.pow(nQ, 1.85) / Math.pow(nD, 4.8655) * nL * nConL;
			var residual_head = 5.00;
			var tdh = parseFloat(sizing[7]['value']) + parseFloat(residual_head) + flMetres;
			var cable = sizing[5].value;
			console.log(tdh);
			console.log(flow_rate);
			console.log(cable);
			$.ajax({
				url : 'data?action=sizingdc&tdh=' +tdh+ '&flow_rate=' +flow_rate+ '&cable=' +cable,
				data : sizing,
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.calculate-sizing-dc').html('Sizing Pumps <i class="fas fa-asterisk fa-spin"></i>');
				},
				success: function(result, status, xhr){
					console.log(result);
					// Open Output Tab
					$('.result-area .nav-tabs #three-tab').trigger('click');
					$('.calculate-sizing-dc').html('Start / Calculate <i class="fas fa-arrow-right"></i>');
					var resulthtml = '';
					if(result.length > 0){
						resulthtml += '<table class="table table-striped table-bordered table-hover table-sm"><thead><tr><th scope="col" style="width:15%">Pump</th><th scope="col" style="width:7%">Curve</th><th scope="col" style="width:7%">Motor (W)</th><th scope="col" style="width:15%">Solar array power (W)</th><th scope="col" style="width:15%">Module Arrangement</th><th scope="col" style="width:10%">Panel Model</th><th scope="col" style="width:8%">Cable</th><th scope="col" style="width:5%">Pipe (")</th><th scope="col" style="width:10%">Suitability</th><th scope="col" style="width:7%">Report</th></tr><tr></tr></thead><tbody>';
						for(var g = 0; g < result.length; g++){
								resulthtml += '<tr><td rowspan="' +result[g].string_options_count+ '" class="product" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '">' +result[g].equipment_model+ '</td><td rowspan="' +result[g].string_options_count+ '" class="pump-curve" data-product="' +result[g].equipment_id+ '" data-tdh="' +result[g].tdh+ '" data-flow="' +result[g].flow_rate+ '" data-type="dc"><i class="fas fa-chart-line"></i></td><td rowspan="' +result[g].string_options_count+ '">' +result[g].motor_w+ '</td><td>' +result[g].string_options[0].total_power+ '</td><td>' +result[g].string_options[0].number_of_panels_per_string+ ' x ' +result[g].string_options[0].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[0].panel_id+ '" data-productname="' +result[g].string_options[0].panel_model+ '">' +result[g].string_options[0].panel_model+ '</td><td>' +result[g].string_options[0].cable+ 'mm<sup>2</sup></td><td rowspan="' +result[g].string_options_count+ '">' +result[g].pipe_outlet+ '</td><td rowspan="' +result[g].string_options_count+ '">' +result[g].curve_head.appropriate+ '%</td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '0" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[0].panel_model+ '|' +result[g].string_options[0].panel_id+ '|' +result[g].string_options[0].number_of_panels_per_string+ '|' +result[g].string_options[0].strings+ '|' +result[g].string_options[0].cable+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].string_options[0].cable+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[0].short_circuit_current+ '|' +result[g].string_options[0].open_circuit_voltage+ '|' +result[g].equipment_id+ '|dc|' +result[g].tdh+ '|' +result[g].flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								for(var j = 1; j < result[g].string_options.length; j++){
									resulthtml += '<tr><td>' +result[g].string_options[j].total_power+ '</td><td>' +result[g].string_options[j].number_of_panels_per_string+ ' x ' +result[g].string_options[j].strings+ ' string(s)</td><td class="product" data-product="' +result[g].string_options[j].panel_id+ '" data-productname="' +result[g].string_options[j].panel_model+ '">' +result[g].string_options[j].panel_model+ '</td><td>' +result[g].string_options[j].cable+ 'mm<sup>2</sup></td><td><button class="btn btn-secondary btn-sm view-report view-report' +[g]+ '' +[j]+ '" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].inverter_model_name+ '|' +result[g].inverter_id+ '|' +result[g].string_options[j].panel_model+ '|' +result[g].string_options[j].panel_id+ '|' +result[g].string_options[j].number_of_panels_per_string+ '|' +result[g].string_options[j].strings+ '|' +result[g].string_options[j].cable+ '|' +result[g].curve_head.appropriate+ '|' +result[g].efficiency+ '|' +result[g].string_options[j].cable+ '|' +result[g].pipe_outlet+ '|' +result[g].string_options[j].short_circuit_current+ '|' +result[g].string_options[j].open_circuit_voltage+ '|' +result[g].equipment_id+ '|dc|' +result[g].tdh+ '|' +result[g].flow_rate+ '" data-toggle="tooltip" title="View Report"><i class="far fa-eye"></i></button></td></tr>';
								}
						}
						resulthtml += '</tbody></table>';
					// 	// Show Output Graph
						Highcharts.chart('output-results-chart', {
			    			chart: {
			    				type: 'column'
			    			},
			    			title: {
			    				text: 'Output - ' +location_details
			    			},
			    			xAxis: {
			    				categories: [
			        				'JAN',
			        				'FEB',
			        				'MAR',
			        				'APR',
			        				'MAY',
			        				'JUN',
			        				'JUL',
			        				'AUG',
			        				'SEP',
			        				'OCT',
			        				'NOV',
			        				'DEC',
			        				'AVG'
			        				],
			    				crosshair: true
			    			},
			    			yAxis: {
			    				min: 0,
						        title: {
						            text: 'Q[m<sup>3<sup>/day]'
						        }
						    },
						    tooltip: {
						        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
						        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
						            '<td style="padding:0"><b>{point.y:.1f} m³/day</b></td></tr>',
						        footerFormat: '</table>',
						        shared: true,
						        useHTML: true
						    },
						    colors : ['#029bf4'],
						    plotOptions: {
						        column: {
						            pointPadding: 0.2,
						            borderWidth: 0,
						            dataLabels: {
						                enabled: true,
						                color: 'black'
						            },
						            colorByPoint : true
						        }
						    },
						    series: [{
						        name: location_details,
						        data: [parseFloat(delivery_output[0].toFixed(2)), parseFloat(delivery_output[1].toFixed(2)), parseFloat(delivery_output[2].toFixed(2)), parseFloat(delivery_output[3].toFixed(2)), parseFloat(delivery_output[4].toFixed(2)), parseFloat(delivery_output[5].toFixed(2)), parseFloat(delivery_output[6].toFixed(2)), parseFloat(delivery_output[7].toFixed(2)), parseFloat(delivery_output[8].toFixed(2)), parseFloat(delivery_output[9].toFixed(2)), parseFloat(delivery_output[10].toFixed(2)), parseFloat(delivery_output[11].toFixed(2)), parseFloat(delivery_output[12].toFixed(2))]
						    }]
						});
						$('input[name=\"average_output\"]').attr('value', parseFloat(delivery_output[0].toFixed(2))+ '|' +parseFloat(delivery_output[1].toFixed(2))+ '|' +parseFloat(delivery_output[2].toFixed(2))+ '|' +parseFloat(delivery_output[3].toFixed(2))+ '|' +parseFloat(delivery_output[4].toFixed(2))+ '|' +parseFloat(delivery_output[5].toFixed(2))+ '|' +parseFloat(delivery_output[6].toFixed(2))+ '|' +parseFloat(delivery_output[7].toFixed(2))+ '|' +parseFloat(delivery_output[8].toFixed(2))+ '|' +parseFloat(delivery_output[9].toFixed(2))+ '|' +parseFloat(delivery_output[10].toFixed(2))+ '|' +parseFloat(delivery_output[11].toFixed(2))+ '|' +parseFloat(delivery_output[12].toFixed(2)));	
        				$('.output-details').html(sizing[14]['value']+ ' m³');
        				var pipe_materials = ['HDPE', 'LDPE', 'uPVC', 'Rubber Lined', 'New Steel', 'New Steel', 'Medium Steel', 'Corroded Steel'];
        				$('.pipe-details').text(pipe_materials[sizing[11]['value']]);
        				$('.pipe-length-details').text(nL+ 'm');
        				$('.total-dynamic-details').text((Math.round(tdh * 100) / 100)+ 'm');
        				$('.output-average-month').html(Math.round(delivery_output[12])+ ' m³');

					} else {
						resulthtml += '<p class="no-results">No pump meets your search criteria<p>';
					}
					$('.output-results').html(resulthtml);
				},
				complete: function(xhr, status){
                	// console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}
	});
	// SUNFLO Sizing
	$('.calculate-sizing-sunflo').on('click', function(e){
		var sizing = $('.sizing-form').serializeArray();
		e.preventDefault();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.sizing').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});
		var country_name = $('.sizing-form input[name=\"country\"]').val();
		var location_name = $('.sizing-form input[name=\"location_name\"]').val();
		var latitude_info = $('.sizing-form input[name=\"latitude_info\"]').val();
		var longitude_info = $('.sizing-form input[name=\"longitude_info\"]').val();
		var average_irradiation = $('.sizing-form input[name=\"average_irradiation\"]').val();
		if(country_name == '' || country_name == ''  || country_name == ''  || country_name == ''  || country_name == '' ){
			errors.push(1);
			$('.get-gps').addClass('error-found');
		} else {
			$('.get-gps').removeClass('error-found');
		}
		if(errors.length == 0){
			$.ajax({
				url : 'data?action=sizingsunflo',
				data : sizing,
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.calculate-sizing-sunflo').html('Sizing Pumps <i class="fas fa-asterisk fa-spin"></i>');
				},
				success: function(result, status, xhr){
					console.log(result);
					// Open Output Tab
					$('.result-area .nav-tabs #three-tab').trigger('click');
					$('.calculate-sizing-sunflo').html('Start / Calculate <i class="fas fa-arrow-right"></i>');
					var resulthtml = '';
					if(result.length > 0){
						resulthtml += '<table class="table table-striped table-bordered table-hover"><thead><tr><th scope="col" style="width:5%">#</th><th scope="col" style="width:27%;text-align:left">Pump</th><th scope="col" style="width:7%">Curve</th><th scope="col" style="width:5%">Motor (W)</th><th scope="col" style="width:5%">PV Modules</th><th scope="col" style="width:10%">Module Model</th><th scope="col" style="width:10%">Cable Length, 2.5mm<sup>2</sup></th><th scope="col" style="width:10%">Indicative Performance, m³/day</th><th scope="col" style="width:10%">Pump Outlet (")</th><th scope="col" style="width:10%">Report</th></tr></thead><tbody>';
						for(var g = 0; g < result.length; g++){
							resulthtml += '<tr><td>' +result[g].row+ '</td><td class="product" style="text-align:left" data-product="' +result[g].product_id+ '" data-productname="' +result[g].equipment_model+ '"><strong>' +result[g].equipment_model+ '</strong></td><td class="pump-curve-sunflo" data-product="' +result[g].equipment_id+ '" data-head="' +result[g].pipe_head+ '" data-flow="' +result[g].system_flow+ '" data-type="sunflo"><i class="fas fa-chart-line"></i></td><td>' +result[g].motor_w+ '</td><td>' +result[g].panel_count+ '</td><td class="product" data-product="' +result[g].panel_id+ '" data-productname="' +result[g].panel_model+ '">' +result[g].panel_model+ '</td><td>' +result[g].cable_length_2_5mm+ 'm</td><td>' +result[g].flow_per_day+ '</td><td>' +result[g].pump_outlet+ '</td><td><button class="btn btn-light view-sunflo-report view-sunflo-report' +[g]+ '0" data-product="' +result[g].equipment_model+ '|' +result[g].product_id+ '|' +result[g].panel_model+ '|' +result[g].panel_id+ '|' +result[g].panel_count+ '|' +result[g].cable_length_2_5mm+ '|' +result[g].pipe_head+ '|' +result[g].pump_outlet+ '|' +result[g].product_id+ '|sunflo|' +result[g].system_flow+ '|' +result[g].equipment_id+ '" data-toggle="tooltip" title="View Report"><i class="far fa-file-pdf"></i></button></td></tr>';
						}
					} else {
						resulthtml += '<p class="no-results">No pump meets your search criteria<p>';
					}
					$('.output-results').html(resulthtml);
				},
				complete: function(xhr, status){
                	// console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}
		
	});

	// Start Loading Google Map
	$('.get-gps').on('click', function(){

		// Set current location details
		var mapcenter = {
			lat: -1.2920659000000123,
			lng: 36.821989115344195
		};
		var location_name = 'Drag this icon to your desired location and click on <strong>Get / Search Location</strong>';
		$('.latitude-display').text(mapcenter.lat);
		$('.longitude-display').text(mapcenter.lng);
		displaymap(mapcenter, location_name);
		$('.confirm-location').addClass('d-none');
		$('.confirm-location').text('Confirm');
		// Set the text on the Button to Get Location
		$(this).html('<i class="fas fa-map-marker-alt"></i> Get Location');
		
	});

	$('.confirm-location').on('click', function(){
		$('.result-area .nav-tabs #two-tab').html('Irradiation');
		var latitude = $('.latitude-display').text();
		var longitude = $('.longitude-display').text();
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.location-form').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});
		if(errors.length == 0){
			var location_form = $('.location-form').serializeArray();
			$.ajax({
				url : 'data?action=getirradiationoffline',
				data : {
					location_form : location_form
				},
				method : 'POST',
				dataType : 'JSON',
				beforeSend: function(){
					$('.confirm-location').text('Fetching Irradiation');
				},
				success: function(result, status, xhr){

					console.log(result);
					$('.confirm-location').text(result.status.text);

					if(result.status.status == 1){

						$('.googlemaps-modal').modal('hide');
						// console.log(result);
		        		$('.result-area .nav-tabs #two-tab').html('Irradiation <i class="fas fa-check text-success"></i>');

		        		$('input[name=\"latitude_info\"]').attr('value', result.gps.lat);
		        		$('input[name=\"longitude_info\"]').attr('value', result.gps.lng);

		        		$('input[name=\"country\"]').prop('value', result.country_name);
		        		$('input[name=\"location_name\"]').prop('value', result.location_name);

		        		var DNR = result.details.DNR;

		        		Highcharts.chart('results-chart', {
		        			chart: {
		        				type: 'column'
		        			},
		        			title: {
		        				text: 'Direct Normal Irradiation'
		        			},
		        			subtitle: {
		        				text: 'Source: NASA.gov POWER Single Point Data Access'
		        			},
		        			xAxis: {
		        				categories: [
		            				'JAN',
		            				'FEB',
		            				'MAR',
		            				'APR',
		            				'MAY',
		            				'JUN',
		            				'JUL',
		            				'AUG',
		            				'SEP',
		            				'OCT',
		            				'NOV',
		            				'DEC',
		            				'AVG'
		            				],
		        				crosshair: true
		        			},
		        			yAxis: {
		        				min: 0,
						        title: {
						            text: 'kW-hr/m<sup>2<sup>/day'
						        }
						    },
						    tooltip: {
						        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
						        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
						            '<td style="padding:0"><b>{point.y:.1f} kW-hr/m<sup>2</sup>/day</b></td></tr>',
						        footerFormat: '</table>',
						        shared: true,
						        useHTML: true
						    },
						    colors : ['#fad72b'],
						    plotOptions: {
						        column: {
						            pointPadding: 0.2,
						            borderWidth: 0,
						            dataLabels: {
						                enabled: true,
						                color: 'black'
						            },
						            colorByPoint : true
						        }
						    },
						    series: [{
						        name: result.location_name,
						        data: [DNR[1], DNR[2], DNR[3], DNR[4], DNR[5], DNR[6], DNR[7], DNR[8], DNR[9], DNR[10], DNR[11], DNR[12], DNR[13]]

						    }]
						});

		        		// Also display the same on the Report
		        		Highcharts.chart('irradiation-data', {
		        			chart: {
		        				type: 'column',
										height: 500
		        			},
		        			title: {
		        				text: 'Direct Normal Irradiation'
		        			},
		        			subtitle: {
		        				text: 'Source: NASA.gov POWER Single Point Data Access'
		        			},
		        			xAxis: {
		        				categories: [
		            				'JAN',
		            				'FEB',
		            				'MAR',
		            				'APR',
		            				'MAY',
		            				'JUN',
		            				'JUL',
		            				'AUG',
		            				'SEP',
		            				'OCT',
		            				'NOV',
		            				'DEC',
		            				'AVG'
		            				],
		        				crosshair: true
		        			},
		        			yAxis: {
		        				min: 0,
						        title: {
						            text: 'kW-hr/m<sup>2<sup>/day'
						        }
						    },
						    tooltip: {
						        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
						        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
						            '<td style="padding:0"><b>{point.y:.1f} kW-hr/m<sup>2</sup>/day</b></td></tr>',
						        footerFormat: '</table>',
						        shared: true,
						        useHTML: true
						    },
						    colors : ['#fad72b'],
						    plotOptions: {
						        column: {
						            pointPadding: 0.2,
						            borderWidth: 0,
						            dataLabels: {
						                enabled: true,
						                color: 'black'
						            },
						            colorByPoint : true
						        }
						    },
						    series: [{
						        name: result.location_name,
						        data: [DNR[1], DNR[2], DNR[3], DNR[4], DNR[5], DNR[6], DNR[7], DNR[8], DNR[9], DNR[10], DNR[11], DNR[12], DNR[13]]

						    }]
						});

		        		$('input[name=\"average_irradiation\"]').attr('value', DNR[1]+ '|' +DNR[2]+ '|' +DNR[3]+ '|' +DNR[4]+ '|' +DNR[5]+ '|' +DNR[6]+ '|' +DNR[7]+ '|' +DNR[8]+ '|' +DNR[9]+ '|' +DNR[10]+ '|' +DNR[11]+ '|' +DNR[12]+ '|' +DNR[13]);
		        		$('input[name=\"pump_option_output\"]').trigger('change');

		        		// Remove Require from the COuntry and Location Dropdowns
		        		$('select[name=\"country\"], select[name=\"location_code\"]').removeAttr('required');
		        		$('select[name=\"location_code\"]').prop('disabled', false);
		        		$('input[name=\"location_details\"]').attr('value', result.location_name);

		        		$('.location-details').text(result.location_name+ ', ' + '(' +(Math.round(result.gps.lat * 100000) / 100000)+ ',' +parseFloat(result.gps.lng).toFixed(5)+ ')');

		        		// Set Get GPS button to Location Ok
		        		$('.get-gps').removeClass('error-found');
		        		$('.get-gps').html(result.location_name +' <i class="fas fa-check text-success"></i>');

		        	}

		        var daily_irradiation_categories = [];
		        var daily_irradiation_values = [];

		        for(var j = 0; j < result.daily_irradiation.length; j++){
		        	daily_irradiation_categories.push(result.daily_irradiation[j].real_time);
		        	daily_irradiation_values.push(result.daily_irradiation[j].avg_rad);
		        }

		        // Display Daily Irradiation
		        Highcharts.chart('results-daily-irradiation-chart', {
		        	chart: {
				        height : 500					        
					    },
					    title: {
					        text: 'Daily Irradiation'
					    },
					    subtitle: {
					        text: 'Source: NASA.gov POWER Single Point Data Access'
					    },
					    xAxis: {
					        categories: daily_irradiation_categories,
					        crosshair: true
					    },
					    yAxis: {
					        min: 0,
					        title: {
					            text: 'Irradiation (W-hr/m²/hr)'
					        }
					    },
					    tooltip: {
					        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
					        pointFormat: '<tr><td style="color:{series.color};padding:0">{point.y}: </td>' +
					            '<td style="padding:0"><b>W-hr/m²/hr</b></td></tr>',
					        footerFormat: '</table>',
					        shared: true,
					        useHTML: true
					    },
					    plotOptions: {
					        column: {
					            pointPadding: 0.2,
					            borderWidth: 0,
					            dataLabels: {
					            	enabled: true,
					            	color: 'black'
					            }
					        }
					    },
					    series: [{
					    		type: 'column',
					        name: result.location_name,
					        data: daily_irradiation_values,
					        color: '#fad82b'
					    }]
		        });

		        // Display Daily Irradiation
		        Highcharts.chart('daily-irradiation-chart', {
		        	chart: {
				        height : 500					        
					    },
					    title: {
					        text: 'Daily Irradiation'
					    },
					    subtitle: {
					        text: 'Source: NASA.gov POWER Single Point Data Access'
					    },
					    xAxis: {
					        categories: daily_irradiation_categories,
					        crosshair: true
					    },
					    yAxis: {
					        min: 0,
					        title: {
					            text: 'Irradiation (W-hr/m²/hr)'
					        }
					    },
					    tooltip: {
					        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
					        pointFormat: '<tr><td style="color:{series.color};padding:0">{point.y}: </td>' +
					            '<td style="padding:0"><b>W-hr/m²/hr</b></td></tr>',
					        footerFormat: '</table>',
					        shared: true,
					        useHTML: true
					    },
					    plotOptions: {
					        column: {
					            pointPadding: 0.2,
					            borderWidth: 0,
					            dataLabels: {
					            	enabled: true,
					            	color: 'black'
					            }
					        }
					    },
					    series: [{
					    		type: 'column',
					        name: result.location_name,
					        data: daily_irradiation_values,
					        color: '#fad82b'
					    }]
		        });

		        $('input[name=\"daily_irradiation\"]').attr('value', daily_irradiation_values.join('|'));

				},
				complete: function(xhr, status){
					// $('.get-gps').html('<i class="fas fa-map-marker-alt"></i>');
					console.log(xhr);
				},
				error: function(status){
					console.log(status);
				}
			});
		}
	});

	// For Solarization, get value of Phase option selected
	$('input[name=\"phase\"]').on('change', function(){
		var ischecked = $(this).is(':checked');
		if(ischecked == true){
			var checkvalue = $(this).val();
			$.ajax({
				url: 'data?action=getmotorpowers',
                data: {
                	phase : checkvalue
                },
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){
                	var motorshtml = '<option value="">-- Select Motor Size --</option>';
                	for(var j = 0; j < data.length; j++){
                		motorshtml += '<option value="' +data[j]+ '">' +data[j]+ 'kW</option>';
                	}
                	$('.pump-motor').html(motorshtml);
                },
                complete : function(xhr, status){
                  // console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});
		}
	});

	// Start Tool Tips
	$('[data-toggle="tooltip"]').tooltip();

	// Check if checkbox is checked
	$('input[name=\"custom_module\"]').on('change', function(){
		var ischecked = this.checked;

		// Open Modal and Add the List of Custom Modules
		if(ischecked){
			$('.account-settings .modal-header h5').text('Custom Modules');
			$.ajax({
				url: 'pages/custommodules.php',
        method: 'POST',
        dataType: 'html',
        success: function(data, status, xhr){
        	$('.account-settings .modal-body').html(data);
        	$('.account-settings').modal();
        },
        complete : function(xhr, status){
          console.log(xhr);
        },
        error : function(status){
          console.log(status);
        }
			});			
		}
		
	});

	// Add Custom Module
	$('.account-settings').on('click', '.add-cstom-module', function(e){
		
		e.preventDefault();
		var errors = [];
		$('.account-settings').find('.custom-module-form :input').each(function(){
			if($(this)[0].required){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			}
		});

		if(errors.length == 0){
			$('.account-settings').find('.add-cstom-module').text('Adding...');
			var custommodule = $('.account-settings').find('input').serialize();
			$.ajax({
				url: 'data?action=addcustommodule',
                data: custommodule,
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){
                	$('.account-settings').find('.add-cstom-module').text(data.text);
                	if(data.status == 1){
                		$('.account-settings').find('.custom-module-form :input').each(function(){
                			if($(this)[0].type == 'text'){
                				$(this).val('');
                			}
					});
                	}
                	console.log(data);
                },
                complete : function(xhr, status){
                  console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});
		}
	});

	// Add Module
	$('.add-module').on('click', function(e){
		// Check if there is another Module already in the DOM
		e.preventDefault();
		var modulescount = $('.sidebar-sticky').find('.form-group-last-row');
		var modulescountrecord = modulescount.length + 1;
		if(modulescountrecord < 3){
			var modulehtml = '<div class="form-group row form-group-last-row form-group-last-row' +modulescountrecord+ '">'
								+ '<label class="col-md-4">#' +modulescountrecord+ ' Module <i class="far fa-trash-alt float-right"></i></label>'
									+ '<div class="col-md-8">'
										+ '<input type="number" class="form-control" name="custom_w[]" placeholder="W" required="required">'
										+ '<input type="number" class="form-control" name="custom_vmppt[]" placeholder="Vmppt" required="required">'
										+ '<input type="number" class="form-control" name="custom_voc[]" placeholder="Voc" required="required">'
									+ '</div>'
								+ '</div>';
			// Check if the class form-group-last-row exists
			if(modulescount.length > 0){
				$(modulehtml).insertAfter('.form-group-last-row' +modulescount.length);
			} else {
				$(modulehtml).insertAfter('.last-row');
			}			
		}
	});

	$('.account-settings').on('click', '.check-availability', function(e){
		e.preventDefault();
		$(this).html('<i class="fas fa-cog fa-spin"></i>');
		var part_number = $(this).attr('data-partnumber');
		var part_name = $(this).attr('data-partname');
		var thisbtn = $(this);
		$.ajax({
			url: 'data?action=checkpartavailability',
      data: {
      	part_number : part_number,
      	part_name : part_name
      },
      method: 'POST',
      dataType: 'JSON',
      success: function(data, status, xhr){
      	console.log(data);
      	thisbtn.html(data.text);
      	thisbtn.attr('title', data.title);            	
      },
      complete : function(xhr, status){
        console.log(xhr);
      },
      error : function(status){
        console.log(status);
      }
		});
	});

	// Delete Custom Module
	$('.account-settings').on('click', '.delete-panel', function(){

		var panel_id = $(this).attr('data-panel');
		var panel_name = $(this).parent().text();

		var panel = $(this).parent().parent();
		var panel_icon = $(this);

		var confirm_action = confirm('Please confirm that you would like to delete ' +panel_name+ ' from your custom modules?');

		if(confirm_action == true){

			$.ajax({
				url: 'data?action=deletecustommodule',
                data: {
                	panel_id : panel_id,
                },
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){

                	if(data.status == 1){
                		panel.remove();
                	} else {
                		panel_icon.removeClass('far fa-trash-alt');
                		panel_icon.addClass('far fa-question-circle');
                		panel_icon.attr('title', data.text);
                	}

                	console.log(data);

                },
                complete : function(xhr, status){
                  console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});

		}
	});

	// Remove custom module
	$('.sidebar-sticky').on('click', '.fa-trash-alt', function(){
		$(this).parent().parent().remove();
	});

	$('.hide-overlay').on('click', function(){
		$('.modal-body-overlay').addClass('d-none');
	});

	$('.set-coordinates').on('click', function(e){
		var errors = [];
		var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
		$('.set-coordinates-form').find(':input').each(function(){
			if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			};
		});
		if(errors.length == 0){
			$('.modal-body-overlay').addClass('d-none');
			var coordinates_details = $('.set-coordinates-form').serializeArray();
			var latitude_place = coordinates_details[0].value;
			var longitude_place = coordinates_details[1].value;
			// Update Google Map
			var mapcenter = {
				lat: parseFloat(latitude_place),
				lng: parseFloat(longitude_place)
			};
			$('.latitude-display').text(mapcenter.lat);
			$('.longitude-display').text(mapcenter.lng);
			map = new google.maps.Map(document.getElementById('googlemap'), {
				center: mapcenter,
				zoom: 15
			});
			map.setOptions({
				styles : [
					{
						featureType: 'poi',
						stylers: [{visibility: 'off'}]
					},
					{
						featureType: 'transit',
						elementType: 'labels.icon',
						stylers: [{visibility: 'off'}]
					}
				]
			});
			var marker = new google.maps.Marker({
				position: mapcenter,
				draggable: true,
				map: map,
				animation: google.maps.Animation.DROP,
				icon : 'img/Map-Icon.png'
			});
			google.maps.event.addListener(marker, 'click', function(){
				var latitude = marker.getPosition().lat();
				var longitude = marker.getPosition().lng();
				$('.latitude-display').text(latitude);
				$('.longitude-display').text(longitude);
			});			
		}
	});

	// Get the product Id
	$('.output-results').on('click', '.product', function(){
		var product_id = $(this).attr('data-product'); // Get the Product Id
		var product_name = $(this).attr('data-productname'); // Get the Product Id
		$('.product-modal .modal-title').html(product_name);
		var thisproduct = $(this);
		$(this).addClass('progress-bar-striped bg-warning progress-bar-animated');
		$.ajax({
			url: 'https://www.davisandshirtliff.com/index.php?option=com_hikashop&ctrl=productinfo&format=raw',
      data: {
      	product_id : product_id
      },
      method: 'POST',
      dataType: 'JSON',
      success: function(data, status, xhr){
      	var imageshtml = '';
      	for(var j = 0; j < data.images.length; j++){
      		imageshtml += '<img src="' +data.images[j].file_image+ '" alt="' +data.images[j].file_name+ '" title="' +data.images[j].file_name+ '" />';
      	}
      	$('.product-modal .images').html(imageshtml);
      	$('.product-modal .description').html(data.product_description);

      	var product_datasheet = product_name;

      	// Find the Table and loop through it
      	$('.product-modal .description').find('table tr').each(function(){
      		// var get the value of the first element text
      		var product_model = $(this).find('td:first-child').text();
      		product_model = product_model.replace(/\s+/g, '');
      		product_model = product_model.toLowerCase();
      		product_name = product_name.replace(/\s+/g, '');
      		product_name = product_name.toLowerCase();
      		if(product_model == product_name){
      			$(this).addClass('highlight-row');
      		}            		
      	});
      	$('.product-modal .description').find('table').addClass('table table-bordered table-sm');
      	$('.product-modal').modal({
      		backdrop : true,
      		show : true
      	});
      	var documentshtml = '<a href="' +data.datasheet.file_path+ '" class="btn btn-block btn-outline-primary" target="_blank">' +product_datasheet+ ' Datasheet<i class="far fa-file-pdf float-right"></i></a>';
      	$('.product-modal .documents').html(documentshtml);
      },
      complete : function(xhr, status){
        console.log(xhr);
        thisproduct.removeClass('progress-bar-striped bg-warning progress-bar-animated');
      },
      error : function(status){
        console.log(status);
      }
		});		
	});

	$('.output-results').on('click', '.pump-curve', function(){

		var equipment_id = $(this).attr('data-product'); // Get the Equipment ID
		var pump_tdh = $(this).attr('data-tdh'); // Get the TDH
		var pump_flow = $(this).attr('data-flow'); // Get Pump Flow
		var power_type = $(this).attr('data-type'); // Get Power Type
		var thisproduct = $(this);
		$(this).addClass('progress-bar-striped bg-warning progress-bar-animated');

		console.log(equipment_id);
		console.log(pump_tdh);
		console.log(pump_flow);
		console.log(power_type);
		
		$.ajax({
			url: 'data?action=getproductcurve',
            data: {
            	equipment_id : equipment_id,
            	pump_tdh : pump_tdh,
            	pump_flow : pump_flow,
            	power_type : power_type
            },
            method: 'POST',
            dataType: 'JSON',
            beforeSend: function(xhr, settings){
            	$('.curve-settings .modal-body').html('<div class="pump-curve-details" id="pump-curve-details"></div><div class="efficiency-curve-details" id="efficiency-curve-details"></div>');
            },
            success: function(result, status, xhr){

            	console.log(result);

            	var curve = result.curve;
            	var efficiency = result.efficiency;
            	var system = result.system;
            	var duty = result.duty;
            	var leastpoint = result.leastpoint;
            	var duty_efficiency = result.duty_efficiency;

            	$('.curve-settings').modal();
            	$('.curve-settings .modal-header h5').text(result.name+ ' PUMP, SYSTEM & EFFICIENCY CURVES');

				Highcharts.chart('pump-curve-details', {
				    chart: {
				        type: 'spline',
				        width : 750,
				        height : 700
				    },
				    credits: {
				    	enabled: true,
				    	text: '<strong>Flow</strong>: ' +duty[0].flow_rate+ 'm³/h<br/><strong>Head</strong>: ' +duty[0].pump_tdh+ 'm',
				    	position: {
				    		align: 'right',
				    		verticalAlign: 'top',
				    		y: 70
				    	},
				    	style: {
				    		color: 'black'
				    	}
				    },
				    title: {
				        text: 'PUMP CURVE - ' +result.name
				    },
				    xAxis: {
				        title: {
				            text: 'FLOW RATE (m³/hr)'
				        },
				        gridLineWidth: 0.25,
				        gridLineColor: '#000000',
				        labels: {
				        	formatter: function() {
				        		return this.value
				        	}
				        }
				    },
				    yAxis: {
				        title: {
				            text: 'PUMP HEAD (m)'
				        },
				        gridLineWidth: 0.25,
				        gridLineColor: '#000000',
				    },
				    tooltip: {
				        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
				        pointFormat: '<tr><td style="padding:0">Head: </td>' + '<td style="padding:0"><b>{point.y:.1f}m</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m³/hr</b></td></tr>',
				        footerFormat: '</table>',
				        shared: true,
				        useHTML: true
				    },
				    plotOptions: {
				        spline: {
				        	marker : {
				                radius: 4,
				                lineColor: '#ff6c00',
				                lineWidth: 6
				            },
				        	lineWidth : 2
				        }
				    },
	    			colors : ['#0082d6', '#ff0000', '#ff6c00', '#ff0000', '#aaaaaa', '#aaaaaa'],
				    series: [
				    {
				    	name : 'PUMP CURVE',
				    	marker : false,
				        data: [
				        	[curve[0].flow_rate, curve[0].head],
				        	[curve[1].flow_rate, curve[1].head],
				        	[curve[2].flow_rate, curve[2].head],
				        	[curve[3].flow_rate, curve[3].head],
				        	[curve[4].flow_rate, curve[4].head],
				        	[curve[5].flow_rate, curve[5].head],
				        	[curve[6].flow_rate, curve[6].head],
				        	[curve[7].flow_rate, curve[7].head],
				        	[curve[8].flow_rate, curve[8].head],
				        	[curve[9].flow_rate, curve[9].head],
				        	[curve[10].flow_rate, curve[10].head]
				        ]
				    }, 
				    {
				    	name : 'SYSTEM CURVE',
				    	marker : false,
				        data: [
				        	[system[0].flow_rate, system[0].system_head],
				        	[system[1].flow_rate, system[1].system_head],
				        	[system[2].flow_rate, system[2].system_head],
				        	[system[3].flow_rate, system[3].system_head],
				        	[system[4].flow_rate, system[4].system_head],
				        	[system[5].flow_rate, system[5].system_head],
				        	[system[6].flow_rate, system[6].system_head],
				        	[system[7].flow_rate, system[7].system_head],
				        	[system[8].flow_rate, system[8].system_head],
				        	[system[9].flow_rate, system[9].system_head],
				        	[system[10].flow_rate, system[10].system_head]
				        ]
				    },
				    {
				    	name : 'Q1',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff6c00',
				    		lineWidth : 1
				    	},
			        data: [
			        	[duty[0].flow_rate, duty[0].pump_tdh]
			        ],
				    },
				    {
				    	name : 'Q2',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff0000',
				    		lineWidth : 1
				    	},
				        data: [
				        	[leastpoint[0].flow, leastpoint[0].head]
				        ]
				    },
				    {
				    	name : 'Line Q',
				    	marker : false,
				    	tooltip : false,
				    	title : false,
				    	lineWidth: 0.5,
				    	lineColor: '#555555',
			        data: [
			        	[duty[0].flow_rate, 0],
			        	[duty[0].flow_rate, duty[0].pump_tdh]
			        ]
				    },
				    {
				    	name : 'Line H',
				    	marker : false,
				    	tooltip : false,
			        data: [
			        	[0, duty[0].pump_tdh],
			        	[duty[0].flow_rate, duty[0].pump_tdh]
			        ]
				    }
				    ]
				});

				if(power_type == 'ac'){
					Highcharts.chart('efficiency-curve-details', {
					    chart: {
					        type: 'spline',
					        width : 750,
					        height : 700
					    },
					    title: {
					        text: 'PUMP EFFICIENCY CURVE - ' +result.name
					    },
					    xAxis: {
					        title: {
					            text: 'FLOW RATE (m³/hr)'
					        },
					        gridLineWidth: 0.25,
				        	gridLineColor: '#000000',
					        labels: {
					        	formatter: function() {
					        		return this.value
					        	}
					        }
					    },
					    yAxis: {
					        title: {
					            text: 'ETA (%)'
					        },
					        gridLineWidth: 0.25,
				        	gridLineColor: '#000000',
					    },
					    tooltip: {
					        headerFormat: '<table>',
					        pointFormat: '<tr><td style="padding:0">ETA: </td>' + '<td style="padding:0"><b>{point.y:.1f}%</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m³/hr</b></td></tr>',
					        footerFormat: '</table>',
					        shared: true,
					        useHTML: true
					    },
		    			colors : ['#2ba42b'],
					    plotOptions: {
					        spline: {
					        	marker : {
					                radius: 4,
					                lineColor: '#ff6c00',
					                lineWidth: 1
					            },
					        	lineWidth : 2
					        }
					    },
					    series: [{
					    	name : 'PUMP CURVE',
					    	marker : false,
					        data: [
					        	[efficiency[0].flow_rate, efficiency[0].efficiency],
					        	[efficiency[1].flow_rate, efficiency[1].efficiency],
					        	[efficiency[2].flow_rate, efficiency[2].efficiency],
					        	[efficiency[3].flow_rate, efficiency[3].efficiency],
					        	[efficiency[4].flow_rate, efficiency[4].efficiency],
					        	[efficiency[5].flow_rate, efficiency[5].efficiency],
					        	[efficiency[6].flow_rate, efficiency[6].efficiency],
					        	[efficiency[7].flow_rate, efficiency[7].efficiency],
					        	[efficiency[8].flow_rate, efficiency[8].efficiency],
					        	[efficiency[9].flow_rate, efficiency[9].efficiency],
					        	[efficiency[10].flow_rate, efficiency[10].efficiency]
					        ]
					    },
					    {
					    	name : 'E1',
					    	marker : {
					    		symbol : 'circle',
					    		radius : 2,
					    		lineColor : '#ff0000',
					    		lineWidth : 1
					    	},
					        data: [
					        	[duty_efficiency[0].flow_rate, duty_efficiency[0].efficiency]
					        ]
					    }]
					});
				}

            },
            complete : function(xhr, status){
              console.log(xhr);
              thisproduct.removeClass('progress-bar-striped bg-warning progress-bar-animated');
            },
            error : function(status){
              console.log(status);
            }
		});
	});

	$('.output-results').on('click', '.pump-curve-sunflo', function(){

		var equipment_id = $(this).attr('data-product');
		var pump_head = $(this).attr('data-head');
		var pump_flow = $(this).attr('data-flow');
		var thisproduct = $(this);

		$(this).addClass('progress-bar-striped bg-warning progress-bar-animated');

		$.ajax({
			url: 'data?action=getproductsunflocurve',
			data: {
				equipment_id : equipment_id,
				pump_head : pump_head,
				pump_flow : pump_flow
			},
			method: 'POST',
			dataType: 'JSON',
			beforeSend: function(xhr, settings){
				$('.curve-settings .modal-body').html('<div class="pump-curve-details" id="pump-curve-details"></div><div class="efficiency-curve-details" id="efficiency-curve-details"></div>');
			},
			success: function(result, status, xhr){
				console.log(result);
				var curve = result.curve;
				var system = result.system;
				var duty = result.duty;
				var leastpoint = result.leastpoint;

				$('.curve-settings').modal();
				$('.curve-settings .modal-header h5').text(result.name+ ' PUMP & SYSTEM CURVES');

				Highcharts.chart('pump-curve-details', {
					chart: {
						type: 'spline',
						width : 750,
						height : 700
					},
	    		legend: {
	    			enabled: true
	    		},
					title: {
						text: 'PUMP CURVE - ' +result.name
					},
					xAxis: {
						title: {
							text: 'FLOW RATE (m³/hr)'
						},
						gridLineWidth: 1,
						labels: {
							formatter: function() {
								return this.value
							}
						}
					},
					yAxis: {
						title: {
							text: 'PUMP HEAD (m)'
						},
						gridLineWidth: 1,
					},
					tooltip: {
						headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
						pointFormat: '<tr><td style="padding:0">Head: </td>' + '<td style="padding:0"><b>{point.y:.1f}m</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m³/hr</b></td></tr>',
						footerFormat: '</table>',
						shared: true,
						useHTML: true
					},
					plotOptions: {
						spline: {
							marker : {
								radius: 4,
								lineColor: '#ff6c00',
								lineWidth: 1
							},
							lineWidth : 0.7
						}
					},
					colors : ['#0082d6', '#ff0000', '#ff6c00', '#ff0000', '#cccccc', '#cccccc'],
					series: [
					{
						name : 'PUMP CURVE',
						marker : false,
						data: [
							[curve[0].flow_rate, curve[0].head],
							[curve[1].flow_rate, curve[1].head],
							[curve[2].flow_rate, curve[2].head],
							[curve[3].flow_rate, curve[3].head],
							[curve[4].flow_rate, curve[4].head],
							[curve[5].flow_rate, curve[5].head],
							[curve[6].flow_rate, curve[6].head],
							[curve[7].flow_rate, curve[7].head],
							[curve[8].flow_rate, curve[8].head],
							[curve[9].flow_rate, curve[9].head],
							[curve[10].flow_rate, curve[10].head]
						]
					},
					{
						name : 'SYSTEM CURVE',
						marker : false,
				        data: [
				        	[system[0].flow_rate, system[0].system_head],
				        	[system[1].flow_rate, system[1].system_head],
				        	[system[2].flow_rate, system[2].system_head],
				        	[system[3].flow_rate, system[3].system_head],
				        	[system[4].flow_rate, system[4].system_head],
				        	[system[5].flow_rate, system[5].system_head],
				        	[system[6].flow_rate, system[6].system_head],
				        	[system[7].flow_rate, system[7].system_head],
				        	[system[8].flow_rate, system[8].system_head],
				        	[system[9].flow_rate, system[9].system_head],
				        	[system[10].flow_rate, system[10].system_head]
				        ]
				    },
				    {
				    	name : 'Q1',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff6c00',
				    		lineWidth : 1
				    	},
				        data: [
				        	[duty[0].flow_rate, duty[0].pump_tdh]
				        ]
				    },
				    {
				    	name : 'Q2',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff0000',
				    		lineWidth : 1
				    	},
				        data: [
				        	[leastpoint[0].flow, leastpoint[0].head]
				        ]
				    },
				    {
				    	name : 'Line Q',
				    	marker : false,
				    	tooltip : false,
				    	title : false,
				        data: [
				        	[duty[0].flow_rate, 0],
				        	[duty[0].flow_rate, duty[0].pump_tdh]
				        ]
				    },
				    {
				    	name : 'Line H',
				    	marker : false,
				    	tooltip : false,
				        data: [
				        	[0, duty[0].pump_tdh],
				        	[duty[0].flow_rate, duty[0].pump_tdh]
				        ]
				    }]
				});
			},
			complete : function(xhr, status){
				console.log(xhr);
				thisproduct.removeClass('progress-bar-striped bg-warning progress-bar-animated');
			},
			error : function(status){
				console.log(status);
			}
		});
	});

	$('.output-results').on('click', '.view-report', function(){

		// Clear the project first
		$('input[name=\"project_id\"]').prop('value', '');
		var sizing = $('.sizing-form').serializeArray();
		var buttonhtml = $(this).html();
		var buttonclass = $(this).attr('class');
		$(this).html('<i class="fas fa-cog fa-spin"></i>');
		// Get the Product ID and Other details
		var solutionstring = $(this).attr('data-product');
		$('input[name=\"solutionstring\"]').prop('value', solutionstring);
		var solution = solutionstring.split('|');

		var average_irradiation = $('input[name=\"average_irradiation\"]').val();
		if(solution[8] != 'solarization'){
			var average_output = $('input[name=\"average_output\"]').val();
		}

		// Convert to Arrays
		var irradiation = average_irradiation.split('|');
		var output = [];
		var irradiationhtml = '';
		var outputhtml = '';
		for(var i = 0; i < irradiation.length; i++){
			var monthly_output = parseFloat((parseFloat(irradiation[i]) * parseFloat(solution[19])).toFixed(2));
			irradiationhtml += '<td>' +irradiation[i]+ '</td>';
			if(solution[8] != 'solarization'){
				output.push(monthly_output);
				outputhtml += '<td>' +monthly_output+ '</td>';
			}
		}

		$('.average-irradiation-data').html(irradiationhtml);
		$('.average-output-data').html(outputhtml);

		if(solution[8] != 'solarization'){
			// Show Output Graph
			Highcharts.chart('output-data', {
				chart: {
					type: 'column',
					height: 500
				},
				title: {
					text: 'Output - ' +sizing[1].value
				},
				xAxis: {
					categories: [
	   				'JAN',
	   				'FEB',
	   				'MAR',
	   				'APR',
	   				'MAY',
	   				'JUN',
	   				'JUL',
	   				'AUG',
	   				'SEP',
	   				'OCT',
	   				'NOV',
	   				'DEC',
	   				'AVG'
	   				],
					crosshair: true
				},
				yAxis: {
					min: 0,
			        title: {
			            text: 'Q[m<sup>3<sup>/day]'
			        }
			    },
			    tooltip: {
			        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
			            '<td style="padding:0"><b>{point.y:.1f} m³/day</b></td></tr>',
			        footerFormat: '</table>',
			        shared: true,
			        useHTML: true
			    },
			    colors : ['#029bf4'],
			    plotOptions: {
			        column: {
			            pointPadding: 0.2,
			            borderWidth: 0,
			            dataLabels: {
			                enabled: true,
			                color: 'black'
			            },
			            colorByPoint : true
			        }
			    },
			    series: [{
			        name: sizing[1].value,
			        data: [output[0], output[1], output[2], output[3], output[4], output[5], output[6], output[7], output[8], output[9], output[10], output[11], output[12]]
			    }]
			});

			var daily_output_input = $('input[name=\"daily_output\"]');
			console.log(daily_output_input);

			if(daily_output_input.length > 0){

				var daily_output = daily_output_input.val().split('|').map(Number);
				console.log(daily_output);

				var daytimes = [];
				for(var g = 0; g < daily_output.length; g++){
					var newtime = 0 + 6;
					if(newtime.length < 2){
						newtime = '0' +newtime;
					}
					daytimes.push(newtime+ '00Hrs');
				}

				// Display Daily Irradiation
        Highcharts.chart('output-daily-results-chart', {
        	chart: {
		        height : 500					        
			    },
			    title: {
			        text: 'Output - ' +sizing[1].value
			    },
			    xAxis: {
			        categories: daytimes,
			        crosshair: true
			    },
			    yAxis: {
			        min: 0,
			        title: {
			            text: 'Output (m³)'
			        }
			    },
			    tooltip: {
			        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			        pointFormat: '<tr><td style="color:{series.color};padding:0">{point.y}: </td>' +
			            '<td style="padding:0"><b>Q[m³]</b></td></tr>',
			        footerFormat: '</table>',
			        shared: true,
			        useHTML: true
			    },
			    plotOptions: {
			        column: {
			            pointPadding: 0.2,
			            borderWidth: 0,
			            dataLabels: {
			            	enabled: true,
			            	color: 'black'
			            }
			        }
			    },
			    series: [{
			    		type: 'column',
			        name: sizing[1].value,
			        data: daily_output,
			        color: '#0082d6'
			    }]
        });

			}

		}

		$('.pump-name').text(solution[0]);
		$('.inverter-name').text(solution[2]);
		$('.panels-name').text(solution[4]);
		$('.panels-details-content').text(solution[6]+ ' x ' +solution[7]);
		$('.cable-details').html(sizing[5].value+ 'm, ' +solution[8]);
		$('.motor-cable-table').html('Length ' +sizing[5].value+ 'm, Cross Sectional Area ' +solution[8]);
		$('.cable-details-water-level').html(sizing[5].value+ 'm');

		if(solution[1] != ''){
			getproductdetails(solution[0], solution[1], 'pump-content', false, buttonclass, 'pump');
			generatepumpcurves(solution[15], solution[17], solution[18], solution[16]);
		}
		if(solution[3] != 0){
			getproductdetails(solution[2], solution[3], 'inverter-content', false, buttonclass, 'inverter');
		}
		// Check if panels are custom or not
		if(solution[5] != ''){
			getproductdetails(solution[4], solution[5], 'panels-content', true, buttonclass, 'panels');
		} else {
			$('.result-area .nav-tabs #six-tab').trigger('click');
		}		
		// Determine the PV Disconnect Value
		if(solution[3] != 0){
			getpvdisconnect(solution[6], solution[7], solution[13], solution[14]);
		}

		// Determine Wiring Diagram
		// wiring-content
		var wiringdiagram = '';
		if(solution[3] == 0){// Is a DC Solar Pump
			wiringdiagram += 'dc-';
		}
		// Check Strings
		if(parseFloat(solution[7]) == 1){
			var paneloptions = ['11', '21', '31', '41', '51'];
			var panelarrangement = solution[6] + solution[7];
			if(paneloptions.includes(panelarrangement)){
				wiringdiagram += solution[6] + 'x' + solution[7];
			} else {
				wiringdiagram += 'nx' + solution[7];
			}
		} else if(parseFloat(solution[7]) == 2){
			var paneloptions = ['12', '22', '32', '42', '52'];
			var panelarrangement = solution[6] + solution[7];			
			if(paneloptions.includes(panelarrangement)){
				wiringdiagram += solution[6] + 'x' + solution[7];
			} else {
				wiringdiagram += 'nx' + solution[7];
			}
		} else if(parseFloat(solution[7]) == 3){
			var paneloptions = ['23', '33', '43', '53'];
			var panelarrangement = solution[6] + solution[7];			
			if(paneloptions.includes(panelarrangement)){
				wiringdiagram += solution[6] + 'x' + solution[7];
			} else {
				wiringdiagram += 'nx' + solution[7];
			}
		} else {
			wiringdiagram += 'nxn';
		}
		console.log('Modules : ' +solution[6]);
		console.log('Strings : ' +solution[7]);
		// console.log('Diagram : ' +wiringdiagram);

		$('input[name=\"wiringdiagram\"]').prop('value', wiringdiagram);
		$('.wiring-content .product-content-image').html('<img src="' +fileurl+ '/img/wiring/' +wiringdiagram+ '.jpg" class="img-fluid" alt="Wiring Diagram" />');
		$('.wiring-content .product-content-text').html('<div class="panels-holder">' +solution[6]+ ' modules per string</div><div class="strings-holder">' +solution[7]+ ' strings in parallel</div>');

		// Additional Borehole Parameters
		$('.min-borehole-diameter').html(sizing[25].value);
		$('.static-water-level').html(sizing[26].value);
		$('.borehole-tested-yield').html(sizing[27].value);
		$('.pump-inlet-depth').html(sizing[28].value);
		$('.main-aquifer').html(sizing[29].value);
		$('.distance-solar-details').html(sizing[30].value);
		$('.distance-delivery').html(sizing[31].value);

	});

	$('.output-results').on('click', '.view-report-solarization', function(){
		// Clear the project first
		$('input[name=\"project_id\"]').prop('value', '');
		var sizing = $('.sizing-form').serializeArray();
		var buttonhtml = $(this).html();
		var buttonclass = $(this).attr('class');
		$(this).html('<i class="fas fa-cog fa-spin"></i>');
		// Get the Product ID and Other details
		var solutionstring = $(this).attr('data-product');
		$('input[name=\"solutionstring\"]').prop('value', solutionstring);
		var solution = solutionstring.split('|');
		console.log(solution);
		console.log(sizing);
		var average_irradiation = $('input[name=\"average_irradiation\"]').val();
		if(solution[8] != 'solarization'){
			var average_output = $('input[name=\"average_output\"]').val();
		}
		// Convert to Arrays
		var irradiation = average_irradiation.split('|');
		var output = [];
		var irradiationhtml = '';
		var outputhtml = '';
		for(var i = 0; i < irradiation.length; i++){
			var monthly_output = parseFloat((parseFloat(irradiation[i]) * parseFloat(solution[19])).toFixed(2));
			irradiationhtml += '<td>' +irradiation[i]+ '</td>';
			if(solution[8] != 'solarization'){
				output.push(monthly_output);
				outputhtml += '<td>' +monthly_output+ '</td>';
			}

		}
		console.log(output);
		$('.average-irradiation-data').html(irradiationhtml);
		$('.average-output-data').html(outputhtml);
		if(solution[8] != 'solarization'){
			// Show Output Graph
			Highcharts.chart('output-data', {
				chart: {
					type: 'column'
				},
				title: {
					text: 'Output - ' +sizing[1].value
				},
				xAxis: {
					categories: [
	   				'JAN',
	   				'FEB',
	   				'MAR',
	   				'APR',
	   				'MAY',
	   				'JUN',
	   				'JUL',
	   				'AUG',
	   				'SEP',
	   				'OCT',
	   				'NOV',
	   				'DEC',
	   				'AVG'
	   				],
					crosshair: true
				},
				yAxis: {
					min: 0,
			        title: {
			            text: 'Q[m<sup>3<sup>/day]'
			        }
			    },
			    tooltip: {
			        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
			            '<td style="padding:0"><b>{point.y:.1f} m³/day</b></td></tr>',
			        footerFormat: '</table>',
			        shared: true,
			        useHTML: true
			    },
			    colors : ['#029bf4'],
			    plotOptions: {
			        column: {
			            pointPadding: 0.2,
			            borderWidth: 0,
			            dataLabels: {
			                enabled: true,
			                color: 'black'
			            },
			            colorByPoint : true
			        }
			    },
			    series: [{
			        name: sizing[1].value,
			        data: [output[0], output[1], output[2], output[3], output[4], output[5], output[6], output[7], output[8], output[9], output[10], output[11], output[12]]
			    }]
			});
		}
		console.log(solution);
		$('.pump-name').text(solution[0]);
		$('.inverter-name').text(solution[2]);
		$('.panels-name').text(solution[4]);
		$('.panels-details-content').text(solution[6]+ ' x ' +solution[7]);
		$('.cable-details').html(sizing[5].value+ 'm, ' +solution[8]);
		$('.motor-cable-table').html('Length ' +sizing[5].value+ 'm, Cross Sectional Area ' +solution[8]);
		$('.cable-details-water-level').html(sizing[5].value+ 'm');
		if(solution[1] != ''){
			getproductdetails(solution[0], solution[1], 'pump-content', false, buttonclass, 'pump');
			generatepumpcurves(solution[15], solution[17], solution[18], solution[16]);
		}
		if(solution[3] != 0){
			getproductdetails(solution[2], solution[3], 'inverter-content', false, buttonclass, 'inverter');
		}
		// Check if the Panels are custom or not
		if(solution[5] != ''){
			getproductdetails(solution[4], solution[5], 'panels-content', true, buttonclass, 'panels');
		} else {
			$('.result-area .nav-tabs #six-tab').trigger('click');
		}		
		// Determine the PV Disconnect Value
		if(solution[3] != 0){
			getpvdisconnect(solution[6], solution[7], solution[13], solution[14]);
		}
		// Determine Wiring Diagram
		// wiring-content
		var wiringdiagram = '';
		if(solution[3] == 0){// Is a DC Solar Pump
			wiringdiagram += 'dc-';
		}
		// Check Strings
		if(parseFloat(solution[7]) == 1){
			var paneloptions = ['11', '21', '31', '41', '51'];
			var panelarrangement = solution[6] + solution[7];
			if(paneloptions.includes(panelarrangement)){
				wiringdiagram += solution[6] + 'x' + solution[7];
			} else {
				wiringdiagram += 'nx' + solution[7];
			}
		} else if(parseFloat(solution[7]) == 2){
			var paneloptions = ['12', '22', '32', '42', '52'];
			var panelarrangement = solution[6] + solution[7];			
			if(paneloptions.includes(panelarrangement)){
				wiringdiagram += solution[6] + 'x' + solution[7];
			} else {
				wiringdiagram += 'nx' + solution[7];
			}
		} else if(parseFloat(solution[7]) == 3){
			var paneloptions = ['23', '33', '43', '53'];
			var panelarrangement = solution[6] + solution[7];			
			if(paneloptions.includes(panelarrangement)){
				wiringdiagram += solution[6] + 'x' + solution[7];
			} else {
				wiringdiagram += 'nx' + solution[7];
			}
		} else {
			wiringdiagram += 'nxn';
		}
		console.log('Modules : ' +solution[6]);
		console.log('Strings : ' +solution[7]);
		console.log('Diagram : ' +wiringdiagram);
		$('input[name=\"wiringdiagram\"]').prop('value', wiringdiagram);
		$('.wiring-content .product-content-image').html('<img src="' +fileurl+ '/img/wiring/' +wiringdiagram+ '.jpg" class="img-fluid" alt="Wiring Diagram" />');
		$('.wiring-content .product-content-text').html('<div class="panels-holder">' +solution[6]+ ' modules per string</div><div class="strings-holder">' +solution[7]+ ' strings in parallel</div>');
	});

	// View SUNFLO Report
	$('.output-results').on('click', '.view-sunflo-report', function(){

		$('input[name=\"project_id\"]').prop('value', '');
		var sizing = $('.sizing-form').serializeArray();
		var buttonhtml = $(this).html();
		var buttonclass = $(this).attr('class');
		$(this).html('<i class="fas fa-cog fa-spin"></i>');

		console.log(sizing);

		var average_irradiation = $('input[name=\"average_irradiation\"]').val();
		var irradiation = average_irradiation.split('|');

		// Get the Product ID and Other details
		var solutionstring = $(this).attr('data-product');
		$('input[name=\"solutionstring\"]').prop('value', solutionstring);

		var solution = solutionstring.split('|');
		console.log(solution);

		var irradiationhtml = '';
		var outputhtml = '';

		var output_delivery = [];

		for(var i = 0; i < irradiation.length; i++){
			var output_monthly = parseFloat((irradiation[i] * solution[10]).toFixed(2));
			irradiationhtml += '<td>' +irradiation[i]+ '</td>';
			outputhtml += '<td>' +output_monthly+ '</td>';
			output_delivery.push(output_monthly);
		}

		console.log(output_delivery);

		$('.average-irradiation-data').html(irradiationhtml);
		$('.average-output-data').html(outputhtml);

		var irradiationdata = $('#results-chart').html();
		$('.irradiation-data').html(irradiationdata);

		Highcharts.chart('output-data', {
			chart: {
    				type: 'column',
						height: 500
    			},
    			title: {
    				text: 'Output - ' +sizing[1].value
    			},
    			xAxis: {
    				categories: [
    				'JAN',
    				'FEB',
    				'MAR',
    				'APR',
    				'MAY',
    				'JUN',
        			'JUL',
        			'AUG',
        			'SEP',
        			'OCT',
        				'NOV',
        				'DEC',
        				'AVG'
        				],
    				crosshair: true
    			},
    			yAxis: {
    				min: 0,
			        title: {
			            text: 'Q[m<sup>3<sup>/day]'
			        }
			    },
			    tooltip: {
			        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
			            '<td style="padding:0"><b>{point.y:.1f} m³/day</b></td></tr>',
			        footerFormat: '</table>',
			        shared: true,
			        useHTML: true
			    },
			    colors : ['#029bf4'],
			    plotOptions: {
			        column: {
			            pointPadding: 0.2,
			            borderWidth: 0,
			            dataLabels: {
			                enabled: true,
			                color: 'black'
			            },
			            colorByPoint : true
			        }
			    },
			    series: [{
			        name: sizing[1].value,
			        data: [output_delivery[0], output_delivery[1], output_delivery[2], output_delivery[3], output_delivery[4], output_delivery[5], output_delivery[6], output_delivery[7], output_delivery[8], output_delivery[9], output_delivery[10], output_delivery[11], output_delivery[12]]
			    }]
			});

		$('.irradiation-data').find('svg.highcharts-root').attr('width', '100%');
		$('.output-data').find('svg.highcharts-root').attr('width', '100%');

		$('.output-details').html(output_delivery[12]+ 'm³');
		$('.output-average-month').html(output_delivery[12]+ 'm³');

		$('.pump-name').text(solution[0]);
		$('.panels-name').text(solution[2]);

		$('.pipe-length-details').text(solution[5]+ 'm');
		$('.total-dynamic-details').text(sizing[5].value+ 'm');

		$('.panels-details-content').text(solution[4]);
		$('.cable-details').html(sizing[5].value+ 'm, 2.5mm<sup>2</sup>');

		$('.motor-cable-table').html('Length ' +sizing[5].value+ 'm, Cross Sectional Area 2.5mm<sup>2</sup>');

		getproductdetails(solution[0], solution[1], 'pump-content', false, buttonclass, 'pump');
		getproductdetails(solution[2], solution[3], 'panels-content', true, buttonclass, 'panels');
		generatepumpcurves(solution[11], solution[6], solution[10], solution[9]);

		$('.result-area .nav-tabs #six-tab').trigger('click');

	});

	// Load Modal Content
	$('.navigation-item').on('click', function(){
		var navigationitem = $(this);
		$('.account-settings .modal-header h5').text(navigationitem.text());

		$.ajax({
			url: 'pages/' +navigationitem.attr('data-page')+ '.php',
            method: 'POST',
            dataType: 'html',
            success: function(data, status, xhr){
            	$('.account-settings .modal-body').html(data);
            },
            complete : function(xhr, status){
              // console.log(xhr);
            },
            error : function(status){
              console.log(status);
            }
		});

	});

	$('.save-project').on('click', function(){
		$(this).html('Saving Project Report <i class="fas fa-cog fa-spin"></i>');
		// Check if Project Has ID
		var project_id = $('input[name=\"project_id\"]').val();
		if(project_id == ''){
			// Open Modal
			$('.save-project-button').trigger('click');
		} else {
			saveproject();
		}
	});

	$('.save-action').on('click', function(){
		var savebutton = $(this);
		var errors = [];
		$('.account-settings form').find(':input').each(function(){
			if($(this)[0].required){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
				if($(this)[0].type === 'email'){
					var email_status = re.test(String($(this).val()).toLowerCase());
					if(email_status == false){
						errors.push(1);
						$(this).addClass('error-found');
					}
				}				
			}
		});
		if(errors.length == 0){

			savebutton.text('Saving...');
			var saveform = $('.account-settings form').serializeArray();

			$.ajax({
				url: 'data?action=' +saveform[0].value,
                data: saveform,
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){

                	console.log(data);
                	if(data.status == 1 && data.reload == 1){
                		setTimeout(location.reload.bind(location), 3000);
                	}

            		// Check if the status action
            		if(saveform[0].value == 'saveproject'){

            			$('.contacts h1').text(data.details.project_name);
            			$('.contacts h2').text(data.details.customer_name);
            			$('.contacts p').text(data.details.project_notes);
            			$('input[name=\"project_id\"]').prop('value', data.project_id);
            			$('input[name=\"customer_id\"]').prop('value', data.details.customer_id);

            			// Show Customer Details
            			$('.contacts .customer-report-details').removeClass('d-none');
            			$('.contacts .customer-physical').text(data.customer.customer_physical);
            			$('.contacts .customer-telephone').text(data.customer.customer_telephone);
            			$('.contacts .customer-email').text(data.customer.customer_email);
            			$('.contacts .customer-postal').text(data.customer.customer_postal);

            			// Show Company Details
            			$('.contacts input[name=\"company_id\"]').prop('value', data.company.company_id);
            			$('.contacts .company-name').text(data.company.company_name);
            			$('.contacts .physical-address').text(data.company.physical_location);
            			$('.contacts .postal-address').text(data.company.postal_address);
            			$('.contacts .company-phone').text(data.company.company_phone);
            			$('.contacts .company-email').text(data.company.company_email);
            			$('.contacts .company-website').text(data.company.company_website);

            			// Logo
            			$('.top-header-report .company-logo').prop('src', fileurl + 'img/logos/' +data.company.company_logo);
            			$('.top-header-report .company-logo').prop('alt', data.company.company_name);

            			$('.account-settings .close-action').trigger('click');
            			$('.save-project').html('Save Project Report <i class="far fa-save"></i>');
            			saveproject();

            		} else if(saveform[0].value == 'getcustommodules'){

            			$('input[name=\"custom_module_panels\"]').prop('value', data.details);
            			$('.account-settings .close-action').trigger('click');

            		} else if(saveform[0].value == 'company'){

            			$('.account-settings .close-action').trigger('click');

            		}

                	savebutton.text(data.text);
                },
                complete : function(xhr, status){
                  console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});
		}
	});

	// Upload Logo
	$('.account-settings').on('click', '.logo-holder', function(){
		var logoholder = $(this);

		$('.form-upload').remove();
		$('body').prepend('<form enctype="multipart/form-data" class="form-upload" style="display: none;"><input type="file" name="file" /></form>');
		$('.form-upload input[name=\'file\']').trigger('click');

		// Start Upload Process
		if (typeof timer != 'undefined') {
			clearInterval(timer);
		}
		timer = setInterval(function () {
			if($('.form-upload input[name=\'file\']').val() != ''){
				clearInterval(timer);
				$.ajax({
					url : 'data?action=upload',
					type : 'post',
					dataType : 'json',
					data : new FormData($('.form-upload')[0]),
					cache: false,
					contentType: false,
					processData: false,
					beforeSend : function (xhr, settings){
						logoholder.html('<i class="fas fa-cog fa-spin"></i> Uploading Company Logo');
					},
					complete : function (xhr, textStatus){
						$('.form-upload').remove();
					},
					success : function (data, textStatus, xhr){
						
						logoholder.removeClass('success');
						logoholder.removeClass('warning');
						
						logoholder.removeClass(data.classstatus);

						if(data.status == 1){
							logoholder.html('<i class="fas fa-times remove-image"></i><img src="img/logos/' +data.file+ '" class="img-fluid" />');
							$('input[name=\"logo\"]').attr('value', data.file);
						} else {
							logoholder.html(data.text);
							$('input[name=\"logo\"]').attr('value', '');
						}

					},
					error : function (xhr, ajaxOptions, thrownError){
						console.log(thrownError);
					}
				});
			}
		}, 1000);
	});

	// Remove Logo
	$('.account-settings').on('click', '.remove-image', function(e){
		e.stopPropagation();
		$('.account-settings').find('.logo-holder').html('<i class="fas fa-plus"></i> Click here to add Logo');
		$('input[name=\"logo\"]').attr('value', '');
	});

	// Sign in Using Microsoft Account
	$('.signin').on('click', function(e){

		$('.login-form input[name=\"email\"]').prop('value', '');
		$('.login-form input[name=\"password\"]').prop('value', '');
		$('.login-btn').html('SIGNING / LOGGING IN <i class="fas fa-cog fa-spin"></i>');

		e.preventDefault();
		myMSALObj.loginPopup(requestObj).then(function (loginResponse) {
	        showWelcomeMessage();
	        acquireTokenPopupAndCallMSGraph();
	    }).catch(function (error) {
	        console.log(error);
	        $('.login-btn').html('SIGN IN / LOGIN <i class="fas fa-lock"></i>');
	    });

	});

	// Show New Customer Form
	$('.account-settings').on('click', '.showhide-customer-form', function(){
		// Check status of the form
		var checkformstatus = $('.account-settings').find('.new-customer-form.d-none');

		if(checkformstatus.length == 1){
			$('.account-settings').find('.new-customer-form').removeClass('d-none');
			$(this).html('Hide New Customer Form <i class="fas fa-eye-slash"></i>');
		} else {			
			$('.account-settings').find('.new-customer-form').addClass('d-none');
			$(this).html('Show New Customer Form <i class="fas fa-eye"></i>');
		}
	});

	// Show New Customer Form
	$('.account-settings').on('click', '.search-customer', function(){

		var btnsearch = $(this);
		var btntext = btnsearch.text();

		var customer_name = $('input[name=\"search_nav\"]').val();

		$('input[name=\"search_nav\"]').removeClass('error-found');
		if(customer_name == ''){
			$('input[name=\"search_nav\"]').addClass('error-found');
		} else {

			btnsearch.text('Searching');
			$.ajax({
				url: 'data?action=searchcrmnavcustomer',
                data: {
                	customer_name : customer_name
                },
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){

                	console.log(data);
                	btnsearch.text(btntext);

                	$('.account-settings').find('.customers-list .dropdown-menu').removeClass('hide-drop');
	            	$('.account-settings').find('.customers-list .dropdown-menu').addClass('show-drop');
	            	var dropdownlist = '';
	            	if(data.length > 0){
	            		for(var j = 0; j < data.length; j++){
	            			dropdownlist += '<a href="#" class="dropdown-item active-link" data-customer="' +data[j].Name+ '|' +data[j].AccountNumber+ '|' +data[j].Email+ '|' +data[j].Telephone+ '">' +data[j].Name+ '</a>';
	            		}            		
	            	} else {
	            		dropdownlist += '<a href="#" class="dropdown-item disabled">No customers to display - Please add to continue</a>';
	            	}
	            	$('.account-settings').find('.customers-list .dropdown-menu').html(dropdownlist);

                },
                complete : function(xhr, status){
                  console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});
		}

	});

	$('.account-settings').on('blur', '.search-customer', function(){
    	// $('.account-settings').find('.customers-list .dropdown-menu').addClass('hide-drop');
    	// $('.account-settings').find('.customers-list .dropdown-menu').removeClass('show-drop');
	});

	$('.account-settings').on('click', '.customer-name-input .dropdown-item', function(){

		var customer_details = $(this).attr('data-customer');
		console.log($(this));

		var customer = customer_details.split('|');
		console.log(customer);

		$('.account-settings').find('.customers-list .dropdown-menu').addClass('hide-drop');
    	$('.account-settings').find('.customers-list .dropdown-menu').removeClass('show-drop');

    	$('.account-settings').find('.new-customer-form').removeClass('d-none');

		$('.account-settings').find('input[name=\"customer_name\"]').prop('value', customer[0]);
		$('.account-settings').find('input[name=\"customer_account\"]').prop('value', customer[1]);
		$('.account-settings').find('input[name=\"customer_telephone\"]').prop('value', customer[3]);
		$('.account-settings').find('input[name=\"customer_email\"]').prop('value', customer[2]);

	});

	// Add New Customer
	$('.account-settings').on('click', '.add-new-customer', function(e){
		e.preventDefault();
		// Handle Validation
		var errors = [];
		$('.account-settings').find('.new-customer-form :input').each(function(){
			if($(this)[0].required){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
				if($(this)[0].type === 'email'){

					var email_status = re.test(String($(this).val()).toLowerCase());
					if(email_status == false){
						errors.push(1);
						$(this).parent().addClass('error-found');
					}
				}
			}
		});

		if(errors.length == 0){
			var customerform = $('.account-settings').find('.new-customer-form').serializeArray();
			$.ajax({
				url: 'data?action=addcustomer',
                data: customerform,
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){
                	console.log(data);
                	if(data.status == 1){
                		$('.account-settings .close-action').trigger('click');
                	}
                },
                complete : function(xhr, status){
                  console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});
		}
	});

	// Search Customer
	$('.account-settings').on('keyup', '.customer-name', function(){
		$.ajax({
			url: 'data?action=searchcustomer',
        data: {
        	customer_name : $(this).val()
        },
        method: 'POST',
        dataType: 'JSON',
        success: function(data, status, xhr){

        	console.log(data);
        	// $('.account-settings').find('.customer-name-group .dropdown-menu').removeClass('hide-drop');
        	// $('.account-settings').find('.customer-name-group .dropdown-menu').addClass('show-drop');

        	if(data.length > 0){
						$('.account-settings').find('.customer-name-group .dropdown-menu').css('display', 'unset');
					} else {
						$('.account-settings').find('.customer-name-group .dropdown-menu').css('display', 'none');
					}

        	var dropdownlist = '';
        	if(data.length > 0){
        		for(var j = 0; j < data.length; j++){
        			dropdownlist += '<a href="#" class="dropdown-item active-link" data-customer="' +data[j].customer_id+ '|' +data[j].customer_name+ '">' +data[j].customer_details+ '</a>';
        		}            		
        	} else {
        		dropdownlist += '<a href="#" class="dropdown-item disabled">No customers to display - Please add to continue</a>';
        	}
        	$('.account-settings').find('.customer-name-group .dropdown-menu').html(dropdownlist);
        },
        complete : function(xhr, status){
          console.log(xhr);
        },
        error : function(status){
          console.log(status);
        }
		});
	});

	// $('.account-settings').on('blur', '.customer-name', function(){
 //    	$('.account-settings').find('.customer-name-group .dropdown-menu').addClass('hide-drop');
 //    	$('.account-settings').find('.customer-name-group .dropdown-menu').removeClass('show-drop');
	// });

	$('.account-settings').on('click', '.customer-name-group .dropdown-item.active-link', function(){

		var customer_details = $(this).attr('data-customer');
		var customer_name = $(this).text();

		var customer = customer_details.split('|');
		$('.account-settings').find('.customer-name-group .dropdown-menu').hide();
		$('.account-settings').find('.customer-name-group .dropdown-menu').html('');

		$('.account-settings').find('input[name=\"customer_id\"]').prop('value', customer[0]);
		$('.account-settings').find('input[name=\"customer_name\"]').prop('value', customer_name);
		$('.account-settings').find('input[name=\"customer_name_search\"]').prop('value', customer[1]);

    	// $('.account-settings').find('.customer-name-group .dropdown-menu').addClass('hide-drop');
    	// $('.account-settings').find('.customer-name-group .dropdown-menu').removeClass('show-drop');

	});

	// Print the Project
	$('.print-project').on('click', function(){
	    $('.report-wrapper').printThis({
	    	loadCSS : [fileurl+ 'css/all.min.css', fileurl+ 'css/bootstrap.min.css', fileurl+ 'css/layout.css'],
	    	importCSS : false
	    });	    
	});

	// Print the Project
	$('.account-settings').on('click', '.print-pdf', function(){

		var thisbtn = $(this);
		var thisbtnhtml = thisbtn.html();

		thisbtn.html('Printing <i class="fas fa-cog fa-spin"></i>');
		var projecthtml = $('.account-settings').find('.report-modal').html();
		var project_id = $(this).attr('data-project');

		// Get Products Content
		var pump_content = $('.pump-content').html();
		var panels_content = $('.panels-content').html();
		var inverter_content = $('.inverter-content').html();

		var irradiation_chart = $('.irradiation-data').highcharts();
		var irradiation_graph = irradiation_chart.getSVG();
		canvg(document.getElementById('irradiation-canvas'), irradiation_graph);
		var irradiation_canvas = document.getElementById('irradiation-canvas');
		var irradiation_img = irradiation_canvas.toDataURL('image/png');

		var output_chart = $('.output-data').highcharts();
		var output_graph = output_chart.getSVG();
		canvg(document.getElementById('output-canvas'), output_graph);
		var output_canvas = document.getElementById('output-canvas');
		var output_img = output_canvas.toDataURL('image/png');

		$.ajax({
			url: 'data?action=printproject',
			data: {
				project_id : project_id,
				pump_content : pump_content,
				panels_content : panels_content,
				inverter_content : inverter_content,
				irradiation_img : irradiation_img,
				output_img : output_img
			},
			method: 'POST',
			dataType: 'JSON',
			success: function(data, status, xhr){
				// thisbtn.html(thisbtnhtml);
				console.log(data);
				// window.open(data.pdf);
				$('.account-settings').find('.footer-buttons').html('<a href="' +data.pdf+ '" target="_blank" class="btn btn-success file-link float-right">Download PDF</a>');
				// $('.account-settings').find('.file-link').trigger('click');

               },
               complete : function(xhr, status){
               	console.log(xhr);
               },
               error : function(status){
               	console.log(status);
               }
		});

	});

	// Print the Project
	$('.account-settings').on('click', '.print-ac-pdf', function(){

		var thisbtn = $(this);
		var thisbtnhtml = thisbtn.html();

		thisbtn.html('Printing <i class="fas fa-cog fa-spin"></i>');
		var projecthtml = $('.account-settings').find('.report-modal').html();
		var project_id = $(this).attr('data-project');

		// Get Products Content
		var pump_content = $('.pump-content').html();
		var panels_content = $('.panels-content').html();
		var inverter_content = $('.inverter-content').html();

		var irradiation_chart = $('.irradiation-data').highcharts();
		var irradiation_graph = irradiation_chart.getSVG();
		canvg(document.getElementById('irradiation-canvas'), irradiation_graph);
		var irradiation_canvas = document.getElementById('irradiation-canvas');
		var irradiation_img = irradiation_canvas.toDataURL('image/png');

		var output_chart = $('.output-data').highcharts();
		var output_graph = output_chart.getSVG();
		canvg(document.getElementById('output-canvas'), output_graph);
		var output_canvas = document.getElementById('output-canvas');
		var output_img = output_canvas.toDataURL('image/png');

		var pump_curve = $('.pump-curve-report').highcharts();
		var pump_graph = pump_curve.getSVG();
		canvg(document.getElementById('pump-curve-canvas'), pump_graph);
		var pump_canvas = document.getElementById('pump-curve-canvas');
		var pump_img = pump_canvas.toDataURL('image/png');

		var efficiency_chart = $('.efficiency-curve-report').highcharts();
		var efficiency_graph = efficiency_chart.getSVG();
		canvg(document.getElementById('efficiency-canvas'), efficiency_graph);
		var efficiency_canvas = document.getElementById('efficiency-canvas');
		var efficiency_img = efficiency_canvas.toDataURL('image/png');

		$.ajax({
			url: 'data?action=printacproject',
			data: {
				project_id : project_id,
				pump_content : pump_content,
				panels_content : panels_content,
				inverter_content : inverter_content,
				irradiation_img : irradiation_img,
				output_img : output_img,
				pump_img : pump_img,
				efficiency_img : efficiency_img
			},
			method: 'POST',
			dataType: 'JSON',
			success: function(data, status, xhr){
				thisbtn.html(thisbtnhtml);
				console.log(data);
				// window.open(data.pdf);
				$('.account-settings').find('.footer-buttons').html('<a href="' +data.pdf+ '" target="_blank" class="btn btn-success file-link float-right">Download PDF</a>');
			},
			complete : function(xhr, status){
				console.log(xhr);
			},
			error : function(status){
				console.log(status);
			}
		});

	});

	// Print the Project
	$('.account-settings').on('click', '.print-surface-pdf', function(){

		var thisbtn = $(this);
		var thisbtnhtml = thisbtn.html();

		thisbtn.html('Printing <i class="fas fa-cog fa-spin"></i>');
		var projecthtml = $('.account-settings').find('.report-modal').html();
		var project_id = $(this).attr('data-project');

		// Get Products Content
		var pump_content = $('.pump-content').html();
		var panels_content = $('.panels-content').html();
		var inverter_content = $('.inverter-content').html();

		var irradiation_chart = $('.irradiation-data').highcharts();
		var irradiation_graph = irradiation_chart.getSVG({
			exporting: {
            sourceWidth: irradiation_chart.chartWidth,
            sourceHeight: irradiation_chart.chartHeight
        }
		});
		canvg(document.getElementById('irradiation-canvas'), irradiation_graph);
		var irradiation_canvas = document.getElementById('irradiation-canvas');
		var irradiation_img = irradiation_canvas.toDataURL('image/png');

		var daily_irradiation_chart = $('.daily-irradiation-data').highcharts();
		var daily_irradiation_graph = daily_irradiation_chart.getSVG({
			exporting: {
            sourceWidth: daily_irradiation_chart.chartWidth,
            sourceHeight: daily_irradiation_chart.chartHeight
        }
		});
		canvg(document.getElementById('daily-irradiation-canvas'), daily_irradiation_graph);
		var daily_irradiation_canvas = document.getElementById('daily-irradiation-canvas');
		var daily_irradiation_img = daily_irradiation_canvas.toDataURL('image/png');

		var output_chart = $('.output-data').highcharts();
		var output_graph = output_chart.getSVG({
			exporting: {
            sourceWidth: output_chart.chartWidth,
            sourceHeight: output_chart.chartHeight
        }
		});
		canvg(document.getElementById('output-canvas'), output_graph);
		var output_canvas = document.getElementById('output-canvas');
		var output_img = output_canvas.toDataURL('image/png');

		var daily_output_chart = $('.daily-output-data').highcharts();
		var daily_output_graph = daily_output_chart.getSVG({
			exporting: {
            sourceWidth: daily_output_chart.chartWidth,
            sourceHeight: daily_output_chart.chartHeight
        }
		});
		canvg(document.getElementById('daily-output-canvas'), daily_output_graph);
		var daily_output_canvas = document.getElementById('daily-output-canvas');
		var daily_output_img = daily_output_canvas.toDataURL('image/png');

		var pump_curve = $('.pump-curve-report').highcharts();
		var pump_graph = pump_curve.getSVG({
			exporting: {
            sourceWidth: pump_curve.chartWidth,
            sourceHeight: pump_curve.chartHeight,
            allowHTML: true,
            chartOptions: {
            	credits: {
            		enabled: true
            	}
            }
        }
		});
		canvg(document.getElementById('pump-curve-canvas'), pump_graph, {
			ignoreDimensions: false
		});

		var pump_canvas = document.getElementById('pump-curve-canvas');
		var pump_img = pump_canvas.toDataURL('image/png');

		var efficiency_chart = $('.efficiency-curve-report').highcharts();
		var efficiency_graph = efficiency_chart.getSVG({
			exporting: {
            sourceWidth: efficiency_chart.chartWidth,
            sourceHeight: efficiency_chart.chartHeight,
            allowHTML: true
        }
		});
		canvg(document.getElementById('efficiency-canvas'), efficiency_graph);
		var efficiency_canvas = document.getElementById('efficiency-canvas');
		var efficiency_img = efficiency_canvas.toDataURL('image/png');

		$.ajax({
			url: 'data?action=printsurfaceproject',
			data: {
				project_id : project_id,
				pump_content : pump_content,
				panels_content : panels_content,
				inverter_content : inverter_content,
				irradiation_img : irradiation_img,
				daily_irradiation_img : daily_irradiation_img,
				output_img : output_img,
				daily_output_img : daily_output_img,
				pump_img : pump_img,
				efficiency_img : efficiency_img
			},
			method: 'POST',
			dataType: 'JSON',
			success: function(data, status, xhr){
				thisbtn.html(thisbtnhtml);
				console.log(data);
				// window.open(data.pdf);
				$('.account-settings').find('.footer-buttons').html('<a href="' +data.pdf+ '" target="_blank" class="btn btn-success file-link float-right">Download PDF</a>');
			},
			complete : function(xhr, status){
				console.log(xhr);
			},
			error : function(status){
				console.log(status);
			}
		});

	});

	// Print the Solarization Project
	$('.account-settings').on('click', '.print-solarization-pdf', function(){

		var thisbtn = $(this);
		var thisbtnhtml = thisbtn.html();

		thisbtn.html('Printing <i class="fas fa-cog fa-spin"></i>');
		var projecthtml = $('.account-settings').find('.report-modal').html();
		var project_id = $(this).attr('data-project');

		// Get Products Content
		var panels_content = $('.panels-content').html();
		var inverter_content = $('.inverter-content').html();

		var irradiation_chart = $('.irradiation-data').highcharts();
		var irradiation_graph = irradiation_chart.getSVG();
		canvg(document.getElementById('irradiation-canvas'), irradiation_graph);
		var irradiation_canvas = document.getElementById('irradiation-canvas');
		var irradiation_img = irradiation_canvas.toDataURL('image/png');

		$.ajax({
			url: 'data?action=printsolarizationproject',
			data: {
				project_id : project_id,
				panels_content : panels_content,
				inverter_content : inverter_content,
				irradiation_img : irradiation_img
			},
			method: 'POST',
			dataType: 'JSON',
			success: function(data, status, xhr){
				thisbtn.html(thisbtnhtml);
				console.log(data);
				// window.open(data.pdf);
				$('.account-settings').find('.footer-buttons').html('<a href="' +data.pdf+ '" target="_blank" class="btn btn-success file-link float-right">Download PDF</a>');
			},
			complete : function(xhr, status){
				console.log(xhr);
			},
			error : function(status){
				console.log(status);
			}
		});

	});


	// Send Feedback
	$('.feedback-modal .submit-feedback').on('click', function(){

		var errors = [];
		$('.feedback-modal').find('form :input').each(function(){
			if($(this)[0].required){
				$(this).removeClass('error-found');
				if($(this).val() === ''){
					errors.push(1);
					$(this).addClass('error-found');
				}
			}
		});

		if(errors.length == 0){
			var feedback_form = $('.feedback-modal form').serializeArray();
			$.ajax({
				url: 'data?action=feedback',
				data: feedback_form,
				method: 'POST',
				dataType: 'JSON',
				success: function(data, status, xhr){
					console.log(data);
	                	if(data.status == 1){
	                		$('.feedback-modal .btn-secondary').trigger('click');
	                	}
	                	$('.feedback-modal').find('form :input').prop('disabled', 'disabled');
	                	$('.feedback-modal .submit-feedback').text(data.text);
	               },
	               complete : function(xhr, status){
	               	console.log(xhr);
	               },
	               error : function(status){
	               	console.log(status);
	               }
			});
		}

	});

	// Delete Custom Module
	$('.account-settings').on('click', '.delete-project', function(){

		var project_id = $(this).attr('data-project');
		var project_name = $(this).parent().text();
		var project = $(this).parent().parent();
		var project_icon = $(this);

		var confirm_action = confirm('Please confirm that you would like to delete ' +project_name+ ' from your projects?');

		if(confirm_action == true){

			$.ajax({
				url: 'data?action=deleteproject',
                data: {
                	project_id : project_id,
                },
                method: 'POST',
                dataType: 'JSON',
                success: function(data, status, xhr){

                	if(data.status == 1){
                		project.remove();
                	} else {
                		project_icon.removeClass('far fa-trash-alt');
                		project_icon.addClass('far fa-question-circle');
                		project_icon.attr('title', data.text);
                	}

                	console.log(data);

                },
                complete : function(xhr, status){
                  console.log(xhr);
                },
                error : function(status){
                  console.log(status);
                }
			});

		}

	});

	// View Project
	$('.account-settings').on('click', '.open-project', function(){

		var project_id = parseInt($(this).attr('data-project'));
		var project_name = $(this).attr('data-projectname');
		var project_type = $(this).attr('data-projecttype');
		var project_power = $(this).attr('data-projectpower');
		var project_pump_id = parseInt($(this).attr('data-productdetails'));
		var project_btn = $(this);

		$(this).html('<i class="fas fa-cog fa-spin"></i>');
		$('.account-settings .modal-header h5').text(project_name);

		var product_idstatus = isNaN(project_pump_id);

		if(product_idstatus === true){
			var project_url = 'pages/projectdetails-solarization.php?project_id=' +project_id;
		} else {
			if(project_type == 'sunflo'){
				var project_url = 'pages/projectdetails-sunflo.php?project_id=' +project_id;
			} else if(project_power == 'surface') {
				var project_url = 'pages/projectdetails-surface.php?project_id=' +project_id;
			} else {
				var project_url = 'pages/projectdetails.php?project_id=' +project_id;
			}
		}

		$.ajax({
			url: project_url,
            method: 'POST',
            dataType: 'html',
            beforeSend: function(){
            	$('.account-settings .modal-body').html('<div class="loading-content"><img src="img/loader.gif"></div>');
            },
            success: function(data, status, xhr){
            	$('.account-settings .modal-body').html(data);
            },
            complete : function(xhr, status){
              // console.log(xhr);
            },
            error : function(status){
              console.log(status);
            }
		});

	});

	// Open Card Modal
	$('.card-item').on('click', function(){

		var card_status = $(this).attr('data-status');
		if(parseInt(card_status) == 1){
			var card_modal = $(this).attr('data-modal');
			$('.main-navigation .dropdown-menu a[data-page="' +card_modal+ '"]').trigger('click');
		}

	});
});

// Animation on page load
window.onload = function(){
	$('body').find('.page-wrapper').remove();
};

function getproductdetails(product_name, product_id, product_class, tabopen, button_class, product){
	$.ajax({
		url: 'https://www.davisandshirtliff.com/index.php?option=com_hikashop&ctrl=productinfo&format=raw',
        data: {
        	product_id : product_id
        },
        method: 'POST',
        dataType: 'JSON',
        success: function(data, status, xhr){
        	// console.log(data);

        	var product_name_description = product_name;

        	$('.' +product_class).find('h3').text(product_name_description);
        	var imageshtml = '';
        	for(var j = 1; j < data.images.length; j++){
        		imageshtml += '<img src="' +data.images[j].file_image+ '" alt="' +data.images[j].file_name+ '" title="' +data.images[j].file_name+ '" class="img-fluid" />';
        	}
        	$('.' +product_class+ ' .product-content-image').html(imageshtml);
        	$('.' +product_class+ ' .product-content-description').html(data.product_description);

        	// Find the Table and loop through it
        	$('.' +product_class+ ' .product-content-description').find('table tr').each(function(){
        		// var get the value of the first element text
        		var product_model = $(this).find('td:first-child').text();
        		product_model = product_model.replace(/\s+/g, '');
        		product_model = product_model.toLowerCase();
        		product_name = product_name.replace(/\s+/g, '');
        		product_name = product_name.toLowerCase();
        		if(product_model == product_name){
        			$(this).addClass('highlight-row');
        		}

        	});

        	$('.' +product_class+ ' .product-content-description').find('table').addClass('table table-bordered table-sm');
        	// $('.' +product+ '-details-table').html(data.product_meta_description);

        	if(tabopen == true){
        		$('.result-area .nav-tabs #six-tab').trigger('click');
        	}
   		var uniqueclass = button_class.split(' ');
   		$('.result-area').find('.' +uniqueclass[(uniqueclass.length - 1)]).html('<i class="fas fa-check"></i>');

        },
        complete : function(xhr, status){
          // console.log(xhr);
        },
        error : function(status){
          console.log(status);
        }
	});
}

function saveproject(){

	// Get Form Information
	var sizing = $('.sizing-form').serializeArray();
	var project_id = $('input[name=\"project_id\"]').val();

	var project_name = $('.contacts h1').text();
	var customer_name = $('.contacts h2').text();
	var project_notes = $('.contacts p').text();

	var average_irradiation = $('input[name=\"average_irradiation\"]').val();
	var average_output = $('input[name=\"average_output\"]').val();

	var solutionstring = $('input[name=\"solutionstring\"]').val();
	var location_name = $('input[name=\"location_details\"]').val();

	var pipe_details = $('.pipe-details').text();
	var pipe_length_details = $('.pipe-length-details').text();
	var tdh = $('.total-dynamic-details').text();

	var delivery_output = $('.output-average-month').html();
	var company_id = $('input[name=\"company_id\"]').val();
	var customer_id = $('input[name=\"customer_id\"]').val();

	var wiringdiagram = $('input[name=\"wiringdiagram\"]').val();

	var project_information = {
		sizing : sizing,
		project_id : project_id,
		project_name : project_name,
		customer_id : customer_id,
		customer_name : customer_name,
		project_notes : project_notes,
		average_irradiation : average_irradiation,
		average_output : average_output,
		solutionstring : solutionstring,
		location_name : location_name,
		pipe_details : pipe_details,
		pipe_length_details : pipe_length_details,
		tdh : tdh,
		delivery_output : delivery_output,
		company_id : company_id,
		wiringdiagram : wiringdiagram
	}

	$.ajax({
		url: 'data?action=saveprojectdetails',
        data: project_information,
        method: 'POST',
        dataType: 'JSON',
        success: function(data, status, xhr){
        	console.log(data);
        	$('.save-project').html('Save Project Report <i class="far fa-save"></i>');
        },
        complete : function(xhr, status){
          console.log(xhr);
        },
        error : function(status){
          console.log(status);
        }
	});
}

function displaymap(mapcenter, location_name){
	map = new google.maps.Map(document.getElementById('googlemap'), {
		center: mapcenter,
		zoom: 15
	});
	map.setOptions({
		styles : [
			{
				featureType: 'poi',
				stylers: [{visibility: 'off'}]
			},
			{
				featureType: 'transit',
				elementType: 'labels.icon',
				stylers: [{visibility: 'off'}]
			}
		]
	});

	infowindow = new google.maps.InfoWindow({
		content: location_name
	});

	marker = new google.maps.Marker({
		position: mapcenter,
		draggable: true,
		map: map,
		animation: google.maps.Animation.DROP,
		icon : 'img/Map-Icon.png'
	});

	infowindow.open(map, marker);

	google.maps.event.addListener(marker, 'dragend', function(){
		var latitude = marker.getPosition().lat();
		var longitude = marker.getPosition().lng();
		$('.latitude-display').text(latitude);
		$('.longitude-display').text(longitude);

		getcoordinatedetails(latitude, longitude);

	});
}

function getcoordinatedetails(latitude, longitude){

	// $('.get-location').text('Getting Location....');
	$('.get-location').html('Getting Location <i class="fas fa-asterisk fa-spin"></i>');
	$.ajax({
		url : 'data?action=getcoordinatedetails',
		data : {
			latlng : latitude+ ',' +longitude
		},
		method : 'POST',
		dataType : 'JSON',
		success: function(data, status, xhr){

			console.log(data);
			console.log(data.country);
			console.log(data.country_code);

			$('input[name=\"latitude_place\"]').prop('value', latitude);
			$('input[name=\"longitude_place\"]').prop('value', longitude);
			$('input[name=\"location_name\"]').prop('value', data.location_name);
			$('input[name=\"location_details\"]').prop('value', data.location_name);
			$('input[name=\"location_id\"]').prop('value', '');
			$('input[name=\"location_code\"]').prop('value', '');

			// Select Country from Drop list of countries
			var countrieslist = '';
			$('select[name=\"country\"] option').each(function(){

				if($(this)[0].value == data.country_code+ '|' +data.country){
					countrieslist += '<option value="' +$(this)[0].value+ '" selected="selected">' +$(this)[0].text+ '</option>';
				} else {
					countrieslist += '<option value="' +$(this)[0].value+ '">' +$(this)[0].text+ '</option>';
				}

			});

			$('select[name=\"country\"]').html(countrieslist);

			var errors = [];
			var entity_types = ['text', 'select-one', 'number', 'checkbox', 'email'];
			$('.location-form').find(':input').each(function(){
				if($.inArray($(this)[0].type, entity_types) !== -1 && $(this)[0].required == true){
					$(this).removeClass('error-found');
					if($(this).val() === ''){
						errors.push(1);
						$(this).addClass('error-found');
					}
				};
			});

			$('.get-location').text('Get / Search Location');
			$('.get-gps').removeClass('error-found');
			
		},
		complete: function(xhr, status){
        	// console.log(xhr);
		},
		error: function(status){
			console.log(status);
		}
	});
}

function getpvdisconnect(panels_count, strings_count, short_circuit_current, panel_voc){
	$.ajax({
		url: 'data?action=getpvdisconnect',
		data: {
			panels_count : panels_count,
			strings_count : strings_count,
			short_circuit_current : short_circuit_current,
			panel_voc : panel_voc
		},
		method: 'POST',
		dataType: 'JSON',
		success: function(data, status, xhr){
			console.log(data);

			if(data.pv_disconnect.other.quantity > 0){
				$('.pv-disconnect-details').html(data.pv_count+ ' No. ' +data.pv_disconnect.pv_disconnect_make+ ' ' +data.pv_disconnect.pv_disconnect_model+ ' and ' +data.pv_disconnect.other.quantity+ ' No. ' +data.pv_disconnect.other.model);
			} else {
				$('.pv-disconnect-details').html(data.pv_count+' No. ' +data.pv_disconnect.pv_disconnect_make+ ' ' +data.pv_disconnect.pv_disconnect_model);
			}
			
		},
		complete : function(xhr, status){
			console.log(xhr);
		},
		error : function(status){
			console.log(status);
		}
	});
}

function generatepumpcurves(equipment_id, pump_tdh, pump_flow, power_type){
	
	console.log(equipment_id);
	console.log(pump_tdh);
	console.log(pump_flow);
	console.log(power_type);

	$.ajax({
		url: 'data?action=getproductcurve',
        data: {
        	equipment_id : equipment_id,
        	pump_tdh : pump_tdh,
        	pump_flow : pump_flow,
        	power_type : power_type
        },
        method: 'POST',
        dataType: 'JSON',
        beforeSend: function(xhr, settings){
        	// $('.curve-settings .modal-body').html('<div class="pump-curve-details" id="pump-curve-details"></div><div class="efficiency-curve-details" id="efficiency-curve-details"></div>');
        },
        success: function(result, status, xhr){

        	console.log(result);

        	var curve = result.curve;
        	var efficiency = result.efficiency;
        	var system = result.system;
        	var duty = result.duty;
        	var leastpoint = result.leastpoint;
        	var duty_efficiency = result.duty_efficiency;

			Highcharts.chart('pump-curve-content', {
			    chart: {
			        type: 'spline',
			        height: 700
			    },
			    credits: {
			    	enabled: true,
			    	text: '<strong>Flow</strong>: ' +duty[0].flow_rate+ 'm³/h<br/><strong>Head</strong>: ' +duty[0].pump_tdh+ 'm',
			    	position: {
			    		align: 'right',
			    		verticalAlign: 'top',
			    		y: 70
			    	},
			    	style: {
			    		color: 'black'
			    	}
			    },
			    title: {
			        text: 'PUMP CURVE - ' +result.name
			    },
			    xAxis: {
			        title: {
			            text: 'FLOW RATE (m³/hr)'
			        },
			        gridLineWidth: 1,
			        labels: {
			        	formatter: function() {
			        		return this.value
			        	}
			        }
			    },
			    yAxis: {
			        title: {
			            text: 'PUMP HEAD (m)'
			        },
			        gridLineWidth: 1,
			    },
			    tooltip: {
			        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			        pointFormat: '<tr><td style="padding:0">Head: </td>' + '<td style="padding:0"><b>{point.y:.1f}m</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m³/hr</b></td></tr>',
			        footerFormat: '</table>',
			        shared: true,
			        useHTML: true
			    },
			    plotOptions: {
			        spline: {
			        	marker : {
			                radius: 4,
			                lineColor: '#ff6c00',
			                lineWidth: 1
			            },
			        	lineWidth : 0.7
			        }
			    },
    			colors : ['#0082d6', '#ff0000', '#ff6c00', '#ff0000', '#cccccc', '#cccccc'],
			    series: [
			    {
			    	name : 'PUMP CURVE',
			    	marker : false,
			        data: [
			        	[curve[0].flow_rate, curve[0].head],
			        	[curve[1].flow_rate, curve[1].head],
			        	[curve[2].flow_rate, curve[2].head],
			        	[curve[3].flow_rate, curve[3].head],
			        	[curve[4].flow_rate, curve[4].head],
			        	[curve[5].flow_rate, curve[5].head],
			        	[curve[6].flow_rate, curve[6].head],
			        	[curve[7].flow_rate, curve[7].head],
			        	[curve[8].flow_rate, curve[8].head],
			        	[curve[9].flow_rate, curve[9].head],
			        	[curve[10].flow_rate, curve[10].head]
			        ]
			    }, 
			    {
			    	name : 'SYSTEM CURVE',
			    	marker : false,
			        data: [
			        	[system[0].flow_rate, system[0].system_head],
			        	[system[1].flow_rate, system[1].system_head],
			        	[system[2].flow_rate, system[2].system_head],
			        	[system[3].flow_rate, system[3].system_head],
			        	[system[4].flow_rate, system[4].system_head],
			        	[system[5].flow_rate, system[5].system_head],
			        	[system[6].flow_rate, system[6].system_head],
			        	[system[7].flow_rate, system[7].system_head],
			        	[system[8].flow_rate, system[8].system_head],
			        	[system[9].flow_rate, system[9].system_head],
			        	[system[10].flow_rate, system[10].system_head]
			        ]
			    },
			    {
			    	name : 'Q1',
			    	marker : {
			    		symbol : 'circle',
			    		radius : 2,
			    		lineColor : '#ff6c00',
			    		lineWidth : 1
			    	},
			        data: [
			        	[duty[0].flow_rate, duty[0].pump_tdh]
			        ]
			    },
			    {
			    	name : 'Q2',
			    	marker : {
			    		symbol : 'circle',
			    		radius : 2,
			    		lineColor : '#ff0000',
			    		lineWidth : 1
			    	},
			        data: [
			        	[leastpoint[0].flow, leastpoint[0].head]
			        ]
			    },
			    {
			    	name : 'Line Q',
			    	marker : false,
			    	tooltip : false,
			    	title : false,
			        data: [
			        	[duty[0].flow_rate, 0],
			        	[duty[0].flow_rate, duty[0].pump_tdh]
			        ]
			    },
			    {
			    	name : 'Line H',
			    	marker : false,
			    	tooltip : false,
			        data: [
			        	[0, duty[0].pump_tdh],
			        	[duty[0].flow_rate, duty[0].pump_tdh]
			        ]
			    }]
			});

			if(power_type == 'ac' || power_type == 'surface'){
				Highcharts.chart('efficiency-curve-content', {
				    chart: {
				        type: 'spline',
			        	height: 700
				    },
				    title: {
				        text: 'PUMP EFFICIENCY CURVE - ' +result.name
				    },
				    xAxis: {
				        title: {
				            text: 'FLOW RATE (m³/hr)'
				        },
				        gridLineWidth: 1,
				        labels: {
				        	formatter: function() {
				        		return this.value
				        	}
				        }
				    },
				    yAxis: {
				        title: {
				            text: 'ETA (%)'
				        },
				        gridLineWidth: 1,
				    },
				    tooltip: {
				        headerFormat: '<table>',
				        pointFormat: '<tr><td style="padding:0">ETA: </td>' + '<td style="padding:0"><b>{point.y:.1f}%</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m³/hr</b></td></tr>',
				        footerFormat: '</table>',
				        shared: true,
				        useHTML: true
				    },
	    			colors : ['#2ba42b'],
				    plotOptions: {
				        spline: {
				        	marker : {
				                radius: 4,
				                lineColor: '#ff6c00',
				                lineWidth: 1
				            },
				        	lineWidth : 0.7
				        }
				    },
				    series: [{
				    	name : 'PUMP CURVE',
				    	marker : false,
				        data: [
				        	[efficiency[0].flow_rate, efficiency[0].efficiency],
				        	[efficiency[1].flow_rate, efficiency[1].efficiency],
				        	[efficiency[2].flow_rate, efficiency[2].efficiency],
				        	[efficiency[3].flow_rate, efficiency[3].efficiency],
				        	[efficiency[4].flow_rate, efficiency[4].efficiency],
				        	[efficiency[5].flow_rate, efficiency[5].efficiency],
				        	[efficiency[6].flow_rate, efficiency[6].efficiency],
				        	[efficiency[7].flow_rate, efficiency[7].efficiency],
				        	[efficiency[8].flow_rate, efficiency[8].efficiency],
				        	[efficiency[9].flow_rate, efficiency[9].efficiency],
				        	[efficiency[10].flow_rate, efficiency[10].efficiency]
				        ]
				    },
				    {
				    	name : 'E1',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff0000',
				    		lineWidth : 1
				    	},
				        data: [
				        	[duty_efficiency[0].flow_rate, duty_efficiency[0].efficiency]
				        ]
				    }]
				});
			}

        },
        complete : function(xhr, status){
          console.log(xhr);
        },
        error : function(status){
          console.log(status);
        }
	});
}

function saveuplift_details(){

	// Get Min and Max Uplift
	// var min_panel_uplift = $('input[name=\"min_panel_uplift\"]').val();
	// var max_panel_uplift = $('input[name=\"max_panel_uplift\"]').val();

	// $.ajax({
	// 	url: 'data?action=uplift',
	// 	data: {
	// 		min_panel_uplift : min_panel_uplift,
	// 		max_panel_uplift : max_panel_uplift,
	//      },
	//      method: 'POST',
	//      dataType: 'JSON',
	//      success: function(data, status, xhr){
	//      	// console.log(data);
	//      },
	//      complete : function(xhr, status){
	//      	// console.log(xhr);
	//      },
	//      error : function(status){
	//      	console.log(status);
	//      }
	// });

}
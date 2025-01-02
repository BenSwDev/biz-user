function addOrder(data){
	console.log(data.treatmentMasterSex);
	var slot = $(".row[data-row='" + data.row + "'][data-col='" + data.col + "']");

	//debugger;
	if(caltype > 0){
		var adminApproved = ((data.adminApproved>0)? "approved" : "");
		var approved = ((data.approved>0)? "מאושר" : "לא מאושר");
		var allpaid = (parseInt(data.paidTotal) - parseInt(data.priceTotal) >= 0)? "allpaid" : "";
		var orderID = data.orderID;
		var spaT = data.parentOrder !=  data.orderID ? " spa" : "";
		if(spaT){
			var tGender = data.treatmentMasterSex == 0? "" : (data.treatmentMasterSex == 1? " t-Male" : " t-Female");
			var gender = data.treatmentClientSex == 1? "👨🏻�" : "👩🏼�";
			var genderClass = data.treatmentClientSex == 1? "male" : "female";
		}
		var cleandiv = '';
		if(data.cleanTime>0){
			var size = Math.round((parseInt(data.cleanTime)/(parseInt(data.treatmentLen) + parseInt(data.cleanTime)))*100);
			cleandiv ='<div class="cleanbreak" style="min-width:'+ size +'%;min-height:'+ size +'%;"></div>';
		}
		var writeslot;

		if(data.allDay == 1){
			//debugger;
			if(viewtype == 2){
				writeslot  = '<div class="order allday" data-parentOrder="'+data.parentOrder+'" data-orderid="' + orderID +'" data-orderidbysite="' + data.unitID + '" data-size="' + data.width + '" data-margin="' + data.right + '" style="width:' + data.width + '%;right:' + data.right + '%"><div class="all"></div></div>';
				slot.append(writeslot);
			}else{
				slot.addClass('busy');
			}
		}else{

			var boxtitle =	data.timeFrom.substring(11,16)+" "+(data.therapistID>0? " - " + therapists[data.therapistID] : " - ללא מטפל") +  '&#013;'+
							(data.unitID>0?  roomsNames[data.unitID] : "ללא חדר") +  '&#013;' +
							(pExtras[data.parentOrder]? pExtras[data.parentOrder]+'&#013;' : "") +
							(pComments[data.parentOrder]? pComments[data.parentOrder]+'&#013;' : "") +
							(data.customerName? data.customerName+'&#013;' : "") +
							(data.customerPhone? data.customerPhone : "אין מספר") + '&#013;' +
							(data.treatmentID>0? treatments[data.treatmentID] : "לא נבחר טיפול")+ " "+(data.treatmentLen>0? data.treatmentLen+" דקות" : "") +  '&#013;עלות טיפול - ₪' + data.price ;

			//console.log(caltype);
			writeslot  = '<div title="'+boxtitle+'" class="order ' + adminApproved + spaT + tGender +'" data-parentOrder="'+data.parentOrder+'" data-orderid="' + orderID +'" data-orderidbysite="' + data.unitID + '" data-size="' + data.width + '" data-margin="' + data.right + '" style="width:' + data.width + '%;right:' + data.right + '%">';
			writeslot += '<div class="all"></div><div class="container">';
			writeslot += '<div class="c_status c_s'+data.client_status+'"  ></div>';
			writeslot += cleandiv;
			writeslot += '<input type="hidden" class="guid" value="' + data.guid + '">';
			if(spaT){
				writeslot += '<div class="gender ' + genderClass + '">' + gender + '</div>';
			}
			writeslot += '<div class="name customerName"><span class="'+(data.declareID>0?  "V" : "") + (data.h_negatives>0?  " semi" : "")+'"></span>' + data.customerName + '</div>';
			if(data.customerPhone){
				writeslot += '<div class="phone">'+ (data.customerPhone? data.customerPhone : "אין מספר") + '</div>';
			}
			if(spaT){
				writeslot += '<div class="phone">'+ (data.treatmentID>0? treatments[data.treatmentID] : "לא נבחר טיפול") + '</div>';
				writeslot += '<div class="phone">'+ (data.treatmentLen>0? data.treatmentLen+" דק" : "--") + '</div>';
			}
			if(data.price){
			writeslot += '<div class="bottom"><div class="price '+ allpaid +'">₪' + data.price + '</div></div>';
			}
			if(data.p_sourceID!='0' && data.p_sourceID && sourcesArray[data.p_sourceID]){
				writeslot += '<div class="domain-icon '+ data.p_sourceID +'" style="background-color: '+sourcesArray[data.p_sourceID].color+'">'+ sourcesArray[data.p_sourceID].letterSign +'</div>';
			}else{
				writeslot += '<div class="domain-icon" style="background-image:url(' + data.icon + ')"></div>';
			}
			writeslot += '<div class="whatsapp call" style="display:none" data-phone="' + data.customerPhone + '"></div>';
			writeslot += '</div></div>';

			slot.append(writeslot);
		}
	}else{
		//debugger;
		var _status = data.status==0? "break" : "";
		var writeslot;
		writeslot  = '<div title=" ' + data.timeFrom + ' - ' +  data.timeUntil + ' " data-size="' + data.width + '" data-margin="' + data.right + '" class="shift_idan approved '+ _status +'" style="width:' + data.width + '%;right:' + data.right + '%"><div class="all"></div>';
		writeslot += '<div class="the_overflow"><div class="start_time_look">' + data.timeFrom.substring(11,16) + '</div>';
		writeslot += '<div class="start_time_look"> - </div><div class="start_time_look">' +  data.timeUntil.substring(11,16) + '</div></div>';
		writeslot += '</div>';
		slot.append(writeslot);
	}
}

function addShift_inOrders(data){
	var slot = $(".row[data-row='" + data.row + "'][data-col='" + data.col + "']");
	var writeslot;
	var _status = data.status==0 ? "break" : "";
	console.log(data.col);
	writeslot  = '<div class="shift '+_status+'" data-size="' + data.width + '" data-margin="' + data.right + '" style="width:' + data.width + '%;right:' + data.right + '%"><div class="all"></div></div>';
	slot.append(writeslot);
}

function addLocked(data){
	//debugger;
	var slot = $(".row[data-row='" + data.row + "'][data-col='" + data.col + "']");
	slot.find('.lock').addClass('active');
}

//Add Remove AllDay
/*function newallDayOrder(element,data){
	debugger;


	//var data = {'unitID':this.roomID, 'from':this.orderDate,'allDay':1};
	$.post('ajax_order.php',data, function(res){
		switch(res.status){
			case 1:
				$(element).addClass("busy");
			break;
			case 2:
				$(element).removeClass("busy");
			break;
			case 3:
				swal.fire({icon: 'info', title: 'החדר תפוס'});
			break;

			default:
		}

	},'json')

}*/


$('.days-table:not(.blocked) .l-side .rooms.shifts .row').on('click', function() {
	openNewShift(this);
});


$('.atfusa .days-table .r-side .rooms .row .lock').on('click', function(e) {
	//debugger;
	$.post('ajax_lockShift.php',{uid:$(this).attr('data-uid'),date:$(this).attr('data-date')},function(res){
		console.log(res);
	}).done(function(res){
		if(res.error){
			swal.fire({icon: 'error',title: res.error});
		}else{
			if(res.event == 1){
				//$(".atfusa .row[data-uid='" + $(this).attr('data-uid') +"']").each(function(){$(this).remove()});
				location.reload();

				//alert('LOCK');
			}

		}

	});
});

$('body .atfusa .days-table .l-side .rooms .row ').on('click', '.all', function(e) {
	//debugger;
	var _d = $(this);

    if (dragging == 0){
	//debugger;
	var orderID = _d.closest('.order').data("orderid");
	var orderIDBySite = _d.closest('.order').data("orderidbysite");
	e.stopPropagation();
	$("#orderForm-orderID").val(orderID);
	openSpaFrom({"orderID":orderID});
	$('.create_order .mainTitle').text("הזמנה מספר "+orderIDBySite);
	$('.create_order').fadeIn('fast');
	}

});

$('.atfusa .days-table:not(.blocked) .l-side .rooms:not(.shifts) .row').on('click', function(e) {
	//debugger;
	var target = $(event.target);
	if(target.hasClass('c_status')){
		change_c_s(target);
		return;
	}

	if(target.hasClass('price')){
		var orderID = target.closest('.order').attr('data-parentOrder');
		openPayOrder({"orderID":orderID});
		return;
	}

	var _d = $(this);
	console.log(_d.data('col'));

	if($('#a_tfusa').hasClass('dayly')){
		//debugger;
		if($('#a_tfusa').hasClass('flipped')){
			var _pos = e.pageY - $(this).offset().top;
			var _total = $(this).height();
		}else{
			var _pos = e.pageX - $(this).offset().left;
			var _total = $(this).width();
			_pos = _total - _pos;
		}
		_minutes = Math.round((_pos/_total)*6) * 10;
		var _hour = _d.data('col').substring(11,13);
		if(_minutes>=60){
			_hour = parseInt(_hour) + 1;
			_minutes = "00";
		}
		if(_minutes < 1){
			_minutes = "00";
		}
		var _new_time = _d.data('col').substring(0,11)+ _hour + ":" + _minutes + _d.data('col').substring(16);
		_d.data('col', _new_time);
	}





	setTimeout(function() {
    if (dragging == 0){
		//debugger;
		if($(e.target).hasClass('lock')) {
			$.post('ajax_lockShift.php',{uid:_d.attr('data-uid'),date:_d.attr('data-date')},function(res){

				console.log(res);

			}).done(function(res){
				if(res.error){
					swal.fire({icon: 'error',title: res.error});
				}else{
					if(res.event == 1){
						$(e.target).addClass('active');
					}else{
						$(e.target).removeClass('active');
					}
				}


			});
			//alert('LOCK');
		} else {
			//debugger;
			var locked = _d.find('.lock');
			if(locked.hasClass('active')){
				swal.fire({icon: 'error',title: 'כדי להכניס טיפול יש לבטל נעילה'});
			}else{
				var data = _d.data(), dt = data.col.split(' ');
				openSpaFrom({}).then(function(){
					insertTreatmentNew({terid:data.uid, date:dt[0], hour:dt[1]});
				});
			}
		}
	}
	}, 300)
});

/*$('.atfusa.units:not(.dayly) .rooms .row').on('click', function(e) {
	//debugger;
	var orderDate = $(this).data("date");
	var uid = $(this).data("uid");
	if(uid){
		newallDayOrder($(this),{
			from: orderDate,
			unitID: uid,
			allDay: 1
		});
	}


});*/




function openNewShift(elem){
	//alert('shiftpop');
	//debugger;

    if (dragging == 0){
		if($(".l-side > .order").length){
			swal.fire({icon: 'error',title: 'לא ניתן לפתוח 2 הזמנות במקביל'});

		}else{
			var siteID = $('#sid').val();
			var unitID = $(elem).data("uid");
			var OrderIDS = $(elem).data("num");
			var worker_name = $(elem).data("name");
			var date = $(elem).data("date");
			var ptype = $(elem).data("pagetype");


			if(!date){
				var myDay = new Date();
				date = getDayFormat(myDay);
			}
			var today = new Date(date.split("/").reverse().join("/"));
			var tomorrow = new Date(today);
			tomorrow.setDate(tomorrow.getDate() + 1);
			tomorrow = getDayFormat(tomorrow);

			var data = {unitID:unitID
								,startDate:date
								,endDate:tomorrow
								,ptype:ptype
								,worker_name:worker_name
								,sid:siteID
								,OrderIDS:OrderIDS
								,workers:workers
								};
			openShiftForm(data);




			window.event.cancelBubble = true;
		}
	}
}


function openShiftForm(data){

	//debugger;
	$.post('ajax_shiftFrom.php',{data:data},function(res){
		$("#orderLside").append(res);
	}).done(function(){

		/*$('.timePick').datetimepicker({
			datepicker: false,
			format: 'H:i',
			step: 30
		});*/
		$('.timePicks:not(.added)').each(function(){$(this).AnyPicker(
            {
                mode: "datetime",
                dateTimeFormat: "HH:mm",
                minValue: "00:00",
                maxValue: "23:59",
				intervals: 
				{
					h: 1,
					m: 30,
					s: 1
				}
            }).addClass('added').timetator();
		});
		$('.timePicks').on('blur',function(){
			//debugger;
			setTimeout(function(){
				$('#ap-button-cancel').trigger('click');
			},1)
		});

		if($(window).width() < 992) {
			$('.readonlymob').prop('readonly', true);
		}


	});
}


$('.weekly_shifts').click(function(){
	//debugger;
	var siteID = $('#sid').val();
	var unitID = $(this).data("uid");
	var OrderIDS = $(this).data("num");
	var worker_name = $(this).data("name");
	var date = tfusaDate

	var data = {
		unitID:unitID
		,startDate:date
		,worker_name:worker_name
		,sid:siteID
	};

	$.post("ajax_shiftFromWeek.php", {
        data:data
    },
	function(res){
		$("#orderLside").append(res);
	}).done(function(){

		/*$('.timePick').datetimepicker({
			datepicker: false,
			format: 'H:i',
			step: 30
		});*/
		
		$('.timePicks:not(.added)').each(function(){$(this).AnyPicker(
            {
                mode: "datetime",
                dateTimeFormat: "HH:mm",
                minValue: "00:00",
                maxValue: "23:59",
				intervals: 
				{
					h: 1,
					m: 30,
					s: 1
				}
            }).addClass('added').timetator();
		});

		$('.timePicks').on('blur',function(){
		//debugger;
		setTimeout(function(){
			$('#ap-button-cancel').trigger('click');
		},1)
		});

		if($(window).width() < 992) {
			$('.readonlymob').prop('readonly', true);
		}
	});


});

function gen_step_week(data){	
	//debugger;
	$.post("ajax_shiftFromWeek.php", {
        data:data
    },
	function(res){
		$("#orderLside").append(res);
	}).done(function(){

		/*$('.timePick').datetimepicker({
			datepicker: false,
			format: 'H:i',
			step: 30
		});*/
		
		$('.timePicks:not(.added)').each(function(){$(this).AnyPicker(
            {
                mode: "datetime",
                dateTimeFormat: "HH:mm",
                minValue: "00:00",
                maxValue: "23:59",
				intervals: 
				{
					h: 1,
					m: 30,
					s: 1
				}
            }).addClass('added').timetator();
		});

		$('.timePicks').on('blur',function(){
		//debugger;
		setTimeout(function(){
			$('#ap-button-cancel').trigger('click');
		},1)
		});

		if($(window).width() < 992) {
			$('.readonlymob').prop('readonly', true);
		}
	});
}



function insertShift(ele){

        var con = true;

        var the_input_data = $('#orderForm').serializeArray();
        var time_units = [];

        $(ele).parent().parent().find('.the_shift_zonesss').find('.time_units_row').each(function(){

            var start_time = "";
            var end_time = "";
            var _status = "";

            $(this).find('input').each(function(){
				//debugger;
                if ($(this).attr("name") == "startTime[]") {start_time = $(this).val();}
                if ($(this).attr("name") == "endTime[]") {end_time = $(this).val();}
                if ($(this).attr("name") == "status[]") {_status = $(this).val();}
            });

            var unit_rec = {
                'start_time':start_time,
                'end_time':end_time,
				'status':_status
            }
            time_units.push(unit_rec);

        });

        console.log("time_units->"+JSON.stringify(time_units));

        // בודק שכל ההתחלות יותר קטנות מהסיום
        $(time_units).each(function(kk,vv){
            if (parseInt(vv.start_time) > parseInt(vv.end_time)) {
                con = false;
            }

        });

        if (con) {

        $('#idan_time_units').val(JSON.stringify(time_units));

	$('.holder').show();
	//debugger;
	$.post("ajax_shiftPlus.php"
               ,$('#orderForm').serialize()
               ,function(res){
		if(res.success){
	        $('.holder').hide();
			swal.fire({icon: 'success',title: res.text}).then(function() {
                window.location.reload();
            });
		}
		else if(res.error){
            swal.fire({icon: 'error',title: res.text});
            $('.holder').hide();
		}

	},"JSON");


        } else {
            Swal.fire("נא לציין שעת סיום יותר גבוהה משעת התחלה");
        }
}

function more_shifts(ele,_get_type) {
	//debugger;
	if(_get_type=="break")
		_type=0;
	else
		_type=1;

    var last_end = "00:00";
    var last_start = "10:00";

    $(ele).closest('#orderForm').find('.time_units_row').each(function(){

            var start_time = "";
            var end_time = "";

            $(this).find('input').each(function(){
                if ($(this).attr("name") == "startTime[]") {start_time = $(this).val();}
                if ($(this).attr("name") == "endTime[]") {end_time = $(this).val();}
            });

            last_end = end_time;
            last_start = start_time;


        });
	var new_start;
	var type_name;
	if(_type){
		new_start=last_end;
		type_name = "משמרת";
	}else{		
		new_start=last_start;
		type_name = "הפסקה";
	}

    var svgclock = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20"><path d="M10 1C5 1 1 5 1 10 1 15 5 19 10 19 15 19 19 15 19 10 19 5 15 1 10 1ZM10 17C6.1 17 3 13.9 3 10 3 6.1 6.1 3 10 3 13.9 3 17 6.1 17 10 17 13.9 13.9 17 10 17ZM10.8 10L10.8 6.2C10.8 5.8 10.4 5.5 10 5.5 9.6 5.5 9.3 5.8 9.3 6.2L9.3 10.3C9.3 10.3 9.3 10.3 9.3 10.3 9.3 10.5 9.3 10.7 9.5 10.9L12.3 13.7C12.6 14 13.1 14 13.4 13.7 13.7 13.4 13.7 12.9 13.4 12.6L10.8 10Z"></path></svg>';
    var svg_remove = '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#000000"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M14.59 8L12 10.59 9.41 8 8 9.41 10.59 12 8 14.59 9.41 16 12 13.41 14.59 16 16 14.59 13.41 12 16 9.41 14.59 8zM12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>';

    var html = "";
    html += "<div class='time_units_row the_res "+_get_type+"'>";
	html += "<input type='hidden' name='status[]' value= '"+_type+"'>";

    html += "<div class='the_remove_but' onclick=\"$(this).parent().remove();\">"+svg_remove+"</div>";

    html += "<div class='inputWrap half date time'>";
    html += "<input type='text' value='"+new_start+"' name='startTime[]' class='timePicks readonlymob' >";
    html += svgclock;
    html += "<label for='from'>תחילת "+ type_name +"</label>";
    html += "</div>";

    html += "<div class='inputWrap half date time'>";
    html += "<input type='text' value='"+new_start+"' name='endTime[]' class='timePicks readonlymob' >";
    html += svgclock;
    html += "<label for='from'>סוף "+ type_name +"</label>";
    html += "</div>";
    html += "</div>";

    $('.the_shift_zonesss').append(html);

    /*$('.timePick').datetimepicker({
			datepicker: false,
			format: 'H:i',
			step: 30,
                        setMin: last_end
		});*/
	/*$('.timePicks:not(.added)').AnyPicker(
	{
		mode: "datetime",
		dateTimeFormat: "HH:mm",
		minValue: "00:00",
		maxValue: "23:59",
		intervals: 
		{
			h: 1,
			m: 30,
			s: 1
		}
	}).addClass('added').timetator();

	$('.timePicks').on('blur',function(){
		//debugger;
		setTimeout(function(){
			$('#ap-button-cancel').trigger('click');
		},1)
	});

	if($(window).width() < 992) {
		$('.readonlymob').prop('readonly', true);
	}*/


}





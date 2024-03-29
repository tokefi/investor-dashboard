function addChildProjects(proj) {
	console.log(proj);
	$('.selected-child-container').append('<div class="row medicine-record" data-mark="'+medicineName+'" style="padding: 7px 0 2px;background: #ecf6ff; margin-bottom:5px;"><div class="col-sm-3"><label class="line-ht-2-5">'+medicineName+'</label><input name="medicine_tab_name[]" type="hidden" value="'+medicineName+'"></div><div class="col-xs-5 col-sm-4"><div class="input-group"><input type="number" min="1" value="1" name="medicine_quantity[]" class="form-control" placeholder="Quantity in number" required><span class="input-group-addon"><small><select name=strength_unit[]><option>pills</option><option>satchet</option><option>amps</option><option>box</option><option>strips</option></select></small></span></div></div><div class="col-xs-5 col-sm-4"><div class="input-group"><input type="text" name="medicine_strength[]" class="form-control" placeholder="Strength in mg" required><span class="input-group-addon"><small><select name="quantity_unit[]"><option>mg</option><option>mg/ml</option></select></small></span></div></div><div class="col-xs-2 col-sm-1 text-center"><i class="fa fa-close line-ht-2-5 text-danger remove-medicine" aria-hidden="true" data-delete="'+medicineName+'"></i></div></div>');
}
function deleteChild(id) {
	$.ajax({
		url:'/dashboard/application/'+id+'/delete',
		type: 'POST',
		data: {},
		dataType: 'JSON',
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
	}).done(function (data) {
		if(data.status == 1){
			location.reload();
		}
	})
}

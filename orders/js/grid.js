$(function() {
	
	$("#list").jqGrid({
		url: 'ajax.php?action=getOrders',
		editurl: 'ajax.php?action=edit',
		datatype: 'json',
		colNames: ['ID', 'Дата заказа', 'Имя', 'Телефон', 'E-Mail', 'Адрес', 'IP', 'Параметры заказа', 'Upsell', 'Комментарий', 'Статус', '<i class="fa fa-pencil-square-o" aria-hidden="true"></i>'],
		colModel: [
			{
				name: 'id',
				index: 'id',
				width: 50,
				align: 'center',
				search: false
			},
			{
				name: 'date_create',
				index: 'date_create',
				width: 100,
				align: 'center'
			},
			{
				name: 'name',
				index: 'name',
				width: 130,
				editable: true
			},
			{
				name: 'phone',
				index: 'phone',
				width: 130,
				editable: true
			},
			{
				name: 'email',
				index: 'email',
				width: 130,
				editable: true
			},
			{
				name: 'address',
				index: 'address',
				width: 150,
				sortable: false,
				editable: true,
				edittype: 'textarea'
			},
			{
				name: 'ip',
				index: 'ip',
				width: 120,
				sortable: false
			},
			{
				name: 'params',
				index: 'params',
				width: 140,
				sortable: false
			},
			{
				name: 'upsell',
				index: 'upsell',
				width: 120,
				sortable: false,
				editable: true,
				edittype: 'textarea'
			},
			{
				name: 'comment',
				index: 'comment',
				width: 150,
				sortable: false,
				editable: true,
				edittype: 'textarea'
			},
			{
				name: 'status',
				index: 'status',
				width: 130, 
				align: 'center',
				editable: true,
				edittype: 'select',
				editoptions: {dataUrl: 'ajax.php?action=getStatus'},
				stype: 'select',
				searchoptions: {dataUrl: 'ajax.php?action=getStatus'}
			},
			{
				name: 'action',
				index: 'action',
				width: 50, 
				align: 'center',
				sortable: false,
				search: false
			}		
		],
		rowNum: 30,
		rowList: [10, 30, 50],
        scrollOffset: 0,
		width: '100%',
		height: '100%',
		pager: '#pager',
		sortname: 'id',
		viewrecords: true,
		sortorder: 'desc',
		gridComplete: function() {
			var ids = $('#list').jqGrid('getDataIDs');
			
			for(var i=0; i < ids.length; i++) {
				$('#list').jqGrid('setRowData', ids[i], {
					action: '<a href="#" class="row-edit" title="Редактировать"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;&nbsp;<a href="#" class="row-delete" title="Удалить"><i class="fa fa-times" aria-hidden="true"></i></a>'
				});
			}
		},
		onSelectRow: function(id) {
			$('#list').setColProp('status', {editoptions: {dataUrl: 'ajax.php?action=getStatus&id=' + id}});
		}
	});
	
	$(document).on('click', '.row-edit', function(event) {
		event.preventDefault();
		
		var row_id = $(this).closest('tr').attr('id');
		$('#list').jqGrid('editRow', row_id);
		
		$('#list').jqGrid('setRowData', row_id, {
			action: '<a href="#" class="row-save" title="Сохранить"><i class="fa fa-floppy-o" aria-hidden="true"></i></a>&nbsp;&nbsp;<a href="#" class="row-restore" title="Отменить"><i class="fa fa-reply" aria-hidden="true"></i></a>'
		});
	});
	
	$(document).on('click', '.row-save', function(event) {
		event.preventDefault();
		
		var row_id = $(this).closest('tr').attr('id');
		$('#list').jqGrid('saveRow', row_id, function() {
			$('#list').trigger('reloadGrid');
		});
		
		$('#list').jqGrid('setRowData', row_id, {
			action: '<a href="#" class="row-edit" title="Редактировать"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;&nbsp;<a href="#" class="row-delete" title="Удалить"><i class="fa fa-times" aria-hidden="true"></i></a>'
		});
	});
	
	$(document).on('click', '.row-restore', function(event) {
		event.preventDefault();
		
		var row_id = $(this).closest('tr').attr('id');
		$('#list').jqGrid('restoreRow', row_id);
		
		$('#list').jqGrid('setRowData', row_id, {
			action: '<a href="#" class="row-edit" title="Редактировать"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>&nbsp;&nbsp;<a href="#" class="row-delete" title="Удалить"><i class="fa fa-times" aria-hidden="true"></i></a>'
		});
	});
	
	$(document).on('click', '.row-delete', function(event) {
		event.preventDefault();
		
		var row_id = $(this).closest('tr').attr('id');
		$('#list').jqGrid('delGridRow', row_id);
	});
	
	$(document).on('click', 'td[aria-describedby="list_params"]:not(:has(.editable))', function(event) {
		event.preventDefault();
		
		var text = $(this).html().replace(/;\s/g, '<br>').replace('&nbsp;', '').replace(/;$/, '');
		
		if(text != '') {
			$.jgrid.info_dialog.call(this,
				'Параметры заказа',
				text
			);
		}
	});
	
	$(document).on('click', 'td[aria-describedby="list_upsell"]:not(:has(.editable))', function(event) {
		event.preventDefault();
		
		var text = $(this).html().replace(/\s\|\s/g, '<br> - ').replace('&nbsp;', '');
		
		if(text != '') {
			$.jgrid.info_dialog.call(this,
				'Upsell',
				'- ' + text
			);
		}
	});
	
	$(document).on('click', 'td[aria-describedby="list_address"]:not(:has(.editable))', function(event) {
		event.preventDefault();
		
		var text = $(this).html().replace('&nbsp;', '');
		
		if(text != '') {
			$.jgrid.info_dialog.call(this,
				'Адрес',
				text
			);
		}
	});
	
	$(document).on('click', 'td[aria-describedby="list_comment"]:not(:has(.editable))', function(event) {
		event.preventDefault();
		
		var text = $(this).html().replace('&nbsp;', '');
		
		if(text != '') {
			$.jgrid.info_dialog.call(this,
				'Комментарий',
				text
			);
		}
	});
	
	$('#list').jqGrid('filterToolbar', {
		stringResult: true,
		autosearch: true
	});
	
	$('#list').jqGrid('navGrid', '#pager', {
		search: false,
		add: false,
		edit: false,
		del: false,
		refresh: true
	});

});
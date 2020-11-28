$(function() {
	$("input[type='password'][data-eye]").each(function(i) {
		var $this = $(this);
		var $form_group = $this.closest('.form-group');
		$this.add('.invalid-feedback', $form_group).wrapAll($("<div/>", {
			style: 'position:relative'
		})).end();
		$this.css({
			paddingRight: 60
		});
		$this.after($("<div/>", {
			html: 'Показать',
			class: 'btn btn-dark btn-sm',
			id: 'passeye-toggle-'+i,
			style: 'position:absolute;right:10px;top:20px;transform:translate(0,-50%);-webkit-transform:translate(0,-50%);-o-transform:translate(0,-50%);padding: 2px 7px;font-size:12px;cursor:pointer;'
		}));
		$this.after($("<input/>", {
			type: 'hidden',
			id: 'passeye-' + i
		}));
		$this.on("keyup paste", function() {
			$("#passeye-"+i).val($(this).val());
		});
		$("#passeye-toggle-"+i).on("click", function() {
			if($this.hasClass("show")) {
				$this.attr('type', 'password');
				$this.removeClass("show");
				$(this).removeClass("btn-outline-dark");
			}else{
				$this.attr('type', 'text');
				$this.val($("#passeye-"+i).val());				
				$this.addClass("show");
				$(this).addClass("btn-outline-dark");
			}
		});
	});
	
	$(':checkbox[name="aggree"]').on('click', function() {
		if($(this).is(':checked')) {
			$(this).closest('form').find(':submit').removeAttr('disabled');
		}
		else {
			$(this).closest('form').find(':submit').attr('disabled', 'disabled');
		}
	}).trigger('click');
	
	if(location.search.indexOf('register=success') >= 0) {
		$('#register-success').modal('show');
		$('#register-success').on('hidden.bs.modal', function() {
			location.href = 'login.php';
			return;
		})
	}
	
	if(location.search.indexOf('forgot=success') >= 0) {
		$('#forgot-success').modal('show');
		$('#forgot-success').on('hidden.bs.modal', function() {
			location.href = 'login.php';
			return;
		})
	}
	
});
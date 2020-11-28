var phone_format,
	winWidth,
	winHeight,
	headHeight,
	popuped = false;
$(document).ready(function() {
	$('a.fancybox').fancybox();
	var service_name = $('input.config_info[name="service_name"]').val();
	if (service_name) {
		service_name = service_name+' ';
	}
	
	var url = 'send.php';
	winWidth = $(window).width();
	winHeight = $(window).height();
	headHeight = $('header').outerHeight();
	phone_format = 'one';
	//$('.phone input').inputmask('+7 999 999-99-99');

	popupFix();

	$('.menu_toggle').on('click', function() {
		$('body').toggleClass('menu-opened');
	});

	$('.menu').find('ul').find('a').on('click',function() {
		$('body').removeClass('menu-opened');
	});

	var form_top = $('.home').offset().top + 300;
	$(window).scroll(function() {
		var scroll_top = $(this).scrollTop();
		if (scroll_top > form_top) {
			$('.menu').addClass('scrolled');
		} else {
			$('.menu').removeClass('scrolled');
			$('.menu ul li').removeClass('active');
		}
	});

	$('.tiped').find('input').on('focus', function() {
		$(this).closest('label').addClass('filled');
	});
	$('.tiped').find('input').on('blur change keyup paste input', function() {
		var value = $(this).val();
		if (value == '') {
			if (!$(this).is(':focus')) {
				$(this).closest('label').removeClass('filled');
			}
		} else {
			$(this).closest('label').addClass('filled');
		}
	});
	$('input').on('change keyup paste input', function() {
		$(this).closest('label').removeClass('red');
	});

	$('input,textarea').on('focus active',function() {
		$(this).closest('label').addClass('focus');
	});

	$('input,textarea').on('blur',function() {
		$(this).closest('label').removeClass('focus');
	});

	//initMap('55.753210, 37.666992', 'map');

	function reviewsHeight() {
		if (winWidth > 500) {
			$('.reviews').find('.review').css('height','auto');
			if (winWidth > 500) {
				var revPad = $('.reviews').find('.review.re1,  .review.re2');
			}
			if (winWidth > 800) {
				var revPad = $('.reviews').find('.review.re1,  .review.re2, .review.re3');
			}
			var height = 0;
			$('.reviews').find('.review').each(function() {
				var height1 = $(this).outerHeight();
				if (height1 > height) {
					height = height1;
				}
			});
			$('.reviews').find('.review').css('height',height);
			revPad.css('height',height+60+'px');
		} else {			
			$('.reviews').find('.review').css('height','auto');
		}
	}
	reviewsHeight();

	/*function TotalLength(){
		var path = document.querySelector('#check');
		var len = Math.round(path.getTotalLength() );
		alert("Длина пути - " + len);
	}
	TotalLength();*/

	var hrCurrent = 0,
		hrNext = 1,
		hrPrev = 0,
		hrLen = 6,
		delay = 8000,
		duration = 400,
		hrBlock = $('.hour').find('.hour_block'),
		hrInterval;
	function goToSection(section) {
		hrCurrent = section;
		hrNext = hrCurrent + 1;
		hrPrev = hrCurrent - 1;
		hrBlock.removeClass('hour-10 hour-20 hour-30 hour-40 hour-50 hour-60');
		hrBlock.addClass('hour-'+hrCurrent+'0');
		hrBlock.find('.hbg_one').removeClass('active');
		hrBlock.find('.hbg_one.hbg-'+hrCurrent+'0').addClass('active');
		hrBlock.find('.ht_one').removeClass('active');
		hrBlock.find('.ht_one.ht-'+hrCurrent+'0').addClass('active');
		clearTimeout(hrInterval);
		if (hrNext < hrLen+1) {
			hrInterval = setTimeout(function() {
				goToSection(hrNext);
			},delay+duration);
		}
	}
	hrBlock.find('.hour_control').on('click',function() {
		if ($(this).hasClass('hc-next')) {
			goToSection(hrNext);
		}
		if ($(this).hasClass('hc-prev')) {
			goToSection(hrPrev);
		}
	});
	hrBlock.find('.hour_sectors').find('path[data-section]').on('click',function() {
		var section = parseInt($(this).attr('data-section'));
		goToSection(section);
	});


	$('.scroll-animate').each(function () {
		var block = $(this);
		$(window).scroll(function() {
			var top = block.offset().top + 300;
			var bottom = block.height()+top;
			top = top - $(window).height();
			var scroll_top = $(this).scrollTop();
			setTimeout(function(){
				if ((scroll_top > top) && (scroll_top < bottom)) {
					if (!block.hasClass('animate')) {
						block.addClass('animate');
						block.trigger('animateIn');
					}
				} else {
					block.removeClass('animate');
					block.trigger('animateOut');
				}
			}, 300);
		});	
	});

	$('.hour').on('animateIn', function() {
		if (!$(this).hasClass('active')) {
			$(this).addClass('active');
			goToSection(1);
		}
	});
	
	$(window).resize(function() {
		winWidth = $(window).width();
		winHeight = $(window).height();
		headHeight = $('header').outerHeight();

		popupFix();
		reviewsHeight();
	});

	if (device.desktop() === false) {
		$('.home').find('.home_fabric').html('<img src="images/home_fabric.png" alt="" />');
	} else {
		$('.home').find('.home_fabric').html('<video id="movie" loop autoplay muted><source src="video/floating.ogv" type="video/ogg; codecs=&quot;theora, vorbis&quot;"><source src="video/floating.webm" type="video/webm"><source src="video/floating.mp4" type="video/mp4"></video>');
		
	}

	$('.button').click(function() {
		$('body').find('form:not(this)').children('label').removeClass('red');
		
		var answer = checkForm($(this).closest('form').get(0));
		if(answer != false)	{
			var $form = $(this).closest('form');
			var name = $('input[name="name"]', $form).val();
			if (phone_format == 'one') {
				var phone = $('input[name="phone"]', $form).val();
			} else if (phone_format == 'three') {
				var phone = $('input[name="phone1"]', $form).val()+' '+$('input[name="phone2"]', $form).val()+' '+$('input[name="phone3"]', $form).val();
			}
			var thxp = $('.button', $form).attr('data-thx');
			
			$.ajax({
				type: "POST",
				url: url,
				dataType: "json",
				data: "name="+name+"&phone="+phone
			}).always(function() {
					location.href="thanks.html";
					thx(thxp);
				
			});
		} else {
			$(this).closest('form').find('label.red').first().find('input, textarea').trigger('focus');
		}
	});

	/* Youtube fix */
	$('iframe').each(function() {
		var ifr_source=$(this).attr('src');
		var wmode="wmode=transparent";
		if(ifr_source.indexOf('?')!=-1) {
			var getQString=ifr_source.split('?');
			var oldString=getQString[1];
			var newString=getQString[0];
			$(this).attr('src',newString+'?'+wmode+'&'+oldString)
		} else $(this).attr('src',ifr_source+'?'+wmode)
	});

	// закрытие попапа по нажатию на Esc
	if (popuped = true) {
		$(document).keydown(function(e) {
			if (e.which == 27) {
				popup_out();
			}
		});
	}

	// отправка формы по нажатию на Enter (при фокусе на input или textarea)
	$('.form-enter').find('input, textarea').on('focus',function() {
		$(this).closest('form.form-enter').addClass('focused');
	}).on('blur',function() {
		$(this).closest('form.form-enter').removeClass('focused');
	});
	$('.form-enter').find('input, textarea').on('focus',function() {
		var form = $(this).closest('form.form-enter');
		var btn = form.find('.btn-enter');
		$(document).keydown(function(e) {
			if (form.hasClass('focused')) {
				if (e.which == 13) {
					btn.trigger('click');
				}
			}
		});
	});

	/* Youtube fix */
	$('iframe').each(function() {
		var ifr_source=$(this).attr('src');
		var wmode="wmode=transparent";
		if(ifr_source.indexOf('?')!=-1) {
			var getQString=ifr_source.split('?');
			var oldString=getQString[1];
			var newString=getQString[0];
			$(this).attr('src',newString+'?'+wmode+'&'+oldString)
		} else $(this).attr('src',ifr_source+'?'+wmode)
	});

	if (popuped = true) {
		$(document).keydown(function(e) {
			if (e.which == 27) {
				popup_out();
			}
		});
	}

	if(phone_format == 'three') {
		$('input[name="phone2"]').focus(function() {
			$(this).keydown(function(event){
				if(event.keyCode != 8) {
					if($(this).val().length >= 3 && event.keyCode != 8)
						$(this).parent().siblings().find('input[name="phone3"]').focus();
				}
			});
		});
		$('input[name="phone3"]').focus(function() {
			$(this).keydown(function(event){
				if(event.keyCode == 8 && $(this).val().length == 0) {
					$(this).parent().siblings().find('input[name="phone2"]').focus();
				}
			});
		});
	}
});

function popupFix() {
	var maxHeight = winHeight*0.9;
	$('.popup').each(function() {
		$(this).css('max-height',maxHeight+'px');
	});
	var m_top = -$('.activePopup').outerHeight() / 2 + 'px';
	var m_left = -$('.activePopup').outerWidth() / 2 + 'px';
	$('.activePopup').css({
		'margin-top': m_top,
		'margin-left': m_left
	});
}

function popup(id, form, h1, h2, btn) {
	$('.popup').fadeOut(150);
	$('.popup').removeClass('activePopup');
	$('.popup_overlay').fadeIn(150);
	$('#'+id).addClass('activePopup');
	if(id == 'request') {
		var def_h1 = 'Оставить заявку';
		var def_h2 = 'Заполните форму,<br>и&nbsp;мы&nbsp;обязательно свяжемся с&nbsp;вами!';
		var def_btn = 'Оставить заявку';
	}
	if(h1 != '') {$('#'+id).find('.popup_h1').html(h1);} else {$('#'+id).find('.popup_h1').html(def_h1);}
	if(h2 != '') {$('#'+id).find('.popup_h2').html(h2);} else {$('#'+id).find('.popup_h2').html(def_h2);}
	if(btn != '') {$('#'+id).find('.button').html(btn);} else {$('#'+id).find('.button').html(def_btn);}
	$('.activePopup').fadeIn(150);
	if(id == 'request2') {
		initMap('55.753210, 37.666992', 'map2');
	}
	popupFix();
	$('input.config_info[name="formname"]').attr('value', form);
	popuped = true;
}

function popup_out() {
	$('.popup_overlay').fadeOut(150);
	$('.popup').fadeOut(150);
	$('.popup').removeClass('activePopup');
	$('body').find('label').removeClass('red');
	popuped = false;
}

function formname(name) {
	$('input.config_info[name="formname"]').attr('value', name);
}

function thx(thx) {
	$('.popup').fadeOut(150);
	$('.popup').removeClass('activePopup');
	popup(thx);
	if(phone_format == 'one') {
		$('input[type="text"]').each(function(){
			$(this).val('');
		});
	} else if(phone_format == 'three') {
		$('input[type="text"]:not(input[name="phone1"])').each(function(){
			$(this).val('');
		});
	}
	$('textarea').val('');
}

function checkForm(form1) {
	var $form = $(form1);
	var checker = true;
	var name = $('input[name="name"]', $form).val();
	if(phone_format == 'one') {
		var phone = $('input[name="phone"]', $form).val();
	} else if(phone_format == 'three') {
		var phone1 = $('input[name="phone1"]', $form).val();
		var phone2 = $('input[name="phone2"]', $form).val();
		var phone3 = $('input[name="phone3"]', $form).val();
	}
	var email = $('input[name="email"]', $form).val();

	if($form.find('.name').hasClass('required')) {
		if(!name) {
			$form.find('.name').addClass('red');
			checker = false;
		} else {
			$form.find('.name').removeClass('red');
		}
	}

	if(phone_format == 'one') {
		if($form.find('.phone').hasClass('required')) {
			if(!phone) {
				$form.find('.phone').addClass('red');
				checker = false;
			} else if(/[^0-9\+ ()\-]/.test(phone)) {
				$form.find('.phone').addClass('red');
				checker = false;
			} else {
				$form.find('.phone').removeClass('red');
			}
		}
	} else if(phone_format == 'three') {
		if($form.find('.phone').hasClass("required")) {
			if(!phone1) {
				$form.find('.phone').children('input[name="phone1"]').parent().addClass('red');
				checker = false;
			} else if(/[^0-9+]/.test(phone1)) {
				$form.find('.phone').children('input[name="phone1"]').parent().addClass('red');
				checker = false;
			} else {
				$form.find('.phone').children('input[name="phone1"]').parent().removeClass('red');
			}

			if(!phone2) {
				$form.find('.phone').children('input[name="phone2"]').parent().addClass('red');
				checker = false;
			} else if(/[^0-9]/.test(phone2)) {
				$form.find('.phone').children('input[name="phone2"]').parent().addClass('red');
				checker = false;
			} else {
				$form.find('.phone').children('input[name="phone2"]').parent().removeClass('red');
			}

			if(!phone3) {
				$form.find('.phone').children('input[name="phone3"]').parent().addClass('red');
				checker = false;
			} else if(/[^0-9 -]/.test(phone3) || phone3.length < 4) {
				$form.find('.phone').children('input[name="phone3"]').parent().addClass('red');
				checker = false;
			} else {
				$form.find('.phone').children('input[name="phone3"]').parent().removeClass('red');
			}
		}
	}

	if($form.find('.email').hasClass('required')) {
		if(!email) {
			$form.find('.email').addClass('red');
			checker = false;
		} else if(!/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/.test(email)) {
			$form.find('.email').addClass('red');
			checker = false;
		} else {
			$form.find('.email').removeClass('red');
		}
	}

	if(checker != true) { return false; }
}
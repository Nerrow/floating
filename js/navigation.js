		//Get Sections top position
		function getTargetTop(elem){
			
			//gets the id of the section header
			//from the navigation's href e.g. ("#html")
			var id = elem.attr("href");

			//Height of the navigation
			var offset = headHeight;

			//Gets the distance from the top and 
			//subtracts the height of the nav.
			return $(id).offset().top - offset + 1;
		}

		//Smooth scroll when user click link that starts with #
		$('.menu ul a[href^="#"]').click(function(event) {
			
			//gets the distance from the top of the 
			//section refenced in the href.
			var target = getTargetTop($(this));


			//scrolls to that section.
			$('html, body').animate({scrollTop:target}, 500);

			//prevent the browser from jumping down to section.
			event.preventDefault();

		});

		//Pulling sections from main nav.
		var sections = $('.menu ul a[href^="#"]');

		// Go through each section to see if it's at the top.
		// if it is add an active class
		function checkSectionSelected(scrolledTo){
			
			//How close the top has to be to the section.
			var threshold = 100;

			var i;

			for (i = 0; i < sections.length; i++) {
				
				//get next nav item
				var section = $(sections[i]);

				//get the distance from top
				var target = getTargetTop(section);
				
				//Check if section is at the top of the page.
				if (scrolledTo > target - threshold && scrolledTo < target + threshold) {

					//remove all selected elements
					sections.parent('li').removeClass("active");

					//add current selected element.
					section.parent('li').addClass("active");
				}

			};
		}


		//Check if page is already scrolled to a section.
		checkSectionSelected($(window).scrollTop());

		$(window).scroll(function(e){
			checkSectionSelected($(window).scrollTop())
		});
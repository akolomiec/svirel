$(function()
	{
		$("#shadow-under-pleer").fadeOut(0);
		$('#div-last-search').fadeOut(0);
		$(".btn-small.btn-under-progress").fadeOut(0);
		$('.track').click(function(){
			$('.track-selected').removeClass("track-selected");
			$(this).addClass("track-selected");
		});
        $(".jp-shuffle-off").hide();
        $(".scan-off").hide();
        $('#top100').addClass("selected-categories");
		//Подсвечивание градиентом дива с песней при нажатии
		$('.unselected-categories').click(function(){
			$('.unselected-categories').removeClass("selected-categories");
			$(this).addClass("selected-categories");
		});
		
		
		//Градиентный текст
		$('h1').gradientText({
        colors: ['#50AADC', '#640064']
    	});
		
		
		//Появление тени под плеером при прокрутки страницы
		$(window).scroll(function () {
		var scrolledpx = parseInt($(window).scrollTop()); // Определяем количество прокрученных пикселей
		if(scrolledpx >= 10) { // Если прокручено столько-то пикселей от начала страницы
					$("#shadow-under-pleer").fadeIn(500);
		} else {
		$("#shadow-under-pleer").fadeOut(500);
		}
		});

		
		//Появление последних поисковых запросов
		$('#left-column-of-pleer').mouseover(function() {
		  $('#div-last-search').fadeIn(180);
		});
		
		$('#left-column-of-pleer').mouseleave(function() {
		  $('#div-last-search').fadeOut(180);
		});
		
		
		
		//Появление кнопок под прогрессом
		$('#td-main-controls').mouseover(function() {
		  $(".btn-small.btn-under-progress").fadeIn(180);
		});
		
		$('#td-main-controls').mouseleave(function() {
		  $(".btn-small.btn-under-progress").fadeOut(180);
		});
		
		
		
		
		
		
	}
);
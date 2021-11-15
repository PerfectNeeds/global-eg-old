$(function(){
	 
"use strict";	 

    var $menu          = $('.menu_container'),
        $menu_ul       = $('ul', $menu),
        $collapser     = $('.mobile_collapser', $menu),
        $lihasdropdown = $('.menu_container ul li').has( ".dmui_dropdown_block" );

    $collapser.on('click', function(){
        $menu_ul.toggleClass('collapsed');
    })
    
    $lihasdropdown.addClass('has-dropdown');
    $lihasdropdown.on('click', function(event){
	$lihasdropdown.not(this)
	.children(".dmui_dropdown_block").removeClass('show')
	.children(".dmui-container").removeClass('show');
        $(this)
            .children(".dmui_dropdown_block")
            .toggleClass('show')
            .children(".dmui-container")
            .toggleClass('show');
        event.stopPropagation();
    })

    // HIDE DROPDOWN MENU WHEN CLICKING ELSEWHERE (v1.0.2)
    $(document.body).on('click', function(){
        $lihasdropdown
        .children(".dmui_dropdown_block").removeClass('show')
        .children(".dmui-container").removeClass('show');
    })

});

// FIX Menu Resize Bug from mobile to desktop (Thanks to irata for fixing that!) (v1.0.2)
$(window).resize(function(){
		      
"use strict";		      

$('.menu_container ul').removeClass('collapsed'); 

});
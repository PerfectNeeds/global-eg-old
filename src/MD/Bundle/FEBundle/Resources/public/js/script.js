//
$(document).ready(function() {
    $('.feature_teaser:nth-child(6), .feature_teaser:nth-child(5)').removeClass('col-sm-4');
    $('.feature_teaser:nth-child(6), .feature_teaser:nth-child(5)').removeClass('col-md-4');
    $('.feature_teaser:nth-child(6), .feature_teaser:nth-child(5)').addClass('col-sm-6');
    $('.feature_teaser:nth-child(6), .feature_teaser:nth-child(5)').addClass('col-md-6');
    $('.port:nth-child(1), .port:nth-child(2), .port:nth-child(3)').addClass('views');
    $('.more').click(function() {
        $('.port').addClass('views');
        $(this).fadeOut();
    });
    $('.team_members .team-mem:first-child').addClass('mang');
    $('.more_details').click(function() {
        $(this).next('.more-details').show();
        $(this).next().next('.less_details').show();
        $(this).parent().next('li.more-details').show();
        $(this).parent().next().next('li.more-details').show();
        $(this).parent().next().next().next('li.more-details').show();
        $(this).parent().next().next().children('.less_details').show();
        $(this).parent().next('li').next('li').next().children('.less_details').show();
        $(this).hide();
    });
    $('.less_details').click(function() {
        $(this).prev('.more-details').hide();
        $(this).prev().prev('.more_details').show();
        $(this).hide();
        $(this).parent('li').prev('li').prev('li').hide();
        $(this).parent('li').prev('li').prev('li').prev('li').children('.more_details').show();
        $(this).parent('li').prev('li').prev('li').prev('li').children('.more_details').next('.more-details').hide();
        $(this).parent('li').hide();
        $(this).parent('li').prev('li').hide();
    });
    $('.more_details.lis').click(function() {
        $(this).parent().next('.more-details').show();
        $(this).parent().next().next('.more-details').show();
        $(this).hide();
    });
    $('.less_details.liles').click(function() {
        $(this).parent().prev().prev().children('.more_details').show();
        $(this).parent().prev('ul').hide();
        $(this).parent().hide();
        $(this).hide();
    });
});
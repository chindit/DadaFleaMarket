/**
 * Created by david on 1/07/16.
 */
$(document).ready(function() {

    /**
     * Show hidden <div> and fill it with $(this) picture
     */
    $('.thumbnail').click(function (event) {
        event.preventDefault();
        var image = $(this).children().attr('data-link');
        if (image === undefined)
            return; //No picture to show
        var template = '<img src="'+image+'" alt="Aperçu" />';
        var box = $('#box-image');
        box.html(template);
        var width = $(this).children().attr('data-width');
        var height = $(this).children().attr('data-height');
        box.show();
        box.css('margin-left: -1500px');var test = '0px -'+(width/2)+'px';
        document.getElementById('box-image').style.margin=test;
    });

    /**
     * Hide big picture if clicked
     */
    $('#box-image').click(function(){
       $(this).hide();
    });

    /**
     * (Un)publish an advert
     */
    $('a.ajax-publish').click(function(event){
        event.preventDefault();
        var id = ($(this).attr('data-id'));
        var reponse = confirm('Êtes-vous certain de vouloir '+$(this).html()+' cette annonce?');
        if(reponse === true)
            console.log('OK');
        else
            console.log('KO');
    });
});

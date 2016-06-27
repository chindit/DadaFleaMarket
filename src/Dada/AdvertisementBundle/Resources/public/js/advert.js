/**
 * Created by david on 24/06/16.
 */
$(document).ready(function(){
    //By default, adding a button
    addInsertImageButton();

    $('#advertisement_current_location').click(function(event){
        event.preventDefault();
        getLocation();
    });

    $('#insert_image').click(function(event){
        event.preventDefault();
        addImageField();
        return false;
    });

    $('.alert').click(function(event){
        $(this).hide();console.log('passed');
        event.preventDefault();
    });
});

function addInsertImageButton(){
    var emplacement = $('#advertisement_images');
    var index = emplacement.find(':input').length;

    if(index == 0){
        addImageField();
    }
    else{
        emplacement.children('div').each(function(){
            addDeleteLink($(this));
        });
    }
}

function addImageField(){
    var emplacement = $('#advertisement_images');
    var index = emplacement.find(':input').length;
    if(index == 3){
        emplacement.append('<div class="alert alert-warning">Seulement 3 images sont autorisées</div>');
        return;
    }
    var template = emplacement.attr('data-prototype').replace(/__name__label__/g, 'Image n°'+(index+1)).replace(/__name__/g, index).replace('<div>', '<div class="image-box">');

    var prototype = $(template);
    addDeleteLink(prototype);
    emplacement.append(prototype);
}

function addDeleteLink(prototype){
    var deleteLink = $('<a href="#" class="btn btn-danger">Supprimer</a>');
    prototype.append(deleteLink);
    deleteLink.click(function(event){
        prototype.remove();
        event.preventDefault();
        return false;
    });
}

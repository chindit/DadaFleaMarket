/**
 * Created by david on 24/06/16.
 */
$(document).ready(function(){
    //By default, adding a button
    addInsertImageButton();
    addInsertTownButton();

    $('#advertisement_current_location').click(function(event){
        event.preventDefault();
        getLocation();
    });

    $('#insert_image').click(function(event){
        event.preventDefault();
        addImageField();
        return false;
    });

    $('#insert_town').click(function(event){
        event.preventDefault();
        addTownField();
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

function addInsertTownButton(){
    var emplacement = $('#advertisement_town');
    var index = emplacement.find(':input').length;

    if(index == 0){
        addTownField();
    }
    else{
        emplacement.children('div').each(function(){
            addDeleteLink($(this));
        });
    }
}

function addTownField(){
    var emplacement = $('#advertisement_town');

    var index = emplacement.find(':input').length;

    var template = emplacement.attr('data-prototype').replace(/__name__label__/g, 'Ville n°'+(index+1)).replace(/__name__/g, index).replace('<div>', '<div class="jumbotron">');

    var prototype = $(template);
    addDeleteLink(prototype);
    emplacement.append(prototype);
}

function addImageField(){
    var emplacement = $('#advertisement_images');
    //console.log(emplacement);

    var index = emplacement.find(':input').length;

    //Checking if we are editing and, if yes, how many images do we have
    var editKey = $('#edit-nb-images');
    if(editKey.attr('data-images') != undefined){
        //Key found -> edition
        index = index+parseInt(editKey.attr('data-images'));
        //console.log(index);
    }

    if(index == 3){
        emplacement.append('<div class="alert alert-warning">Seulement 3 images sont autorisées</div>');
        return;
    }

    var template = emplacement.attr('data-prototype').replace(/__name__label__/g, 'Image n°'+(index+1)).replace(/__name__/g, index).replace('<div>', '<div class="jumbotron">');

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
        //Adding automatically 1 town
        var emplacement = $('#advertisement_town');
        var index = emplacement.find(':input').length;
        if(index == 0){
            addTownField();
        }
        return false;
    });
}

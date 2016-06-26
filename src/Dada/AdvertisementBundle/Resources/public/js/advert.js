/**
 * Created by david on 24/06/16.
 */
$(document).ready(function(){
    $('#advertisement_current_location').click(function(event){
        event.preventDefault();
        getLocation();
    });
});
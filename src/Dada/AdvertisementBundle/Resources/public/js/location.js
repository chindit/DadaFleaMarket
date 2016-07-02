/**
 * Created by david on 24/06/16.
 */
/**
 * Return user's position
 * @param pos
 */
function locationSuccess(pos) {
    var crd = pos.coords;
    var url = $('#googlemaps-url').html();
    url = url.replace('unikey-lat', crd.latitude);
    url = url.replace('unikey-long', crd.longitude);

    $.getJSON(url, function(json){
        $('#advertisement_location').val(json.results[0].address_components[2].long_name);
        $('#advertisement_latitude').val(json.results[2].geometry.location.lat);
        $('#advertisement_longitude').val(json.results[2].geometry.location.lng);
    });
}
/**
 * Show an error if user's position is missing
 * @param err
 */
function locationError(err) {
    console.warn('ERROR(' + err.code + '): ' + err.message);
}

/**
 * Get user location
 * Calls «locationSuccess» or «locationError»
 */
function getLocation(){
    var options = {
        enableHighAccuracy: true,
        timeout: 5000,
        maximumAge: (60*60*1000) //1 hour cache
    };
    navigator.geolocation.getCurrentPosition(locationSuccess, locationError, options);
}
if (typeof mamgd_app !== 'function') {

    var mamgd_app = function () {

        var dialog;
        var processing = false;

        this.save_location = function(index){

            var location_name, lat, lon, remove_row;
            jQuery('body').css('cursor','wait');

            jQuery('.local-app-find-location-row').each(function(){
                if(jQuery(this).data('id') == index){
                    location_name = jQuery(this).data('location');
                    lat = jQuery(this).data('lat');
                    lon = jQuery(this).data('lon');
                    remove_row = jQuery(this);
                }
            })

            if(location_name && lat && lon) {

                var data = {
                    'action': 'geodirmam_manage_locations',
                    'subaction': 'add',
                    'location_name': location_name,
                    'lat': lat,
                    'lon': lon
                };

                jQuery.post(ajaxurl, data, function (result) {
                    jQuery('body').css('cursor', 'default');
                    var row_count = jQuery('.local-app-location-row').length;
                    var html = '<tr class="local-app-location-row" data-id="' + row_count + '" data-location="' + location_name + '"><td>' + location_name + '</td><td align="right">' + lat + '</td><td align="right">' + lon + '</td><td><span><div class="button" onclick="mamgd_app.remove_location(' + row_count + ');" ><span class="fa fa-minus-circle">&nbsp;</span></div></span></td></tr>';
                    mamgd_app.add_row_in_location_manager(location_name,html);

                    if(remove_row){
                        remove_row.remove();
                    }

                });

            }

            return true;
        }

        this.add_row_in_location_manager = function(location_name, html){

            var counter = 0;
            var row_count = jQuery('.local-app-location-row').length;

            if(row_count == 0){
                jQuery('.local-app-location-row-header').last().after(html);
                return true;
            }

              jQuery('.local-app-location-row').each(function() {
                counter++;
                if (jQuery(this).find('td:eq(0)').text() >= location_name) {
                  jQuery(this).before(html);
                  return false;
                }

                if (row_count === counter ) {
                  jQuery('.local-app-location-row').last().after(html);
                    return true;
                }
              });

        }


        this.add_location = function(){

            var buttons = {
                    "Close": function(){
                        dialog.dialog("close");
                    }
                };
            var html = '<input id="local-app-search-location-text" type="text" placeholder="Search for location to add to your list..." style="width:80%">&nbsp;<div class="button" onclick="mamgd_app.search_for_location();" ><span class="fa fa-search">&nbsp;</span></div>';
            html = html.concat('<div id="local-app-search-location-results" style="width:95%;margin:.75em;"></div>');

            processing = false;

            mamgd_app.show_alert_message(html, 600, 800, "Add a Location to the GeoFilters List", buttons);

            jQuery('#local-app-search-location-text').on('keyup keypress', function(e) {
                var code = e.keyCode || e.which;
                if (code == 13) {
                    e.preventDefault();
                    mamgd_app.search_for_location();
                }
            });


        }

        this.search_for_location = function(){

            if(!processing) {
                processing = true;

                var location_name = jQuery('#local-app-search-location-text').val();

                if (location_name.length == 0) {
                    jQuery('#local-app-search-location-results').text('Please enter a location name to search');
                    return;
                }

                jQuery('body').css('cursor', 'wait');

                jQuery('#local-app-search-location-results').text('Searching...');


                var data = {
                    'action': 'geodirmam_get_location_coordinates',
                    'location': location_name
                };

                jQuery.post(ajaxurl, data, function (result) {

                    if (result) {
                        var records_found = false;

                        var html = '<table style="width:100%">';
                        html = html.concat('<tr><td style="font-weight: bold">Location Name</td><td align="right" style="font-weight: bold">Latitude</td><td align="right" style="font-weight: bold">Longitude</td><td>&nbsp;</td></tr>');

                        jQuery(result).each(function(index, value) {
                            var add_record = true;
                            jQuery('.local-app-location-row').each(function() {
                                if (jQuery(this).find('td:eq(0)').text() == value.location_name) {
                                    add_record = false;
                                }
                            })
                            if(add_record){
                                records_found = true;
                                html = html.concat('<tr class="local-app-find-location-row" data-id="'+index+'" data-location="'+value.location_name+'" data-lat="'+value.lat+'" data-lon="'+value.lon+'"><td>'+value.location_name+'</td><td align="right">'+value.lat+'</td><td align="right">'+value.lon+'</td><td><span><div class="button" onclick="mamgd_app.save_location('+index+');" ><span class="fa fa-plus-circle">&nbsp;</span></div></span></td></tr>');
                            }
                        })

                        html = html.concat('</table>');

                        if(!records_found){
                            html = 'No locations found for your search';
                        }


                        jQuery('#local-app-search-location-results').html(html);

                    }

                    processing = false;
                    jQuery('body').css('cursor', 'default');
                });
            }

        };

        this.remove_location = function(index){

            jQuery('body').css('cursor','wait');

            var location_name;
            jQuery('.local-app-location-row').each(function(){
                if(jQuery(this).data('id') == index){
                    location_name = jQuery(this).data('location');
                }
            })

            if(!location_name){
                jQuery('body').css('cursor','default');
                return;
            }

            var data = {
                'action': 'geodirmam_manage_locations',
                'subaction' : 'remove',
                'location_name' : location_name
            };

            jQuery.post(ajaxurl, data, function (result) {
                jQuery('.local-app-location-row').each(function(){
                if(jQuery(this).data('id') == index){
                    jQuery(this).remove();
                }
            })
                jQuery('body').css('cursor','default');
            });

        }

        this.show_alert_message = function(message , height , width , title, buttons){

            if (!jQuery('#local_app_dialog').length) {
                jQuery('body').append('<div id="local_app_dialog"></div>');
            }

            if(!height) height = 250;
            if(!width) width = 400;
            if(!title) title = 'Message';

            dialog = jQuery( "#local_app_dialog" ).dialog({
                closeOnEscape: false,
                height: height,
                width: width,
                modal: true,
                title: title,
                open: function(event, ui) {

                    jQuery(".ui-dialog-titlebar-close").hide();

                    var html_line = message;

                    jQuery('#local_app_dialog').html(html_line);

                },
                buttons: buttons
            });
        }
    }
}


jQuery(document).ready(function(){
    try {

        mamgd_app = new mamgd_app;

    }catch(err){
        alert(err.message);
    }
});

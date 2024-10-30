<?php

add_action( 'wp_ajax_geodirmam_get_location_coordinates', 'geodirmam_get_location_coordinates' );
add_action( 'wp_ajax_nopriv_geodirmam_get_location_coordinates', 'geodirmam_get_location_coordinates' );

function geodirmam_get_location_coordinates(){
    $lm = new geodirmam_location_manager_tab();
    $lm->ajax_handler();
}

add_action( 'wp_ajax_geodirmam_manage_locations', 'geodirmam_manage_locations' );
add_action( 'wp_ajax_nopriv_geodirmam_manage_locations', 'geodirmam_manage_locations' );

function geodirmam_manage_locations(){
    $lm = new geodirmam_location_manager_tab();
    $lm->manage_locations();
}

class geodirmam_location_manager_tab {

    function __construct(){

    }

    function manage_locations(){

        if(function_exists('local_app_update_cursor')) local_app_update_cursor(true);

        $locations = get_option('tsl-local-app-location-manager');

        switch($_REQUEST['subaction']){
            case 'add':
                $add_me = true;
                foreach($locations['results'] as $index => $location){
                    if($location['location_name'] == $_REQUEST['location_name']) $add_me = false;
                }
                if($add_me && strlen($_REQUEST['location_name'])>0 && strlen($_REQUEST['lat'])>0 && strlen($_REQUEST['lon'])>0 ){
                    $locations['results'][] = array('location_name' => $_REQUEST['location_name'] , 'lat' => $_REQUEST['lat'] , 'lon' => $_REQUEST['lon'] );
                }
                break;
            case 'remove':
                foreach($locations['results'] as $index => $location){
                    if($location['location_name'] == $_REQUEST['location_name']) unset($locations['results'][$index]);
                }
                break;
        }

        $sort_locations = $locations['results'];
        $this->mamgd_app_sksort($sort_locations, 'location_name',true);
        $locations['results'] = $sort_locations;

        update_option('tsl-local-app-location-manager' , $locations);
        wp_send_json( array('status' => 'done' ) );
        die;

    }

    function ajax_handler($testing = false, $location = null){

        if(isset($_REQUEST['location'])){
            $location = $_REQUEST['location'];
        }else if(!$location) {
            if($testing) return array();
            wp_send_json(array());
            die();
        }

        $querystring = 'https://tinyscreenlabs.com/wp-admin/admin-ajax.php?action=tsl_get_coordinates&location='.$location;

        $response = wp_remote_get($querystring);

        if (is_array($response) && !is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            unset($data['results'][0]);
            if($testing) return $data;

            $result = array();

            foreach($data['results'] as $index => $value ){
                $result[] = array('location_name' => $value['location_name'] , 'lat' => $value['lat'] , 'lon' => $value['lon'] );
            }

            wp_send_json( $result );
            die();
        }
    }

    function create_tab(){

        $locations = get_option('tsl-local-app-location-manager');

        $sort_locations = $locations['results'];
        $this->mamgd_app_sksort($sort_locations, 'location_name',true);
        $locations['results'] = $sort_locations;

        update_option('tsl-local-app-location-manager' , $locations);

        $html_line = '<table id="local-app-location-manager-listings" style="width:100%;margin-left:.5em;">';
        $html_line .= '<tr class="local-app-location-row-header" ><td style="font-weight: bold">Location Name</td><td align="right" style="font-weight: bold">Latitude</td><td align="right" style="font-weight: bold">Longitude</td><td>&nbsp;</td></tr>';

        $row_number = 0;
        foreach($locations['results'] as $index => $location){
            $html_line .= '<tr class="local-app-location-row" data-id="'.$row_number.'" data-location="'.$location['location_name'].'"><td>'.$location['location_name'].'</td><td align="right">'.$location['lat'].'</td><td align="right">'.$location['lon'].'</td><td><span><div class="button" onclick="mamgd_app.remove_location('.$row_number.');" ><span class="fa fa-minus-circle">&nbsp;</span></div></span></td></tr>';
            $row_number++;
        }

        $html_line .= '<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><div class="button" onclick="mamgd_app.add_location();" ><span class="fa fa-plus-circle">&nbsp;</span></div></td></tr>';

        $html_line .= '</table>';


        return $html_line;
    }

    function mamgd_app_sksort(&$array, $subkey="id", $sort_ascending=false) {

        $temp_array = array();

        if (count($array))
            $temp_array[key($array)] = array_shift($array);

        foreach($array as $key => $val){
            $offset = 0;
            $found = false;
            foreach($temp_array as $tmp_key => $tmp_val)
            {
                if(!$found and strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
                {
                    $temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
                                                array($key => $val),
                                                array_slice($temp_array,$offset)
                                              );
                    $found = true;
                }
                $offset++;
            }
            if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
        }

        if ($sort_ascending) $array = array_reverse($temp_array);

        else $array = $temp_array;
    }

}

    ?>
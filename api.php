<?php
/**
* Grab latest post title by an author!
*
* @param array $data Options for the function.
* @return string|null Post title for the latest,  * or null if none.
*/

add_filter('media_upload_tabs', function($tabs) {
    $tabs['dbsdk_grabfromurl'] = __('Designit');
    return($tabs);
});

add_action('media_upload_dbsdk_grabfromurl', function() {
    return dbsdk_HandleUpload();
});

// Check image name extension
function dbsdk_fileType($fileType = NULL){
    $result = '';
    $arr = array(
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
    );

    foreach ($arr as $key => $value) {
        if($value == $fileType){
            $result = $key;
        }
    }

    return $result;
}

function dbsdk_HandleUpload() {
    $flag = true;
    $post_id = $_REQUEST['post_id'] ? (int)$_REQUEST['post_id'] : 0;
    $dbsdk_grabfrom_url = sanitize_text_field($_POST['dbsdk_grabfrom_url']);
    $dbsdk_file_name = sanitize_text_field($_POST['dbsdk_file_name']);

    if ( isset( $dbsdk_grabfrom_url ) && $dbsdk_grabfrom_url != '' && $dbsdk_file_name != '' && get_post_status($post_id)) {

        $file_array = array();
        $file_array['tmp_name'] = download_url($dbsdk_grabfrom_url);

        // Get info image
        $fileType = getimagesize ($file_array['tmp_name']);
        $image_type = $fileType[2];
     
        // Check file_array is an image or not
        if(!in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP))){
            $flag = false;
        }

        // Check image name extension
        $ex = dbsdk_fileType($fileType['mime']);

        if($ex != '' && $flag == true){
            $file_array['name'] = $dbsdk_file_name . '.' . $ex;

            if (is_wp_error($file_array['tmp_name'])) {
                @unlink($file_array['tmp_name']);
                return new WP_Error('grabfromurl', 'Could not download image from remote source');
            }

            $attachmentId = media_handle_sideload($file_array, $post_id);

            $obj_data = (object)[];

            if( $attachmentId ){
                // create the thumbnails
                $attach_data = wp_generate_attachment_metadata($attachmentId, get_attached_file($attachmentId));

                wp_update_attachment_metadata( $attachmentId,  $attach_data );
                
                // Get image info in media library after upload image on wordpress
                $arr_info_image = wp_get_attachment_image_src($attachmentId, array('700', '600'), "", array( "class" => "img-responsive" ));

                $arr_temp = array('url' => $arr_info_image[0], 'width' => $arr_info_image[1], 'height' => $arr_info_image[2], 'is_intermediate' => $arr_info_image[3]);
                
                $obj_data->image_info = $arr_temp;
                $obj_data->post_id = $post_id;
            }
            
            header("Content-type: application/json; charset=utf-8");
            echo json_encode($obj_data);
        }
    }else{
        $errors = array();
        $id = 0;
        return wp_iframe( 'media_upload_type_form', 'image', $errors, $id );
    }
}
<?php

namespace  App\Http\Helpers;
use  \getID3;

class FilesHelper {
    function getFileType( $fileName ) {
        $extension = pathinfo( $fileName, PATHINFO_EXTENSION );

        switch ( $fileName) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
            return 'image';
            case 'mp4':
            case 'webm':
            case 'mov':
            case 'avi':
            return 'video';
            case 'mp3':
            case 'ogg':
            return 'audio';
            case 'zip':
            return 'archive';
            default:
            return 'unknownw';
        }
    }

    function determineFileType( $filePath ) {
        // Get the file size in bytes
        $fileSize = filesize( $filePath );

        // Define size thresholds for each type
        $movieThreshold = 100000000;
        // Example threshold for a 'movie' ( 100MB )
        $skitThreshold = 50000000;
        // Example threshold for a 'skit' ( 50MB )

        // Compare the file size with the thresholds
        if ( $fileSize > $movieThreshold ) {
            return 'movie';
        } elseif ( $fileSize > $skitThreshold ) {
            return 'clip';
        } else {
            return 'skit';
        }
    }

    function fi( $filePath ) {
        $getID3 = new getID3;
        return $getID3->analyze( $filePath );
    }
}
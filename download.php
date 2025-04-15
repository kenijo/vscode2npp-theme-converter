<?php

if (isset($_GET['zipFilename'])) {
    $zipFilename = 'cache' . DIRECTORY_SEPARATOR . urldecode($_GET['zipFilename']);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="npp-theme.zip"');
    header('Content-Length: ' . filesize($zipFilename));
    readfile($zipFilename);
    unlink($zipFilename);
}

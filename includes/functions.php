<?php
function cleanInput($data)
{
    return htmlspecialchars(trim($data));
}

function getPhotoPath($photo)
{
    if (!empty($photo) && file_exists(__DIR__ . '/../assets/img/' . $photo)) {
        return 'assets/img/' . $photo;
    }

    return 'assets/img/default.png';
}

function getAdminPhotoPath($photo)
{
    if (!empty($photo) && file_exists(__DIR__ . '/../assets/img/' . $photo)) {
        return '../../assets/img/' . $photo;
    }

    return '../../assets/img/default.png';
}
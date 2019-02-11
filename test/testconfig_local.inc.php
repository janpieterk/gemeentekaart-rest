<?php

//  Copyright (C) 2006-2008 Meertens Instituut / KNAW
//  Copyright (C) 2019 Jan Pieter Kunst
//
//  The following code is a derivative work of the code from the Meertens Kaart module.
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
//
//  You should have received a copy of the GNU General Public License along
//  with this program; if not, write to the Free Software Foundation, Inc.,
//  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.

// Change this to the hostname of the web server running the gemeentekaaart-rest REST service
define('KAART_SERVER_HOSTNAME', 'localhost');

// Leave these alone
define('KAART_TESTDIRECTORY', __DIR__ . '/tmp');
define('KAART_SERVER_PATH', '/' . basename(dirname(__DIR__)) . '/');
define('KAART_REFERENCE_IMAGES_DIR', __DIR__ . '/reference_images');


// curl_file_create for PHP < 5.5
// see http://www.php.net/curl_file_create
if (!function_exists('curl_file_create')) {
    function curl_file_create($filename, $mimetype = '', $postname = '')
    {
        return "@$filename;filename="
          . ($postname ?: basename($filename))
          . ($mimetype ? ";type=$mimetype" : '');
    }
}

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

require('rest.config.inc.php');

use JanPieterK\GemeenteKaart\Kaart;
use JanPieterK\GemeenteKaart\REST\RequestParser;
use JanPieterK\GemeenteKaart\REST\WebService;

$requestparser = new RequestParser();

if ($requestparser->error) {
    REST::error(400, $requestparser->getError());
    exit;
}

$parameters = $requestparser->getParameters();

if (empty($parameters)) {
    $kaart_url = ((isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) ? 'https'
            : 'http')
        .'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    echo REST::html_start('Kaart REST service documentation');
    echo str_replace('##KAART_URL##', $kaart_url,
        file_get_contents('RESTdocumentation.html'));
    echo REST::html_end();
    exit;
}

if (! isset($parameters['type'])) {
    $parameters['type'] = 'gemeentes';
}

if (isset($parameters['possibletypes'])) {
    $data = json_encode(Kaart::getAllowedMaptypes());
    header('Content-type: application/json');
    echo $data;
    exit;
}

if (isset($parameters['possibleformats'])) {
    $data = json_encode(Kaart::getAllowedFormats());
    header('Content-type: application/json');
    echo $data;
    exit;
}

if (isset($parameters['possibleyears'])) {
    $data = json_encode(Kaart::getAllowedYears($parameters['type']));
    header('Content-type: application/json');
    echo $data;
    exit;
}

if (isset($parameters['possiblemunicipalities']) || isset($parameters['possibleareas'])) {
    if (isset($parameters['year'])) {
        try {
            $kaart = new Kaart($parameters['type'], $parameters['year']);
        } catch (\InvalidArgumentException $e) {
            REST::error(400, $e->getMessage());
            exit;
        }
    } else {
        $kaart = new Kaart($parameters['type']);
    }
    $possibleareas = $kaart->getPossibleAreas();
    if (is_null($possibleareas)) {
        $possibleareas = array();
    }
    $data = json_encode($possibleareas);
    header('Content-type: application/json');
    echo $data;
    exit;
}

if (isset($parameters['pathsfile'])) {
    if (stream_resolve_include_path(REST_COORDS_DIR.'/'.$parameters['pathsfile'])
        !== FALSE) {
        $paths_file = REST_COORDS_DIR.'/'.$parameters['pathsfile'];
    } else {
        $paths_file = null;
    }
    $kaart = new Kaart($parameters['type']);
    $kaart->setPathsFile($paths_file);
} elseif (isset($parameters['year'])) {
    try {
        $kaart = new Kaart($parameters['type'], $parameters['year']);
    } catch (\InvalidArgumentException $e) {
        REST::error(400, $e->getMessage());
        exit;
    }
} else {
    $kaart = new Kaart($parameters['type']);
}

if (!isset($parameters['data'])) {
    $parameters['data'] = array();
}

WebService::createMap($kaart, $parameters['type'], $parameters);

if (isset($parameters['imagemap'])) {
    // so that imagemap=1 returns a partial imagemap when links and/or tooltips are set and interactive is not set
    // and a complete imagemap when links and/or tooltips are not set
    if (!isset($parameters['links']) && !isset($parameters['tooltips']) && !isset($parameters['linkhighlightedonly'])) {
        $kaart->setInteractive(true);
    }
    // Goes nowhere, but this is needed to create the imagemap
    $kaart->fetch($parameters['format']);
    header('Content-type: text/html');
    echo $kaart->getImagemap();
} elseif (isset($parameters['base64'])) {
    header('Content-type: text/plain');
    echo base64_encode($kaart->fetch($parameters['format']));
} else {
    $kaart->show($parameters['format']);
}

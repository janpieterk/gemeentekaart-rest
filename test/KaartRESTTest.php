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

require('testconfig_local.inc.php');
use PHPUnit\Framework\TestCase;

echo "Testing REST services on " .  KAART_SERVER_HOSTNAME . KAART_SERVER_PATH . "\n";

class KaartRESTTest extends TestCase
{

    private $_ch;
    private $_base_url;

    public function setUp()
    {
        $this->_base_url = 'http://' . KAART_SERVER_HOSTNAME . KAART_SERVER_PATH;
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, 1);
    }

    public function tearDown()
    {
        curl_close($this->_ch);
    }

    private function saveFile($filename, $data)
    {
        file_put_contents(KAART_TESTDIRECTORY . '/' . $filename, $data);
        return file_get_contents(KAART_TESTDIRECTORY . '/' . $filename);
    }

    private function setupJSONRequest($url, $file)
    {
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_POST, 1);
        $json = file_get_contents($file);
        curl_setopt(
            $this->_ch,
            CURLOPT_HTTPHEADER,
            array('Content-type: application/json', 'Content-length: ' . strlen($json))
        );
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $json);
    }
    
    /**
     * @param $actual
     * @param $expected
     * @param int $fuzzfactor
     * @return mixed
     * @throws \ImagickException
     */
    private function compareTwoImages($actual, $expected, $fuzzfactor = 0)
    {
        if ($fuzzfactor === 0) {
            $image1 = new imagick($actual);
            $image2 = new imagick($expected);
            $result = $image1->compareImages($image2, imagick::METRIC_MEANABSOLUTEERROR);
        } else {
            // see http://nl1.php.net/manual/en/imagick.compareimages.php + comments
            $image1 = new imagick();
            $image2 = new imagick();
            $image1->SetOption('fuzz', $fuzzfactor . '%');
            $image1->readImage($actual);
            $image2->readImage($expected);
            $result = $image1->compareImages($image2, 1);
        }
        return $result[1];
    }

    /**
     * @throws ImagickException
     */
    public function testGetBitmapMunicipalities()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testCreateHighlightedMunicipalitiesMap()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png&width=500';
        $this->setupJSONRequest($url, __DIR__ . '/data/simplemunicipalitieslist.json');
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    public function testCreateMunicipalitiesImagemapWithLinksNonInteractive()
    {
        $filename = substr(__FUNCTION__, 4) . '.html';
        $url = $this->_base_url . '?type=gemeentes&format=png&imagemap=1';
        $this->setupJSONRequest($url, __DIR__ . '/data/complexmunicipalitieslist.json');
        $kaart = curl_exec($this->_ch);
        $expected = '6c2781d32fb598c119827127c2ddd48f';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }

    public function testCreateMunicipalitiesImagemapWithLinksInteractive()
    {
        $filename = substr(__FUNCTION__, 4) . '.html';
        $url = $this->_base_url . '?type=gemeentes&format=png&imagemap=1&interactive=1';
        $this->setupJSONRequest($url, __DIR__ . '/data/complexmunicipalitieslist.json');
        $kaart = curl_exec($this->_ch);
        $expected = 'b8b9c8579e25181dd589cb35a649be41';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }


    public function testCreateMunicipalitiesImagemapWithLinkNonInteractiveHighlightedOnly()
    {
        $filename = substr(__FUNCTION__, 4) . '.html';
        $url = $this->_base_url . '?type=gemeentes&format=png&imagemap=1&linkhighlightedonly=1';
        $this->setupJSONRequest($url, __DIR__ . '/data/complexmunicipalitieslist_link.json');
        $kaart = curl_exec($this->_ch);
        $expected = '2d2aabf64ed159d4b89c2bcff3971224';
        file_put_contents(KAART_TESTDIRECTORY . '/' . $filename, $kaart);
        $actual = md5(file_get_contents(KAART_TESTDIRECTORY . '/' . $filename));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }


    public function testCreateMunicipalitiesSVGWithLinkNonInteractiveHighlightedOnly()
    {
        $filename = substr(__FUNCTION__, 4) . '.svg';
        $url = $this->_base_url . '?type=gemeentes&format=svg&&linkhighlightedonly=1';
        $this->setupJSONRequest($url, __DIR__ . '/data/complexmunicipalitieslist_link.json');
        $kaart = curl_exec($this->_ch);
        $expected = 'a4a35e230d9239e75ce2cf5f5402b77f';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }


    public function testCreateMunicipalitiesImagemapWithTooltipsNonInteractive()
    {
        $filename = substr(__FUNCTION__, 4) . '.html';
        $url = $this->_base_url . '?type=gemeentes&format=png&imagemap=1';
        $this->setupJSONRequest($url, __DIR__ . '/data/tooltips.json');
        $kaart = curl_exec($this->_ch);
        $expected = '331e4fd98195255973978b91ecc6a5d2';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }

    public function testMunicipalitiesInteractiveSVG()
    {
        $filename = substr(__FUNCTION__, 4) . '.svg';
        $url = $this->_base_url . '?type=municipalities&interactive=1&format=svg';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $expected = '84f867931815112aed2cbf2064cae597';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }

    function testImagemapMunicipalities()
    {
        $filename = substr(__FUNCTION__, 4) . '.html';
        $url = $this->_base_url . '?type=gemeentes&format=png&imagemap=1';
        $this->setupJSONRequest($url, __DIR__ . '/data/simplemunicipalitieslist.json');
        $kaart = curl_exec($this->_ch);
        $expected = 'e502b3ef7811040dca72a4b352498827';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }


    public function testGetPossibleMunicipalities()
    {
        $filename = substr(__FUNCTION__, 4) . '.json';
        $url = $this->_base_url . '?possiblemunicipalities=1';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $expected = 'd1b6cc8773f9a13b24d77f5b894ea519';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }

    /**
     * @throws ImagickException
     */
    public function testBase64Map()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=dialectareas&format=png&width=300&base64=1';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, base64_decode($kaart));
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    public function testXSSAttack()
    {
        $filename = substr(__FUNCTION__, 4) . '.svg';
        $url = $this->_base_url
        . '?type=municipalities&format=svg&link=%22%3E%3C/a%3E%3C/g%3E%3C/g%3E%3Cg%20transform=%22scale%281000%29%22%3E%3Ctext%20x=%22100%22%20y=%2240%22%20fill=%22red%22%20transform=%22rotate%2830%2020,40%29%22%3EJAN%20PIETER!!!%3C/text%3E%3Cimage%20x=%2220%22%20y=%22200%22%20width=%22300%22%20height=%2280%22%20xlink:href=%22http://www.janpieterkunst.nl/jpk.jpg%22%20/%3E%3C/g%3E%3Cg%20transform=%22translate%287324,624118%29%20scale%281,-1%29%22%3E%3Cg%3E%3Ca%20xlink:href=%22';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $expected = '9a656ecdb25cd3ccb06c34d9473a72e9';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }

    public function testGetPossibleAreas()
    {
        $filename = substr(__FUNCTION__, 4) . '.json';
        $url = $this->_base_url . '?possibleareas=1&type=corop';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $expected = '8af7136556368a5ee32336a9bf717611';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");

        $filename = substr(__FUNCTION__, 4) . '.provincies.json';
        $url = $this->_base_url . '?possibleareas=1&type=provincies';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $expected = '752b4bf92f4cfb4940d577329f121403';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");

        $url = $this->_base_url . '?possibleareas=1';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HEADER, 1);
        curl_setopt($this->_ch, CURLOPT_NOBODY, 1);
        $page = curl_exec($this->_ch);
        $this->assertStringStartsWith('HTTP/1.1 400 Bad Request', $page);
    }
    
    public function testGetPossibleAreasWithYear()
    {
        $filename = substr(__FUNCTION__, 4) . '.json';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?possibleareas=1&type=gemeentes&year=1933';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $result = curl_exec($this->_ch);
        $this->assertEquals($result, file_get_contents($reference_image));
    }

    /**
     * @throws ImagickException
     */
    public function testsetAdditionalAreasCorop()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png&additionaldata=corop&width=800';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testsetAdditionalAreasProvinces()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png&additionaldata=provinces&width=800';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testsetAdditionalAreasDialect()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png&additionaldata=dialectareas&width=800';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }


    /**
     * @throws ImagickException
     */
    public function testHighlightedCorop()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=corop&format=png&width=500';
        $this->setupJSONRequest($url, __DIR__ . '/data/simplecoroplist.json');
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testCreateHighlightedMunicipalitiesNLFlandersMap()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=municipalities_nl_flanders&format=png';
        $this->setupJSONRequest($url, __DIR__ . '/data/simplemunicipalitieslist_nl_flanders.json');
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testVragenlijstenKaart()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png&width=600&additionaldata=corop';
        $this->setupJSONRequest($url, __DIR__ . '/data/data_vragenlijsten.json');
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testOutlinedProvincie()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=gemeentes&format=png&width=600&additionaldata=provincies';
        $this->setupJSONRequest($url, __DIR__ . '/data/data_outlined_provincie.json');
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    /**
     * @throws ImagickException
     */
    public function testCSVFileUpload()
    {
        $filename = substr(__FUNCTION__, 4) . '.png';
        $reference_image = KAART_REFERENCE_IMAGES_DIR . '/' . $filename;
        $url = $this->_base_url . '?type=dialectareas&format=png&width=400';
        $data['Filedata'] = curl_file_create(realpath(__DIR__ . '/data/dialectareas.csv'), 'text/csv', 'uploadfile');
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_POST, 1);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt(
            $this->_ch,
            CURLOPT_HTTPHEADER,
            array('Content-Type: multipart/form-data')
        );
        $kaart = curl_exec($this->_ch);
        $this->saveFile($filename, $kaart);
        $result = $this->compareTwoImages(KAART_TESTDIRECTORY . '/' . $filename, $reference_image);
        $this->assertEquals(0, $result, "check file $filename");
    }

    public function testAlternatePathsfile()
    {
        $filename = substr(__FUNCTION__, 4) . '.svg';
        $url = $this->_base_url . '?type=municipalities&format=svg&interactive=1&pathsfile=alternate_paths.json';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $kaart = curl_exec($this->_ch);
        $expected = '086dcbec637a100340d088877f2e4841';
        $actual = md5($this->saveFile($filename, $kaart));
        $this->assertEquals($expected, $actual, "check file " . KAART_TESTDIRECTORY . "/$filename");
    }


    public function testGetPossibleTypes()
    {
        $url = $this->_base_url . '?possibletypes=1';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $actual = curl_exec($this->_ch);
        $expected = '["municipalities","gemeentes","corop","provincies","provinces","municipalities_nl_flanders","municipalities_flanders","dialectareas"]';
        $this->assertEquals($expected, $actual);
    }

    public function testGetPossibleFormats()
    {
        $url = $this->_base_url . '?possibleformats=1';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $actual = curl_exec($this->_ch);
        $expected = '["gif","png","jpg","jpeg","svg","kml","json"]';
        $this->assertEquals($expected, $actual);
    }

        public function testGetPossibleYears()
    {
        $url = $this->_base_url . '?type=provincies&possibleyears=1';
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        curl_setopt($this->_ch, CURLOPT_HTTPGET, 1);
        $actual = curl_exec($this->_ch);
        $expected = '[1830,1860,1890,1920,1940,1950,1980]';
        $this->assertEquals($expected, $actual);
    }
}

# GemeenteKaart REST service
Deze documentatie in het [Nederlands](README.nl.md).
___

This is the REST service to [janpieterk/gemeentekaart-core](https://github.com/janpieterk/gemeentekaart-core)

## Getting Started


To install as a project in ```<DIRECTORYNAME>```, using composer:

* Make sure you have a web server running with PHP support

```
$ cd <YOUR WEBROOT>
$ composer create-project janpieterk/gemeentekaart-rest <DIRECTORYNAME> --repository='{"type":"vcs","url":"https://github.com/janpieterk/gemeentekaart-rest"}' --stability=dev
```

* Now visit ```http://<YOUR WEBHOST>/<DIRECTORYNAME>``` to view the documentation and access the REST service.

## Summary

This service can be used to create a choropleth map of either the municipalities of The Netherlands (borders as of 2007, 443 municipalities), the municipalities of Flanders (308 municipalities), the municipalities of The Netherlands and Flanders combined, the 40 COROP areas of The Netherlands, the twelve provinces of The Netherlands, or the 28 dialect areas of Daan/Blok (1969) mapped on municipality borders. Areas can be assigned colors, typically to denote the relative frequency of some phenomenon. Some predefined combinations are also possible, at the moment: municipalities with COROP areas, municipalities with provinces, municipalities with dialect areas. 


## Getting started

Maps can be requested via GET requests or POST requests with content-type ```application/json```. If the only parameters are key-value pairs, they can go either in the
request string (GET request) or in a JSON document in the request body (POST request). If there are structured
parameters a POST request with a JSON request body is advisable. Request string parameters can be
combined with a JSON request body in a POST request. If the same parameters are in the request string and in the
JSON document, the parameters from the request string have precedence over the ones in the request body.

### GET requests

Map of the municipalities, default size and format: ```http://<RESTURL>/?type=municipalities```

Map of the COROP areas, default size and format:  ```http://<RESTURL>/?type=corop```

Map of the municipalities combined with the COROP areas: ```http://<RESTURL>/?type=municipalities&additionaldata=corop```

#### JSON POST requests

POST the following document to the REST service:

```json
{
  "type": "gemeentes",
  "formet": "png",
  "width": 500,
  "data": {
    "g_0432": "#FFE680",
    "g_0420": "#FFDD55",
    "g_0448": "#FFD42A",
    "g_0476": "#FFCC00",
    "g_0373": "#D4AA00",
    "g_0400": "#AA8800",
    "g_0366": "#806600",
    "g_0463": "#FFCC00",
    "g_0462": "#FFEEAA"
  }
}
```
And get this back:
[map](img/json_example_3.png)

### Uploading files with POST requests

Content type should be `multipart/form-data`.

An alternative way to get data into the Kaart service is to upload a commma-separated file with area codes and colors. For example, the municipalities map above can alse be obtained by uploading a file with the following content to  `http://<RESTURL>/?type=gemeentes&format=png&width=500`:

```csv
"g_0432","#FFE680"
"g_0420","#FFDD55"
"g_0448","#FFD42A"
"g_0476","#FFCC00"
"g_0373","#D4AA00"
"g_0400","#AA8800"
"g_0366","#806600"
"g_0463","#FFCC00"
"g_0462","#FFEEAA"
```

## Full list of possible parameters

### `additionaldata`
**string**

Used to add an additional layer of data to a map. Currently only the combinations `type=municipalities&additionaldata=corop` and `type=municipalities&additionaldata=provinces`  are possible.

--- 
### `base64`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a base64-encoded string representation of the map is returned.

---
### `data`
**array, object or string**

a list of area codes with either highlight colors or an object containing outline, fill and strokewidth. Should generally be used in a POSTed JSON document, however simple lists can be appended to the GET string. Examples (see also above) List of highlighted municipalities:
```json
{ 
    "g_0432" : "#FFE680", 
    "g_0420" : "#FFDD55", 
    "g_0448" : "#FFD42A", 
    "g_0476" : "#FFCC00", 
    "g_0373" : "#D4AA00", 
    "g_0400" : "#AA8800", 
    "g_0366" : "#806600", 
    "g_0463" : "#FFCC00", 
    "g_0462" : "#FFEEAA" 
 } 
```
Draw the province of Groningen without a fill and with a red outline which is twice as wide as the default linewidth for the map:
```json
  {"p_20" : { "fill" : "none", "outline" : "red", "strokewidth": 2} }
```
Colors can be the name of a color (one of the 140 HTML colors), a hexadecimal representation of the form #RRGGBB, or or a hexadecimal representation of the form AABBGGRR. The 'AA' (opacity) part only has an effect with KML maps. 

---
### `format`
**string**

`svg`, `png`, `gif`, `jpeg`, `kml` or `json`. `json` returns a geoJSON object. 

---
### `height`
**integer**

height in pixels of the map. It is generally not a good idea to set the height explicitly, it is better to set the width and let the map calculate its own height. 

---
### `imagemap`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a list of HTML `<area>` elements instead of a map image is returned. Can be used to create clickable bitmap maps. Note: for maps of type municipalities, imagemap results in a complete imagemap for all municipalities only if:

1. there are no `links` or `tooltips` set;
2. there are `links` or `tooltips se`t and `interactive` is set to true;
3. `link` (singular) is set and `linkhighlightedonly` is not set.

In other cases you get a partial imagemap with only the areas set in links or tooltips. 

---
### `interactive`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, for SVG maps, onmouseover and onmouseout events to show placenames, and for bitmaps maps, area elements with title and alt attributes with placenames are added to all placemarks or areas. 

---
### `link`
**string**

Template for a link to be added to area codes. `%s` placeholders will be replaced by either the area code. For maps of a bitmap format, the links will be added to the imagemap of the picture, for maps of format SVG or KML they will be embedded in the XML. Example: `?type=municipalities&format=svg&link=http%3A%2F%2Fwww.example.com%2F%3Fcode%3D%25s` (link = urlencoded form of `http://www.example.com/?code=%s`) Applies to all areas unless the parameter `linkhighlightedonly` is set to true. 

---
### `linkhighlightedonly`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a link set with the `link` parameter is only added to higlighted areas which are set with the `data` parameter. 

---
### `links`
**object**

List of links to be added to areas. Can only be used in a POSTed JSON document. Example: 
```json
{
    "g_0462" : "http://www.example.com/something/",
    "g_0366" : "http://www.example.com/something/else/"
 }
```

---
### `pathsfile`
**string**

If it is necessary to use a non-standard file with paths in a choropleth map, a filename can be given with this parameter. Note that this file must exist on the server in the correct location (see [config file](rest.config.inc.php)) and be in the correct format. See the `coords` directory of `gemeentekaart-core` for examples.

---
### `possibleareas`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a list (in JSON format) of possible areas (area codes plus names) is returned. Note that the `type` parameter should be present also. 

---
### `possibleformats`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a list (in JSON format) of possible map formats is returned.


---
### `possiblemunicipalities`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a list (in JSON format) of possible municipalities (municipality codes plus names) is returned. This is a synonym for `possibleareas=1&type=municipalities`. Note that the `type` parameter can be left out. 

---
### `possibletypes`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a list (in JSON format) of possible map types for the `type` parameter is returned.

---
### `target`
**string**

optional target for the link given in the `link` parameter (see above). Value of the "target" attribute of the href element. 

---
### `tooltips`
**object**

List of tooltips (informative texts) to be added to areas. Can only be used in a POSTed JSON document. Example: 
```json
{
    "g_0462" : "informative text 1",
    "g_0366" : "informative text 2"
 }
```

---
### `title`
**string**

Optional title of the map, will be displayed in-picture above the map.

---
### `type`
**string**

one of the choropleth types. See `posslbletypes`

---
### `width`
**string**

width in pixels of the map. Overrides the default.

---
## Acknowledgments

* This project is a derivative work of part of the code from the [Meertens Kaart module](http://www.meertens.knaw.nl/kaart/downloads.html).
* The REST service uses the [REST service PHP library](https://github.com/pieterb/REST) by Pieter van Beek. 

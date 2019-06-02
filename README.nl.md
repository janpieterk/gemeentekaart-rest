# GemeenteKaart REST service
This documentation in [English](README.md).
___

Dit is de REST service voor [janpieterk/gemeentekaart-core](https://github.com/janpieterk/gemeentekaart-core)

## Hoe te installeren

* Zorg dat je een webserver met PHP-ondersteuning hebt draaien

Installeren vanuit github met composer:
```bash
$ cd <YOUR WEBROOT>
$ composer create-project janpieterk/gemeentekaart-rest --repository='{"type":"vcs","url":"https://github.com/janpieterk/gemeentekaart-rest"}'
```

Installeren vanuit [packagist.org](https://packagist.org) met composer:
```bash
$ cd <YOUR WEBROOT>
$ composer create-project janpieterk/gemeentekaart-rest
```

Installeren met git en composer:
```bash
$ cd <YOUR WEBROOT>
$ git clone https://github.com/janpieterk/gemeentekaart-rest.git
$ cd gemeentekaart-rest
$ composer install
```

* Ga nu naar `http://<YOUR WEBHOST>/gemeentekaart-rest` om de documentatie te bekijken en de REST service te benaderen.

De PHPUnit tests van gemeentekaart-rest laten lopen:

* edit de file [testconfig.local.inc.php](test/testconfig_local.inc.php) en verander de constante `KAART_SERVER_HOSTNAME` in de hostnaam van de webserver waarop de gemeentekaart-rest REST-service draait. Start dan het shellscript `test/run_tests.sh`.

## Samenvatting

Deze service kan gebruikt worden om een vlakkenkaart van de Nederlandse gemeentes te maken (gemeentegrenzen van 2007, 443 gemeentes), de gemeentes van Vlaanderen (308 gemeentes), de gemeentes van Nederland en Vlaanderen gecombineerd,  de veertig Nederlandse [COROPgebieden](https://nl.wikipedia.org/wiki/COROP), de twaalf Nederlandse [provincies](https://nl.wikipedia.org/wiki/Provincies_van_Nederland), of de 28 [dialectgebieden](https://nl.wikipedia.org/wiki/Jo_Daan#/media/File:Dutch-dialects.svg) uit Daan/Blok (1969) afgebeeld op gemeentegrenzen. Gemeentes kunnen ingekleurd worden. Een gebruikelijke use case is om hiermee de relatieve dichtheid van een of ander verschijnsel aan te geven. Een aantal voorgedefinieerde combinaties is ook mogelijk, op het moment: gemeentes met COROPgebieden, gemeentes met provincies, gemeentes met dialectgebieden. 

### Nieuw in versie 1.1

Vanaf versie 1.1 zijn de historische gemeente- en provinciegrenzen uit de dataset [NLGis shapefiles](https://doi.org/10.17026/dans-xb9-t677) door Dr. O.W.A. Boonstra  openomen in het project. Grenzen van 1812 tot en met 1997 zijn beschikbaar en kunnen opgevraagd worden door een `yea<` parameter aan het request toe te voegen. Merk op dat geen `year` parameter resulteert in de grenzen van 2007 van versie 1.0. Kaarten die gemaakt zijn met de  NLGis data set gebruiken _Amsterdamse codes_ voor gemeentes (zie Van der Meer & Boonstra 2011) terwijl de kaarten van versie 1.0 CBS codes gebruiken.

## Hoe te beginnen

Kaarten kunnen opgevraagd worden met GET-requests of met POST-requests met content-type `application/json`. Als de enige parameters key/waarde paren zijn kunnen ze ofwel in de requeststring ondergebracht worden (GET-request) ofwel in een JSON document in de body van een POST request. Voor parameters met meer structuur is een POST-request met JSON requestbody aan te raden. Parameters in de requeststring kunnen gecombineerd worden met een JSON requestbody in een POST-request. Als dezelfde parameters zowel in de requeststring als in een JSON document voorkomen hebben de parameters in de reqeuststring voorrang.

### GET-requests

Kaart van de gemeentes, default format en grootte: `http://<RESTURL>/?type=municipalities`

Kaart van de COROPgebieden, default format en grootte:  `http://<RESTURL>/?type=corop`

Kaart van de gemeente gecombineerd met de COROPgebieden: `http://<RESTURL>/?type=municipalities&additionaldata=corop`

#### JSON POST-requests

POST het volgende document (gebruikt CBS-codes) naar de REST-service:

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
And krijg dit terug:
[kaart](img/json_example_3.png)

POST het volgende document (gebruikt Amsterdamse codes) naar de REST-service:

```json
{
    "data": {
        "a_11150": "#990000",
        "a_10345": "#990000",
        "a_11434": "#990000",
        "a_10722": "#990000"
    },
    "year": 1821,
    "type": "gemeentes",
    "format": "svg",
    "interactive": true
}
```
En krijg dit terug:
[map](img/svg_example.svg)

### Met POST-requests files uploaden

Content-type moet `multipart/form-data` zijn.

Een alternatieve manier om data naar de Kaart-service te sturen is het uploaden van een kommagescheiden bestand met gebiedscodes en kleuren. De gemeentekaart hierboven kan bijvoorbeeld ook verkregen worden door een bestand met de volgende inhoud te uploaden naar  `http://<RESTURL>/?type=gemeentes&format=png&width=500`:

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

## Complete lijst van mogelijke parameters

### `additionaldata`
**string**

Gebruikt om een extra laag aan een kaart toe te voegen. Op het moment zijn alleen de combinaties `type=municipalities&additionaldata=corop`, `type=municipalities&additionaldata=provinces` en `type=municipalities&additionaldata=dialectareas` mogelijk.

--- 
### `base64`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt de kaart teruggegeven als base64-gecodeerde string.

---
### `data`
**array, object of string**

Een lijst van gebiedscodes, met ofwel enkele kleuren, ofwel een object met omlijning, vulling en lijndikte. Te gebruiken in een JSON-document. Voorbeelden: lijst van ingekleurde gemeentes (CBS-codes):
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
De provincie Groningen zonder vulling en met een rode omlijning die twee keer zo dik is als de default lijndikte van de kaart:
```json
  {"p_20" : { "fill" : "none", "outline" : "red", "strokewidth": 2} }
```

Kleuren kunnen de naam van een kleur zijn (een van de 140 HTML-kleuren), een hexadecimale representatie van de vorm #RRGGBB, of een hexadecimale representatie van de vorm AABBGGRR. Het 'AA' gedeelte (ondoorschijnendheid) heeft alleen effect bij KML-kaarten.

---
### `format`
**string**

`svg`, `png`, `gif`, `jpeg`, `kml` or `json`. `json` geeft een geoJSON-object terug. 

---
### `height`
**integer**

Hoogte in pixels van de kaart. Het is doorgaans geen goed idee om de hoogte expliciet te zetten. Beter is de breedte te zetten en de kaart op grond daarvan zijn eigen hoogte te laten berekenen.

---
### `imagemap`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt een lijst van HTML `<area>` teruggegeven in plaats van een kaart. Kan gebruikt worden om aanklikbare bitmap-kaarten te maken. Let op: `imagemap` levert alleen een complete imagemap voor alle gebieden op als:
 
1. er geen `links` of `tooltips` gezet zijn;
2. als er `links` or `tooltips` gezet zijn en `interactive` op true gezet is;
3. `link` (enkelvoud) gezet is en `linkhighlightedonly` niet gezet is.

In andere gevallen krijg je een gedeeltelijke imagemap met alleen de gebieden met links of tooltips. 

---
### `interactive`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet worden bij SVG-kaarten onmouseover en onmouseout events toegevoegd om namen van gebieden te tonen en voor bitmap-kaarten worden er `<area>`-elementen gegenereerd met title- en alt-attributen om namen van gebieden te tonen (op te vragen met `imagemap`).

---
### `link`
**string**

Template voor een link om aan alle gebieden toegevoegd te worden. `%s` placeholders worden vervangen door de gebiedscode. Bij bitmapkaarten worden de links toegevoegd aan de imagemap van de afbeelding, bij SVG- of KML-kaarten worden ze onderdeel van de XML. Voorbeeld: `?type=municipalities&format=svg&link=http%3A%2F%2Fwww.example.com%2F%3Fcode%3D%25s` (link = url-gecodeerde vorm van `http://www.example.com/?code=%s`). Heeft betrekking op alle gebieden tenzij de parameter `linkhighlightedonly` op true gezet is. 

---
### `linkhighlightedonly`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt een link die met de `link` parameter gezet is alleen toegevoegd een ingekleurde gebieden die met de `data` parameter gezet zijn.

---
### `links`
**object**

Lijst met links om aan gebieden toe te voegen. Kan alleen in een ge-POST JSON-document gebruikt worden. Voorbeeld: 
```json
{
    "g_0462" : "http://www.example.com/something/",
    "g_0366" : "http://www.example.com/something/else/"
 }
```

---
### `pathsfile`
**string**

Als het nodig is om een niet-standaard bestand met paden te gebruiken voor een vlakkenkaart kan er met deze parameter een filenaam opgegeven worden. Let op dat deze file moet bestaan op de server in de correcte locatie (zie [config file](rest.config.inc.php)) en het juiste format moet hebben. Zie de `coords`-directory van `gemeentekaart-core` voor voorbeelden.

---
### `possibleareas`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt er een lijst (in JSON-format) teruggegeven met mogelijke gebieden (gebiedscodes plus namen). Merk op dat er ook een `type`-parameter moet zijn. Een `year` parameter is mogelijk bij gemeentes en provincies, zie `possiblemunicipalities` en `possibleyears`.

---
### `possibleformats`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt er een lijst (in JSON-format) teruggegeven met mogelijke formats voor een kaart.

---
### `possiblemunicipalities`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt er een lijst (in JSON-format) teruggegeven met mogelijke gemeentes (gemeentecodes plus namen). Merk op dat de `type`-parameter weggelaten kan worden (is in dit geval impliciet `municipalities`/`gemeentes`.)  Merk op dat toevoegen van een `year` parameter resulteert in in de Amsterdamse codes and gemeentes voor het gegeven jaar terwijl geen '`year` parameter resulteert in de CBS codes van 2007 (gedrag van versie 1.0).

---
### `possibletypes`
**boolean**

Als deze parameter op `1`, `on`, `true` of `yes` is gezet wordt er een lijst (in JSON-format) teruggegeven met mogelijke kaarttypes voor de `type` parameter.

---
### `possibleyears`
**boolean**

If this parameter is set to `1`, `on`, `true` or `yes`, a list (in JSON format) of possible years for the `year` parameter for the given map type is returned.

---
### `target`
**string**

optionele 'target' voor de link die opgegeven is met de `link` parameter (zie aldaar). Waarde van het "target" attribuut van het href element. 

---
### `tooltips`
**object**

Lijst van tooltips (informatieve teksten) om aan gebieden toe te voegen. Kan alleen in een ge-POST JSON-document gebruikt worden. Voorbeeld:

```json
{
    "g_0462" : "informatieve tekst 1",
    "g_0366" : "informatieve tekst 2"
 }
```

---
### `title`
**string**

Titel van de kaart. Niet verplicht. Wordt onderdeel van de afbeelding, boven de kaart getoond.

---
### `type`
**string**

Een van de vlakkenkaarttypes. Zie `possibletypes`.

---
### `width`
**string**

Breedte in pixels van de kaart. Vervangt de standaardbreedte.

---
### `year`
**integer**

Het gewenste jaar voor het gevraagde kaarttype.

---
## Bibliografie
J. Daan en D.P. Blok (1969). _Van randstad tot landrand. Toelichting bij de kaart: dialecten en naamkunde. Bijdragen en mededelingen der Dialectencommissie van de Koninklijke Nederlandse Akademie van Wetenschappen te Amsterdam 37_, Amsterdam, N.V. Noord-Hollandsche uitgevers maatschappij.

Meer, Ad van der  and Onno Boonstra (2011). _Repertorium van Nederlandse gemeenten vanaf 1812, waaraan toegevoegd: de Amsterdamse code_. 2de editie. [Den Haag: DANS.] (DANS Data Guides, 2). [https://dans.knaw.nl/nl/over/organisatie-beleid/publicaties/DANSrepertoriumnederlandsegemeenten2011.pdf](https://dans.knaw.nl/nl/over/organisatie-beleid/publicaties/DANSrepertoriumnederlandsegemeenten2011.pdf)

## Dankwoord

* Dit project is een afgeleide van een gedeelte van de code van de [Meertens Kaartmodule](http://www.meertens.knaw.nl/kaart/downloads.html).
* De REST service gebruikt de [REST service PHP library](https://github.com/pieterb/REST) door Pieter van Beek. 
* Zie ook het dankwoord bij [janpieterk/gemeentekaart-core](https://github.com/janpieterk/gemeentekaart-core) 

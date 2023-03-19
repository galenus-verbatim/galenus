# [en] Verbatim


PHP lib to publish an XML/[TEI](https://tei-c.org/release/doc/tei-p5-doc/en/html/REF-ELEMENTS.html)/[Epidoc](http://epidoc.stoa.org/) corpus produced with [Verbapie]().


# [fr] Spécifications (en cours de mise en ordre)

Verbatim est une application pour publier, chercher et annoter des corpus de grec ancien conforme au schéma XML/[TEI](https://tei-c.org/release/doc/tei-p5-doc/en/html/REF-ELEMENTS.html)/[Epidoc](http://epidoc.stoa.org/). Il sera en premier lieu dédié à l’œuvre de Galien pour des recherches linguistiques, mais tout de suite conçu pour s’adapter à d’autres oeuvres, dont notamment, de la correspondance byzantine.

## Fonctionnalités de base

Ces fonctionnalités sont prioritaires et requises pour l’œuvre de Galien.

* Doit pouvoir s’installer sur un hébergement mutualisé PHP à bas coût (ex : doctorant)
* Possibilité de mise en parallèle de plusieurs éditions et traductions d’une même unité textuelle 
* URL pérenne pour chaque découpage du texte : fichier / livre / chapitre / occurrence | page Kühn, conforme au patrons d’identification cts de Perseus (cf. [Athénée de Naucratis](https://digitalathenaeus.org/))
* Système d’annotation au mot près d’une unité textuelle, indépendamment de l’édition ou de la langue
* Recherche plein texte lemmatisée du grec
* Navigation claire dans une œuvre par la table des matières
* Accueil personnalisable et textes rédigés autour de l’édition dans l’esprit d’un WordPress
* Personnalisation XSLT et CSS pour adapter la présentation du texte en ligne à du balisage supplémentaire (ex: bulle au survol d’une oeuvre citée donnant une référence bibliographique complète)

## Fonctionnalités supplémentaires

D’autres fonctionnalités sont souhaitées et doivent être permises par l’architecture

* Index des noms de personnes
* Index des citations
* Index des œuvres citées
* Index verborum memorabilium (index des mots rares défini comme moins de 3 apparitions dans le TLG)

## Question concrètes

* Quelle norme de translittération pour chercher dans le grec ? Le BetaCode Perseus ?

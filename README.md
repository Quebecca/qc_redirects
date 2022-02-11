Qc Redirects
==============================================================
*La [version française](#documentation-qc_redirects) de la documentation suit le texte anglais*

## About
This extension adds features to the TYPO3 Core Redirects module.

- Ability to import a list of redirects by copy-pasting a list of redirects from an Excel/CSV file (exemple found in /Documentation/).
- Adds a new, optional **Title** field so when you use complex Regexp, you got a more readable item. 
- Shows the created date (field createdon)
- Shows the modified date (field updateon)

## How to import a redirect list
The best way to import is by using a CSV or an Excel file, in which we define the values of the fields to be entered.
The extension offers the option to choose the separation character.
The order of fields, used to import redirects, is : 

    Title, Source host, Source path, Target, Start time, Snd time, Is regular expression , Status code. 

The value of the source host, the source path, and the target are required.
The regular expression column takes two possible values 'true' or 'false'

### Files example
In the /Documentation/ folder you will find 2 files: One in CSV format and the other in XLS (Excel) format.


-----------

[Version française]

## Documentation qc_redirects

### À propos
Cette extension ajoute des fonctionnalités au module TYPO3 Redirects.

- Ajouts de redirections par copier-coller à partir d'un fichier Excel ou CSV (voir dans le dossier /Documentation pour des exemples)
- Ajout d'un nouveau champ **Titre**, permettant de faciliter le repérage lorsqu'on utilise des expressions régulières dans le champs source.
- Affichage de la date de création (champ "createdon")
- Affichage de la date de modification (champ "modifiedon")

## Importer une liste de redirection
La meilleure façon d’importer les redirections est d’utiliser un fichier CSV ou Excel, selon un ordre précis.  
L’extension offre la possibilité de choisir le caractère de séparation.
L’ordre d’importation des champs est le suivant :

    Titre, Chemin de hôte, Chemin de source, Cible, Date de debut, Date de fin, Est une expression régulière, Code d'état. 

La valeur de champ Chemin d'hôte, Chemin de source et Cible sont obligatoires.  
Le champ "Est une expression régulière" peut prendre uniquement les valeurs 'true' ou 'false'.

### Fichiers d'exemples
Dans le dossier /Documentation/ , vous y trouverez 2 fichiers: Un au format CSV, l'autre au format XLS (Excel).

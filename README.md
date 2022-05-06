Qc Redirects
==============================================================
*La [version française](#documentation-qc_redirects) de la documentation suit le texte anglais*

## About
This extension adds features to the TYPO3 Core Redirects module.

- Ability to import a list of redirects by copy-pasting a list of redirects from an Excel/CSV file (exemple found in /Documentation/).
- Adds a new, optional **Title** field so when you use complex Regexp, you got a more readable item. 
- Shows the created date (field createdon)
- Shows the modified date (field updateon)
- Adds the title and the creation date columns to the 'Redirects' module table
- Adds filter by title
- Adds sorting by creation date and alphabetical sorting for the title as well as other columns of the 'Redirects' module table

## How to import a redirect list
The best way to import is by using a CSV or an Excel file, in which we define the values of the fields to be entered.
The extension offers the option to choose the separation character.
The order of fields, used to import redirects, is : 

     Source host, Source path, Target, Is regular expression, Title, Start time, End time, Status code. 

The value of the source host, the source path, the target, and 'Is regular expression' are required.

The regular expression column takes two possible values 'true' or 'false'.

NB: the default fields can be empty:

    www.example.com;/example;targetExample;false;;;;

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
- Ajouts de la colonne de titre et la date de création à la table de redirections de module 'Redirects'
- Ajouts de filtre par titre
- Ajouts de tri par date de création et le tri alphabétique pour le titre ainsi que d'autre colonnes de la table des redirections de module 'Redirects'

## Importer une liste de redirection
La meilleure façon d’importer les redirections est d’utiliser un fichier CSV ou Excel, selon un ordre précis.  
L’extension offre la possibilité de choisir le caractère de séparation.
L’ordre d’importation des champs est le suivant :

     Chemin de hôte, Chemin de source, Cible, Est une expression régulière, Titre, Date de debut, Date de fin, Code d'état. 

La valeur de champ Chemin d'hôte, Chemin de source, Cible et 'Est une expression régulière' sont obligatoires.

Le champ "Est une expression régulière" peut prendre uniquement les valeurs 'true' ou 'false'.

NB: les champs par défaut peuvent être vides : 

    www.example.com;/example;targetExample;false;;;;


### Fichiers d'exemples
Dans le dossier /Documentation/ , vous y trouverez 2 fichiers: Un au format CSV, l'autre au format XLS (Excel).

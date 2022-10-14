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
The required fields to be imported by order are : 

    source_host, source_path, target, is_regexp

The field "is_regexp" takes two possible values 'true' or 'false'.

You can import the others optional fields by specifying them in the 'Advanced field names', then you can import them easily in the import section, example :

In the 'Additional field names' we specify the optional fields by order and separated by comma: 

    title, disabled, keep_query_parameters
   
Then in the Import section : 

    www.example.com;/example;targetExample;false;MyTitleExample;true;false

Note : the optional fields can be empty, for example :
    
    www.example.com;/example;targetExample;false;;;

Note : There are fields that accept only 'true' or 'false' value to be imported, visite the Redirect TCA configuration.

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

Les champs obligatoires à importer par ordre sont les suivants : 

    source_host, source_path, target, is_regexp

Le champ "is_regexp" peut prendre uniquement les valeurs 'true' ou 'false'.

Vous pouvez importer les autres champs optionnels en les spécifiant dans le champ 'Noms des champs supplémentaires', puis on peut les ajouter dans le champ d'importation, exemple :

- Dans le champ 'Noms des champs supplémentaires' on ajoute par ordre les champs optionnels à importer :


    title, disabled, keep_query_parameters

Après dans le champ d'importation on peut les ajouter aux champs obligatoires :

    www.example.com;/example;targetExample;false;MyTitleExample;true;false

- NB: les champs optionnels peuvent être vides : 

    www.example.com;/example;targetExample;false;;;;


### Fichiers d'exemples
Dans le dossier /Documentation/ , vous y trouverez 2 fichiers: Un au format CSV, l'autre au format XLS (Excel).

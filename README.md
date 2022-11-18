Qc Redirects
==============================================================
*La [version française](#documentation-qc_redirects) de la documentation suit le texte anglais*

## About
This extension adds features to the TYPO3 Core Redirects module.

- Ability to import a list of redirects by copy-pasting a list of redirects from an Excel/CSV file (exemple found in /Documentation/);
- Adds a new, optional, **Title** field so if you use complex Regexp in the "Source" field, you got a more readable record;
- Shows the created date (field createdon) in detail view;
- Shows the modified date (field updateon) in detail view;
- Adds the title and the creation date columns to the 'Redirects' module list;
- Adds filter by Title;
- Adds sorting by creation date and alphabetical sorting for the title as well as other columns of the 'Redirects' module list.

## How to import a redirect list
The best way to import lots of redirects is by using a CSV or an Excel file, in which you define the values of the fields to be entered.
The extension offers the option to choose the separation character.
The required fields are: 

    source_host, source_path, target, is_regexp

The field "is_regexp" takes two possible values: 'true' or 'false'.

You can include others optional fields by specifying them in the 'Advanced field names', then you can import them easily in the import section, example:

In the 'Additional field names' you specify the optional fields by order and separated by comma: 

    title, disabled, keep_query_parameters
   
Then in the Import section: 

    www.example.com;/example;targetExample;false;MyTitleExample;true;false

Note : the optional fields can be empty, for example:
    
    www.example.com;/example;targetExample;false;;;

Note 2 : There are fields that accept only 'true' or 'false' value, check the Redirect TCA configuration of the Core extension for more information.

### Files example
In the `/Documentation/` folder you will find 2 files: One in CSV format and the other in XLS (Excel) format.


-----------

[Version française]

## Documentation QC Redirects

### À propos
Cette extension ajoute des fonctionnalités au module TYPO3 Redirects.

- Ajouts de redirections par copier-coller à partir d'un fichier Excel ou CSV (voir dans le dossier `/Documentation/` pour des exemples);
- Ajout d'un nouveau champ **Titre**, permettant de faciliter le repérage lorsqu'on utilise des expressions régulières dans le champs "Source";
- Affichage de la date de création (champ "createdon") dans l'affichage des enreditrements;
- Affichage de la date de modification (champ "modifiedon") dans l'affichage des enreditrements;
- Ajouts de la colonne de Titre et de la date de création dans la liste de redirections de module 'Redirects';
- Ajouts d'un filtre par Titre;
- Ajouts de tris par date de création et tri alphabétique pour le titre ainsi que d'autre colonnes de la table des redirections.

## Importer une liste de redirections
La meilleure façon d’importer les redirections est d’utiliser un fichier CSV ou Excel, selon un ordre à respecter.  
L’extension offre la possibilité de choisir le caractère de séparation.

Les champs obligatoires à importer sont les suivants : 

    source_host, source_path, target, is_regexp

Le champ "is_regexp" peut prendre uniquement les valeurs 'true' ou 'false'.

Vous pouvez importer les autres champs optionnels en les spécifiant dans le champ 'Noms des champs supplémentaires', puis on peut les ajouter dans le champ d'importation, exemple :

Dans le champ 'Noms des champs supplémentaires' on ajoute par ordre les champs optionnels à importer:

   title, disabled, keep_query_parameters

Après dans le champ d'importation on peut ajouter aux champs obligatoires:

    www.example.com;/example;targetExample;false;MyTitleExample;true;false

Note: les champs optionnels peuvent être vides : 

    www.example.com;/example;targetExample;false;;;;

Note 2 : Certain champs acceptent seulement des valeurs 'true' ou 'false'. Vérifiez la configuration TCA de l'extension Redirects pour plus d'information.

### Fichiers d'exemples
Dans le dossier `/Documentation/` , vous y trouverez 2 fichiers: Un au format CSV, l'autre au format XLS (Excel).

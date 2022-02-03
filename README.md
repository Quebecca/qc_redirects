Qc Redirects
==============================================================
*La [version française](#documentation-qc-redirects) de la documentation suit le texte anglais*
## About
This extension aims to improve the core Redirects module.
The extension offers the ability to import a list of redirects, by filling in the text fields with the redirects to import.
## How to import a redirect list
The best way to import is by using a csv or excel file, in which we define the values of the fields to be entered.
The extension offers the option to choose the character that will be used to separate the values entered for importing the list.
The extension also added a title field that will be useful in case of importing redirects using regular expressions.
The order used to import redirects is as follows: 

    Title, Source host, Source path, Target, Start time, Snd time, Is regular expression , Status code. 
The value of the source host, the source path, and the target is required.
The regular expression column, take to possible values 0 or 1

-----------
[Version française]
## Documentation qc redirects
### À propos
Cette extension vise à améliorer le module de base Redirects.
L’extension offre la possibilité d’importer une liste de redirections, en remplissant les champs de texte avec les redirections à importer.
## Comment importer une liste de redirection
La meilleure façon d’importer est d’utiliser un fichier csv ou excel, dans lequel nous définissons les valeurs des champs à saisir.
L’extension offre la possibilité de choisir le caractère qui sera utilisé pour séparer les valeurs entrées pour importer la liste.
L’extension a également ajouté un champ de titre qui sera utile en cas d’importation de redirections en utilisant des expressions régulières.
L’ordre d’importation des redirections est le suivant :

    Titre, Chemin de hôte, Chemin de source, Cible, Date de debut, Date de fin, Est un expression régulière, code d'état. 
La valeur de champ Chemin de hôte,Chemin de source,Cible doivent être remplies.
Le chmaps "Est un expression régulière" peut prendre deux valeurs '1' ou '0'.
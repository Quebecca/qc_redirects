/**
 * Module: TYPO3/CMS/QcRedirects/QcRedirectModuleBE
 */
define(['jquery'], function($) {
  $(document).ready(function (){
    $('.resetButton').click(function(e){
      e.preventDefault();
      $('#extraFields').prop('value', '');
      $('#importedList').prop('value', '');
      $('#separationCharacter').prop('selectedIndex',0);
    })
  })
})

$(document).ready(function(){	
  
 //Html 5 Data Filter
  $(function(){
      
      /* Set Game Tile Filter */
      var filter_div = $('[data-filter]');
      //console.log(filter_div);
      
     /* On Click Tab */
     $('.search-tab').on('click', function() {
        //console.log($(this).attr("data-game-type"));        
        var val = $(this).attr("data-game-type");         
          if (val == 'all') {//If tab filter is all
               /* Show All Tiles */
               filter_div.show();
               return;
            } else { 
              /* Hide/Show Game Tile based on [data-game-type] */
              filter_div.hide();          
              filter_div.filter(function(){            
                return $(this).data('filter').indexOf(val)>-1
              }).show();            
          }         
      });  
    
      /* On Search */
      $('#search-field').keyup(function(event){
          //console.log(event.which);
         //https://stackoverflow.com/questions/11179556/jquery-event-which-returns-13-on-enter-key-press
          if(event.which == 13){ 
                //(8 Backspace / 13 Enter)
               /* Show All Tiles if a specific Key is pressed */
              this.value = ''; 
               /* Removes Focus From Text Input */
              $(this).blur();             
              return;
          } else if (this.value == '') { 
              /* Show All Tiles if input is empty */
              filter_div.show();  
              return;
          } 
          var val = $.trim(this.value);
          filter_div.hide();
          if(val.length == 0) return;
          filter_div.filter(function(){            
              return $(this).data('filter').indexOf(val)>-1
          }).show();
        
      }).blur(function(event){  
            this.value = '';
            //console.log(event);
            if (event.relatedTarget == null) {
                filter_div.show();  
            } 
            /*else if (event.relatedTarget.className != 'search-tab') {
                filter_div.show();             
            }*/
            return;
      });
    
  });

  // End Document Ready
});

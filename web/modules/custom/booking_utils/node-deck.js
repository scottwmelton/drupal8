
(function ($) {

  $('#json-field-refresh').click( function() {

      test = $('#edit-field-room-json-data-0-value').val() ;
      objs = $.parseJSON( test ) ;
      num = objs.length;
      $('#deck-rooms').children().remove();

      jQuery.each( objs, function( index, obj) {
        roomNumber = obj.roomNumber;
        rmId = "rm" + roomNumber ;
        $('<div>' + roomNumber +  '</div>')
          .addClass('room-item')
          .attr( "id", rmId)
          .addClass( obj.roomShape )
          .css( "left", obj.roomLeft )
          .css("width", obj.roomWidth  )
          .css("height", obj.roomHeight  )
          .css( "top", obj.roomTop )
          .appendTo('#deck-rooms');
      });
    });

}(jQuery));

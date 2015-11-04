(function(){

  var orig = DeskPRO.User.ElementHandler.NewTicket.prototype._initFields;
  var initCustomAdds = function() {
    $('[data-customadds]:visible').each(function(){
      var $el = $(this)
        , $ac = $('<input type="text" />')
          .attr('class', $el.attr('class'))
          .val($el.val())
          .insertBefore($el)
        ;
      $el.hide();

      $ac.dawaautocomplete({
        select: function(event, data) {
          $el.val(data.value);
        }
      });
    });
  };

  DeskPRO.User.ElementHandler.NewTicket.prototype._initFields = function() {
    orig.call(this);
    initCustomAdds();
  };

  // init on ticket edit page
  $(function(){
    initCustomAdds();
  });

})();
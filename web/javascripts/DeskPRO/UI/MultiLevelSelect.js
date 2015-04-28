(function ($) {
  $.fn.dpMultiLevelSelect = function () {
    $(this).each(function () {

      var $el = $(this),
          map = $el.data('map');

      if ($el.data('dp-multi-level-select')) {
        return $el;
      }

      if ('choice-collapsed' !== $el.data('custom-field') || !map) {
        return $el;
      }

      var $cont = $('<div></div>').insertBefore($el);
      var add = function (node) {

        if (!node.children || !node.children.length) {
          return false;
        }

        var $select = $('<select style="display: block;margin: 3px 0;" data-no-select2="1"></select>').appendTo($cont).hide();
        node.$select = $select;
        $select[0]._node = node;

        $.each(node.children, function (i, child) {
          $select.append('<option value="' + child.id + '">' + child.title + '</option>');
          if (node.children) {
            add(child);
          }
        });


        $select.on('change', function () {
          var val = parseInt($(this).val());
          var process = function (node) {
            if (!node.children) return;
            $.each(node.children, function (i, child) {
              if (val === child.id) {

                if (child.$select) {
                  child.$select.show().trigger('change');
                } else {
                  $el.val(val);
                }

              } else {
                child.$select && child.$select.hide();
                process(child);
              }
            });
          };

          process(this._node);
        });

        return $select;
      };


      var $select = add({id: 0, children: map}, []);
      $select && $select.show().trigger('change');
      $el.data('dp-multi-level-select', true);

    });
  };
})(jQuery);



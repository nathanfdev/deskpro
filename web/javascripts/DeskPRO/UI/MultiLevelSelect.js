(function ($) {
  $.fn.dpMultiLevelSelect = function () {
    $(this).each(function () {

      var $el = $(this),
          map = $el.data('map'),
          flat = {};

      // Already has old style two level select (department field)
      if ($el.hasClass('dp-two-select')) {
        return $el;
      }

      if ($el.data('dp-multi-level-select')) {
        return $el;
      }

      if ('choice-collapsed' !== $el.data('custom-field') || !map) {
        return $el;
      }

      // Hide original
      $el.hide();

      var $cont = $('<div></div>').insertBefore($el),
          $emptyAllowed = $el.children('option[value=""]').length;
      $cont.addClass('multilevel-select');

      var add = function (node, lvl) {

        if (!lvl) {
          lvl = 0;
        }

        flat[node.id] = node;
        if (!node.children || !node.children.length) {
          return false;
        }

        var $select = $('<select data-no-select2="1"></select>');
        var $selectWrap = $('<div class="multilevel-select-wrap"></div>').addClass('level-' + lvl);

        $selectWrap.append($select);
        $selectWrap.appendTo($cont).hide();

        node.$select = $select;
        node.$selectWrap = $selectWrap;

        $select[0]._node = node;

        $emptyAllowed && $select.append('<option value=""></option>');
        $.each(node.children, function (i, child) {
          $select.append('<option value="' + child.id + '">' + child.title + '</option>');
          child.parent = node;
          if (node.children) {
            add(child, lvl+1);
          }
        });


        $select.on('change', function () {
          var val = parseInt($(this).val());
          NaN === val && $el.val('');

          var process = function (node) {
            if (!node.children) return;
            $.each(node.children, function (i, child) {
              if (val === child.id) {
                if (child.$selectWrap) {
                  child.$selectWrap.show();
                  child.$select.trigger('change');
                } else {
                  $el.val(val);
                }
              } else {
                child.$selectWrap && child.$selectWrap.hide();
                process(child);
              }
            });
          };

          process(this._node);
        });

        return node;
      };

      var top = add({id: 0, children: map}, 0);

      // init value
      var current = flat[$el.val()];
      if (current) {
        while (current) {
          var parent = current.parent;
          parent && parent.$select && parent.$selectWrap.show() && parent.$select.val(current.id);
          current = parent;
        }
      } else {
        top.$select && top.$selectWrap.show() && top.$select.trigger('change');
      }

      $el.data('dp-multi-level-select', true);

    });
  };
})(jQuery);



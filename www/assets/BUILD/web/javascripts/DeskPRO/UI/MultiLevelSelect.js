(function ($) {

  var withSelect2 = typeof DP != 'undefined' && DP.select && $.fn.select2;

  function setupSimple($el, map) {
    var val = $el.val();
    var emptyAllowed = $el.children('option[value=""]').length;
    var $select = $el.html('');

    if (emptyAllowed) {
      $select.append('<option></option>');
    }

    $.each(map, function(i, parent) {
      if (parent.children && parent.children.length) {
        var $g = $('<optgroup></optgroup>');
        $g.attr('label', parent.title);

        $.each(parent.children, function(i, child) {
          var $opt = $('<option value="' + child.id + '"></option>');
          $opt.text(child.title);
          var ft = parent.title + ' > ' + child.title;
          $opt.data('full-title', ft).attr('data-full-title', ft);
          $g.append($opt);
        });

        $select.append($g);
      } else {
        var $pOpt = $('<option value="' + parent.id + '"></option>');
        $pOpt.text(parent.title);
        $select.append($pOpt);
      }
    });

    $select.val(val).trigger('change');

    if (withSelect2) {
      $select.data('no-select2', null);
      DP.select($select);
      $select.data('no-select2', 1);
    }
  }

  function setupMulti($el, map) {
    var flat = {};

    // Hide original
    $el.hide();

    var $cont = $('<div></div>').insertBefore($el),
        $emptyAllowed = $el.children('option[value=""]').length;
    $cont.addClass('multilevel-select');

    var isBuilding = true;

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
        var $opt = $('<option value="' + child.id + '"></option>');
        $opt.text(child.title);
        $select.append($opt);
        child.parent = node;
        if (node.children) {
          add(child, lvl+1);
        }
      });

      $select.on('change', function () {
        var val = parseInt($(this).val());

        var process = function (node) {
          if (!node.children) return;
          $.each(node.children, function (i, child) {
            if (val === child.id) {
              if (child.$selectWrap) {
                child.$selectWrap.show();
                child.$select.trigger('change');
              } else {
                if ($el.val() !== val) {
                  $el.val(val).trigger('change');
                }
              }
            } else {
              child.$selectWrap && child.$selectWrap.hide();
              process(child);
            }
          });
        };

        if (!isBuilding && withSelect2) {
          $select.data('no-select2', null);
          DP.select($select);
          $select.data('no-select2', 1);
        }

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

    if (withSelect2) {
      $cont.find('select').each(function () {
        var $s = $(this);
        $s.data('no-select2', null);
        DP.select($s);
        $s.data('no-select2', 1);
      });
    }

    isBuilding = false;
  }

  $.fn.dpMultiLevelSelect = function () {
    return this.each(function () {

      var $el = $(this),
          map = $el.data('map');

      // Already has old style two level select (department field)
      if ($el.hasClass('dp-two-select') || $el.hasClass('with-select2')) {
        return $el;
      }

      if ($el.data('dp-multi-level-select')) {
        return $el;
      }

      if (!map) {
        return DP.select($el, {}, true);
      }

      if ('choice-collapsed' !== $el.data('custom-field')) {
        return $el;
      }

      var render = function () {
        if (parseInt($el.data('max-depth')) <= 2 && !$el.data('no-select2')) {
          // if only two levels (optgroup, opt), then faster to use normal select2
          return DP.select($el, {}, true);
        } else {
          $el.parent().find('.multilevel-select').remove();
          setupMulti($el, map);
        }
      };

      $el.addClass('dp-two-select');

      // re-render for multi select only
      // simple selectbox uses save element, just modifies its options
      // otherwise it causes infinite recursion
      if ($el.data('max-depth') > 2) {
        $el.on('change', function() {
          setTimeout(render, 1);
        });
      }

      render();
    });
  };
})(jQuery);



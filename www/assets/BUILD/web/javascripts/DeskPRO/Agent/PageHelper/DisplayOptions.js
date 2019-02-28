Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.DisplayOptions = new Orb.Class({
  Implements: [Orb.Util.Events, Orb.Util.Options],

  initialize: function (page, options) {

    var self = this;

    this.page = page;

    this.options = {
      triggerElement:   null,
      resultId:         0,
      prefSaveResultId: null,
      prefId:           '',
      refreshUrl:       '',
      refreshCallback:  null,
      fields:           null
    };
    this.setOptions(options);

    if (this.options.prefSaveResultId === null) {
      this.options.prefSaveResultId = this.options.resultId;
    }

    if (!this.options.triggerElement) {
      this.options.triggerElement = $('.display-options-trigger', this.page.wrapper);
    }

    $(this.options.triggerElement).on('click', (function (ev) {
      ev.stopPropagation();
      ev.preventDefault();
      this.open();
    }).bind(this));

    // Automatically set up the quick sort menu button
    var menuBtn = $('.order-by-menu-trigger button', this.page.wrapper);
    var menuEl  = $('ul.order-by-menu', this.page.wrapper);
    if (menuBtn.length && menuEl.length) {
      this.orderByMenu = new DeskPRO.UI.Menu({
        triggerElement: menuBtn.first(),
        menuElement:    menuEl.first(),
        onItemClicked:  (function (info) {
          var item = $(info.itemEl);

          var prop  = item.data('field');
          var label = item.text();

          $('.label', menuBtn).text(label);

          var disOptWrap = self.getWrapperElement();
          var sel        = $('select.sel-order-by', disOptWrap);
          $('option', sel).prop('selected', false);
          $('option.' + prop.replace('.', '_'), sel).prop('selected', true);

          self.saveAndRefresh({ isSortUpdate: true });

        }).bind(this)
      });
    }

    if (this.options.fields) {
      var $list     = $('ul.display-fields-list.on-list', this.getWrapperElement()),
          f         = this.options.fields,
          $children = $list.children().has('input:checked');

      $children.sort(function (a, b) {
        var ia = f.indexOf($(a).data('field')),
            ib = f.indexOf($(b).data('field'));

        if (ia > ib) {
          return 1;
        }
        if (ia > -1 && ia < ib) {
          return -1;
        }
        return 0;
      });
      $children.detach().prependTo($list);
    }

    var onList  = $('ul.display-fields-list.on-list', this.getWrapperElement());
    var offList = $('ul.display-fields-list.off-list', this.getWrapperElement());
    var ul      = $('ul.display-fields-list.on-list', this.getWrapperElement());
    onList.find(':checkbox').on('click', function () {
      var check = $(this);
      var li    = $(this).closest('li');

      if (check.attr('checked')) {
        li.detach().removeClass('off').appendTo(onList);
        if (!offList.find('> li').length) {
          offList.hide();
        }
      } else {
        li.detach().addClass('off').prependTo(offList);
        offList.show();
      }

      ul.find('> li.bogus').remove();
      self.makeBogus(ul);
    }).not(':checked').each(function () {
      $(this).closest('li').detach().addClass('off').appendTo(offList);
    });
    if (!offList.find('> li').length) {
      offList.hide();
    }


    this.page.addEvent('destroy', (function () {
      this.destroy();
    }).bind(this));
  },

  makeBogus: function (ul) {
    // Use bogus invisible draggables so when dragging to end of the list, the dragging
    // item is placed between one of these invisible ones. The event handlers
    // make sure they're always at the end.
    // - This is to fix making it too hard to position something at the end.
    var exist = ul.find('> li.bogus').length;
    for (var i = exist; i < 8; i++) {
      var li = $('<li class="bogus">&nbsp;</li>');
      li.css({
        width:      30,
        visibility: 'hidden'
      });

      ul.append(li);
    }
  },

  _initOverlay: function () {

		if (this._hasInit) {
			return;
		}
    this._hasInit = true;

    var self         = this;
    var ul           = $('ul.display-fields-list.on-list', this.getWrapperElement());
    this.optionsList = $('ul.display-fields-list.on-list', this.getWrapperElement()).sortable({
      forceHelperSize: true,
      opacity:         0.6,
      update:          function () {
        ul.find('> li.bogus').remove();
        self.makeBogus(ul);
      }
    });

    this.getWrapperElement().detach().appendTo('body');
    this.getWrapperElement().css('z-index', '10101');

    this.getWrapperElement().on('click', function (ev) {
      ev.stopPropagation();
    });

    this.backdropEl = $('<div class="backdrop dp-overlay-backdrop" />');
    this.backdropEl.css('z-index', '10100').hide().appendTo('body');

    this.backdropEl.on('click', (function (ev) {
      ev.stopPropagation();
      this.close();
    }).bind(this));

    $('header .close-trigger', this.getWrapperElement()).on('click', (function (ev) {
      ev.stopPropagation();
      ev.preventDefault();
      this.close();
    }).bind(this));

    $('.save-trigger', this.getWrapperElement()).on('click', (function () {
      this.saveDisplayOptions();
    }).bind(this));
  },

  getDisplayFields: function () {
    var fields = [];

    $(':checkbox:checked', this.getWrapperElement()).each(function () {
      fields.push($(this).attr('name'));
    });

    return fields;
  },

  saveDisplayOptions: function () {
    this.getWrapperElement().addClass('loading');
    this.saveAndRefresh();
  },

  saveAndRefresh: function (context) {
    var wrap = this.getWrapperElement();

    var data          = [];
    var displayFields = [];
    var pref_name     = 'prefs[agent.ui.' + this.options.prefId + '-display-fields.' + this.options.prefSaveResultId + '][]';

    var has = false;

    $('input[type="checkbox"]:checked', wrap).each(function () {
      var name = $(this).attr('name');
      has      = true;
      data.push({
        name:  pref_name,
        value: name
      });
      displayFields.push(name)
    });

    if (!has) {
      data.push({
        name:  pref_name,
        value: 'NONE'
      });
    }

    // and the ordering
    var orderBy = $('select[name="order_by"]', wrap).val();
    data.push({
      name:  'prefs[agent.ui.' + this.options.prefId + '-order-by.' + this.options.prefSaveResultId + ']',
      value: orderBy
    });

    // We reload the same page which will have changes applied
    var url = this.options.refreshUrl;

    var updateInfo = {
      displayFields: displayFields,
      orderBy:       orderBy,
      overlay:       this,
      context:       context || {}
    };

    if (this.options.preRefreshCallback) {
      this.options.preRefreshCallback(updateInfo);
    }

    if (this.options.isListView) {
      var page = this.page;
      $.ajax({
        timeout:  20000,
        type:     'POST',
        url:      BASE_URL + 'agent/misc/ajax-save-prefs',
        data:     data,
        context:  this,
        complete: function () {
          if (this.options.refreshCompleteCallback) {
            this.options.refreshCompleteCallback(updateInfo);
          }
          if (this.isOpen()) {
            this.close();
          }
        },
        success:  function () {
          if (this.options.refreshCallback) {
            this.options.refreshCallback(updateInfo);
          } else {
            page.meta.pageReloader();
          }
        }
      });
    } else {
      $.ajax({
        timeout:  20000,
        type:     'POST',
        url:      BASE_URL + 'agent/misc/ajax-save-prefs',
        data:     data,
        context:  this,
        complete: function () {
          if (this.options.refreshCompleteCallback) {
            this.options.refreshCompleteCallback(updateInfo);
          }
          if (this.isOpen()) {
            this.getWrapperElement().removeClass('loading');
            this.close();
          }
        },
        success:  function () {
          if (this.options.refreshCallback) {
            this.options.refreshCallback(updateInfo);
          } else {
            DeskPRO_Window.loadListPane(url);
          }
        }
      });
    }
  },

  open: function () {
    this._initOverlay();

    this.updatePositions();

    this.getWrapperElement().addClass('open');
    this.backdropEl.show();

    this.getWrapperElement().addClass('open');

    this.fireEvent('opened', [this]);
  },

  isOpen: function () {
    if (!this._hasInit || !this.getWrapperElement().is('.open')) {
      return false;
    }

    return true;
  },

  close: function () {
		if (!this._hasInit || !this.isOpen()) {
			return;
		}

    this.getWrapperElement().removeClass('open');
    this.backdropEl.hide();
    this.fireEvent('closed', [this]);
  },

  /**
   * Update the positions of the elements
   */
  updatePositions: function () {

    var elW = this.getWrapperElement().width();
    var elH = this.getWrapperElement().height();

    var pageW = $(window).width();
    var pageH = $(window).height();

    this.getWrapperElement().css({
      top:  55,
      left: (pageW - elW) / 2
    });
  },

  getWrapperElement: function () {
    return this.wrapper ? this.wrapper : (this.wrapper = this.page.getEl('display-options'));
  },

  destroy: function () {
    if (this._hasInit) {
      this.getWrapperElement().remove();
      this.backdropEl.remove();
    }

    delete this.wrapper;
    delete this.backdropEl;
    delete this.options;
    delete this.page;
  }
});

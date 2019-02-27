'use strict';
Orb.createNamespace('DeskPRO.Agent.PageFragment.List.Helper');

DeskPRO.Agent.PageFragment.List.Helper.TicketMassActions = new Orb.Class({
  Implements: [Orb.Util.Events, Orb.Util.Options],

  initialize: function(options) {
    this.options = {
      /**
       * Scope of the list
       */
      $scope: null,

      frameEl: null
    };

    this.setOptions(options);
    this.$scope = this.options.$scope;

    this._formUpdatedDebounce = _.debounce(this._formUpdated, 500);
    this.backdropEls = null;
  },

  updateUi: function() {

  },

  _resetWrapper: function(callback) {
    if (this.wrapper) {
      this.wrapper.hide();

      if (this.textarea && this.textarea.data('redactor')) {
        this.textarea.destroyEditor();
      }

      if (this.snippetsViewer) {
        this.snippetsViewer.destroy();
        this.snippetsViewer = null;
      }

      this.wrapper.find('.inner-mount-point').empty();
      this.wrapper.hide();
    } else {
      this.wrapper = this.options.frameEl;
      this.wrapper.detach().hide().appendTo('body');
      this.countEl = $('.selected-tickets-count', this.wrapper);

      DeskPRO_Window.initInterfaceLayerEvents(this.wrapper);
    }

    callback();

    $('select.macro', this.wrapper).off();
    $('.apply-actions', this.wrapper).off();

    this._loadInside();
  },

  /**
   * Resets the wrapper back to the original, and then runs all of the init again.
   */
  reset: function() {
    var wasopen = this.isOpen();
    this.close();

    this._resetWrapper(function() {
      this._hasInit = false;

      if (wasopen) {
        this.open();
      }
    });
  },


  /**
   * Get the main wrapper element around the mass actions UI controls.
   *
   * @return {jQuery}
   */
  getElement: function() {
    return this.wrapper;
  },


  /**
   * Form updated, fire the updated callback
   */
  _formUpdated: function() {

  },


  /**
   * Inits the overlay controls lazily on first open
   */
  _initOverlay: function() {
    var self = this;
    if (this._hasInit) {
      return;
    }
    this._hasInit = true;

    this.wrapper.detach().appendTo('body');
    this.wrapper.css('z-index', '21001');

    this.baseId = this.wrapper.data('base-id');

    this.wrapper.on('click', function(ev) {
      ev.stopPropagation();
    });

    // Three backdrops to surround each side of the list pane: left, right, top
    var back1 = $('<div class="backdrop mass-actions" />');
    var back2 = $('<div class="backdrop mass-actions" />');
    var back3 = $('<div class="backdrop mass-actions" />');
    this.backdropEls = $([back1.get(0), back2.get(0), back3.get(0)]);
    this.backdropEls.css('z-index', '21000').hide().appendTo('body');

    this.backdropEls.on('click', (function(ev) {
      ev.stopPropagation();
      this.close();
    }).bind(this));

    $('header .close-trigger', this.wrapper).first().on('click', (function(ev) {
      ev.stopPropagation();
      ev.preventDefault();
      this.close();
    }).bind(this));
  },

  _initInside: function() {
    var self = this;

    //------------------------------
    // Convert radios
    //------------------------------

    var tpl = DeskPRO_Window.util.getPlainTpl($('.radio-tpl', this.wrapper));

    var groupedRadios = {};
    $(':radio.button-toggle', this.wrapper).each(function() {
      var name = $(this).attr('name');
      if (!groupedRadios[name]) {
        groupedRadios[name] = [];
      }

      groupedRadios[name].push(this);
    });

    Object.values(groupedRadios).forEach(function(els) {
      var newEls = [];
      els = $(els);

      var clickFn = function() {
        var boundId = $(this).data('bound-id');
        var radio = $('#' + boundId);

        // Toggle off already checked (ie none selected now)
        if (radio.is(':checked')) {
          radio.attr('checked', false);
          newEls.removeClass('radio-on');

          // Normal radio behavior
        } else {
          radio.attr('checked', true);
          newEls.removeClass('radio-on');
          $(this).addClass('radio-on');
        }

        self._formUpdatedDebounce();
      };

      els.each(function() {

        var wrapper = $(this).parent();
        var title = $.trim($('.radio-title', wrapper).text());

        var newEl = $(tpl);
        newEl.addClass($(this).data('attach-class'));
        $('.radio-title', newEl).text(title);

        if (!$(this).attr('id')) {
          $(this).attr('id', Orb.getUniqueId());
        }

        newEl.data('bound-id', $(this).attr('id'));

        newEl.on('click', clickFn);

        wrapper.hide();
        newEl.insertAfter(wrapper);

        newEls.push(newEl.get(0));
      });

      newEls = $(newEls);
    });

    //------------------------------
    // Attach change listeners
    //------------------------------


    $('select.macro', this.wrapper).on('change', function() {
      self.loadMacro($(this).val());
      self._formUpdatedDebounce();
    });

    // todo should be redo to $scope models
    var $assignMe = this.getElById('assign_me'), $unassignAgent = this.getElById('unassign_agent');
    $assignMe.on('click', function() {
      $('select[name="actions[agent]"]', this.wrapper).val($(this).data('me')).trigger('change');
    });
    $unassignAgent.on('click', function() {
      $('select[name="actions[agent]"]', this.wrapper).val(0).trigger('change');
    });
    $('select[name="actions[agent]"]').on('change', function() {
      $(this).val() === $assignMe.data('me') ? $assignMe.hide() : $assignMe.show();
      parseInt($(this).val()) ? $unassignAgent.show() : $unassignAgent.hide();
    });
    var $assignTeam = this.getElById('assign_team'),
      $unassignTeam = this.getElById('unassign_team');
    $assignTeam.on('click', function() {
      $('select[name="actions[agent_team]"]', this.wrapper).val($(this).data('team')).trigger('change');
    });
    $unassignTeam.on('click', function() {
      $('select[name="actions[agent_team]"]', this.wrapper).val(0).trigger('change');
    });
    $('select[name="actions[agent_team]"]').on('change', function() {
      $(this).val() === $assignTeam.data('team') ? $assignTeam.hide() : $assignTeam.show();
      parseInt($(this).val()) ? $unassignTeam.show() : $unassignTeam.hide();
    });
    var $assignFollow = this.getElById('follower_me');
    $assignFollow.on('click', function() {
      var $sel = $('select[name="actions[add_participants][add_participants][]"]', this.wrapper),
        me = $(this).data('me').toString(),
        val = $sel.val();
      val ? val.push(me) : val = [me];
      $sel.val(val);
      $sel.trigger('change');
    });
    $('select[name="actions[add_participants][add_participants][]"]').on('change', function() {
      var val = $(this).val();
      val && val.length && val.indexOf($assignFollow.data('me').toString()) > -1 ? $assignFollow.hide() : $assignFollow.show();
    });

    $('.apply-actions', this.wrapper).on('click', (function(ev) {
      this.apply();
    }).bind(this));

    //------------------------------
    // Reply Box
    //------------------------------

    this.recordSnippetUse = function(snippetId) {
      var el = $("#" + self.baseId + "_snippet_ids");
      var current = el.val() || '';
      var newval = current.length ? current + ',' + snippetId : snippetId + '';
      el.val(newval);
    };

    var textarea = this.getElById('replybox_txt');
    this.textarea = textarea;

    this.getElById('reply_fake').on('click', (function() {
      this.getElById('reply_fake').hide();
      this.getElById('reply_real').show();
      this._initEditor(textarea);
      textarea.setFocus();
    }).bind(this));

    var sels = this.wrapper.find('select.dpe_select');

    sels.each(function() {
      DP.select($(this));
    });

    this.wrapper.bind('fileuploaddone', function() {
      self.getElById('attach_row').fadeIn();
      self.wrapper.find('[name="attach\\[\\]"]').each(function() {
        $(this).name('actions[reply][attach_ids][]');
      });
    });
    this.wrapper.bind('fileuploadstart', function() {
      self.getElById('attach_row').fadeIn();
      self.updatePositions();
    });

    this.wrapper.on('click', '.remove-attach-trigger', function() {

      var row = $(this).closest('li');
      row.remove();

      var rows = $('ul.files li', self.getElById('attach_row'));
      if (!rows.length) {
        self.getElById('attach_row').hide().addClass('is-hidden');
      }

      self.updatePositions();
    });

    if (this.assignOptionBox) {
      this.assignOptionBox.destroy();
    }

    var add = $('.other-properties-wrapper', this.wrapper);

    // Remove all the stuff we have layed out in a different way
    // on this popup
    $('div.type', add).each(function() {
      var type = $(this).data('rule-type');
      if (!type) {
        return;
      }

      if (type !== 'add_labels' && type !== 'remove_labels' && type.indexOf('ticket_field[') === -1 && type.indexOf('people_field[') === -1) {
        $(this).remove();
      }
      self.updatePositions();
    });

    this.actionsEditor = new DeskPRO.Form.RuleBuilder($('.actions-builder-tpl', add));

    var actList = $('.other-properties-wrapper', this.wrapper);
    $('.add-term-row', add).show().on('click', function() {
      var x = Orb.getUniqueId();
      var basename = 'actions_set[' + x + ']';
      self.actionsEditor.addNewRow($('.search-terms', actList), basename);
      self.updatePositions();
    });

    this.wrapper.find('input, select, textarea').on('click change blur focus', function() {
      self._formUpdatedDebounce();
    });
  },

  _renderInside: function(html) {
    var self = this;
    var $inside = this.wrapper.find('.inner-mount-point');
    var $loading = this.wrapper.find('.loading-point');
    $inside.html(html);
    DeskPRO_Window.initInterfaceLayerEvents($inside);
    DP.select($('select.macro', $inside));
    this._initInside();

    $loading.hide();
    $inside.show();

    this.updatePositions();
    window.setTimeout(function() {
      if (this._hasInit) self.updatePositions();
    }, 400);
  },

  _loadInside: function() {
    var self = this;
    $.ajax({
      method: 'GET',
      url:    DP_BASE_URL + 'agent/ticket-search/mass-action-overlay'
    }).then(function(data) {
      self.cachedTemplate = data;
      self._renderInside(self.cachedTemplate);
    });
  },

  getElById: function(id) {
    return $('#' + this.baseId + '_' + id);
  },

  getActionFormValues: function(appendArray, isApply, info) {
    var self = this;
    appendArray = appendArray || [];

    if (!info) {
      info = {};
    }
    info.actionsCount = 0;

    if (this.wrapper.find('select.macro_id')[0] && this.wrapper.find('select.macro_id').val() !== '0') {
      appendArray.push({
        name:  'run_macro_id',
        value: this.wrapper.find('select.macro_id').val()
      });
      info.actionsCount = 1;
      return appendArray;
    }

    $('input, select, textarea', this.wrapper).filter('[name^="actions["], [name^="actions_set["], .do-send-data').each(function() {

      var val = $(this).val(), name = $(this).attr('name');

      if (!val) {
        val = '';
      }

      if (val === '-1') {
        val = '';
      }

      if (!$(this).is(':checked')) {
        if ($(this).is(':radio')) {
          return;
        } else if ($(this).is(':checkbox')) {
          val = '0';
        }
      }

      if (val === '') {
        return;
      }

      // Dont send reply type when we're just fetching previews
      if (!isApply && name === 'actions[reply][reply_text]') {
        return;
      }
      if (!isApply && name === 'actions[reply][is_html]') {
        return;
      }

      // Empty reply, dont add it
      if (name === 'actions[reply][reply_text]') {
        var copy = $.trim(self.wrapper.find('.ticketreply').find('.redactor_editor').text()).replace(/\s/g, ' ');
        var tmp = $('<div/>').html(self.wrapper.find('textarea.signature-value-html').val());
        var sig = $.trim(tmp.text()).replace(/\s/g, ' ');

        if (!copy || copy === sig) {
          return;
        }
      }

      // process labels array
      // labels should be send to server as array, so we add to appendArray each value separately
      // appendArray  example:
      // [
      //    [
      //      name: 'actions_set[orb_uuid_249][labels][]',
      //      value: 'label1'
      //    ],
      //    [
      //      name: 'actions_set[orb_uuid_249][labels][]',
      //      value: 'label2'
      //    ]
      // ]
      if (
          $(this).is('select')
          && name.indexOf('[labels]') !== -1
          && Array.isArray(val)
      ) {
        val.forEach(function(v, index) {
          appendArray.push({
            name:  name,
            value: v
          });
        });

        return;
      }

      appendArray.push({
        name:  name,
        value: val
      });

      info.actionsCount++;
    });

    return appendArray;
  },

  /**
   * Apply the changes
   */
  apply: function() {
    var self = this;
    var formData = [];

    var formDataInfo = {
      checkedCount: 0,
      actionsCount: 0
    };

    var ticketIds = this.options.getCheckedIds();
    formDataInfo.checkedCount = ticketIds.length;
    if (!formDataInfo.checkedCount) {
      return;
    }

    ticketIds.forEach(function(tid) {
      formData.push({ name: 'result_ids[]', value: tid });
    });

    formData.push({ name: 'return_data', value: '1' });

    this.getActionFormValues(formData, true, formDataInfo);

    // If we dont have any tickets or actions then theres nothing to do
    if (!formDataInfo.checkedCount || !formDataInfo.actionsCount) {
      return;
    }

    this.wrapper.addClass('loading');

    var statusUpdate = this.wrapper.find('input[name="actions[status]"]:checked').val();

    DeskPRO_Window.util.ajaxWithClientMessages({
      url:      BASE_URL + 'agent/ticket-search/ajax-save-actions',
      type:     'POST',
      data:     formData,
      dataType: 'json',
      context:  this,
      success:  function(data) {
        self.wrapper.removeClass('loading');

        this.close();

        this.fireEvent('postApply', [this, data, formDataInfo]);

        if (data && data.failed_tickets && data.failed_tickets.length) {
          DeskPRO_Window.showAlert('Note: ' + data.failed_tickets.length + ' ticket(s) were not updated because you do not have permission to make the requested changed.');
        }
        if (data && data.validation_failed_tickets && data.validation_failed_tickets.length) {
          DeskPRO_Window.showAlert('Note: ' + data.validation_failed_tickets.length + ' ticket(s) were not updated because they did not pass validation checks.');
        }

        if (statusUpdate === 'hidden.deleted' || statusUpdate === 'hidden.spam') {
          // hide any open tickets
          $.each(data.success_tickets, function(k, ticketId) {
            var tab = DeskPRO_Window.getTabWatcher().findTab('ticket', function(tab) {
              return (tab && tab.page && tab.page && tab.page.meta.ticket_id === ticketId);
            });
            if (tab) {
              DeskPRO_Window.removePage(tab.page);
            }
          });
        }
      }
    });

  },


  /**
   * Update the positions of the elements
   */
  updatePositions: function() {

    //------------------------------
    // The wrapper overlaps the content pane section
    //------------------------------

    var pos = $('#dp_content').offset();
    var top = pos.top - 4;

    var bottom = 10;
    var height = '';

    var scrollContent = $('.inner-mount-point', this.wrapper).first();
    var contentH = false;
    var hasHeader = !!($('> section > header', this.wrapper).length);
    var hasFooter = !!($('> section > footer', this.wrapper).length);

    if (scrollContent.length) {
      contentH = scrollContent.height();
      if (hasHeader) {
        contentH += 36;
      }
      if (hasFooter) {
        contentH += 45;
      }

      contentH += 50;
    }

    if (hasHeader) {
      $('> section > article', this.wrapper).removeClass('no-header');
    } else {
      $('> section > article', this.wrapper).addClass('no-header');
    }

    if (hasFooter) {
      $('> section > article', this.wrapper).removeClass('no-footer');
    } else {
      $('> section > article', this.wrapper).addClass('no-footer');
    }

    if (contentH < 350) {
      contentH = 350;
    }

    var maxH = $(window).height() - top - 10;

    if (contentH && contentH < maxH) {
      bottom = '';
      height = contentH;
    }

    this.wrapper.css({
      top:    pos.top - 4,
      left:   pos.left + 8,
      right:  3,
      bottom: bottom,
      height: height
    });

    //------------------------------
    // The backdrops surround each side of the list pane
    //------------------------------

    var leftEnd = 269; // Where the left ends (aka where listpane starts)

    if (!DeskPRO_Window.paneVis.source) {
      leftEnd = 78;
    }

    var topEnd = 50; // Where the top ends (aka header height)
    var contentStart = pos.left;

    if (!this.options.isListView) {
      this.backdropEls.eq(0).css({
        top:    0,
        width:  leftEnd,
        bottom: 0,
        left:   0
      });

      this.backdropEls.eq(1).css({
        top:    0,
        height: topEnd,
        width:  contentStart - leftEnd,
        left:   leftEnd
      });

      this.backdropEls.eq(2).css({
        top:    0,
        right:  0,
        bottom: 0,
        left:   contentStart
      });
    }

    this.updateUi();
  },


  /**
   * Load a macro into the form
   */
  loadMacro: function(macro_id) {

    var macroEl = $('.macro-options', this.wrapper);
    var inputActionsEl = $('.actions-input', this.wrapper);

    macro_id = parseInt(macro_id);
    if (!macro_id) {
      macroEl.hide();
      macroEl.find('ul.actions-list').empty();
      macroEl.find('input.macro_id').remove();
      inputActionsEl.show();
      this.updateUi();
      this.updatePositions();
      return;
    }

    var macroBtnEl = $('div.macro-load', this.wrapper).addClass('loading');

    $.ajax({
      url:      BASE_URL + 'agent/ticket-search/ajax-get-macro-actions',
      data:     { macro_id: macro_id },
      type:     'GET',
      dataType: 'json',
      context:  this,
      success:  function(data) {

        inputActionsEl.hide();
        macroEl.show();

        var input = $('<input type="hidden" class="macro_id" name="run_macro_id" />');
        input.val(macro_id);
        input.appendTo(macroEl);

        var ul = macroEl.find('ul.actions-list');
        ul.empty();

        data.descriptions.forEach(function(desc) {
          var li = $('<li />');
          li.html(desc);

          ul.append(li);
        });

        macroBtnEl.removeClass('loading');

        this.updateUi();
        this.updatePositions();
      }
    });

    this.updatePositions();
  },

  insertSnippet: function(snippet, blobs, langId) {
    var ticketLangId = self.page ? self.page.getEl('value_form').find('.language_id').val() : 0;
    if (langId) {
      ticketLangId = langId;
    }
    window.LegacySnippetInserter.insertSnippet(
      snippet,
      blobs,
      ticketLangId,
      null,
      'ticket',
      this.textarea,
      this.attachBlobs.bind(this),
      this.recordSnippetUse.bind(this)
    );
    this.isSnippetOpen = false;
  },

  attachBlobs: function(blobs, source) {
    var self = this;
    var $attachRow = this.getElById('attach_row');
    blobs.forEach(function (info) {
      var blob = source[info];
      if (blob) {
        var html = window.tmpl($('.template-download', self.wrapper).attr('id'))({files: [blob]});
        $attachRow.find('ul.files:first').append(html);
      }
    });
    $attachRow.slideDown().removeClass('is-hidden');
  },


  /**
   * Is the overlay currently open?
   *
   * @return {Boolean}
   */
  isOpen: function() {
    return this._hasInit && this.wrapper.is('.open');
  },


  /**
   * Open this overlay
   */
  open: function() {
    var self = this;

    if (self.isOpen()) {
      return;
    }

    var _open = function() {
      self._initOverlay();
      self.updatePositions();
      DeskPRO_Window.layout.addEvent('resized', self.updatePositions, self);
      self.wrapper.addClass('open');
      self.backdropEls.show();

      self.wrapper.addClass('open').show();
      self.updatePositions();

      self.$scope.halfrealtime = true;
      self.$scope.massActionsOpen = true;
      self.$scope.$safeApply();
    };
    this._resetWrapper(_open);
  },


  /**
   * Close the overlay
   */
  close: function() {
    if (!this.isOpen()) {
      return false;
    }

    DeskPRO_Window.layout.removeEvent('resized', this.updatePositions, this);
    this.wrapper.removeClass('open');
    this.backdropEls.hide();
    this.fireEvent('closed', [this]);

    if (this.options.resetOnClose) {
      this.reset();
    }

    this.$scope.halfrealtime = false;
    this.$scope.massActionsOpen = false;
    this.$scope.$safeApply();
  },

  _initEditor: function(textarea) {
    var self = this;

    if (this.textarea && this.textarea.data('redactor')) {
      return;
    }

    var sig = this.wrapper.find('textarea.signature-value-html').val() || "";
    sig = sig.replace(/<div class="dp-signature-start">([\w\W]*)<\/div>/, '<p class="dp-signature-start">$1</p>');

    if (sig) {
      textarea.val(($.browser.msie ? '<p></p><p></p>' : '<p><br></p><p><br></p>') + '\n\n' + sig);
    }

    self.getElById('is_html_reply').val('1');

    DeskPRO_Window.initRteAgentReply(textarea, {
      defaultIsHtml:        true,
      inlineHiddenPosition: this.getElById('is_html_reply'),
      minHeight:            120,

      callback: function(obj) {
        obj.addBtnFirst('dp_attach', 'Click here to attach a file. You may also drag a file from your computer desktop into this reply area to upload attachments faster.', function() {
        });
        obj.addBtnAfter('dp_attach', 'dp_snippets', 'Open snippets', function() {
        });
        obj.addBtnSeparatorAfter('dp_attach');

        var snippetBtn = obj.$toolbar.find('.redactor_btn_dp_snippets').closest('li');
        snippetBtn.addClass('snippets').find('a').html('<span class="show-key-shortcut">S</span>nippets');
        snippetBtn.on('click', function(ev) {
          Orb.cancelEvent(ev);
          if (window.DP_HAS_NEW_SNIPPETS) {
            var event = new CustomEvent('dpLeftDrawer', {
              detail: {
                module: 'SnippetsMenu',
                width: 745,
                style: {
                  zIndex: 22000
                },
                insertSnippet: self.insertSnippet.bind(self)
              }
            });
            window.document.dispatchEvent(event);
          } else {
            self.snippetsViewer.open();
          }
        });

        var attachBtn = obj.$toolbar.find('.redactor_btn_dp_attach').closest('li');
        attachBtn.addClass('attach');
        attachBtn.find('a').text('Attach').append('<input type="file" class="file" name="file-upload" />');

        obj.addBtnSeparatorAfter('dp_snippets');
      }
    });
    this.getElById('is_html_reply').val(1);

    //------------------------------
    // Snippets Viewer
    //------------------------------

    this.snippetsViewer = new DeskPRO.Agent.Widget.SnippetViewer({
      driver:         DeskPRO_Window.ticketSnippetDriver,
      onBeforeOpen:   function() {
        var pos = $('#dp_content').offset();
        var contentStart = pos.left;

        self.backdropEls.eq(0).css({ width: '61px' });
        self.backdropEls.eq(1).css({ width: contentStart - 61, left: 61 });

        if (textarea.data('redactor')) {
          try {
            textarea.data('redactor').saveSelection();
          } catch (e) {
          }
        }
      },
      onBeforeClose:   function() {
        var pos = $('#dp_content').offset();
        var contentStart = pos.left;
        self.backdropEls.eq(0).css({ width: '269px' });
        self.backdropEls.eq(1).css({ width: contentStart - 269, left: 269 });
      },

      onSnippetClick: function(info) {
        self.backdropEls.eq(0).css({ width: '269px' });
        var snippetId = info.snippetId;
        var snippetCode = info.snippetCode;

        var agentText;
        var defaultText;
        var useText;
        var result;

        snippetCode.forEach(function(info) {
          if (info.value) {
            if (info.language_id === DESKPRO_PERSON_LANG_ID) {
              agentText = info.value;
            }
            if (info.language_id === DESKPRO_DEFAULT_LANG_ID) {
              defaultText = info.value;
            }
            useText = info.value;
          }
        });

        if (agentText) {
          useText = agentText;
        } else if (defaultText) {
          useText = defaultText;
        }

        self.recordSnippetUse(snippetId);

        result = useText || '';

        if (textarea.data('redactor')) {
          try {
            textarea.data('redactor').restoreSelection();
            textarea.data('redactor').setBuffer();
          } catch (e) {
          }

          var html = result;
          html = html.replace(/<\/p>\s*<p>/g, '<br/>');
          html = html.replace(/^<p>/, '');
          html = html.replace(/<\/p>$/, '');
          textarea.data('redactor').insertHtml(html);
        } else {
          self.page.insertTextInReply(result);
        }

        self.snippetsViewer.close();
      }
    });

    //------------------------------
    // Insert snippet by %-% combo
    //------------------------------

    if (textarea.data('redactor')) {
      var ed = textarea.getEditor();
      var api = textarea.data('redactor');

      var te = new DeskPRO.TextExpander({
        textarea: ed,
        onCombo: function(combo, ev) {
          combo = combo.replace(/%/g, '');
          if (!window.DESKPRO_TICKET_SNIPPET_SHORTCODES || !window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo]) {
            return;
          }

          ev.preventDefault();

          for (var i = 0; i < window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo].length; i++) {
            var snippetId = window.DESKPRO_TICKET_SNIPPET_SHORTCODES[combo][i];

            var focus = api.getFocus(),
              focusNode = $(focus[0]),
              testText;

            if (focus[0].nodeType == 3) {
              testText = focusNode.text().substring(0, focus[1]);
            } else {
              focus[0] = focusNode.contents().get(focus[1] - 1);
              focusNode = $(focus[0]);
              testText = focusNode.text();
              focus[1] = testText.length;
            }

            var lastAt = testText.lastIndexOf('%'), matches = [];

            if (lastAt != -1) {
              api.setSelection(focus[0], lastAt, focus[0], focus[1]);
            }

            // web kit handles content editable without an issue. this prevents the span
            // from being extended unnecessarily
            var editable = $.browser.webkit ? ' contenteditable="false"' : '';
            api.insertHtml('<span class="editor-inserting-var snippet-' + snippetId + '" ' + editable + ' data-snippet-id="' + snippetId + '">Inserting snippet</span>');

            if (window.DP_HAS_NEW_SNIPPETS) {
              var snippet = window.LegacyStoreProvider.getSnippets().get(snippetId);
              var blobs = window.LegacyStoreProvider.getSnippetBlobs();
              self.insertSnippet(snippet.toJS(), blobs.toJS());
            } else {
              $.ajax({
                url:      BASE_URL + 'agent/text-snippets/tickets/' + snippetId + '.json',
                dataType: 'json',
                success:  function (data) {
                  var snippet = data.snippet;
                  var ticketLangId = self.page ? self.page.getEl('value_form').find('.language_id').val() : 0;
                  var snippetId = snippet.id;
                  var snippetCode = snippet.snippet;

                  self.recordSnippetUse(snippetId);

                  var agentText;
                  var defaultText;
                  var wantText;
                  var useText;

                  snippetCode.forEach(function (info) {
                    if (info.language_id == ticketLangId) {
                      wantText = info.value;
                    }
                    if (info.language_id == DESKPRO_PERSON_LANG_ID) {
                      agentText = info.value;
                    }
                    if (info.language_id == DESKPRO_DEFAULT_LANG_ID) {
                      defaultText = info.value;
                    }
                    useText = info.value;
                  });

                  if (wantText) {
                    useText = wantText;
                  } else if (agentText) {
                    useText = agentText;
                  } else if (defaultText) {
                    useText = defaultText;
                  }

                  useText = useText.replace(/<\/p>\s*<p>/g, '<br/>');
                  useText = useText.replace(/^<p>/, '');
                  useText = useText.replace(/<\/p>$/, '');
                  var result = $('<div>' + useText + '</div>');

                  var el = api.$editor.find('.editor-inserting-var.snippet-' + snippetId);
                  var cursor = $('<span class="_cursor"></span>');
                  var cursorPos = result.find('> p');
                  if (!cursorPos[0]) {
                    cursorPos = result;
                  }

                  el.after(result);
                  cursorPos.append(cursor);
                  el.remove();

                  api.setSelection(cursor[0], 0, cursor[0], 0);
                  api.syncCode();
                }
              });
            }
          }
        }
      });

      this.updatePositions();
    }

    //------------------------------
    // Upload handling
    //------------------------------

    DeskPRO_Window.util.fileupload(this.wrapper, {
      url:              this.wrapper.data('upload-url'),
      uploadTemplate:   $('.template-upload', this.replyBox),
      downloadTemplate: $('.template-download', this.replyBox),
      uploadUrlParameters: {
        tag: 'ticket_attachment'
      }
    });
  },

  destroy: function() {
    if (this.textarea && this.textarea.data('redactor')) {
      this.textarea.destroyEditor();
    }

    if (this.snippetsViewer) {
      this.snippetsViewer.destroy();
      this.snippetsViewer = null;
    }

    if (this._hasInit) {
      this.wrapper.remove();
      this.backdropEls.remove();
    }

    if (this.$scope) {
      this.$scope.massActionsOpen = null;
    }

    this._hasInit = false;
  }
});

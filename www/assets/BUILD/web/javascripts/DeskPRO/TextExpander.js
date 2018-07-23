Orb.createNamespace('DeskPRO');

DeskPRO.TextExpander = new Orb.Class({

  Implements: [Orb.Util.Events, Orb.Util.Options],

  initialize: function(options) {
    this.options = {
      textarea: null,
      type:     'ticket',
    };
    var self = this;

    this.setOptions(options);

    this.comboString = null;
    this.$txt = $(this.options.textarea);

    this.shortcutList = $('<ul />').addClass('message-shortcut-list').hide().appendTo(document.body);
    this.shortcutListOpen = false;

    this.shortcutList.on('mousedown', 'li', function(e) {
      e.preventDefault();
      self.insertSnippet($(this).data('code'), e);
    });

    this.$txt.on('click blur', function() {
      self.shortcutListOpen = false;
      self.shortcutList.hide();
    });

    this.$txt.on('keypress', function(ev) {
      var combo;
      // % key
      if (ev.which === 37) {
        if (!self.comboString) {
          self.comboString = '%';
        } else {
          combo = self.comboString + '%';
          self.insertSnippet(combo, ev);
        }

        var textContent = window.getSelection().anchorNode ? window.getSelection().anchorNode.textContent : ev.currentTarget.textContent;
        var found = textContent && textContent.match(/%[-a-z0-9:._]+%?/i);
        if (found) {
          combo = found[0];
          self.insertSnippet(combo, ev);
        }
      } else if (ev.which === 8) {
        // nothing
        // some browsers like (firefox) pass backspace event
        // into keypress, while others (webkit, ie) do not

        // Other input keys after 'start'
        // of combo string
      } else if (self.comboString) {
        var chr = String.fromCharCode(ev.which);
        if (chr.match(/[a-zA-Z0-9:\.\-_\s]/)) {
          self.comboString += chr;
        } else {
          self.comboString = null;
        }
      } else {
        self.comboString = null;
      }

      self.updateShortcutList(ev);
    });

    this.$txt.on('keydown', function(ev) {
      var li;
      switch (ev.which) {
        case 38: // up
        case 40: // down
        case 13: // enter
          if (self.shortcutListOpen) {
            ev.preventDefault();

            if (ev.which === 13) { // enter - inserting the selected
              li = self.shortcutList.find('li.selected');
              if (!li.length) {
                li = self.shortcutList.find('li:first');
              }

              self.insertSnippet(li.data('code'), ev);
            } else if (ev.which === 40) { // down - moves down the list
              li = self.shortcutList.find('li.selected');
              if (!li.length) {
                self.shortcutList.find('li:first').addClass('selected');
              } else {
                li.removeClass('selected');
                var next = li.next('li');
                if (next.length) {
                  next.addClass('selected');
                } else {
                  self.shortcutList.find('li:first').addClass('selected');
                }
              }
            } else if (ev.which === 38) { // up - moves up the list
              li = self.shortcutList.find('li.selected');
              if (!li.length) {
                self.shortcutList.find('li:last').addClass('selected');
              } else {
                li.removeClass('selected');
                var prev = li.prev('li');
                if (prev.length) {
                  prev.addClass('selected');
                } else {
                  self.shortcutList.find('li:last').addClass('selected');
                }
              }
            }
          }

          break;
        case 9: // tab
        case 27: // esc
        case 33: // page up
        case 34: // page down
        case 35: // end
        case 36: // home
        case 37: // left
        case 39: // right
          if (self.shortcutListOpen) {
            self.shortcutListOpen = false;
            self.shortcutList.hide();
          }
          break;
        default:
          return;
      }
    });

    // Handle backspace
    this.$txt.on('keyup', function(ev) {
      if (self.comboString && ev.which === 8) {
        self.comboString = self.comboString.substring(0, self.comboString.length-1);
        if (self.shortcutListOpen) {
          self.updateShortcutList(ev);
        }
      }
    });
  },

  updateShortcutList: function(ev) {
    var self = this;
    if (!self.comboString || !self.comboString.length > 3) {
      if (self.shortcutListOpen) {
        self.shortcutListOpen = false;
        self.shortcutList.hide();
      }
      return;
    }
    var input = self.comboString.replace(/%/g, '');
    var re = new RegExp('^' + input);
    var matches = [];
    var extra = [];
    var shortcuts = Object.keys(this.options.type === 'chat' ? window.DESKPRO_CHAT_SNIPPET_SHORTCODES : window.DESKPRO_TICKET_SNIPPET_SHORTCODES);
    for (var i = 0; i < shortcuts.length; i++) {
      var code = shortcuts[i];
      if (code.match(re)) {
        matches.push(code);
      } else if (code.match(input)) {
        extra.push(code);
      }
    }

    matches = matches.concat(extra).slice(0, 5);

    if (matches.length) {
      var selectedId = self.shortcutList.find('li:selected').data('code');

      self.shortcutList.empty();
      for (var i = 0; i < matches.length; i++) {
        var li = $('<li>')
          .text(matches[i])
          .data('code', matches[i]);
        if (matches[i] === selectedId) {
          li.addClass('selected');
        }
        self.shortcutList.append(li);
      }

      var $el = $(ev.currentTarget);

      try {
        var anchorRect = window.getSelection().getRangeAt(0).getBoundingClientRect(); // get the text range
      } catch (e) {
        // fallback for safari
        if (ev) {
          anchorRect = {
            top:  $el.offset().top,
            left: $el.offset().left
          };
        } else {
          throw e;
        }
      }

      // window.getSelection() can return empty results, get position by rte box
      if (!anchorRect.left && !anchorRect.top) {
        anchorRect = {
          top:  $el.offset().top,
          left: $el.offset().left
        };
      }

      self.shortcutList.css({
        top:  anchorRect.top - self.shortcutList.outerHeight() - 1,
        left: anchorRect.left
      });

      self.shortcutList.show();
      self.shortcutListOpen = true;
    } else {
      self.shortcutListOpen = false;
      self.shortcutList.hide();
    }
  },

  insertSnippet: function(combo, ev) {
    if (this.shortcutListOpen) {
      this.shortcutListOpen = false;
      this.shortcutList.hide();
    }
    this.comboString = null;
    this.fireEvent('combo', [combo, ev]);
  },

  destroy: function() {
    this.options = null;
    this.$txt = null;
    this.destroyEvents();
  }
});

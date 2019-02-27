Orb.createNamespace('DeskPRO.Agent.RuleBuilder');

DeskPRO.Agent.RuleBuilder.DateTimeTerm = new Orb.Class({
  Extends: DeskPRO.Agent.RuleBuilder.TermAbstract,

  initRow: function() {
    this._initUi();
  },

  initValues: function() {
    var timestamp = null, date = null;

    timestamp = this.date1Input.val();
    if (timestamp) {
      date = new Date(timestamp * 1000);
      this.date1Display.data('DateTimePicker').date(date);
    }

    timestamp = this.date2Input.val();
    if (timestamp) {
      date = new Date(timestamp * 1000);
      this.date2Display.data('DateTimePicker').date(date);
    }

    //------------------------------
    // Existing values
    //------------------------------

    var relative1 = $('.relative', this.date1);
    var relative2 = $('.relative', this.date2);

    if (parseInt($('.date1-relative-input', this.rowEl).val())) {
      $('.relative-input', relative1).val($('.date1-relative-input', this.rowEl).val());
      $('.relative-type', relative1).val($('.date1-relative-type', this.rowEl).val());

      $('.date', this.date1).hide();
      $('.relative', this.date1).show().addClass('on');
    }

    if (parseInt($('.date2-relative-input', this.rowEl).val())) {
      $('.relative-input', relative2).val($('.date2-relative-input', this.rowEl).val());
      $('.relative-type', relative2).val($('.date2-relative-type', this.rowEl).val());

      $('.date', this.date2).hide();
      $('.relative', this.date2).show().addClass('on');
    }

    this.updateStatus();
  },

  _initUi: function() {

    //------------------------------
    // References to elements and move
    // overlay into body
    //------------------------------

    this.opInput = $('select.op', this.rowEl);

    this.date1Input = $('input.date1-input', this.rowEl);
    this.date2Input = $('input.date2-input', this.rowEl);

    this.currentValue = $('.status-value', this.rowEl);
    this.currentValue.text('(click to set)');
    this.currentValue.on('click', this.show.bind(this));

    this.dateWrap = $('.date-wrap', this.rowEl);

    this.backdrop = $('<div class="backdrop" style="display: none"></div>');
    this.backdrop.appendTo('body');
    this.backdrop.on('click', this.hide.bind(this));

    this.wrapper = $('<div class="field-overlay" style="display:none"><div class="close-trigger"></div></div>');
    $('.close-trigger', this.wrapper).on('click', this.hide.bind(this));

    this.dateWrap.detach().appendTo(this.wrapper).css('display', 'block');
    this.wrapper.appendTo('body');

    this.date1 = $('.date1', this.dateWrap);
    this.date2 = $('.date2', this.dateWrap);

    this.date1Display = $('input.date-display', this.date1);
    this.date2Display = $('input.date-display', this.date2);

    //------------------------------
    // Init date elements
    //------------------------------


    this.date1Display.datetimepicker({
      format: 'YYYY-MM-DD HH:mm',
      widgetParent: this.date1Display.parent().css({
        position: 'relative',
        display: 'inline-block'
      }),
      icons: {
        time: 'far fa-clock',
        date: 'far fa-calendar',
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    });

    this.date1Display.on('dp.change', function(){
      $(this).trigger('change');
    });

    this.date2Display.datetimepicker({
      format: 'YYYY-MM-DD HH:mm',
      widgetParent: this.date2Display.parent().css({
        position: 'relative',
        display: 'inline-block'
      }),
      icons: {
        time: 'far fa-clock',
        date: 'far fa-calendar',
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right'
      }
    });

    this.date2Display.on('dp.change', function(){
      $(this).trigger('change');
    });


    var self = this;

    this.date1Display.on('dp.change', function(){
      self.date1Input.val(Math.round(self.date1Display.data('DateTimePicker').date()._d.getTime() / 1000));
      self.updateStatus();
    });
    this.date2Display.on('dp.change', function(){
      self.date2Input.val(Math.round(self.date2Display.data('DateTimePicker').date()._d.getTime() / 1000));
      self.updateStatus();
    });

    $('.relative1-input, .relative2-input, select').on('change', function(){
      self.updateStatus();
    });

    //------------------------------
    // Switcher between relative input
    //------------------------------

    $('.switcher', this.date1).on('click', (function() {
      var date1 = $('.date', this.date1)
        , date2 = $('.date', this.date2)
        , rel1 = $('.relative', this.date1)
        , rel2 = $('.relative', this.date2)
        ;

      if (date1.is(':visible')) {
        date1.hide();
        date2.hide();
        rel1.show().addClass('on');
        rel2.show().addClass('on');
      } else {
        rel1.hide().removeClass('on');
        rel2.hide().removeClass('on');
        $('.date1-relative-input', this.rowEl).val('');
        $('.date2-relative-input', this.rowEl).val('');
        date1.show();
        date2.show();
      }
    }).bind(this));
  },

  show: function() {

    if (this.opInput.val() == 'between') {
      this.dateWrap.addClass('two');
    } else {
      this.dateWrap.removeClass('two');
    }

    this.wrapper.css({
      left: this.currentValue.offset().left,
      top: this.currentValue.offset().top
    });

    this.backdrop.show();
    this.wrapper.show();
  },

  updateStatus: function() {

    var str1 = '', str2 = '', status = '';

    // If we're using the relative times, update the values on close
    var relative1 = $('.relative', this.date1);
    var relative2 = $('.relative', this.date2);

    if (relative1.hasClass('on')) {
      $('.date1-relative-input', this.rowEl).val($('.relative-input', relative1).val());
      $('.date1-relative-type', this.rowEl).val($('.relative-type', relative1).val());

      // Erase any calendar time we mightve set before
      this.date1Input.val('');

      if ($.trim($('.relative-input', relative1).val()).length) {
        str1 = $('.relative-input', relative1).val() + ' ' + $('.relative-type', relative1).val() + ' ago';
      }

    } else {
      var date1 = this.date1Display.data('DateTimePicker').date();
      if (date1) {
        str1 = date1.format('HH:mm MMM Do, YYYY');
      }
    }

    if (relative2.hasClass('on')) {
      $('.date2-relative-input', this.rowEl).val($('.relative-input', relative2).val());
      $('.date2-relative-type', this.rowEl).val($('.relative-type', relative2).val());
      this.date2Input.val('');

      if ($.trim($('.relative-input', relative2).val()).length) {
        str2 = $('.relative-input', relative2).val() + ' ' + $('.relative-type', relative2).val() + ' ago';
      }
    } else {
      var date2 = this.date2Display.data('DateTimePicker').date();
      if (date2) {
        str2 = date2.format('HH:mm MMM Do, YYYY');
      }
    }

    if (!str1.length) str1 = '(click to set)';
    if (!str2.length) str2 = '(click to set)';

    if (this.opInput.val() == 'between') {
      status = str1 + ' and ' + str2;
    } else {
      status = str1;
    }

    this.currentValue.text(status);
  },

  hide: function() {
    this.updateStatus();
    this.backdrop.hide();
    this.wrapper.hide();
  },

  destroy: function() {
    this.wrapper.remove();
    this.backdrop.remove();
  }
});

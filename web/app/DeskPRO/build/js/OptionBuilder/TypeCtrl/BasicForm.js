(function() {
  define(function() {
    var DeskPRO_OptionBuilder_TypeCtrl_BasicForm;
    return DeskPRO_OptionBuilder_TypeCtrl_BasicForm = (function() {
      function DeskPRO_OptionBuilder_TypeCtrl_BasicForm($scope, $element, $attrs, dpTemplateManager) {
        var _this = this;
        this.$scope = $scope;
        this.element = $element;
        this.attrs = $attrs;
        this.els = {};
        this.els.select = this.element.find('select.option_type');
        this.els.select.select2();
        this.els.addBtn = this.element.find('.add_btn');
        this.els.optionList = this.element.find('.dp-ob-options');
        this.els.noOptionsMessage = this.els.optionList.find('.dp-ob-no-options');
        this.els.addBtn.on('click', function(ev) {
          var val;
          ev.preventDefault();
          val = _this.els.select.val();
          if (val === "0") {
            _this.select.select2('open');
            return;
          }
          return _this.addRow(val, {});
        });
      }

      /*
        	# Add a new row to the form
        	#
        	# @param {String} type
        	# @param {Object} model
      */


      DeskPRO_OptionBuilder_TypeCtrl_BasicForm.prototype.addRow = function(type, model) {};

      DeskPRO_OptionBuilder_TypeCtrl_BasicForm.FACTORY = [
        '$scope', '$element', '$attrs', 'dpTemplateManager', function($scope, $element, $attrs, dpTemplateManager) {
          return new DeskPRO_OptionBuilder_Controller($scope, $element, $attrs, dpTemplateManager);
        }
      ];

      return DeskPRO_OptionBuilder_TypeCtrl_BasicForm;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=BasicForm.js.map
*/
(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Icons_Ctrl_List;
    Admin_Icons_Ctrl_List = (function(_super) {
      __extends(Admin_Icons_Ctrl_List, _super);

      function Admin_Icons_Ctrl_List() {
        return Admin_Icons_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Icons_Ctrl_List.CTRL_ID = 'Admin_Icons_Ctrl_List';

      Admin_Icons_Ctrl_List.CTRL_AS = 'Ctrl';

      Admin_Icons_Ctrl_List.DEPS = [];

      Admin_Icons_Ctrl_List.prototype.init = function() {
        this.busy = false;
        return this.categories = {};
      };

      Admin_Icons_Ctrl_List.prototype.initialLoad = function() {
        return this.initCategories();
      };

      Admin_Icons_Ctrl_List.prototype.initCategories = function() {
        var def;
        this.busy = true;
        def = this.$q.defer();
        this.$timeout((function(_this) {
          return function() {
            var category, current, iconClass, iconImage, imageId, path, rule, stylesheet, _i, _j, _len, _len1, _ref, _ref1;
            _ref = document.styleSheets;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              stylesheet = _ref[_i];
              if ((stylesheet.href == null) || -1 === stylesheet.href.indexOf('icons-style.css')) {
                continue;
              }
              current = null;
              category = null;
              path = null;
              _ref1 = stylesheet.rules;
              for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                rule = _ref1[_j];
                if (rule instanceof CSSFontFaceRule) {
                  current = rule.style['font-family'];
                  path = rule.style['content'].substr(1, rule.style['content'].length - 2);
                  category = current.charAt(9).toUpperCase() + current.slice(10);
                  _this.categories[category] = [];
                  continue;
                }
                if ((current == null) || (rule.selectorText == null) || 0 !== rule.selectorText.indexOf('.' + current)) {
                  continue;
                }
                iconClass = rule.selectorText.substr(1, rule.selectorText.length - 9);
                iconImage = path + '/png/' + iconClass.substr(current.length + 1) + '.png';
                imageId = 'dp_file:icons:' + iconImage;
                _this.categories[category].push({
                  "class": iconClass,
                  image: iconImage,
                  imageId: imageId
                });
              }
            }
            _this.busy = false;
            return def.resolve();
          };
        })(this), 10);
        return def.promise;
      };

      Admin_Icons_Ctrl_List.prototype.selectIcon = function(path) {
        this.$scope.$emit('icon.selected', path);
        return this.$scope.$dismiss();
      };

      return Admin_Icons_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Icons_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map

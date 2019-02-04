define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_Icons_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Icons_Ctrl_List';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = [];
    }

    init() {
      this.busy = false;
      return this.categories = {};
    }


    initialLoad() {
      return this.initCategories();
    }


    initCategories() {
      this.busy = true;
      const def = this.$q.defer();

      this.$timeout(
        () => {
          for (const stylesheet of Array.from(document.styleSheets)) {
            if (!stylesheet || ((stylesheet.href == null) || (stylesheet.href.indexOf('icons-style.css') === -1))) { continue; }

            const rules = stylesheet.cssRules || stylesheet.rules || [];
            if (!rules || !rules.length) { continue; }

            let current = null;
            let category = null;
            let path = null;

            for (const rule of Array.from(rules)) {
              if (rule instanceof CSSFontFaceRule) {
                var content;
                if (rule.style.getPropertyValue) {
                  current = rule.style.getPropertyValue('font-family');
                  content = rule.style.cssText;
                } else {
                  current = rule.style['font-family'];
                  content = rule.style.src;
                }

                if (!content) { content = ''; }
                const re = content.match(/url\((.*?)\)/);

                if (!current || !content || !re) {
                  current = null;
                  content = null;
                  continue;
                }

                path = re[1];
                path = path.substr(path.indexOf('vendor/'));
                path = path.substr(0, path.lastIndexOf('/'));
                path = `ASSET_DIR/${path}`;

                current = current.replace(/^['"\-]+/, '');
                current = current.replace(/['"\-]+$/, '');

                category = current.charAt(9).toUpperCase() + current.slice(10);
                this.categories[category] = [];
                continue;
              }

              if ((current == null) || (rule.selectorText == null) || (rule.selectorText.indexOf(`.${current}`) !== 0)) { continue; }

              const iconClass = rule.selectorText.substr(1, rule.selectorText.length - 9);
              const iconImage = `${path}/png/${iconClass.substr(current.length + 1)}.png`;
              const imageId   = `dp_file:icons:${iconImage}`;

              this.categories[category].push({
                class: iconClass,
                image: iconImage,
                imageId
              });
            }
          }

          this.busy = false;
          return def.resolve();
        },
        10
      );

      return def.promise;
    }


    // ?
    selectIcon(path) {
      this.$scope.$emit('icon.selected', path);
      return this.$scope.$dismiss();
    }
  }
  Admin_Icons_Ctrl_List.initClass();


  return Admin_Icons_Ctrl_List.EXPORT_CTRL();
});

define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Icons_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Icons_Ctrl_List'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = []

    init: ->
      @busy = false
      @categories = {}



    initialLoad: ->
      @initCategories()



    initCategories: ->

      @busy = true
      def = @$q.defer()

      @$timeout(
        =>
          for stylesheet in document.styleSheets
            continue if !stylesheet or (!stylesheet.href? || -1 == stylesheet.href.indexOf 'icons-style.css')

            rules = stylesheet.cssRules || stylesheet.rules || [];
            continue if !rules || !rules.length;

            current = null
            category = null
            path = null

            for rule in rules
              if rule instanceof CSSFontFaceRule
                if rule.style.getPropertyValue
                  current = rule.style.getPropertyValue('font-family')
                  content = rule.style.cssText
                else
                  current = rule.style['font-family']
                  content = rule.style['src']

                if !content then content = ''
                re = content.match(/url\((.*?)\)/)

                if not current or not content or not re
                  current = null
                  content = null
                  continue

                path = re[1]
                path = path.substr(path.indexOf('vendor/'))
                path = path.substr(0, path.lastIndexOf('/'))
                path = 'ASSET_DIR/' + path

                current = current.replace(/^['"\-]+/, '');
                current = current.replace(/['"\-]+$/, '');

                category = current.charAt(9).toUpperCase() + current.slice(10)
                @categories[category] = []
                continue

              continue if !current? || !rule.selectorText? || 0 != rule.selectorText.indexOf('.'+current)

              iconClass = rule.selectorText.substr(1, rule.selectorText.length - 9)
              iconImage = path + '/png/' + iconClass.substr(current.length + 1) + '.png'
              imageId   = 'dp_file:icons:' + iconImage

              @categories[category].push
                class: iconClass
                image: iconImage
                imageId: imageId

          @busy = false
          def.resolve()
        10
      )

      def.promise



    # ?
    selectIcon: (path) ->
      @$scope.$emit('icon.selected', path)
      @$scope.$dismiss()



  Admin_Icons_Ctrl_List.EXPORT_CTRL()
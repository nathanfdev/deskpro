define [
  'underscore',
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Arrays',
  'Admin/OptionBuilder/TypesDef/BaseTypesDef'
], (_,
  Util,
  Arrays,
  BaseTypesDef) ->
  class Admin_OptionBuilder_TypesDef_BaseActionTypesDef extends BaseTypesDef
    constructor: (@$q, @Api, @Api2, @dpTemplateManager) ->
      @options_data   = null
      @inputTemplate  = 'OptionBuilder/type-actions-input.html'
      @selectTemplate = 'OptionBuilder/type-actions-select.html'
      @isTemplate     = 'OptionBuilder/type-actions-is.html'
      @init()

    init: ->
      return


    ###
      # Gets a type definition by calling a getX method on this class
    ###
    getDef: (type, options = {}) ->
      typeName = type
      options.type = type

      typeFunc = "get#{typeName}"
      if @[typeFunc]?
        return @[typeFunc](options)
      else
        console.error("Bad type with no definition getter: #{typeFunc}")
        me = @
        return {
          getTemplate: ->
            return me.dpTemplateManager.get(me.inputTemplate)
          getData: ->
            return {}
          getDataFormatter: ->
            return {
              getViewValue: (value = {}, data) ->
                return {}
              getValue: (model = {}, data) ->
                return null
            }
        }


    ###
      # Constructs a standard select box type
    ###
    getStandardSelect: (options) ->
      type = options.type
      prop_name = options.propName
      data_name = options.dataName
      options_formatter = options.optionsFormatter || null
      is_multi = options.isMulti
      extraOptions = options.extraOptions || null

      if not options_formatter
        options_formatter = (options) =>
          return @standardOptionsFormatter(options, extraOptions)

      me = @

      return {
        getTemplate: ->
          return me.dpTemplateManager.get(options.template || me.selectTemplate)

        getData: ->
          operators = options.operators || []
          if options.options
            return {
              options: if options_formatter then options_formatter(options.options) else options.options,
              multiselect: is_multi,
              operators: operators
            }
          if data_name
            defer = me.$q.defer()
            me.loadDataOptions().then(=>
              defer.resolve({
                options: if options_formatter then options_formatter(me.options_data[data_name]) else me.options_data[data_name],
                multiselect: is_multi
                operators: operators
              })
            )

            return defer.promise
          else
            return {
              operators: operators
            }

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              val = value.options?[prop_name] || null
              if val == null and data.options and prop_name
                val = data.options[0]?.value || null

              return {
                value: val,
                op: value.options?.op || value.op || _.first(data.operators)
              }
            getValue: (model = {}, data) ->
              value = {}
              value.type               = type
              value.options            = {}
              value.options[prop_name] = model.value
              value.options.op         = model.op
              return value
          }
      }


    ###
      # Constructs a standard "is" template (no options, just a boolean is)
    ###
    getStandardIs: (options) ->
      type = options.type
      prop_name = options.propName
      icon = options.icon

      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get(me.isTemplate)

        getData: ->
          return {}

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              return {
                value: true,
                op: 'is',
                icon: icon || false
              }
            getValue: (model = {}, data) ->
              value = {}
              value.type               = type
              value.options            = {}
              value.options[prop_name] = true
              return value
          }
      }


    ###
      # Constructs a standard input box
    ###
    getStandardInput: (options) ->
      type = options.type
      prop_name = options.propName

      me = @
      return {
        getTemplate: ->
          return me.dpTemplateManager.get(options.template || me.inputTemplate)

        getData: ->
          operators = options.operators || []
          return {
            options: options
            operators: operators
          }

        getDataFormatter: ->
          return {
            getViewValue: (value = {}, data) ->
              val = value.options?[prop_name] || ''
              if Util.isArray(val) then val = val.join(',')
              return {
                value: val
                op: value.options?.op || value.op || _.first(data.operators)
                with_formatter: value.options?.with_formatter || false
              }
            getValue: (model = {}, data) ->
              val = model.value || ''
              if options.tags
                val = val.split(',')

              value = {}
              value.type               = type
              value.options            = {}
              value.options[prop_name] = val
              value.options.op         = model.op
              if options.with_formatter
                value.options.with_formatter = !!model.with_formatter
              return value
          }
      }


    ###
      # Constructs standard input from a custom field def
    ###
    getStandardForFieldDef: (field, options = {}) ->
      if not options.propName then options.propName = 'value'

      options.operators = ['set', 'unset']
      if field.type_name == 'choice'
        options.options  = field.choices.map((o) -> { title: o.title, value: o.id + "" })
        options.template = 'OptionBuilder/type-actions-custom-select.html'
        options.isMulti  = !!field.options.multiple
        return @getStandardSelect(options)
      else if field.type_name == 'toggle'
        options.options  = [{ title: 'On', value: "1" }, { title: "Off", value: "0" }]
        options.template = 'OptionBuilder/type-actions-custom-select.html'
        return @getStandardSelect(options)
      else
        options.template = 'OptionBuilder/type-actions-custom-input.html'
        options.with_formatter = true
        return @getStandardInput(options)


    ###
      # Sets the getter for a custom field
      #
      # @param {String} base_name The base name of the field type (e.g., TicketField, UserField etc)
      # @param {Object} f         The field
      # @retrn {String} The name of the field that was set
      ###
    initFieldGetter: (base_name, f, force) ->
      fname = base_name + f.id

      if not this['get' + fname] || force?
        this['get' + fname] = (options = {}) =>
          options.type = base_name + f.id
          return @getStandardForFieldDef(f, options)

      return fname

define [
  'DeskPRO/Util/Strings',
  'Reports/Legacy/Builder/DataService/ReportBuilderCustom',
  'Reports/Legacy/Builder/DataService/ReportBuilderBuiltIn',
], (
  Strings,
  DataService_ReportBuilderCustom,
  DataService_ReportBuilderBuiltIn,
) ->
  ###
  # A simple wrapper around the data services
  ###
  class Admin_Main_Service_DataServiceManager
    constructor: (@$injector) ->
      @ds_cache = {}
      @registered = {}

    get: (serviceId) ->
      if @ds_cache[serviceId]
        obj = @ds_cache[serviceId]
      else
        obj = null

        # If this class has a custom initXXX method, call that
        # instead uf the default
        initName = 'init' + Strings.ucFirst(Strings.toCamelCase(serviceId))
        if @[initName]?
          obj = @[initName]()

        if not obj
          name = 'DataService_' + serviceId
          eval("constructor = #{name};")

          if not constructor
            throw new Error("Invalid data service name: " + name)

          obj = @$injector.instantiate(constructor)

        @ds_cache[serviceId] = obj

      return obj
define ['DeskPRO/Util/Util'], (Util) ->
  class EditAgentNotifPrefs



    constructor: (prefsTable) ->
      @prefsTable = prefsTable



    getFilterSubs: ->
      filterSubs = {}

      getFilterSubObj = (id) ->
        if filterSubs[id] then return filterSubs[id]
        filterSubs[id] =
          filter_id: id,
          email: [],
          alert: []

      for groupName in ['sys_filters', 'custom_filters']
        for typeName in ['email', 'alert']
          if not @prefsTable.subs["#{groupName}_#{typeName}"]? then continue

          for row in @prefsTable.subs["#{groupName}_#{typeName}"].rows
            subObj = getFilterSubObj(row.filter.id)

            for col in row.cols
              for opt in col
                if opt.value
                  subObj[typeName].push(opt.name)

      vals = Util.values(filterSubs)
      vals.filter((a) -> return true if a != "" and a != false and a != 0)



    getOtherSubs: ->
      appSubs = {}

      getAppSubObj = (id) ->
        if appSubs[id] then return appSubs[id]
        appSubs[id] = {
          type: id,
          email: [],
          alert: []
        }

      for groupName in ['chat', 'crm', 'feedback', 'publish', 'task', 'twitter', 'account']
        if not @prefsTable.subs[groupName]? then continue

        for row in @prefsTable.subs[groupName].rows
          subObj = getAppSubObj(groupName)

          for opt in row.cols
            if opt.value
              if opt.name.match(/_email$/)
                shortName = opt.name.replace(/_email$/, '')
                subObj.email.push(shortName)
              else
                shortName = opt.name.replace(/_alert$/, '')
                subObj.alert.push(shortName)

      vals = Util.values(appSubs)
      vals.filter((a) -> return true if a != "" and a != false and a != 0)

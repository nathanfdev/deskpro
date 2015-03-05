define [
  'DeskPRO/Util/Util'
], (
  Util
) ->
  class SlaFormMapper
    ###
      # Converts a model we get from the API into a form model that we can use in our page
      #
      # @param {Object} model
      # @return {Object}
    ###
    getFormFromModel: (model) ->
      form = {}
      form.title         = model.title || ''
      form.sla_type      = model.sla_type || 'first_response'
      form.active_time   = model.active_time || 'all'
      form.apply_type    = model.apply_type || 'all'
      form.warn_time     = [30, 'minutes']
      form.fail_time     = [60, 'minutes']
      form.hours_set     = {}
      form.warn_actions  = {}
      form.fail_actions  = {}
      form.apply_terms   = {}

      if model.active_time == 'custom'
        days = [false, false, false, false, false, false]
        for day in model.work_days
          days[day] = true

        form.hours_set = {
          start_hour: Math.floor(model.work_start / 3600),
          start_min:  Math.floor((model.work_start % 3600) / 60),
          end_hour:   Math.floor(model.work_end / 3600),
          end_min:    Math.floor((model.work_end % 3600) / 60),
          work_days:  days,
          holidays:   model.work_holidays,
          timezone:   model.work_timezone
        }

      if model.warn_time and model.warn_time_unit
        form.warn_time = [model.warn_time, model.warn_time_unit]

      if model.fail_time and model.fail_time_unit
        form.fail_time = [model.fail_time, model.fail_time_unit]

      if model.warn_actions?.actions?.length
        for action in model.warn_actions.actions
          rowId = Util.uid('action')
          form.warn_actions[rowId] = action

      if model.fail_actions?.actions?.length
        for action in model.fail_actions.actions
          rowId = Util.uid('action')
          form.fail_actions[rowId] = action

      if model.apply_terms?.terms?.length
        for termSet in model.apply_terms.terms
          if not termSet.set_terms or not termSet.set_terms.length then continue
          setId = Util.uid('termset')
          form.apply_terms[setId] = {}

          for term in termSet.set_terms
            rowId = Util.uid('term')
            form.apply_terms[setId][rowId] = term

      return form


    ###
      # Converts the form model into a model we can post back to the API
      # Essentially the reverse of getFormFromModel
      #
      # @param {Object} form
      # @return {Object}
    ###
    getPostDataFromFormModel: (form) ->
      postData = {
        title:          form.title,
        sla_type:       form.sla_type,
        active_time:    form.active_time,
        apply_type:     form.apply_type,
        warn_time:      form.warn_time[0],
        warn_time_unit: form.warn_time[1],
        fail_time:      form.fail_time[0],
        fail_time_unit: form.fail_time[1],
        warn_actions:   [],
        fail_actions:   [],
        apply_terms:    []
      }

      if form.active_type == 'custom'
        postData.hours_set = form.hours_set

      if form.apply_type == 'terms'
        for own _, crit_set of form.apply_terms
          set = []
          for own _, crit of crit_set
            if crit.type
              set.push(crit)
          if set.length
            postData.apply_terms.push(set)

      if form.warn_actions
        for own _, act of form.warn_actions
          if act.type
            postData.warn_actions.push(act)

      if form.fail_actions
        for own _, act of form.fail_actions
          if act.type
            postData.fail_actions.push(act)


      if form.active_time == 'custom'
        work_days = []
        for enabled, day in form.hours_set.work_days
          if enabled
            work_days.push(day)

        postData.work_start    = (form.hours_set.start_hour * 3600) + (form.hours_set.start_min * 60)
        postData.work_end      = (form.hours_set.end_hour * 3600) + (form.hours_set.end_min * 60)
        postData.holidays      = (form.hours_set.holidays)
        postData.work_days     = work_days
        postData.work_timezone = form.hours_set.timezone

      return postData
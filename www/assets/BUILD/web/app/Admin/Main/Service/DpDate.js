define [
  'moment'
], (
  moment
) ->
  ###
  # Date formatter service
  ###
  class Admin_Main_Service_DpDate

    constructor: () ->

      # todo: get formats from App Settings
      @formats =
        full: 'ddd, Do MMM YYYY'
        fulltime: 'ddd, Do MMM YYYY h:mma'
        day: 'MMM D YYYY'
        day_short: 'MMM D'
        time: 'h:mm a'

      @default = 'fulltime'



    local: (date) ->
      return date if !date

      if 'string' == typeof date
        date = moment.utc(date).toDate()
      else if date instanceof Date
        date = moment.utc(date.getTime()).toDate()

      date



    format: (date, format) ->
      format = @default if !format
      return null if !@formats[format]

      date = @local date
      return null if !date

      moment(date).format @formats[format]

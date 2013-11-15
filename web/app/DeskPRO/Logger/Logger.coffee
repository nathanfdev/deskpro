define [
	'DeskPRO/Util/Util'
], (
	Util
)->
	class Logger
		###
    	# @param {String} name
		###
		constructor: (name) ->
			@name = name
			@handlers = []
			@processors = []


		###
    	# @return {String}
		###
		getName: ->
			return @name


		###
    	# @param {Object} handler
		###
		pushHandler: (handler) ->
			@handlers.unshift(handler)


		###
    	# @return {Object}
		###
		popHandler: ->
			return @handlers.shift()


		###
    	# @return {Function}
		###
		pushProcessor: (processor) ->
			@processors.unshift(processor)


		###
    	# @return {Object}
		###
		popProcessor: ->
			return @processors.shift()


		###
    	# @param {Integer} level
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		addRecord: (level, message, context = {}) ->
			if not @handlers.length
				return false

			messageRaw = message

			if Util.isArray(message)
				message = message.join(" ")

			record = {
				message:    message,
				messageRaw: messageRaw,
				context:    context,
				level:      level,
				level_name: Logger.LEVELS[level],
				channel:    @name,
				date:       new Date(),
				extra:      {}
			}

			handlerKey = null
			for handler,k in @handlers
				if handler.isHandling(record)
					handlerKey = k
					break

			if handlerKey == null
				return false

			for proc in @processors
				if proc.process?
					record = proc.process(record)
				else
					record = proc(record)

			while @handlers[handlerKey]?
				if @handlers[handlerKey].isHandling(record)
					if @handlers[handlerKey].handle(record)
						break

				handlerKey++

			return true


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		debug: (message, context) ->
			@addRecord(Logger.DEBUG, message, context)


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		info: (message, context) ->
			@addRecord(Logger.INFO, message, context)


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		warning: (message, context) ->
			@addRecord(Logger.WARN, message, context)


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		error: (message, context) ->
			@addRecord(Logger.ERROR, message, context)


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		critical: (message, context) ->
			@addRecord(Logger.CRITICAL, message, context)


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		alert: (message, context) ->
			@addRecord(Logger.ALERT, message, context)


		###
    	# @param {String} message
    	# @param {Object} context
    	# @return {bool}
		###
		emergency: (message, context) ->
			@addRecord(Logger.EMERGENCY, message, context)


		@DEBUG     = 100
		@INFO      = 200
		@NOTICE    = 250
		@WARNING   = 300
		@ERROR     = 400
		@CRITICAL  = 500
		@ALERT     = 550
		@EMERGENCY = 600
		@LEVELS    = {
			100: 'DEBUG',
			200: 'INFO',
			250: 'NOTICE',
			300: 'WARNING',
			400: 'ERROR',
			500: 'CRITICAL',
			550: 'ALERT',
			600: 'EMERGENCY',
		}
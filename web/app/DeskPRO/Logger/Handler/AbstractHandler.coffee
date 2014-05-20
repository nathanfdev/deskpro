define [
	'DeskPRO/Logger/Logger',
	'DeskPRO/Logger/Formatter/LineFormatter',
], (
	Logger,
	LineFormatter
) ->
	class AbstractHandler
		constructor: (@level = Logger.DEBUG, @bubble = true) ->
			@processors = []
			@formatter = null


		###
    	# @param {Object} record
    	# @return {bool}
    	###
		isHandling: (record) ->
			return record.level >= @level


		###
    	# Handle a number of records at once
    	#
    	# @param {Array} records
    	###
		handleBatch: (records) ->
			for rec in records
				@handle(rec)


		###
    	# Handle the log record
    	#
    	# @param {Object} record
    	# @return {bool}
    	###
		handle: (record) ->
			throw new Error("Unimplemented")


		###
    	# @return {Function}
		###
		pushProcessor: (processor) ->
			@processors.unshift(processor)
			return @


		###
    	# @return {Object}
		###
		popProcessor: ->
			return @processors.shift()

		###
    	# Sets the formatter
    	#
    	# @param {Object} formatter
		###
		setFormatter: (formatter) ->
			@formatter = formatter
			return @


		###
    	# @return {Object}
		###
		getFormatter: ->
			if @formatter == null
				@formatter = @getDefaultFormatter()

			return @formatter


		###
    	# @return {Object}
		###
		getDefaultFormatter: ->
			return new LineFormatter()


		###
    	# @return {Integer}
		###
		getLevel: ->
			return @level

		###
    	# Sets the level
    	#
    	# @param {Integer} level
		###
		setLevel: (level) ->
			@level = level
			return @


		###
    	# @return {bool}
		###
		getBubble: ->
			return @bubble


		###
    	# Enable/disable bubble
    	#
    	# @param {bool} bubble
		###
		setBubble: (bubble) ->
			@bubble = bubble
			return @
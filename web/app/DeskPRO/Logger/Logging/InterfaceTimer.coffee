define ->
	class InterfaceTimer
		constructor: (@logger) ->
			@lastController = null
			@isWithinLoad = false
			@digestStart = null
			@digestEnd = null

		startControllerLoad: (controller) ->
			@lastController = controller
			@isWithinLoad = true

		startDigest: ->
			@digestStart = new Date()

		endDigest: ->
			@digestEnd = new Date()

			if @isWithinLoad
				level = null
				time = @digestEnd.getTime() - @digestStart.getTime()
				if time > 500
					level = 'notice'
				if time > 750
					level = 'warning'

				if level
					@logger[level](["[InterfaceTimer] (#{@lastController.constructor.CTRL_ID}) Load Digest Time: {0}ms", time])

				@isWithinLoad = false

		endControllerLoad: ->
			return
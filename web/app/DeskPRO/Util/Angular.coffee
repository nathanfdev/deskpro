define ->
	class DeskPRO_Util_Angular
		###
    	# Get an object of k=>v services injected into a constructor of object given an array of args.
    	#
    	# @param {Object} object An object annotated with $inject
    	# @param {Array} args    An array of args, typically args of a constructor
    	# @return {Object}
		###
		getInjectedArgs: (object, args) ->
			injectedArgs = {}

			injectedNames = null
			if object.$inject
				injectedNames = object.$inject
			else if object.constructor.$inject
				injectedNames = object.constructor.$inject

			if not injectedNames then injectedArgs

			for arg, i in args
				argName = injectedNames[i]
				injectedArgs[argName] = arg

			return injectedArgs


		###
    	# Takes the objects injected (gotten via getInjectedArgs) and assigns them to properties
    	# on the object,
    	#
    	# @param {Object} object An object annotated with $inject
    	# @param {Array} args    An array of args, typically args of a constructor
		###
		setInjectedProperties: (object, args) ->
			injectedArgs = @getInjectedArgs(object, args)
			for own k, v of injectedArgs
				object[k] = v

	return new DeskPRO_Util_Angular()
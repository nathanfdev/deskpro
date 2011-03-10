Orb.createNamespace('DeskPRO');

/**
 * A central way to handle and pass messages around to listeners. Glorified
 * event manager.
 *
 * <code>
 * var messageBroker = new DeskPRO.MessageBroker();
 * messageBroker.addMessageListener('example.message.*', function (data) { alert(data); });
 * messageBroker.sendMessage('example.message.test', "Hello, world!");
 * </code>
 */
DeskPRO.MessageBroker = new Orb.Class({

	initialize: function() {
		this.messageTransformers = {};
		this.messageListeners = {};
		this.tagged = {};
	},

	/**
	 * Forward a message type to a separate broker instance.
	 *
	 * @param {String} name The message name
	 * @param {DeskPRO.MessageBroker} messageBroker The broker to forward to
	 */
	addForwarder: function (name, messageBroker) {
		this.addMessageListener(name, function(data, name) {
			messageBroker.sendMessage(name, data);
		});
	},



	/**
	 * Send a message to all listeners.
	 *
	 * @param {String} name The message name
	 * @param {Object} data Any data to send
	 */
	sendMessage: function (name, data) {

		data = this.transformMessage(name, data);

		if (this.messageListeners[name] !== undefined) {
			this.messageListeners[name].each(function(callback) {
				callback(data, name);
			});
		}

		var nameparts = name.split('.');
		var cur_name = null;

		while (nameparts.pop()) {
			cur_name = nameparts.join('.') + '.*';
			if (this.messageListeners[cur_name] !== undefined) {
				this.messageListeners[cur_name].each(function(callback) {
					callback(data, name);
				});
			}
		}
	},



	/**
	 * Run all transformers on some data
	 *
	 * @param {String} name The message name
	 * @param {Object} data The message data to transform
	 * @return {Object} The transformed data
	 */
	transformMessage: function (name, data) {

		if (this.messageTransformers[name] !== undefined) {
			this.messageTransformers[name].each(function(callback) {
				data = callback(data, name);
			});
		}

		var nameparts = name.split('.');
		var cur_name = null;

		while (nameparts.pop()) {
			cur_name = nameparts.join('.') + '.*';
			if (this.messageTransformers[cur_name] !== undefined) {
				this.messageTransformers[cur_name].each(function(callback) {
					data = callback(data, name);
				});
			}
		}

		return data;
	},



	/**
	 * Add a message transformer. `name` follows same rules as `addMessageListener()`.
	 *
	 * @param {String} name Message name
	 * @param {Function} callback Callback that will transform the data
	 */
	addMessageTransformer: function(name, callback) {
		if (this.messageTransformers[name] === undefined) {
			this.messageTransformers[name] = [];
		}

		this.messageTransformers[name].push(callback);
	},



	/**
	 * Add a listener on a message.
	 *
	 * `name` should use dots to separate namespaces/groups of message types.
	 * Use an asterisk at the end of a namespace and all messages of that namespace
	 * will be sent through the same callback: example.*
	 *
	 * @param {String} name Message name
	 * @param {Function} callback Callback to execute with message
	 */
	addMessageListener: function(name, callback, tag) {
		if (this.messageListeners[name] === undefined) {
			this.messageListeners[name] = [];
		}

		this.messageListeners[name].push(callback);

		if (tag) {
			if (!this.tagged[tag]) {
				this.tagged[tag] = [];
			}

			this.tagged[tag].push([name, callback]);
		}
	},



	/**
	 * Remove a listener
	 *
	 * @param {String} name Message name
	 * @param {Function} callback Callback to remove
	 */
	removeMessageListener: function (name, callback) {
		if (this.messageListeners[name] === undefined) {
			return;
		}

		var index = this.messageListeners[name].indexOf(callback);
		if (index != -1) {
			this.messageListeners[name].splice(index, 1);
		}
	},



	/**
	 * Remove all listeners tagged with a certain tag.
	 *
	 * @param tag
	 */
	removeTaggedListeners: function(tag) {
		if (!this.tagged[tag]) return;

		Array.each(this.tagged[tag], function (x) {
			this.removeMessageListener(x[0], x[1]);
		}, this);
	}
});
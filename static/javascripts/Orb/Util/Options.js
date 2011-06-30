Orb.createNamespace('Orb.Util');

Orb.Util.Options = {
	setOptions: function(setOptions){

		var options = $.extend(true, {}, this.options || {}, setOptions);

		if (this.addEvent) {
			for (var option in options){
				if (typeof options[option] != 'function' || !(/^on[A-Z]/).test(option)) continue;

				this.addEvent(option, options[option]);
				delete options[option];
			}
		}

		this.options = options;

		return this;
	},

	getOption: function(option, default_value) {
		if (typeof this.options[option] === undefined) {
			return default_value;
		}

		return this.options[option];
	}
};
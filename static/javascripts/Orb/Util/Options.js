Orb.createNamespace('Orb.Util');

Orb.Util.Options = {
	setOptions: function(){
		var options = this.options = Object.merge.apply(null, [{}, this.options].append(arguments));

		if (this.addEvent) for (var option in options){
			if (typeof options[option] != 'function' || !(/^on[A-Z]/).test(option)) continue;

			this.addEvent(option, options[option]);
			delete options[option];
		}
		return this;
	}
};
Orb.createNamespace('DeskPRO.Form.Validator');

DeskPRO.Form.Validator.Length = new Class({
	Implements: DeskPRO.Form.Validator.AbstractValidator,
	
	_setDefaultOptions: function() {
		this.setOptions({
			regex: /.?/,
			trim: true
		});
	}
	
	_checkForErrors: function(value) {
		
		if (this.options['trim']) {
			value = value.trim();
		}
		
		if (!value.match(this.options['regex'])) {
			this.addError('no_match');
		}
	}
});
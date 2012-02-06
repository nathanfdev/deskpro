Orb.createNamespace('DeskPRO');

/**
 * Finds words in text and wraps them in a span
 */
DeskPRO.WordHighlighter = {
	highlight: function(node, words) {

		// We need the longest words to process first or they'll be passed up in favour of shorter guys
		words.sort(function(a, b) {
			if (a.length > b.length) {
				return -1;
			} else {
				return 1;
			}
		});

		var addedNodes = [];
		this._do(node, words, addedNodes);

		return addedNodes;
	},

	_do: function(node, words, addedNodes) {
		var i;

		if (node.nodeType == 3) {
			for (i = 0; i < words.length; i++) {
				var pos = node.data.toUpperCase().indexOf(words[i].toUpperCase());
				if (pos >= 0 && !$(node.parentNode).hasClass('dp-highlight-word') && !$(node.parentNode).closest('.dp-highlight-word')[0]) {
					var spannode = document.createElement('span');
					spannode.className = 'dp-highlight-word';
					spannode.setAttribute('data-word', words[i]);
					addedNodes.push(spannode);

					var middlebit = node.splitText(pos);
					var endbit = middlebit.splitText(words[i].length);
					var middleclone = middlebit.cloneNode(true);
					spannode.appendChild(middleclone);

					this._do(endbit, words, addedNodes);

					middlebit.parentNode.replaceChild(spannode, middlebit);
				}
			}
		}else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
			var children = $.makeArray(node.childNodes);
			for (i = 0; i < children.length; i++) {
				this._do(children[i], words, addedNodes);
			}
		}
	}
};

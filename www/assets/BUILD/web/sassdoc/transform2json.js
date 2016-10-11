var sassdoc = require('sassdoc');
var fs      = require('fs');

/**
 * Transform sassdoc raw output into grouped variables, fixing variable names from - to _
 * @param sassdoc_items
 * @returns {{}}
 */
function transform(sassdoc_items) {
  var groups = {};
  for (var i = 0; i < sassdoc_items.length; i++) {
    var group = sassdoc_items[i]['group'][0];
    if (!groups.hasOwnProperty(group)) {
      groups[group] = [];
    }

    var name = sassdoc_items[i]['context']['name'];

    var type;
    if (sassdoc_items[i].hasOwnProperty('type')) {
      type = sassdoc_items[i]['type'];
    } else {
      console.error('Error: ' + name + ' has no @type');
    }
    groups[group].push({
      type:          type,
      name:          name,
      default_value: sassdoc_items[i]['context']['value']
    });
  }

  return groups;
}

sassdoc.parse('../pub/src/DeskPRO/Bundle/PortalBundle/Resources/style/vars.scss').then(function(items) {
  fs.writeFileSync('./sassdoc/vars.json', JSON.stringify(transform(items), null, '\t'));
});

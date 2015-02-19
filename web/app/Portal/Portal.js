var Reflux = require('reflux');

// Creating an Action
var textUpdate = Reflux.createAction();
var statusUpdate = Reflux.createAction();

function test() {
    return "123!!!";
}

var abc = require('Portal/test');

exports.test = {
    CHRODER_TEST: true
};
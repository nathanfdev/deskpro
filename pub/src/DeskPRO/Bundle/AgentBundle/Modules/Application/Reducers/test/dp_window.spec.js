import { assert, expect } from "chai";
import { suite, test } from "mocha";

import dp_window from "../dp_window";

suite('dp_window', () => {
  test('no state should return initial state', () => {
    const val = dp_window();
    expect(val).to.have.property('isLoaded', false);
    expect(val).to.have.property('activeAppId', 'tickets');
  });
});
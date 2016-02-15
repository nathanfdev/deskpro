jest.dontMock('DeskPRO/Component/Ampliflux/reducers/handlers');

import { toImmutable } from 'Helpers';

describe('Ampliflux actions handlers', () => {
  const handlers = require('DeskPRO/Component/Ampliflux/reducers/handlers');

  let state;
  beforeEach(() => state = toImmutable({
    a: null,
    b: null,
    c: {
      d: null
    }
  }));

  describe('composeHandlers()', () => {
    it('should compose handlers into one', () => {
      const payload = {a: 1, b: 2};
      const handler = handlers.composeHandlers(
        handlers.setPayload('a', 'a'),
        handlers.setPayload('b', 'b'),
        (immutableState) => immutableState.setIn(['c', 'd'], 3)
      );

      const result = handler(state, payload, {payload});

      expect(result.toJS()).toEqual({
        a: 1,
        b: 2,
        c: {
          d: 3
        }
      });
    });
  });
});

jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');
jest.dontMock('DeskPRO/Component/Ampliflux/reducers/createReducer');

import { toImmutable } from 'helpers';

describe('Ampliflux createReducer()', () => {
  const { createReducer } = require('DeskPRO/Component/Ampliflux/reducers/createReducer');

  it('should return reducer function', () => {
    expect(createReducer({})).toEqual(jasmine.any(Function));
  });

  it('should accept any number of handler objects', () => {
    const initialState = {};
    const handlerObj = {
      ACTION_ONE: jasmine.createSpy('ACTION_ONE_handler').and.returnValue(toImmutable({})),
      ACTION_TWO: jasmine.createSpy('ACTION_TWO_handler').and.returnValue(toImmutable({}))
    };

    // passing several handler objects after the first initialState param
    const reducer = createReducer(initialState, {}, {}, {}, {}, {}, {}, {}, {}, handlerObj, {});
    reducer({}, {type: 'ACTION_ONE'});

    expect(handlerObj.ACTION_ONE).toHaveBeenCalled();
    expect(handlerObj.ACTION_TWO).not.toHaveBeenCalled();
  });

  describe('Handler objects', () => {
    it('should be {actionType: handlerFunction} maps', () => {
      const handlerObj = {
        TEST: jasmine.createSpy('TEST_handler').and.returnValue(toImmutable({}))
      };
      const reducer = createReducer({}, handlerObj);

      reducer({}, {type: 'TEST'});

      expect(handlerObj.TEST).toHaveBeenCalled();
    });
  });

  describe('Reducer function', () => {
    it('should pass initialState, payload and action params to handler functions, ' +
       'where payload === action.payload', () => // eslint-disable-line brace-style
    {
      const handlerObj = {
        TEST: jasmine.createSpy('TEST_handler').and.returnValue(toImmutable({}))
      };
      const reducer = createReducer({}, handlerObj);

      reducer({}, {type: 'TEST', payload: {}});

      expect(handlerObj.TEST).toHaveBeenCalled();
      expect(handlerObj.TEST.calls.argsFor(0).length).toEqual(3);
      expect(handlerObj.TEST.calls.argsFor(0)[1]).toBe(handlerObj.TEST.calls.argsFor(0)[2].payload);
    });

    it('should throw error when handler function doesn\'t return Immutable', () => {
      const reducer = createReducer({}, {TEST: () => ({})});
      expect(() => {
        reducer({}, {type: 'TEST'});
      }).toThrow(new Error('Reducers must return Immutable objects'));
    });

    it('should return a new state when there is an appropriate handler function', () => {
      const newState = toImmutable({});
      const reducer = createReducer({}, {TEST: () => newState});

      const result = reducer({}, {type: 'TEST'});

      expect(result).toEqual(newState);
    });

    it('should return the received state if there is no appropriate handler function', () => {
      const initialState = toImmutable({});
      const reducer = createReducer({}, {});

      const result = reducer(initialState, {type: 'NO_HANDLER_FN_FOR_THIS_ACTION'});

      expect(result).toEqual(initialState);
    });
  });
});

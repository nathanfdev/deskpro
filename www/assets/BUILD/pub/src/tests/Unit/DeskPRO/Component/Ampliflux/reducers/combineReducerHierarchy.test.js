// #define ~/ DeskPRO/Component/Ampliflux/reducers/

jest.autoMockOff();

import 'babel-polyfill';
import { toImmutable } from 'Helpers';

describe('Ampliflux combineReducerHierarchy()', () => {
  const { combineReducerHierarchy } = require('~/combineReducerHierarchy');
  const { createReducer } = require('~/createReducer');
  const redux = require('redux');

  let handlers;
  let hierarchy;
  beforeEach(() => {
    // create action handler function spies
    handlers = {
      one: jasmine.createSpy('ACTION_ONE_handler').and.returnValue(toImmutable({})),
      two: jasmine.createSpy('ACTION_TWO_handler').and.returnValue(toImmutable({})),
      three: jasmine.createSpy('ACTION_THREE_handler').and.returnValue(toImmutable({})),
      four: jasmine.createSpy('ACTION_FOUR_handler').and.returnValue(toImmutable({})),
    };

    // create reducers hierarchy using the spies
    hierarchy = {
      A: {
        B: {
          C: {
            D: createReducer({}, {ACTION_ONE: handlers.one}),
            E: {
              F: createReducer({}, {ACTION_TWO: handlers.two})
            }
          }
        }
      },
      G: {
        H: createReducer({}, {ACTION_THREE: handlers.three})
      },
      I: createReducer({}, {ACTION_FOUR: handlers.four})
    };
  });

  it('should call redux combineReducers', () => {
    spyOn(redux, 'combineReducers').and.callThrough();
    combineReducerHierarchy(hierarchy);
    expect(redux.combineReducers).toHaveBeenCalled();
  });

  it('should combine reducers hierarchy into reducer function', () => {
    const combined = combineReducerHierarchy(hierarchy);
    expect(combined).toEqual(jasmine.any(Function));
  });

  it('should preserve reducers from all of the hierarchy levels', () => {
    const combined = combineReducerHierarchy(hierarchy);
    expect(handlers.one).not.toHaveBeenCalled();
    expect(handlers.two).not.toHaveBeenCalled();
    expect(handlers.three).not.toHaveBeenCalled();
    expect(handlers.four).not.toHaveBeenCalled();

    combined({}, {type: 'ACTION_ONE'});
    combined({}, {type: 'ACTION_TWO'});
    combined({}, {type: 'ACTION_THREE'});
    combined({}, {type: 'ACTION_FOUR'});

    expect(handlers.one.calls.count()).toEqual(1);
    expect(handlers.two.calls.count()).toEqual(1);
    expect(handlers.three.calls.count()).toEqual(1);
    expect(handlers.four.calls.count()).toEqual(1);
  });

  it('should preserve action type to handler function correspondence', () => {
    const combined = combineReducerHierarchy(hierarchy);

    combined({}, {type: 'ACTION_ONE'});

    expect(handlers.one).toHaveBeenCalled();
    expect(handlers.two).not.toHaveBeenCalled();
    expect(handlers.three).not.toHaveBeenCalled();
    expect(handlers.four).not.toHaveBeenCalled();
  });
});

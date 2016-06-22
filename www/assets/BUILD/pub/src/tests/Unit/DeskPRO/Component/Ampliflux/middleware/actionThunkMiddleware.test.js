jest.dontMock('DeskPRO/Component/Ampliflux/middleware/actionThunkMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');

describe('Ampliflux Thunk Middleware', () => {
  const { actionThunkMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/actionThunkMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const args = {dispatch: () => {}, getState: () => {}};
  const nextHandler = actionThunkMiddleware(args);

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      it('should pass action to next handler when neither action nor its\' payload is a function', () => {
        const dummyAction = {type: 'test', payload: 'is not a function'};
        const actionHandler = nextHandler((action) => expect(action).toBe(dummyAction));
        actionHandler(dummyAction);
      });

      it('should return value of next when neither action nor its\' payload is a function', () => {
        const dummyAction = {type: 'test', payload: 'is not a function'};
        const actionHandler = nextHandler(() => 'test');
        expect(actionHandler(dummyAction)).toEqual('test');
      });

      describe('Function actions handling', () => {
        it('should call the received action function with dispatch and getState arguments', () => {
          const actionHandler = nextHandler();
          actionHandler((dispatch, getState) => {
            expect(dispatch).toBe(args.dispatch);
            expect(getState).toBe(args.getState);
          });
        });

        it('should return action function result', () => {
          const action = () => 'test';
          const actionHandler = nextHandler(val => val);
          const result = actionHandler(action);
          expect(result).toEqual('test');
        });
      });

      describe('Function action payload handling', () => {
        it('should call the received via action.payload function with dispatch and getState arguments', () => {
          const actionHandler = nextHandler(() => {});
          const action = createAction('TEST', (dispatch, getState) => {
            expect(dispatch).toBe(args.dispatch);
            expect(getState).toBe(args.getState);
          });
          actionHandler(action);
        });

        it('should return action with the payload value set to the payload function result', () => {
          const action = createAction('TEST', () => 'test');
          const actionHandler = nextHandler(val => val);
          const result = actionHandler(action);
          expect(result.payload).toEqual('test');
        });
      });
    });
  });
});

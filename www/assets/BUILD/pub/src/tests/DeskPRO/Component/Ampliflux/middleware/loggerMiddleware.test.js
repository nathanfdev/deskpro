jest.dontMock('DeskPRO/Component/Ampliflux/middleware/loggerMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');

// Create dummies for missing in node console object methods
console.debug = console.groupEnd = console.group = console.groupCollapsed = () => {
};

describe('Ampliflux Logger Middleware', () => {
  const { loggerMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/loggerMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const args = {
    dispatch: () => {
    }, getState: () => {
    }
  };
  const nextHandler = loggerMiddleware(args);

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      it('should pass action to the next handler when logger is disabled', () => {
        window.DP_ENABLE_ACTION_LOGGER = false;
        const dummyAction = createAction('TEST');
        const actionHandler = nextHandler((action) => expect(action).toBe(dummyAction));
        actionHandler(dummyAction);
      });

      describe('Logging', () => {
        beforeEach(() => {
          window.DP_ENABLE_ACTION_LOGGER = true;
          spyOn(console, 'error');
          spyOn(console, 'debug');
        });

        it('should pass action to the next handler when logger is enabled', () => {
          const dummyAction = createAction('TEST')();
          const actionHandler = nextHandler((action) => expect(action).toBe(dummyAction));
          actionHandler(dummyAction);
        });

        it('should log action handler result via console.debug', () => {
          const action = createAction('TEST')();
          const actionHandler = nextHandler(val => val);

          actionHandler(action);

          expect(console.debug).toHaveBeenCalled();
          expect(console.error).not.toHaveBeenCalled();
        });

        it('should warn via console.error when DSA has error', () => {
          const action = createAction('TEST')();
          action.error = true;
          const actionHandler = nextHandler(val => val);

          actionHandler(action);

          expect(console.error).toHaveBeenCalled();
        });

        it('should throw next handler\'s exception', () => {
          const action = createAction('TEST')();
          const error = new Error();
          const next = jasmine.createSpy('next_handler').and.callFake(() => {
            throw error;
          });
          const actionHandler = nextHandler(next);

          try {
            actionHandler(action);
          } catch (e) {
            expect(e).toBe(error);
          }
        });

        it('should log exceptions within action handlers via console.error', () => {
          const action = createAction('TEST')();
          const error = new Error();
          const next = jasmine.createSpy('next_handler').and.callFake(() => {
            throw error;
          });
          const actionHandler = nextHandler(next);

          try {
            actionHandler(action);
          } catch (e) { // eslint-disable-line no-empty
          } finally {
            expect(console.error).toHaveBeenCalled();
            expect(console.error.calls.argsFor(0)[1]).toBe(error);
          }
        });
      });
    });
  });
});

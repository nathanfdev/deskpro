jest.dontMock('DeskPRO/Component/Ampliflux/middleware/timeoutMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');

describe('Ampliflux Timeout Middleware', () => {
  const { timeoutMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/timeoutMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const nextHandler = timeoutMiddleware();

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      describe('Handling actions w/o meta.delay', () => {
        const dummyAction = createAction('TEST')();

        it('should pass action to the next handler', () => {
          const actionHandler = nextHandler((action) => expect(action).toBe(dummyAction));
          actionHandler(dummyAction);
        });

        it('should not call window.setTimeout', () => {
          spyOn(window, 'setTimeout');
          const actionHandler = nextHandler(val => val);

          actionHandler(dummyAction);

          expect(window.setTimeout).not.toHaveBeenCalled();
        });
      });

      describe('Handling actions with meta.delay', () => {
        const dummyActionFn = createAction('TEST', 'payload', () => ({delay: 10}));
        const dummyAction = dummyActionFn();

        it('should call window.setTimeout', () => {
          spyOn(window, 'setTimeout');
          const actionHandler = nextHandler();

          actionHandler(dummyAction);

          expect(window.setTimeout).toHaveBeenCalled();
        });

        it('should call action next handler within a function passed to the window.setTimeout', () => {
          spyOn(window, 'setTimeout');
          const next = jasmine.createSpy('next_handler');
          const actionHandler = nextHandler(next);

          actionHandler(dummyAction);
          const timeoutFn = window.setTimeout.calls.argsFor(0)[0];
          expect(next).not.toHaveBeenCalled();

          timeoutFn();
          expect(next).toHaveBeenCalled();
        });

        it('should return a function to clear timeout', () => {
          spyOn(window, 'clearTimeout');
          const actionHandler = nextHandler();
          const result = actionHandler(dummyAction);
          expect(window.clearTimeout).not.toHaveBeenCalled();

          result();

          expect(window.clearTimeout).toHaveBeenCalled();
        });
      });
    });
  });
});

jest.dontMock('DeskPRO/Component/Ampliflux/middleware/intervalMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');

describe('Ampliflux Interval Middleware', () => {
  const { intervalMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/intervalMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const nextHandler = intervalMiddleware();

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      describe('Handling actions w/o meta.interval', () => {
        const dummyAction = createAction('TEST')();

        it('should pass action to the next handler', () => {
          const actionHandler = nextHandler((action) => expect(action).toBe(dummyAction));
          actionHandler(dummyAction);
        });

        it('should not call window.setInterval', () => {
          spyOn(window, 'setInterval');
          const actionHandler = nextHandler(val => val);

          actionHandler(dummyAction);

          expect(window.setInterval).not.toHaveBeenCalled();
        });
      });

      describe('Handling actions with meta.interval', () => {
        const dummyActionFn = createAction('TEST', 'payload', () => ({interval: 10}));
        const dummyAction = dummyActionFn();

        it('should call window.setInterval', () => {
          spyOn(window, 'setInterval');
          const actionHandler = nextHandler();

          actionHandler(dummyAction);

          expect(window.setInterval).toHaveBeenCalled();
        });

        it('should call action next handler within a function passed to the window.setInterval', () => {
          spyOn(window, 'setInterval');
          const next = jasmine.createSpy('next_handler');
          const actionHandler = nextHandler(next);

          actionHandler(dummyAction);
          const intervalFn = window.setInterval.calls.argsFor(0)[0];
          expect(next).not.toHaveBeenCalled();

          intervalFn();
          expect(next).toHaveBeenCalled();
        });

        it('should return a function to clear interval', () => {
          spyOn(window, 'clearInterval');
          const actionHandler = nextHandler();
          const result = actionHandler(dummyAction);

          result();

          expect(window.clearInterval).toHaveBeenCalled();
        });
      });
    });
  });
});

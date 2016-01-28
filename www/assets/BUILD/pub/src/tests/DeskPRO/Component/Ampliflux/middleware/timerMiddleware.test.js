jest.dontMock('DeskPRO/Component/Ampliflux/middleware/timerMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');

describe('Ampliflux Timer Middleware', () => {
  const timerProperty = 'started';
  const { timerMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/timerMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const nextHandler = timerMiddleware();

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    /* @ToDo repair test
     describe('Action handler', () => {
     it('should init the specified property of DSA meta with a Date instance', () => {
     const actionFn = createAction('TEST');
     const actionHandler = nextHandler(val => val);
     expect(actionHandler(actionFn()).meta[timerProperty]).toEqual(jasmine.any(Date));
     });
     });
     */
  });
});

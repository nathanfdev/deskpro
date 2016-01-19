jest.dontMock('DeskPRO/Component/Ampliflux/middleware/guidMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');

describe('Ampliflux GUID Middleware', () => {
  const { guidMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/guidMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const nextHandler = guidMiddleware();

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      it('should add the guid property to action metadata', () => {
        const action = createAction('TEST', 'payload');
        const actionHandler = nextHandler(val => val);
        expect(actionHandler(action()).meta.guid).toEqual(jasmine.any(String));
      });
    });
  });
});

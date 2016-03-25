jest.dontMock('DeskPRO/Component/Ampliflux/middleware/redispatchDsaPayload');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/actionUtils');

describe('Ampliflux Redispatch DSA Payload Middleware', () => {
  const { redispatchDsaPayload } = require('DeskPRO/Component/Ampliflux/middleware/redispatchDsaPayload');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const args = {dispatch: jasmine.createSpy('dispatch'), getState: () => {}};
  const nextHandler = redispatchDsaPayload(args);

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      it('should redispatch DSA stored in action payload', () => {
        const inner = createAction('DSA_WITHIN_PAYLOAD')();
        const actionFn = createAction('TEST', inner);
        const actionHandler = nextHandler(val => val);

        actionHandler(actionFn());

        expect(args.dispatch.calls.count()).toEqual(1);
        expect(args.dispatch.calls.argsFor(0)[0]).toEqual(inner);
      });
    });
  });
});

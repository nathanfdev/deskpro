jest.dontMock('../../../../DeskPRO/Component/Ampliflux/actions.js');

describe('createAction', () => {

  const createAction = require('../../../../DeskPRO/Component/Ampliflux/actions.js').createAction;

  it('should create action creator functions', () => {
    const creator = createAction();
    expect(creator).toEqual(jasmine.any(Function));
  });

  describe('action type auto generation', () => {
    it('should generate action type when providing a single function argument', () => {
      const creator = createAction(function() {});
      expect(creator.actionType).toEqual(jasmine.any(String));
    });

    // Isn't it correct for createAction()? Test is failing.
    it('should generate action type for simple actions when no arguments are provided', () => {
      const creator = createAction();
      expect(creator.actionType).toEqual(jasmine.any(String));
    });
  });

  it('should allow to specify action type', () => {
    const creator = createAction('SAMPLE_ACTION');
    expect(creator.actionType).toEqual('SAMPLE_ACTION');
  });
});

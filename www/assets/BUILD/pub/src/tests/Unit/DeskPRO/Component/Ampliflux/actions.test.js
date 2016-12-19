jest.dontMock('DeskPRO/Component/Ampliflux/actions.js');

describe('[deprecated] Ampliflux v1 createAction()', () => {

  const createAction = require('DeskPRO/Component/Ampliflux/actions.js').createAction;

  it('should create action creator functions', () => {
    const creator = createAction();
    expect(creator).toEqual(jasmine.any(Function));
  });

  it('should allow to specify action type', () => {
    const creator = createAction('SAMPLE_ACTION');
    expect(creator.actionType).toEqual('SAMPLE_ACTION');
  });

  describe('action type ID auto generation', () => {

    it('should generate action type when providing a single function argument', () => {
      const creator = createAction(function() {});
      expect(creator.actionType).toEqual(jasmine.any(String));
    });

  });
});

jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Service/hash');

import { toImmutable } from 'Helpers/redux';

describe('Application module: hash service', () => {
  const hash = require('DeskPRO/Bundle/AgentBundle/Modules/Application/Service/hash');

  const state = toImmutable({
    tabs: {
      opened: 'categories'
    },
    list: {
      view: 'card',
      opened: [1, 2, 3],
      selected: [1, 3],
      active: 2
    }
  });
  const encodedState = 'tabs:opened-categories.list:view-card;opened-1,2,3;selected-1,3;active-2';

  describe('stateToString()', () => {
    it('should encode state into human readable string', () => {
      expect(hash.stateToString(state)).toEqual(encodedState);
    });

    it('should throw Error when trying to use a scalar instead of {} as a component state', () => {
      const test = () => hash.stateToString(toImmutable({component: 'scalar state'}));
      expect(test).toThrow('Each component state stored in URL must be an object!');
    });

    it('should throw Error when trying to put a reserved char such as .,:;- within a component name or state', () => {
      let test;

      test = () => hash.stateToString(toImmutable({'component.id': {dummy: 'data'}}));
      expect(test).toThrow('Component identifier "component.id" contains a reserved char');

      test = () => hash.stateToString(toImmutable({'component': {'dummy,param': 'data'}}));
      expect(test).toThrow('Component state key "dummy,param" contains a reserved char');

      test = () => hash.stateToString(toImmutable({'component': {dummy: 'some:data'}}));
      expect(test).toThrow('Component state value "some:data" contains a reserved char');

      test = () => hash.stateToString(toImmutable({'component': {dummy: ['nested;', 'data']}}));
      expect(test).toThrow('Component state value "nested;" contains a reserved char');

      test = () => hash.stateToString(toImmutable({'component': {dummy: ['nested-data']}}));
      expect(test).toThrow('Component state value "nested-data" contains a reserved char');
    });
  });

  describe('stateFromString()', () => {
    it('should decode state string representation into an Immutable object', () => {
      const encoded = hash.stateFromString(encodedState);
      expect(encoded.toJS()).toEqual(state.toJS());
    });

    it('should silently handle incorrect state representations', () => {
      const incorrect = [
        '123::test..---.==.,,:;qwe',
        '.=:,-;123::test..---.==.,,:;qwe',
        '/[\.\-,:;]/g',
        'id:te,st-data:what.ever'
      ];
      incorrect.forEach(str => expect(hash.stateFromString(str)).toEqual(jasmine.any(Object)));
    });
  });
});

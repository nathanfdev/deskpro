jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing');

import { toImmutable } from 'Helpers';

describe('Application module: routing service', () => {
  const routing = require('DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing');

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
      expect(routing.stateToString(state)).toEqual(encodedState);
    });

    it('should throw Error when trying to use a scalar instead of {} as a component state', () => {
      const test = () => routing.stateToString(toImmutable({component: 'scalar state'}));
      expect(test).toThrow(new Error('Each component state stored in URL must be an object!'));
    });

    it('should throw Error when trying to put a reserved char such as .,:;- within a component name or state', () => {
      let test;

      test = () => routing.stateToString(toImmutable({'component.id': {dummy: 'data'}}));
      expect(test).toThrow(new Error('Component identifier "component.id" contains a reserved char'));

      test = () => routing.stateToString(toImmutable({'component': {'dummy,param': 'data'}}));
      expect(test).toThrow(new Error('Component state key "dummy,param" contains a reserved char'));

      test = () => routing.stateToString(toImmutable({'component': {dummy: 'some:data'}}));
      expect(test).toThrow(new Error('Component state value "some:data" contains a reserved char'));

      test = () => routing.stateToString(toImmutable({'component': {dummy: ['nested;', 'data']}}));
      expect(test).toThrow(new Error('Component state value "nested;" contains a reserved char'));

      test = () => routing.stateToString(toImmutable({'component': {dummy: ['nested-data']}}));
      expect(test).toThrow(new Error('Component state value "nested-data" contains a reserved char'));
    });
  });

  describe('stateFromString()', () => {
    it('should decode state string representation into an Immutable object', () => {
      const encoded = routing.stateFromString(encodedState);
      expect(encoded.toJS()).toEqual(state.toJS());
    });

    it('should silently handle incorrect state representations', () => {
      const incorrect = [
        '123::test..---.==.,,:;qwe',
        '.=:,-;123::test..---.==.,,:;qwe',
        '/[\.\-,:;]/g',
        'id:te,st-data:what.ever'
      ];
      incorrect.forEach(str => expect(routing.stateFromString(str)).toEqual(jasmine.any(Object)));
    });
  });
});

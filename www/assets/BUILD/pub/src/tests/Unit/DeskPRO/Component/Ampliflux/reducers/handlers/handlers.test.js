jest.dontMock('DeskPRO/Component/Ampliflux/reducers/handlers');

import { toImmutable } from 'Helpers';
import Immutable from 'immutable';

describe('Ampliflux actions handlers', () => {
  const handlers = require('DeskPRO/Component/Ampliflux/reducers/handlers');

  let state;
  beforeEach(() => {
    state = toImmutable({
      a:          1,
      list:       ['a', 'b'],
      collection: [{ id: 1 }, { id: 2 }, { id: 3 }],
      elements:   [1, 2, 3],
      selected:   [1],

      b: {
        c: {
          d: 2,
          e: 3
        }
      },

      some: {
        bool: {
          prop: false
        }
      }
    });

    return state;
  });

  describe('setValue()', () => {
    it('should set scalar value in state object hierarchy', () => {
      const next = handlers.setValue('b.c.d', 42)(state);
      expect(next.toJS().b.c.d).toEqual(42);
    });

    it('should set scalar value in state object hierarchy (array prop key)', () => {
      const next = handlers.setValue(['b', 'c', 'd'], 42)(state);
      expect(next.toJS().b.c.d).toEqual(42);
    });

    it('should set object value in state object hierarchy', () => {
      const next = handlers.setValue('b.c', { d: { e: { f: 42 } } })(state);
      expect(next.toJS().b.c.d.e.f).toEqual(42);
      expect(next.toJS().b.c.e).toBeUndefined();
    });

    it('should convert POJO value to immutable', () => {
      const value        = { d: 'test' };
      const next         = handlers.setValue('b.c', value)(state);
      const valueInState = next.getIn(['b', 'c']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'd'])).toEqual('test');
    });

    it('should accept immutable objects as value', () => {
      const immutableValue = Immutable.fromJS({ d: 'test' });
      const next           = handlers.setValue('b.c', immutableValue)(state);
      const valueInState   = next.getIn(['b', 'c']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'd'])).toEqual('test');
    });
  });

  describe('mergeValue()', () => {
    it('should merge value in state object hierarchy', () => {
      const next = handlers.mergeValue('b.c', { d: { e: { f: 42 } } })(state);
      expect(next.toJS().b.c.d.e.f).toEqual(42);
      expect(next.toJS().b.c.e).toEqual(3);
    });

    it('should merge value in state object hierarchy (array prop key)', () => {
      const next = handlers.mergeValue(['b', 'c'], { d: { e: { f: 42 } } })(state);
      expect(next.toJS().b.c.d.e.f).toEqual(42);
      expect(next.toJS().b.c.e).toEqual(3);
    });

    it('should merge into root when first arg is falsy', () => {
      const next = handlers.mergeValue(null, { f: 42 })(state);
      expect(next.toJS().f).toEqual(42);
    });

    it('should merge deep if 3rd argument is true', () => {
      const next = handlers.mergeValue('b', { c: { d: { e: 42 } }, f: 4242 }, true)(state);
      expect(next.toJS().b.c.d.e).toEqual(42);
      expect(next.toJS().b.f).toEqual(4242);
    });

    it('should convert POJO value to immutable', () => {
      const value        = { c: { d: { e: 'test' } } };
      const next         = handlers.mergeValue('b', value)(state);
      const valueInState = next.getIn(['b', 'c']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'd', 'e'])).toEqual('test');
    });

    it('should accept immutable objects', () => {
      const immutableValue = Immutable.fromJS({ c: { d: { e: 'test' } } });
      const next           = handlers.mergeValue('b', immutableValue)(state);
      const valueInState   = next.getIn(['b', 'c']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'd', 'e'])).toEqual('test');
    });
  });

  describe('toggleBool()', () => {
    it('should toggle bool property', () => {
      let next = handlers.toggleBool('some.bool.prop')(state);
      expect(next.toJS().some.bool.prop).toEqual(true);

      next = handlers.toggleBool('some.bool.prop')(next);
      expect(next.toJS().some.bool.prop).toEqual(false);

      next = handlers.toggleBool('some.bool.prop')(next);
      expect(next.toJS().some.bool.prop).toEqual(true);
    });
  });

  describe('setPayload()', () => {
    it('should set property from payload', () => {
      const next = handlers.setPayload('b.c.d', 'e.f')(state, { e: { f: 42 } });
      expect(next.toJS().b.c.d).toEqual(42);
    });

    it('should set property from payload (array prop key)', () => {
      const next = handlers.setPayload(['b', 'c', 'd'], ['e', 'f'])(state, { e: { f: 42 } });
      expect(next.toJS().b.c.d).toEqual(42);
    });

    it('should set default value when property is missing in payload', () => {
      const next = handlers.setPayload('b.c.d', 'e.f', 'default')(state, { e: 'and f is missing' });
      expect(next.toJS().b.c.d).toEqual('default');
    });

    it('should convert POJO value to immutable', () => {
      const value        = { e: { f: 'test' } };
      const next         = handlers.setPayload('b.c.d', null)(state, value);
      const valueInState = next.getIn(['b', 'c', 'd']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'd', 'e', 'f'])).toEqual('test');
    });

    it('should accept immutable objects', () => {
      const immutableValue = Immutable.fromJS({ e: { f: 'test' } });
      const next           = handlers.setPayload('b.c.d', 'e')(state, immutableValue);
      const valueInState   = next.getIn(['b', 'c', 'd']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'd', 'f'])).toEqual('test');
    });
  });

  describe('pushPayloadToCollection()', () => {
    it('should add value to collection', () => {
      const next = handlers.pushPayloadToCollection('collection')(state, { id: 6 });
      expect(next.get('collection').size).toEqual(4);
      expect(next.get('collection').get(3).get('id')).toEqual(6);
    });
    it('should merge values to collection', () => {
      const next = handlers.pushPayloadToCollection('collection')(state, [{ id: 6 }, { id: 7 }]);
      expect(next.get('collection').size).toEqual(5);
      expect(next.get('collection').get(3).get('id')).toEqual(6);
      expect(next.get('collection').get(4).get('id')).toEqual(7);
    });
    describe('unique check', () => {
      it('should add value, unique check disabled', () => {
        const next = handlers.pushPayloadToCollection('collection')(state, { id: 2 });
        expect(next.get('collection').size).toEqual(4);
      });
      it('should skip value, unique check enabled', () => {
        const next = handlers.pushPayloadToCollection('collection', true)(state, { id: 2 });
        expect(next.get('collection').size).toEqual(3);
      });
      it('should skip some values, unique check enabled', () => {
        const next = handlers.pushPayloadToCollection('collection', true)(state, [{ id: 2 }, { id: 6 }]);
        expect(next.get('collection').size).toEqual(4);
        expect(next.get('collection').get(3).get('id')).toEqual(6);
      });
    });
  });

  describe('deletePayloadFromCollection()', () => {
    it('should delete first value from collection', () => {
      const next = handlers.deletePayloadFromCollection('collection')(state, Immutable.fromJS({ id: 1 }));
      expect(next.get('collection').size).toEqual(2);
      expect(next.get('collection').toJS()).toEqual([{ id: 2 }, { id: 3 }]);
    });
    it('should delete middle value from collection', () => {
      const next = handlers.deletePayloadFromCollection('collection')(state, Immutable.fromJS({ id: 2 }));
      expect(next.get('collection').size).toEqual(2);
      expect(next.get('collection').toJS()).toEqual([{ id: 1 }, { id: 3 }]);
    });
    it('should delete last value from collection', () => {
      const next = handlers.deletePayloadFromCollection('collection')(state, Immutable.fromJS({ id: 3 }));
      expect(next.get('collection').size).toEqual(2);
      expect(next.get('collection').toJS()).toEqual([{ id: 1 }, { id: 2 }]);
    });
    it('shouldn\'t delete value from collection', () => {
      const next = handlers.deletePayloadFromCollection('collection')(state, Immutable.fromJS({id: 5}));
      expect(next.get('collection').size).toEqual(3);
    });
  });

  describe('togglePayloadInCollection()', () => {
    it('should add value to collection', () => {
      const next = handlers.togglePayloadInCollection('list')(state, 'c');
      expect(next.get('list').size).toEqual(3);
      expect(next.get('list').includes('c')).toBeTruthy();
    });

    it('should remove value the collection if collection already contains it', () => {
      const next = handlers.togglePayloadInCollection('list')(state, 'a');
      expect(next.get('list').size).toEqual(1);
      expect(next.get('list').includes('b')).toBeTruthy();
    });
  });

  describe('handleMassAction()', () => {
    it('should select array of ids', () => {
      const next = handlers.handleMassAction()(state, { select: true, elements: state.get('elements') });
      expect(next.get('selected').size).toEqual(3);
    });

    it('should empty target when handling deselection', () => {
      let next = state;

      next = handlers.handleMassAction()(next, { select: true, elements: state.get('elements') });
      next = handlers.handleMassAction()(next);
      expect(next.get('selected').size).toEqual(0);
    });
  });

  describe('setFullPayload()', () => {
    it('should behave the same as setPayload(path, null)', () => {
      const payload = { e: { f: 42 } };
      const next1   = handlers.setFullPayload('b.c.d')(state, payload);
      const next2   = handlers.setPayload('b.c.d', null)(state, payload);
      expect(next1.toJS()).toEqual(next2.toJS());
    });

    it('should set full object payload', () => {
      const payload = { 1: 1, 2: 2 };
      const next    = handlers.setFullPayload('a')(state, payload);
      expect(next.toJS().a).toEqual(payload);
    });

    it('should set full scalar payload', () => {
      const next = handlers.setFullPayload('a')(state, 'value');
      expect(next.toJS().a).toEqual('value');
    });
  });

  describe('mergePayload()', () => {
    it('should merge payload part into state', () => {
      const next = handlers.mergePayload('b.c', 'h.i')(state, { h: { i: { j: 42 } } });
      expect(next.toJS().b.c.j).toEqual(42);
    });

    it('should merge the default value when data is missing in payload', () => {
      const next = handlers.mergePayload('b.c', 'h.i', { i: 'default' })(state, { h: 'and no .i here' });
      expect(next.toJS().b.c.i).toEqual('default');
    });

    it('should merge deep when 4th argument is true', () => {
      const next = handlers.mergePayload('b.c', null, null, true)(state, { h: 42 });
      expect(next.toJS().b.c.h).toEqual(42);
    });

    it('should convert POJO value to immutable', () => {
      const value        = { h: { i: { j: 'test' } } };
      const next         = handlers.mergePayload('b.c', 'h.i')(state, value);
      const valueInState = next.getIn(['b', 'c']);
      expect(Immutable.Iterable.isIterable(valueInState)).toBeTruthy();
      expect(next.getIn(['b', 'c', 'j'])).toEqual('test');
    });
  });

  describe('mergeFullPayload()', () => {
    it('should merge full payload and behave the same as mergePayload(path, null)', () => {
      const payload = { e: { f: 42 } };
      const next1   = handlers.mergeFullPayload('b.c.d')(state, payload);
      const next2   = handlers.mergePayload('b.c.d', null)(state, payload);
      expect(next1.toJS()).toEqual(next2.toJS());
    });
  });
});

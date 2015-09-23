jest.dontMock('DeskPRO/Component/Ampliflux/reducers/handlers');

import { toImmutable } from 'Helpers/redux';

describe('Ampliflux actions handlers', () => {
  const handlers = require('DeskPRO/Component/Ampliflux/reducers/handlers');

  let state;
  beforeEach(() => state = toImmutable({
    a: 1,
    b: {
      c: {
        d: 2,
        e: 3
      }
    }
  }));

  describe('setValue()', () => {
    it('should set scalar value in state object hierarchy', () => {
      const next = handlers.setValue('b.c.d', 42)(state);
      expect(next.toJS().b.c.d).toEqual(42);
    });

    it('should set object value in state object hierarchy', () => {
      const next = handlers.setValue('b.c', {d: {e: {f: 42}}})(state);
      expect(next.toJS().b.c.d.e.f).toEqual(42);
      expect(next.toJS().b.c.e).toBeUndefined();
    });
  });

  describe('mergeValue()', () => {
    it('should merge value in state object hierarchy', () => {
      const next = handlers.mergeValue('b.c', {d: {e: {f: 42}}})(state);
      expect(next.toJS().b.c.d.e.f).toEqual(42);
      expect(next.toJS().b.c.e).toEqual(3);
    });

    it('should merge into root when first arg is falsy', () => {
      const next = handlers.mergeValue(null, {f: 42})(state);
      expect(next.toJS().f).toEqual(42);
    });

    it('should merge deep if 3rd argument is true', () => {
      const next = handlers.mergeValue('b', {c: {d: {e: 42}}, f: 4242}, true)(state);
      expect(next.toJS().b.c.d.e).toEqual(42);
      expect(next.toJS().b.f).toEqual(4242);
    });
  });

  describe('setPayload()', () => {
    it('should set property from payload', () => {
      const next = handlers.setPayload('b.c.d', 'e.f')(state, {e: {f: 42}});
      expect(next.toJS().b.c.d).toEqual(42);
    });

    it('should set default value when property is missing in payload', () => {
      const next = handlers.setPayload('b.c.d', 'e.f', 'default')(state, {e: 'and f is missing'});
      expect(next.toJS().b.c.d).toEqual('default');
    });
  });

  describe('setFullPayload()', () => {
    it('should behave the same as setPayload(path, null)', () => {
      const payload = {e: {f: 42}};
      const next1 = handlers.setFullPayload('b.c.d')(state, payload);
      const next2 = handlers.setPayload('b.c.d', null)(state, payload);
      expect(next1.toJS()).toEqual(next2.toJS());
    });

    it('should set full object payload', () => {
      const payload = {1: 1, 2: 2};
      const next = handlers.setFullPayload('a')(state, payload);
      expect(next.toJS().a).toEqual(payload);
    });

    it('should set full scalar payload', () => {
      const next = handlers.setFullPayload('a')(state, 'value');
      expect(next.toJS().a).toEqual('value');
    });
  });

  describe('mergePayload()', () => {
    it('should merge payload part into state', () => {
      const next = handlers.mergePayload('b.c', 'h.i')(state, {h: {i: {j: 42}}});
      expect(next.toJS().b.c.j).toEqual(42);
    });

    it('should merge the default value when data is missing in payload', () => {
      const next = handlers.mergePayload('b.c', 'h.i', {i: 'default'})(state, {h: 'and no .i here'});
      expect(next.toJS().b.c.i).toEqual('default');
    });

    it('should merge deep when 4th argument is true', () => {
      const next = handlers.mergePayload('b.c', null, null, true)(state, {h: 42});
      expect(next.toJS().b.c.h).toEqual(42);
    });
  });

  describe('mergeFullPayload()', () => {
    it('should merge full payload and behave the same as mergePayload(path, null)', () => {
      const payload = {e: {f: 42}};
      const next1 = handlers.mergeFullPayload('b.c.d')(state, payload);
      const next2 = handlers.mergePayload('b.c.d', null)(state, payload);
      expect(next1.toJS()).toEqual(next2.toJS());
    });
  });
});

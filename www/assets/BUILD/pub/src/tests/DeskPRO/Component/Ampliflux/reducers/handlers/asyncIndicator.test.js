jest.dontMock('DeskPRO/Component/Ampliflux/reducers/handlers');

import { toImmutable } from 'Helpers';

describe('Ampliflux actions handlers', () => {
  const handlers = require('DeskPRO/Component/Ampliflux/reducers/handlers');

  describe('asyncIndicator()', () => {
    const payload = {};
    const action = {
      payload,
      meta: {
        sequence: null
      }
    };

    let state;
    beforeEach(() => {
      state = toImmutable({
        async: {
          status: {
            loading: null,
            success: null,
            isError: null,
            errorCode: null
          }
        }
      });
    });

    describe('Sequence indicators (loading, success, error) management', () => {
      const handler = handlers.asyncIndicator({
        loading: 'async.status.loading',
        success: 'async.status.success',
        isError: 'async.status.isError',
        errorCode: 'async.status.errorCode'
      });

      it('should set loading indicator to true and others to false when sequence starts', () => {
        action.meta.sequence = 'start';
        const result = handler(state, payload, action);
        expect(result.toJS().async.status.loading).toEqual(true);
        expect(result.toJS().async.status.success).toEqual(false);
        expect(result.toJS().async.status.isError).toEqual(false);
        expect(result.toJS().async.status.errorCode).toEqual(null);
      });

      it('should set success indicator to true and isError to false when sequence succeeds', () => {
        action.meta.sequence = 'success';
        const result = handler(state, payload, action);
        expect(result.toJS().async.status.loading).toEqual(null);
        expect(result.toJS().async.status.success).toEqual(true);
        expect(result.toJS().async.status.isError).toEqual(false);
        expect(result.toJS().async.status.errorCode).toEqual(null);
      });

      it('should set success indicator to false and populate isError and errorCode with error information', () => {
        action.meta.sequence = 'error';
        payload.response = {xhr: {status: 'test_status'}};
        const result = handler(state, payload, action);
        expect(result.toJS().async.status.loading).toEqual(null);
        expect(result.toJS().async.status.success).toEqual(false);
        expect(result.toJS().async.status.isError).toEqual(true);
        expect(result.toJS().async.status.errorCode).toEqual('test_status');
      });

      it('should set loading indicator to false when sequence is done', () => {
        action.meta.sequence = 'done';
        const result = handler(state, payload, action);
        expect(result.toJS().async.status.loading).toEqual(false);
        expect(result.toJS().async.status.success).toEqual(null);
        expect(result.toJS().async.status.isError).toEqual(null);
        expect(result.toJS().async.status.errorCode).toEqual(null);
      });
    });

    describe('Managing a single loading indicator via short syntax asyncIndicator(\'path.to.loading\')', () => {
      const handler = handlers.asyncIndicator('async.status.loading');

      it('should set loading indicator to true when sequence starts', () => {
        action.meta.sequence = 'start';
        const result = handler(state, payload, action);
        expect(result.toJS().async.status.loading).toEqual(true);
      });

      it('should set loading indicator to false when sequence is done', () => {
        action.meta.sequence = 'done';
        const result = handler(state, payload, action);
        expect(result.toJS().async.status.loading).toEqual(false);
      });
    });
  });
});

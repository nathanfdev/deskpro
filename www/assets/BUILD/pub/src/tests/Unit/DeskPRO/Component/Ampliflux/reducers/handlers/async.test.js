jest.dontMock('DeskPRO/Component/Ampliflux/reducers/handlers');

describe('Ampliflux actions handlers', () => {
  const handlers = require('DeskPRO/Component/Ampliflux/reducers/handlers');

  describe('async()', () => {
    const state = {};
    const payload = null;
    const action = {
      payload,
      meta: {
        sequence: null
      }
    };

    let sequenceHandlers;
    let handler;
    beforeEach(() => {
      sequenceHandlers = {
        start: jasmine.createSpy('start'),
        success: jasmine.createSpy('success'),
        error: jasmine.createSpy('error'),
        done: jasmine.createSpy('done'),
      };
      handler = handlers.async(sequenceHandlers);
    });

    it('should call start handler when receiving sequence "start" action', () => {
      action.meta.sequence = 'start';
      handler(state, payload, action);
      expect(sequenceHandlers.start).toHaveBeenCalled();
    });

    it('should call success handler when receiving sequence "success" action', () => {
      action.meta.sequence = 'success';
      handler(state, payload, action);
      expect(sequenceHandlers.success).toHaveBeenCalled();
    });

    it('should call error handler when receiving sequence "error" action', () => {
      action.meta.sequence = 'error';
      handler(state, payload, action);
      expect(sequenceHandlers.error).toHaveBeenCalled();
    });

    it('should call done handler when receiving sequence "done" action', () => {
      action.meta.sequence = 'done';
      handler(state, payload, action);
      expect(sequenceHandlers.done).toHaveBeenCalled();
    });

    it('should return the incoming state when sequence type is none of start, success, error or done', () => {
      action.meta.sequence = 'unknown';
      const result = handler(state, payload, action);
      expect(result).toBe(state);
    });
  });
});

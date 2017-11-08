jest.dontMock('DeskPRO/Component/Ampliflux/middleware/promiseMiddleware');
jest.dontMock('DeskPRO/Component/Ampliflux/actions/createAction');
jest.dontMock('lodash/uniqueId');

describe('Ampliflux Promise Middleware', () => {
  const { promiseMiddleware } = require('DeskPRO/Component/Ampliflux/middleware/promiseMiddleware');
  const { createAction } = require('DeskPRO/Component/Ampliflux/actions/createAction');
  const actionUtils = require('DeskPRO/Component/Ampliflux/actions/actionUtils');

  let args;
  let nextHandler;
  beforeEach(() => {
    args = {
      dispatch: jasmine.createSpy('dispatch'), getState: () => {
      }
    };
    nextHandler = promiseMiddleware(args);
  });

  it('should return a function to handle next', () => {
    expect(nextHandler).toEqual(jasmine.any(Function));
  });

  describe('Next handler', () => {
    it('should return a function to handle action', () => {
      const actionHandler = nextHandler();
      expect(actionHandler).toEqual(jasmine.any(Function));
    });

    describe('Action handler', () => {
      it('should pass action to next handler when payload isn\'t a promise and payload.promise isn\'t set', () => {
        const dummyAction = { type: 'TEST', payload: 'this is not a promise' };
        const actionHandler = nextHandler((action) => expect(action).toBe(dummyAction));
        actionHandler(dummyAction);
      });

      it('should dispatch promise result when action is not DSA', (done) => {
        spyOn(actionUtils, 'isDSA').and.callFake(() => false);
        const promise = new Promise(resolve => resolve('result'));
        const actionFn = createAction('TEST', promise);
        const actionHandler = nextHandler();

        actionHandler(actionFn());

        promise.then(() => {
          expect(args.dispatch.calls.count()).toEqual(1);
          expect(args.dispatch.calls.argsFor(0)[0]).toEqual('result');
          done();
        });
      });

      describe('Sequence', () => {
        let actionHandler;
        beforeEach(() => {
          spyOn(actionUtils, 'isDSA').and.callFake(() => true);
          actionHandler = nextHandler();
        });

        it('should be dispatched when action payload is a promise', (done) => {
          const promise = new Promise(resolve => resolve());
          const actionFn = createAction('TEST', promise);

          actionHandler(actionFn());

          promise.then(() => {
            expect(args.dispatch.calls.count()).toEqual(3);
            done();
          });
        });

        it('should be dispatched when action payload has the promise property (payload.promise)', (done) => {
          const promise = new Promise(resolve => resolve());
          const actionFn = createAction('TEST', { promise });

          actionHandler(actionFn());

          promise.then(() => {
            expect(args.dispatch.calls.count()).toEqual(3);
            done();
          });
        });

        it('should have a unique ID per promise', (done) => {
          const promise1 = new Promise(resolve => resolve());
          const promise2 = new Promise(resolve => resolve());

          actionHandler(createAction('TEST_1', promise1)());
          actionHandler(createAction('TEST_2', promise2)());

          promise1.then(() => {
            promise2.then(() => {
              expect(args.dispatch.calls.count()).toEqual(6);

              const uniqueIds = [];
              const idsCount = {};
              for (let i = 0; i < 6; i++) {
                const id = args.dispatch.calls.argsFor(i)[0].meta.sequenceId;
                if (uniqueIds.indexOf(id) === -1) {
                  uniqueIds.push(id);
                  idsCount[id] = 1;
                } else {
                  idsCount[id]++;
                }
              }
              expect(uniqueIds.length).toEqual(2);
              expect(idsCount[uniqueIds[0]]).toEqual(3);
              expect(idsCount[uniqueIds[1]]).toEqual(3);
              done();
            });
          });
        });

        it('should consists of start, success and done actions', (done) => {
          const promise = new Promise(resolve => resolve('result'));
          const actionFn = createAction('TEST', promise);

          actionHandler(actionFn());

          promise.then(() => {
            expect(args.dispatch.calls.count()).toEqual(3);
            expect(args.dispatch.calls.argsFor(0)[0].meta.sequence).toEqual('start');
            expect(args.dispatch.calls.argsFor(1)[0].meta.sequence).toEqual('success');
            expect(args.dispatch.calls.argsFor(2)[0].meta.sequence).toEqual('done');
            done();
          });
        });

        it('should share meta.sequenceId between start, success and done actions of the same promise', (done) => {
          const promise = new Promise(resolve => resolve('result'));
          const actionFn = createAction('TEST', promise);

          actionHandler(actionFn());

          promise.then(() => {
            expect(args.dispatch.calls.count()).toEqual(3);
            expect(args.dispatch.calls.argsFor(0)[0].meta.sequenceId)
              .toEqual(args.dispatch.calls.argsFor(1)[0].meta.sequenceId);
            expect(args.dispatch.calls.argsFor(1)[0].meta.sequenceId)
              .toEqual(args.dispatch.calls.argsFor(2)[0].meta.sequenceId);
            done();
          });
        });
      });
    });
  });
});

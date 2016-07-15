// #define ~tickets DeskPRO/Bundle/AgentBundle/Modules/Tickets

import { createAgentApp, toImmutable } from 'Helpers';
import { ticketsNavDemoState as demoState } from 'DemoState/Navigation/tickets';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';
import * as settingsSelectors from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/Selectors/settings';
import * as actions from '~tickets/Actions/navActions';
import * as selectors from '~tickets/Selectors/nav';

describe('Tickets Nav (Redux)', () => {

  let dispatch, getState;
  function bootstrap() {
    const app = createAgentApp(demoState);
    dispatch = app.dispatch;
    getState = app.getState;
  }

  beforeEach(bootstrap);

  it('should select filter set counts', () => {
    const filterSetCounts = selectors.filterSetsCountSelector(getState());
    expect(filterSetCounts.count()).toEqual(demoState.Tickets.nav.filterSetsCount.length);
  });

  it('should select stars count', () => {
    const starsCount = selectors.starsCountSelector(getState());
    expect(starsCount.count()).toEqual(demoState.Tickets.nav.starsCount.length);
  });

  it('should select labels', () => {
    const labels = selectors.labelsSelector(getState());
    expect(labels.count()).toEqual(demoState.Tickets.nav.labels.length);
  });

  it('should be loaded', () => {
    expect(selectors.isLoadedSelector(getState())).toEqual(true);
  });

  describe('initialLoad() action', () => {

    it('should send a GET request', () => {
      spyOn(settingsSelectors, 'filterSetGroupingsSettingsSelector').and.returnValue(toImmutable({}));
      spyOn(api, 'sendGet');

      dispatch(actions.initialLoad());

      expect(api.sendGet).toHaveBeenCalled();
    });

    it('should mark nav as loading', () => {
      dispatch(actions.initialLoad());
      expect(selectors.isLoadedSelector(getState())).toEqual(false);
    });
  });

  describe('Filter set editing pop up', () => {

    describe('startFilterEditing() action', () => {

      it('should change the edited filter ID state', () => {
        dispatch(actions.startFilterEditing(42));
        expect(selectors.editedFilterIdSelector(getState())).toEqual(42);
      });
    });

    describe('closeFilterEditing() action', () => {

      it('should flush the edited filter ID state', () => {
        dispatch(actions.startFilterEditing(42));
        dispatch(actions.closeFilterEditing());
        expect(selectors.editedFilterIdSelector(getState())).toEqual(null);
      });
    });

    describe('applyFilterEditingActionFactory() action factory', () => {

      const action = actions.applyFilterEditingActionFactory(1);

      it('should should construct an action', () => {
        expect(action).toEqual(jasmine.any(Function));
      });

      describe('Constructed action', () => {

        it('should close filter editing', () => {
          dispatch(actions.startFilterEditing(42));
          dispatch(action());
          expect(selectors.editedFilterIdSelector(getState())).toEqual(null);
        });

        it('should mark filter as reloading when applying a grouping', () => {
          dispatch(actions.startFilterEditing(42));
          dispatch(action('some_grouping'));
          expect(selectors.loadingFilterIdsSelector(getState()).toJS()).toContain(42);
        });

        it('should result into removed nested count when applying no grouping for a filter', () => {
          function selectNestedCount() {
            return selectors.filterSetsCountSelector(getState()).getIn([1, 'nested', 0, 'nested']).count();
          }
          dispatch(actions.startFilterEditing(10));
          expect(selectNestedCount()).toBeGreaterThan(0);

          dispatch(action());

          expect(selectNestedCount()).toEqual(0);
        });
      });
    });
  });
});

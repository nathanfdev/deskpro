import React, { Component, PropTypes } from 'react';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import { AgentsListContainer, TeamsListContainer, DepartmentsListContainer }
  from '../../../../Common/Components/Form/Lists';
import { FieldGroup, Popup} from '../../../../Common/Components/Popup';

import { connect } from 'react-redux';
@connect(state => ({
  currentParams: paramsSelector(state)
}))

export class AssignActionContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    setParams: PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams: PropTypes.object
  };

  onClick = (param, value) => {
    const { setParams, dispatch, currentParams, resetSingleAction } = this.props;

    if (currentParams.get('assign') && currentParams.get('assign').get(param) === value) {
      dispatch(resetSingleAction('assign'));
    } else {
      const setOfActions = currentParams.get('set_of_actions') ? currentParams.get('set_of_actions').toArray() : [];
      setOfActions.splice(setOfActions.indexOf('unassign'), 1);
      dispatch(setParams({ assign: { [param]: value } }));
    }
  };

  unAssign = (e) => {
    e.preventDefault();
    const { setParams, dispatch, resetSingleAction, currentParams } = this.props;
    const setOfActions = currentParams.get('set_of_actions') ? currentParams.get('set_of_actions').toArray() : [];
    dispatch(resetSingleAction('assign'));
    setOfActions.push('unassign');
    dispatch(setParams({ set_of_actions: [... new Set(setOfActions)] }));
  };

  render() {
    const { currentParams } = this.props;
    const assign = currentParams.get('assign');

    return (
      <div className="dpw-navigation-dropdown-panel">
        <Popup additionalClassNames="assign-form">
          <form>
            <div className="dpw--popup-content">
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-content-right">
                  <div className="dpw-popup-content-item">
                    <div className="dpw-popup-content-item-unassign-all">
                      <a href="#" className="checkbox-link" onClick={this.unAssign}>
                        <span>Unassign All</span>
                        <span className="unassign-all-icon"><span></span></span>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <div className="dpw--popup-item-collection">
                <FieldGroup>
                  <AgentsListContainer
                    selected={assign && assign.get('agent')}
                    onClick={this.onClick}
                    />
                  <TeamsListContainer
                    selected={assign && assign.get('team')}
                    onClick={this.onClick}
                    />
                  <DepartmentsListContainer
                    selected={assign && assign.get('department')}
                    onClick={this.onClick}
                    />
                </FieldGroup>
              </div>
            </div>
          </form>
        </Popup>
      </div>
    );
  }
}

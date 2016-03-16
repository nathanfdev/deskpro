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

  onChange(param, value) {
    const { setParams, dispatch, currentParams, resetSingleAction } = this.props;
    const nextParams = currentParams.get('assign') ? currentParams.get('assign').toJS() : {};

    if (currentParams.get('assign') && currentParams.get('assign').get(param) && value.length < 1) {
      delete nextParams[param];
    } else {
      nextParams[param] = value;
    }

    if (Object.keys(nextParams).length === 0) {
      dispatch(resetSingleAction('assign'));
    } else {
      dispatch(setParams({ assign: nextParams }));
    }
  }

  render() {
    return (
      <div className="dpw-navigation-dropdown-panel">
        <Popup additionalClassNames="assign-form">
          <form>
            <div className="dpw--popup-content">
              <div className="dpw--popup-item-collection">
                <FieldGroup>
                  <AgentsListContainer onChange={this.onChange.bind(this, 'agent')}/>
                  <TeamsListContainer onChange={this.onChange.bind(this, 'team')}/>
                  <DepartmentsListContainer onChange={this.onChange.bind(this, 'department')}/>
                </FieldGroup>
              </div>
            </div>
          </form>
        </Popup>
      </div>
    );
  }
}

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

  onClick(param, value) {
    const { setParams, dispatch, currentParams, resetSingleAction } = this.props;

    if (currentParams.get('assign') && currentParams.get('assign').get(param) === value) {
      dispatch(resetSingleAction('assign'));
    } else {
      dispatch(setParams({ assign: { [param]: value } }));
    }
  }

  render() {
    const { currentParams } = this.props;
    const assign = currentParams.get('assign');

    return (
      <div className="dpw-navigation-dropdown-panel">
        <Popup additionalClassNames="assign-form">
          <form>
            <div className="dpw--popup-content">
              <div className="dpw--popup-item-collection">
                <FieldGroup>
                  <AgentsListContainer selected={assign && assign.get('agent')}
                                       onClick={this.onClick.bind(this)}/>
                  <TeamsListContainer selected={assign && assign.get('team')}
                                      onClick={this.onClick.bind(this)}/>
                  <DepartmentsListContainer selected={assign && assign.get('department')}
                                            onClick={this.onClick.bind(this)}/>
                </FieldGroup>
              </div>
            </div>
          </form>
        </Popup>
      </div>
    );
  }
}

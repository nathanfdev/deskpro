import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Immutable from 'immutable';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import {
  AgentsListContainer, AgentTeamsListContainer, DepartmentsListContainer
}
  from '../../../../Common/Components/Form/Lists';
import { Popup } from '../../../../Common/Components/Popup';

import { connect } from 'react-redux';
@connect(state => ({
  currentParams: paramsSelector(state)
}))

export class AssignActionContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    setParams:         PropTypes.func.isRequired,
    resetSingleAction: PropTypes.func.isRequired,
    currentParams:     PropTypes.object
  };

  setAgent = (value = null) => {
    const { setParams, dispatch, currentParams } = this.props;
    const agent         = { agent: value ? value.first() : null };
    const currentAssign = currentParams.get('assign');
    const assign        = currentAssign ? Object.assign(currentAssign.toJS(), agent) : agent;
    dispatch(setParams({ assign }));
  };

  setTeam = (value = null) => {
    const { setParams, dispatch, currentParams } = this.props;
    const team          = { team: value ? value.first() : null };
    const currentAssign = currentParams.get('assign');
    const assign        = currentAssign ? Object.assign(currentAssign.toJS(), team) : team;
    dispatch(setParams({ assign }));
  };

  setDepartment = (value = null) => {
    const { setParams, dispatch, currentParams } = this.props;
    const department    = { department: value ? value.first() : null };
    const currentAssign = currentParams.get('assign');
    const assign        = currentAssign ? Object.assign(currentAssign.toJS(), department) : department;
    dispatch(setParams({ assign }));
  };

  unAssignAgent = (e) => {
    e.preventDefault();
    this.setAgent();
  };

  unAssignTeam = (e) => {
    e.preventDefault();
    this.setTeam();
  };

  unAssignDepartment = (e) => {
    e.preventDefault();
    this.setDepartment();
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
                <div className="dpw--popup-item-collection">
                  <div className="dpw--popup-content-left">
                    <div className="dpw-popup-content-item">
                      <h3>Assign to agent</h3>
                    </div>
                  </div>
                  <div className="dpw--popup-content-right">
                    <div className="dpw-popup-content-item">
                      <div className="dpw-popup-content-item-unassign-all">
                        <a href="#" className="checkbox-link" onClick={this.unAssignAgent}>
                          <span>Unassign agent</span>
                          <span className="unassign-all-icon"><span /></span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <AgentsListContainer
                    selected={assign && Immutable.Set([assign.get('agent')])}
                    onChange={this.setAgent}
                  />
                </div>
              </div>
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-item-collection">
                  <div className="dpw--popup-content-left">
                    <div className="dpw-popup-content-item">
                      <h3>Assign to agents team</h3>
                    </div>
                  </div>
                  <div className="dpw--popup-content-right">
                    <div className="dpw-popup-content-item">
                      <div className="dpw-popup-content-item-unassign-all">
                        <a href="#" className="checkbox-link" onClick={this.unAssignTeam}>
                          <span>Unassign agents team</span>
                          <span className="unassign-all-icon"><span /></span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <AgentTeamsListContainer
                    selected={assign && Immutable.Set([assign.get('team')])}
                    onChange={this.setTeam}
                  />
                </div>
              </div>
              <div className="dpw--popup-content-line">
                <div className="dpw--popup-item-collection">
                  <div className="dpw--popup-content-left">
                    <div className="dpw-popup-content-item">
                      <h3>Assign to department</h3>
                    </div>
                  </div>
                  <div className="dpw--popup-content-right">
                    <div className="dpw-popup-content-item">
                      <div className="dpw-popup-content-item-unassign-all">
                        <a href="#" className="checkbox-link" onClick={this.unAssignDepartment}>
                          <span>Unassign department</span>
                          <span className="unassign-all-icon"><span /></span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <DepartmentsListContainer
                    selected={assign && Immutable.Set([assign.get('department')])}
                    onChange={this.setDepartment}
                  />
                </div>
              </div>
            </div>
          </form>
        </Popup>
      </div>
    );
  }
}

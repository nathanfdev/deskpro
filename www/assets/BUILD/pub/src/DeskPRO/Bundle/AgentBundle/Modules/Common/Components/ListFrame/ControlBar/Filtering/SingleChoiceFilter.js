import React, { Component, PropTypes } from 'react';
import { Menu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import { SingleChoicePanel } from '../../../Form/SingleChoicePanel';
import { FilterItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/FilterItem';

import { connect } from 'react-redux';
@connect()
export class SingleChoiceFilter extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    stateValue: PropTypes.func.isRequired,
    setParamsAction: PropTypes.func.isRequired,
    setActiveItem: PropTypes.func,
    unsetParams: PropTypes.func.isRequired,
    activeItem: PropTypes.object,
    state: PropTypes.object.isRequired,
    filter: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, setParamsAction, filter, activeItem, setActiveItem, unsetParams, state } = this.props;
    const { label, icon, param, quickFilter, options } = filter;
    const params = [param];
    options.map(option=> {
      if (option.hasOwnProperty('nested')) {
        option.nested.map(opt => {
          params.push(opt.param);
        });
      }
    });
    const filterValue = state.get(param);
    const isActive = Boolean(filterValue);
    const onClick = (value) => () => dispatch(setParamsAction({ [param]: value }));

    return (
      <FilterItem activeItem={activeItem}
                  selected={state.get(param) ? [state.get(param)] : []}
                  setActiveItem={setActiveItem}
                  icon={icon || 'filter'}
                  label={label}
                  isActive={isActive}
                  resetFilter={unsetParams.bind(this, params)}>
        <Menu>
          <SingleChoicePanel title={label}
                             depth
                             currentParams={state.get(param)}
                             setParams={setParamsAction}
                             quickFilter={quickFilter}
                             item={filter}/>
        </Menu>
      </FilterItem>
    );
  }
}

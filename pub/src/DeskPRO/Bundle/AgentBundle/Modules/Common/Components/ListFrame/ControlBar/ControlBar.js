import React, { Component, PropTypes } from 'react';
import { CheckboxContainer } from './MassAction/CheckboxContainer';
import { SortingMenu } from './Sorting/SortingMenu';
import { FilteringMenuContainer } from './Filtering/FilteringMenuContainer';
import { ViewMenuContainer } from './View/ViewMenuContainer';
import { ListFrameMenu } from '../../ListFrameMenu';

export class ControlBar extends Component {
  static propTypes = {
    checkbox: PropTypes.shape({
      count: PropTypes.number.isRequired,
      action: PropTypes.func.isRequired
    }),
    sorting: PropTypes.shape({
      options: PropTypes.array.isRequired,
      sort: PropTypes.string.isRequired,
      order: PropTypes.string.isRequired,
      sortAction: PropTypes.func.isRequired,
      orderAction: PropTypes.func.isRequired
    }),
    filtering: PropTypes.shape({
      filters: PropTypes.arrayOf(PropTypes.shape({
        label: PropTypes.string.isRequired,
        type: PropTypes.oneOf(['date', 'labels', 'select']).isRequired,
        param: PropTypes.string.isRequired,
        icon: PropTypes.string
      })),
      setParamsAction: PropTypes.func.isRequired,
      state: PropTypes.object.isRequired
    }),
    view: PropTypes.shape({
      options: PropTypes.array.isRequired,
      viewMode: PropTypes.string.isRequired,
      viewModeAction: PropTypes.func.isRequired
    })
  };

  render() {
    return (
      <ListFrameMenu>
        <CheckboxContainer {...this.props.checkbox} />
        <SortingMenu {...this.props.sorting} />
        <li>
          <hr/>
        </li>
        <FilteringMenuContainer {...this.props.filtering} />
        <li>
          <hr/>
        </li>
        <ViewMenuContainer {...this.props.view} />
      </ListFrameMenu>
    );
  }
}


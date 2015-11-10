import React, { Component, PropTypes } from 'react';
import { SortingMenu } from './Sorting/SortingMenu';
import { ViewMenuContainer } from './View/ViewMenuContainer';
import { ListFrameMenu } from '../../ListFrameMenu';
import { CheckboxContainer } from './MassAction/CheckboxContainer';

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
    view: PropTypes.shape({
      options: PropTypes.array.isRequired,
      viewMode: PropTypes.string.isRequired,
      viewModeAction: PropTypes.func.isRequired,
      tableConfigurableFields: PropTypes.object.isRequired,
      tableVisibleFields: PropTypes.array.isRequired,
      tableToggleFieldVisibility: PropTypes.func.isRequired,
      cardConfigurableFields: PropTypes.object.isRequired,
      cardVisibleFields: PropTypes.array.isRequired,
      cardToggleFieldVisibility: PropTypes.func.isRequired
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
        <ViewMenuContainer {...this.props.view} />
      </ListFrameMenu>
    );
  }
}


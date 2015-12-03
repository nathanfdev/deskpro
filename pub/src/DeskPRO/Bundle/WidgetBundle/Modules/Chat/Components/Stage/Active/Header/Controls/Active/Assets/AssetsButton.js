import React, { PropTypes } from 'react';
import { ControlItem } from '../../ControlItem';

export class AssetsButton extends React.Component {

  static propTypes = {
    onClick: PropTypes.func
  };

  render() {
    return (
      <ControlItem  onClick={this.props.onClick}>
        <i className="fa fa-angle-double-left"></i>Assets
      </ControlItem>
    );
  }
}

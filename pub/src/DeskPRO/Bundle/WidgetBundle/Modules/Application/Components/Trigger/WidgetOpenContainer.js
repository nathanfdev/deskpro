import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { openWidget } from '../../Actions/dpWindowActions';

@connect()
export class WidgetOpenContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func
  };

  onClick = () => {
    this.props.dispatch(openWidget());
  };

  render() {
    const props = this.props;
    const child = props.children;
    const childProps = child.props;

    return React.cloneElement(child, {
      ...props,
      ...childProps,

      onClick: this.onClick
    });
  }
}

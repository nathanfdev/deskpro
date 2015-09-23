import React, {Component, PropTypes} from 'react';
import classNames from 'classnames';

export class Card extends Component {

  static propTypes = {
    type: PropTypes.string.isRequired,
    moving: PropTypes.string,
    minimized: PropTypes.string
  };

  render() {
    const {type, moving, minimized} = this.props;
    var classes = classNames('dpmw--single-card', {
      'dpmw--single-task-card': type === 'task',
      'floating': type === 'float',
      'minimized': minimized,
      'moving': moving
    });

    return (
      <div className={classes}>
        {this.props.children}
      </div>);
  }
}

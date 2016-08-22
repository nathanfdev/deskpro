import React, { PropTypes } from 'react';
import classNames from 'classnames';

class Toggle extends React.Component {
  static propTypes = {
    active:    PropTypes.bool,
    onChange:  PropTypes.func,
    elementId: PropTypes.string,
    children:  PropTypes.node,
    classes:   PropTypes.array
  };
  static defaultProps = {
    onChange() {
    }
  };

  onClick = () => {
    const newState = !this.props.active;
    this.props.onChange(newState);
  };

  render() {
    const { children, elementId, active, classes } = this.props;
    return  (
      <div className={classNames('ui', 'toggle', 'checkbox', classes)}>
        <input
          type="checkbox"
          id={elementId}
          checked={active}
          onChange={() => {}}
          className="hidden right"
        />
        <label onClick={this.onClick}>
          {children}
        </label>
      </div>
    );
  }
}
export default Toggle;

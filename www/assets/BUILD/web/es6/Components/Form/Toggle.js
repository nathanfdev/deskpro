import React, { PropTypes } from 'react';

class Toggle extends React.Component {
  static propTypes = {
    active: PropTypes.bool,
    onChange: PropTypes.func,
    elementId: PropTypes.string
  };
  static defaultProps = {
    onChange() {
    }
  };

  onClick() {
    const newState = !this.props.active;
    this.props.onChange(newState);
  }

  render() {
    const { children, elementId, active } = this.props;
    return  <div className="ui toggle checkbox">
      <input type="checkbox" id={elementId}
             checked={active} className="hidden right"/>
      <label onClick={this.onClick.bind(this)}>
        {children}
      </label>
    </div>
  }
}
export default Toggle;
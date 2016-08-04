import React, { PropTypes } from 'react';

class Toggle extends React.Component {
  static propTypes = {
    active:    PropTypes.bool,
    onChange:  PropTypes.func,
    elementId: PropTypes.string,
    children:  PropTypes.node
  };
  static defaultProps = {
    onChange() {
    }
  };

  constructor() {
    super();
    this.onClick = this.onClick.bind(this);
  }

  onClick() {
    const newState = !this.props.active;
    this.props.onChange(newState);
  }

  render() {
    const { children, elementId, active } = this.props;
    return  (
      <div className="ui toggle checkbox">
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

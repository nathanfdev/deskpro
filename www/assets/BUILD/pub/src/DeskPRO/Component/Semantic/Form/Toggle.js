import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

class Toggle extends React.Component {

  static propTypes = {
    active:    PropTypes.bool,
    disabled:  PropTypes.bool,
    onChange:  PropTypes.func,
    elementId: PropTypes.string,
    children:  PropTypes.node,
    className: PropTypes.string,
    checkbox:  PropTypes.bool
  };

  static defaultProps = {
    onChange: () => {},
    checkbox: false
    
  };

  constructor(props) {
    super(props);
    this.onClick = this.onClick.bind(this);
  }

  onClick() {
    const { active, disabled, onChange } = this.props;
    if (disabled) {
      return;
    }

    onChange(!active);
  };

  render() {
    const { children, elementId, active, disabled, className, checkbox } = this.props;

    return  (
      <div className={classNames('ui', 'checkbox', className, { disabled, toggle: !checkbox, checked: active })}>
        <input
          type="checkbox"
          id={elementId}
          checked={active}
          onChange={() => {}}
          className="hidden right"
        />
        <label onClick={this.onClick} htmlFor={elementId}>
          {children}
        </label>
      </div>
    );
  }
}

export default Toggle;

import React from 'react';
import classNames from 'classnames';

export default class MenuFooterOptions extends React.Component {

  static propTypes = {
    options: React.PropTypes.array,
    active: React.PropTypes.string,
    onClick: React.PropTypes.func,
    children: React.PropTypes.node
  };

  renderOption(option) {
    const { active } = this.props;
    const classes = classNames('dpwd-radio-button', {
      'active': (active && active === option.id)
    });

    return (
      <span className={classes} key={option.id}>
        <a href="#" onClick={option.onClick}>
          <span className="dpwd-radio-button-disc"/>
          <span className="radio-button-title">{option.label}</span>
        </a>
      </span>);
  }

  render() {
    const { children, options = [] } = this.props;

    return (
      <div className="dpw-navigation-dropdown-options-ordering">
        <span>{children}:</span>
        {options.map(option => this.renderOption(option))
        }
      </div>
    );
  }
}

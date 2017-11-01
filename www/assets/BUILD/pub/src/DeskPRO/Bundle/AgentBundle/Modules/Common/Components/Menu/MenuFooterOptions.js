import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class MenuFooterOptions extends React.Component {

  static propTypes = {
    options:  PropTypes.array,
    active:   PropTypes.string,
    onClick:  PropTypes.func,
    children: PropTypes.node
  };

  renderOption(option) {
    const { active } = this.props;
    const classes = classNames('dpwd-radio-button', {
      'active': (active && active === option.id)
    });

    return (
      <span className={classes} key={option.id}>
        <a href="#" onClick={option.onClick}>
          <span className="dpwd-radio-button-disc" />
          <span className="radio-button-title">{option.label}</span>
        </a>
      </span>
    );
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

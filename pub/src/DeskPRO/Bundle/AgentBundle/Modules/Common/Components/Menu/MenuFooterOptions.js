import React from 'react';
import classNames from 'classnames';

export default class MenuFooterOptions extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    options: React.PropTypes.array,
    active: React.PropTypes.string,
    onClick: React.PropTypes.func,
    children: React.PropTypes.node
  };

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    return (
      <div className="dpw-navigation-dropdown-options-ordering">
        <span>{this.props.children}:</span>
        {this.props.options.map((option) => {
          var classes = classNames('dpwd-radio-button', {
            'active': (this.props.active && this.props.active === option.id)
          });

          return (<span className={classes} key={option.id}>
                    <a href="#" onClick={(e) => {e.preventDefault(); option.onClick.bind(this)();}}>
                      <span className="dpwd-radio-button-disc"/>
                      <span className="radio-button-title">{option.label}</span>
                    </a>
                  </span>);
        })
        }
      </div>
    );
  }
}

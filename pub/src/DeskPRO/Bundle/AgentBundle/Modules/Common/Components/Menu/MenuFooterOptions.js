import React from 'react';

export default class MenuFooterLink extends React.Component {

  /**
   * Valid prop types
   * @type {Object}
   */
  static propTypes = {
    options: React.PropTypes.array,
    active: React.PropTypes.string,
    onClick: React.PropTypes.func,
    children: React.PropTypes.node
  }

  /**
   * Render the menu
   * @return {React.Element} The menu container
   */
  render() {
    return (<div className="dpw-navigation-dropdown-options-ordering">
      <span>{this.props.children}:</span>
      {this.props.options ?
        this.props.options.map((option) => {
          let className = 'dpwd-radio-button';
          if (this.props.active && this.props.active === option.id) {
            className += ' active';
          }

          return (<span className={className} key={option.id}>
                    <a href="#" onClick={option.onClick.bind(this)}>
                      <span className="dpwd-radio-button-disc" />
                      <span className="radio-button-title">{option.label}</span>
                    </a>
                  </span>);
        }) : ''}
    </div>);
  }
}

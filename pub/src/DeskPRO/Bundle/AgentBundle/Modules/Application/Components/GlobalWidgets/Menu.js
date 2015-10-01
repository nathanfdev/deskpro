import React from 'react';

const Menu = React.createClass({

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function(evt) {
    this.props.toggleDropdown();
  },

  render: function() {
    const {offset} = this.props;

    return (
      <div className={'dpw-navigation-dropdown'} style={{left: offset.left, top: offset.bottom}}>
        <ul>
          {this.props.children}
        </ul>
      </div>
    );
  }
});

module.exports = Menu;

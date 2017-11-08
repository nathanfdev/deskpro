import PropTypes from 'prop-types';
import React from 'react';

export class StatusCategory extends React.Component {

  static propTypes = {
    cat:               PropTypes.object,
    setStatusCategory: PropTypes.func,
    isActive:          PropTypes.bool
  };

  onClick = event => {
    event.preventDefault();
    const { setStatusCategory, cat } = this.props;

    setStatusCategory(cat.id);
  };

  render() {
    const { isActive, cat } = this.props;

    return (
      <div className="cat-checkbox-title">
        <input type="checkbox" checked={isActive} onChange={this.onClick} onTouchStart={this.onClick} />
        <a style={isActive ? {} : {}} onClick={this.onClick} onTouchStart={this.onClick}>
          {cat.title}
        </a>
      </div>
    );
  }
}

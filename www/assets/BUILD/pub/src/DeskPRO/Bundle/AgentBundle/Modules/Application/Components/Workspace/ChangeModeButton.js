import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';

export class ChangeModeButton extends React.Component {

  static propTypes = {
    type:       PropTypes.string.isRequired,
    title:      PropTypes.string.isRequired,
    activeType: PropTypes.string.isRequired,
    onChange:   PropTypes.func.isRequired,
    children:   PropTypes.array.isRequired
  };

  clickHandler = () => {
    const { type, onChange } = this.props;
    onChange(type);
  };

  render() {
    const { type, title, activeType, children } = this.props;
    const isActive = type === activeType;

    return (
      <div className={classNames('workspace-state-a', { active: isActive })} onClick={this.clickHandler}>

        <div className="workspace-state-screen">
          {isActive && <span className="active-workspace-mark"><i className="fa fa-check" /></span>}
          {children}
        </div>
        <span className="workspace-state-title">{title}</span>
      </div>
    );
  }
}

import React, { PropTypes } from 'react';

export class ChangeModeButton extends React.Component {

  static propTypes = {
    type: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    activeType: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    children: PropTypes.array.isRequired
  };

  render() {
    const {type, title, activeType, onChange, children} = this.props;
    const isActive = type === activeType;
    const classNames = 'workspace-state-a ' + (isActive ? 'active' : '');

    return (
      <div className={classNames} onClick={onChange.bind(this, type)}>
        <div className="workspace-state-screen">
          {isActive ? (<span className="active-workspace-mark"><i className="fa fa-check"></i></span>) : null}
          {children}
        </div>
        <span className="workspace-state-title">{title}</span>
      </div>
    );
  }
}

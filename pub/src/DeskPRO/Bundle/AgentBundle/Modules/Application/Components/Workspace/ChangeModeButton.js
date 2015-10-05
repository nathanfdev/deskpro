import React, { PropTypes } from 'react';

export class ChangeModeButton extends React.Component {

  static propTypes = {
    type: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    activeType: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired,
    children: PropTypes.object.isRequired
  };

  render() {
    const {type, title, activeType, onChange, children} = this.props;
    const classNames = 'workspace-state-a ' + (type === activeType ? 'active' : '');

    return (
      <div className={classNames} onClick={onChange.bind(this, type)}>
        <div className="workspace-state-screen">
          {children}
        </div>
        <span className="workspace-state-title">{title}</span>
      </div>
    );
  }
}

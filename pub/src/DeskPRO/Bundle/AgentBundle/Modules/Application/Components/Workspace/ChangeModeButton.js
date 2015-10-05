import React, { PropTypes } from 'react';

export class ChangeModeButton extends React.Component {

  static propTypes = {
    type: PropTypes.string.isRequired,
    activeType: PropTypes.string.isRequired,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const {type, activeType, onChange} = this.props;
    const classNames = 'workspace-state-a ' + (type === activeType ? 'active' : '');

    return (
      <div className={classNames} onClick={onChange.bind(this, type)}>
        <div className="workspace-state-screen">
          <span className="active-workspace-mark"><i className="fa fa-check"></i></span>
          <span className="workspace-state-item state-sidebar state-sidebar-hover active"><i className="fa fa-asterisk"></i></span>
          <span className="workspace-state-item left-column"></span>
          <span className="workspace-state-item right-column"></span>
        </div>
        <span className="workspace-state-title">{type}</span>
      </div>
    );
  }
}

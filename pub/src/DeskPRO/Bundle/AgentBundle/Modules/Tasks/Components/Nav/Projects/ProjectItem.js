import React, { PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class ProjectItem extends ListItem {

  static propTypes = {
    label: PropTypes.string.isRequired,
    count: PropTypes.number.isRequired
  };

  constructor(props) {
    const newProps = {
      ...props,
      label: <div part="label"><i className="fa fa-book" /> {props.label}</div>
    };

    super(newProps);

    this.state = {
      showEditIcon: false
    };
  }

  onShowEditIcon = () => {
    this.setState({
      showEditIcon: true
    });
  };

  onHideEditIcon = () => {
    this.setState({
      showEditIcon: false
    });
  };

  renderEditButton() {
    return (
      <div className="list-counter-bucket">
        <a href="#" className="edit-icon" onClick={null}>
          <i className="fa fa-cog" />
        </a>
      </div>
    );
  }

  renderCount(count, active) {
    return (
      <div onMouseEnter={this.onShowEditIcon} onMouseLeave={this.onHideEditIcon}>
        {this.state.showEditIcon ? this.renderEditButton() : super.renderCount(count, active)}
      </div>
    );
  }
}

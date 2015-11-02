import React, { PropTypes } from 'react';
import { ListItem } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class ProjectItem extends ListItem {

  static propTypes = {
    project: PropTypes.object.isRequired,
    onEdit: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

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
    const { project, onEdit } = this.props;

    return (
      <div className="list-counter-bucket">
        <a href="#" className="edit-icon" onClick={onEdit.bind(this, project)}>
          <i className="fa fa-cog" />
        </a>
      </div>
    );
  }

  renderCount(count, active) {
    return (
      <div onMouseEnter={this.onShowEditIcon} onMouseLeave={this.onHideEditIcon}>
        {this.state.showEditIcon
          ? this.renderEditButton()
          : super.renderCount(this.props.project.get('remaining'), active)
        }
      </div>
    );
  }
}

import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/RecordStores/Selectors/projectSelectors';
import { Detached } from 'DeskPRO/Component/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import { ProjectsList } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/Form/Fields/ProjectsList';
import {
  BaseForm,
  Header,
  Popup,
  FieldGroup,
  FullField,
  FloatField,
  CollectionField,
  QuickFilter,
  Unassign,
  AgentsList,
  AgentTeamsList,
  DepartmentsList
} from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Components/Form';

@connect(state => ({
  projects: allProjectsSelector(state)
}))

export class CardProject extends React.Component {

  static propTypes = {
    projectId: PropTypes.number,
    openBySingleClick: PropTypes.bool,
    onSetEditing: PropTypes.func,
    onChange: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      formOpened: false
    };
  }

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  openForm = () => {
    const { onSetEditing } = this.props;
    onSetEditing && onSetEditing(true);
    this.setState({
      formOpened: true
    });
  };

  closeForm = event => {
    const { onSetEditing } = this.props;
    onSetEditing && onSetEditing(false);
    if (this.isUnmounted) {
      return;
    }

    this.setState({
      formOpened: false
    });
  };

  onChange = (value) => {
    const { onChange } = this.props;
    onChange && onChange(value[0] || null);
  };

  render() {
    const { projects, projectId } = this.props;
    const project = projectId ? projects.get(projectId) : null;
    const selected = projectId ? [projectId] : [];
    const prop = {[this.props.openBySingleClick ? 'onClick' : 'onDoubleClick']: this.openForm};

    return (
      <div style={{display: 'inline-block'}}>
        <span className="dpw--card-disc"/>
        <span className="dpwd--card-line-item" ref="button" {...prop}>
          <i className="fa fa-book"/> {project ? project.get('title') : 'N/A'}
        </span>

        <Detached isOpen={this.state.formOpened}
                  positionTarget={this}
                  positionAt="left bottom"
                  zIndex={1002}>

          <ClickOut onClickOut={this.closeForm} ignoreNodes={[this.refs.button, 'popup']}>
            <Popup additionalClassNames="one-column">
              <CollectionField title="Project">
                <ProjectsList values={projects} onChange={this.onChange} selected={selected} />
              </CollectionField>
            </Popup>
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
